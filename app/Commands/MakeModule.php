<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * MakeModule — scaffold a new module from _stubs/ templates.
 *
 * Usage:
 *   php spark make:module Posts            # full module (Controller, Model, Service, Transformer, Routes)
 *   php spark make:module Posts --contract # + Client, Contracts, Config/Services + central forward
 *   php spark make:module Posts --minimal  # Controller + Routes only
 *
 * Automatically updates app/Config/Autoload.php (namespace registration) and,
 * for --contract, app/Config/Services.php (service forward).
 */
class MakeModule extends BaseCommand
{
    protected $group       = 'modules';
    protected $name        = 'make:module';
    protected $description = 'Create a new module from _stubs/ templates';

    protected $usage        = 'make:module <ModuleName> [--contract] [--minimal] [--fe]';
    protected $arguments    = ['ModuleName' => 'The module name, e.g. Posts'];
    protected $options      = [
        '--contract' => 'Include Client, Contracts and Config/Services layers.',
        '--minimal'  => 'Create Controller and Routes only.',
        '--fe'       => 'Scaffold frontend module (views, store, service, composable, routes.js) + inject route.',
    ];

    public function run(array $params): void
    {
        $name = array_shift($params) ?? '';

        if ($name === '' || preg_match('/^[a-zA-Z0-9_]+$/', $name) !== 1) {
            CLI::error('Module name must be alphanumeric, e.g. php spark make:module Posts');
            exit(EXIT_ERROR);
        }

        $withContract = CLI::getOption('contract') !== null;
        $minimal      = CLI::getOption('minimal') !== null;
        $withFe       = CLI::getOption('fe') !== null;

        $plural   = ucfirst($name);
        $singular = preg_replace('/s$/', '', $plural) ?: $plural;

        $tokens = [
            '{{MODULES}}'       => $plural,
            '{{MODULES_LOWER}}' => lcfirst($plural),
            '{{MODULE}}'        => $singular,
            '{{MODULE_LOWER}}'  => lcfirst($singular),
        ];

        $targetDir  = APPPATH . 'Modules/' . $plural;
        $stubDir    = ROOTPATH . '_stubs';

        if (is_dir($targetDir)) {
            CLI::error("Module '{$plural}' already exists at {$targetDir}");
            exit(EXIT_ERROR);
        }

        $templates = [
            'Controllers' => 'Controllers/StubController.php',
            'Models'      => 'Models/StubModel.php',
            'Services'    => 'Services/StubService.php',
            'Transformers'=> 'Transformers/StubTransformer.php',
            'Client'      => 'Client/StubClient.php',
            'Contracts'   => 'Contracts/StubClientInterface.php',
            'Config'      => 'Config/Services.php',
            'Routes'      => 'Routes.php',
        ];

        if ($minimal) {
            $templates = ['Controllers' => 'Controllers/StubController.php', 'Routes' => 'Routes.php'];
        } elseif (! $withContract) {
            unset($templates['Client'], $templates['Contracts'], $templates['Config']);
        }

        foreach ($templates as $folder => $stubFile) {
            $source = $stubDir . '/' . $stubFile;
            if (! is_file($source)) {
                CLI::error("Stub template missing: {$source}");
                exit(EXIT_ERROR);
            }

            $content = str_replace(array_keys($tokens), array_values($tokens), file_get_contents($source));

            $targetFile = $targetDir . ($folder === 'Routes' ? '/Routes.php' : '/' . $folder . '/' . str_replace('Stub', $singular, basename($stubFile)));
            $targetDirForFile = dirname($targetFile);

            if (! is_dir($targetDirForFile)) {
                mkdir($targetDirForFile, 0755, true);
            }

            if (file_put_contents($targetFile, $content) === false) {
                CLI::error("Failed to write {$targetFile}");
                exit(EXIT_ERROR);
            }
        }

        // Register namespace in Autoload.php
        $this->registerNamespace($plural);

        // Forward service in central Config/Services.php for --contract
        if ($withContract && ! $minimal) {
            $this->forwardService($singular, $plural);
        }

        CLI::write('✓ Namespace registered  → app/Config/Autoload.php', 'green');
        if ($withContract && ! $minimal) {
            CLI::write('✓ Service forwarded     → app/Config/Services.php', 'green');
        }

        // Scaffold frontend module for --fe
        if ($withFe) {
            $this->scaffoldFrontend($plural, $singular, $tokens);
            $this->injectFrontendRoute($plural, $singular);
            CLI::write('✓ FE module created     → frontend/src/modules/' . lcfirst($plural), 'green');
            CLI::write('✓ FE route injected     → frontend/src/router/index.js', 'green');
        }

        CLI::write("✓ Module created        → {$targetDir}" . ($minimal ? ' (minimal)' : ''), 'green');
    }

    private function registerNamespace(string $plural): void
    {
        $path = APPPATH . 'Config/Autoload.php';
        $code = file_get_contents($path);
        $needle = "'App\\Modules\\{$plural}'";

        if (str_contains($code, $needle)) {
            return;
        }

        $line = "        {$needle} => APPPATH . 'Modules/{$plural}',\n";

        $closer = "    ];";

        if (! str_contains($code, $closer)) {
            CLI::error("Failed to update {$path}: psr4 array closer not found");
            exit(EXIT_ERROR);
        }

        $code = str_replace($closer, "\n" . $line . $closer, $code);

        file_put_contents($path, $code);
    }

    private function forwardService(string $singular, string $plural): void
    {
        $path = APPPATH . 'Config/Services.php';
        $code = file_get_contents($path);

        $method = strtolower($singular) . 'Client';

        $needle = "function {$method}(";

        if (str_contains($code, $needle)) {
            return;
        }

        $forward = <<<PHP

    public static function {$method}(bool \$getShared = true): \\App\\Modules\\{$plural}\\Contracts\\{$singular}ClientInterface
    {
        return \\App\\Modules\\{$plural}\\Config\\Services::{$method}(\$getShared);
    }
PHP;

        // Insert before the class closing brace (the last '}' in the file).
        $pos = strrpos($code, '}');
        if ($pos === false) {
            CLI::error("Failed to update {$path}: closing brace not found");
            exit(EXIT_ERROR);
        }

        $code = substr($code, 0, $pos) . $forward . "\n" . substr($code, $pos);

        file_put_contents($path, $code);
    }

    private function scaffoldFrontend(string $plural, string $singular, array $tokens): void
    {
        $stubBase   = ROOTPATH . '_stubs/fe';
        $targetBase = ROOTPATH . 'frontend/src/modules/' . lcfirst($plural);

        if (is_dir($targetBase)) {
            CLI::write("⚠ FE module already exists at {$targetBase} — skipped.", 'yellow');
            return;
        }

        // Map: stub file relative path => target file relative path
        $feTemplates = [
            'routes.js'              => 'routes.js',
            'services/stubApi.js'    => 'services/' . lcfirst($plural) . 'Api.js',
            'stores/stub.js'         => 'stores/' . lcfirst($plural) . '.js',
            'composables/useStub.js' => 'composables/use' . $plural . '.js',
            'views/StubListView.vue' => 'views/' . $singular . 'ListView.vue',
        ];

        foreach ($feTemplates as $stubRel => $targetRel) {
            $source = $stubBase . '/' . $stubRel;
            if (! is_file($source)) {
                CLI::error("FE stub missing: {$source}");
                exit(EXIT_ERROR);
            }

            $content    = str_replace(array_keys($tokens), array_values($tokens), file_get_contents($source));
            $targetFile = $targetBase . '/' . $targetRel;
            $targetDir  = dirname($targetFile);

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (file_put_contents($targetFile, $content) === false) {
                CLI::error("Failed to write FE file: {$targetFile}");
                exit(EXIT_ERROR);
            }
        }
    }

    private function injectFrontendRoute(string $plural, string $singular): void
    {
        $routerPath = ROOTPATH . 'frontend/src/router/index.js';

        if (! is_file($routerPath)) {
            CLI::error("Router file not found: {$routerPath}");
            exit(EXIT_ERROR);
        }

        $code       = file_get_contents($routerPath);
        $moduleName = lcfirst($plural);
        $importName = $moduleName . 'Routes';
        $importLine = "import {$importName} from '@/modules/{$moduleName}/routes.js'";

        // Idempotency: skip if already injected
        if (str_contains($code, $importLine)) {
            CLI::write("⚠ FE route already injected for {$plural} — skipped.", 'yellow');
            return;
        }

        // 1. Inject import after the last existing module route import
        $importPattern = "/^import\s+\w+Routes\s+from\s+'@\/modules\/[^']+'\s*$/m";
        preg_match_all($importPattern, $code, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            CLI::error("Cannot locate module route imports in {$routerPath}");
            exit(EXIT_ERROR);
        }

        $lastMatch = end($matches[0]);
        $insertPos = $lastMatch[1] + strlen($lastMatch[0]);
        $code      = substr($code, 0, $insertPos) . "\n" . $importLine . substr($code, $insertPos);

        // 2. Inject spread after the last existing route spread entry
        $spreadPattern = "/^\s+\.\.\.\w+Routes,\s*$/m";
        preg_match_all($spreadPattern, $code, $spreadMatches, PREG_OFFSET_CAPTURE);

        if (empty($spreadMatches[0])) {
            CLI::error("Cannot locate route spread entries in {$routerPath}");
            exit(EXIT_ERROR);
        }

        $lastSpread      = end($spreadMatches[0]);
        $spreadInsert    = "\n  ...{$importName},";
        $insertSpreadPos = $lastSpread[1] + strlen($lastSpread[0]);
        $code            = substr($code, 0, $insertSpreadPos) . $spreadInsert . substr($code, $insertSpreadPos);

        if (file_put_contents($routerPath, $code) === false) {
            CLI::error("Failed to update {$routerPath}");
            exit(EXIT_ERROR);
        }
    }
}