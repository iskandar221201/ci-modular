<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\ClassLikeConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

/**
 * Deptrac — Module Boundary Enforcement
 *
 * Primary rule: Modules may NOT import from another module's internal layers.
 * Cross-module access must go through Contracts/ interfaces only.
 *
 * Allowed cross-module access:
 *   App\Modules\X\* → App\Modules\Y\Contracts\*   (via interface — correct pattern)
 *   App\Modules\X\* → App\Shared\*                (base classes / traits)
 *   App\Modules\X\* → App\Libraries\*             (utilities)
 *
 * Disallowed cross-module access:
 *   App\Modules\X\* → App\Modules\Y\Services\*    (direct service coupling)
 *   App\Modules\X\* → App\Modules\Y\Models\*      (direct model access)
 *   App\Modules\X\* → App\Modules\Y\Controllers\* (controller cross-dependency)
 *   App\Modules\X\* → App\Modules\Y\Transformers\*(transformer cross-dependency)
 *   App\Modules\X\* → App\Modules\Y\Client\*      (Client is implementation detail)
 *
 * NOTE: Deptrac implicitly allows dependencies between classes in the SAME layer.
 * To enforce boundaries between modules, we dynamically scan `app/Modules/*` and
 * generate isolated layers (e.g. `Auth_Services`, `Users_Services`) instead of a single 
 * generic layer (`ModuleServices`). This ensures strict isolation without needing to 
 * manually update this config when new modules are added.
 */
return static function (DeptracConfig $config): void {
    $modules = array_map('basename', glob(__DIR__ . '/app/Modules/*', GLOB_ONLYDIR));

    $layers = [];
    $rulesets = [];

    // ── Shared infrastructure (accessible from anywhere) ───────────
    $layers[] = $shared = Layer::withName('Shared')->collectors(
        ClassLikeConfig::create('App.Shared.*'),
    );
    $layers[] = $libraries = Layer::withName('Libraries')->collectors(
        ClassLikeConfig::create('App.Libraries.*'),
    );
    $layers[] = $appConfig = Layer::withName('Config')->collectors(
        ClassLikeConfig::create('^(App.Config|Config).*'),
    );

    // ── Module public surface (cross-module access ALLOWED) ────────
    $layers[] = $moduleContracts = Layer::withName('ModuleContracts')->collectors(
        ClassLikeConfig::create('App.Modules.[A-Za-z0-9_]+.Contracts.*'),
    );

    // ── Module config (forwarded by central Config\Services) ───────
    $layers[] = $moduleConfig = Layer::withName('ModuleConfig')->collectors(
        ClassLikeConfig::create('App.Modules.[A-Za-z0-9_]+.Config.*'),
    );

    $rulesets[] = Ruleset::forLayer($shared)->accesses($appConfig, $libraries);
    $rulesets[] = Ruleset::forLayer($libraries)->accesses($appConfig, $shared);
    $rulesets[] = Ruleset::forLayer($appConfig)->accesses($libraries, $moduleContracts, $moduleConfig);
    $rulesets[] = Ruleset::forLayer($moduleContracts)->accesses($shared);

    // We need to allow ModuleConfig to access all module-specific services and clients
    $moduleConfigAccesses = [$moduleContracts, $appConfig, $shared];

    // ── Dynamically create internal layers per module ───────────
    foreach ($modules as $module) {
        $modServices = Layer::withName("{$module}_Services")->collectors(
            ClassLikeConfig::create("App.Modules.{$module}.Services.*"),
        );
        $modModels = Layer::withName("{$module}_Models")->collectors(
            ClassLikeConfig::create("App.Modules.{$module}.Models.*"),
        );
        $modTransformers = Layer::withName("{$module}_Transformers")->collectors(
            ClassLikeConfig::create("App.Modules.{$module}.Transformers.*"),
        );
        $modClients = Layer::withName("{$module}_Clients")->collectors(
            ClassLikeConfig::create("App.Modules.{$module}.Client.*"),
        );
        $modControllers = Layer::withName("{$module}_Controllers")->collectors(
            ClassLikeConfig::create("App.Modules.{$module}.Controllers.*"),
        );

        array_push($layers, $modServices, $modModels, $modTransformers, $modClients, $modControllers);

        // Allow ModuleConfig to access this module's clients and services
        $moduleConfigAccesses[] = $modClients;
        $moduleConfigAccesses[] = $modServices;

        $rulesets[] = Ruleset::forLayer($modModels)->accesses($shared);
        $rulesets[] = Ruleset::forLayer($modTransformers)->accesses($shared);

        $rulesets[] = Ruleset::forLayer($modServices)->accesses(
            $shared,
            $libraries,
            $moduleContracts,
            $appConfig,
            $modModels,       // OWN module models
        );

        $rulesets[] = Ruleset::forLayer($modClients)->accesses(
            $moduleContracts,
            $modServices,     // OWN module services
            $shared,
        );

        $rulesets[] = Ruleset::forLayer($modControllers)->accesses(
            $modServices,     // OWN module services
            $modTransformers, // OWN module transformers
            $moduleContracts,
            $shared,
            $libraries,
            $appConfig,
        );
    }

    $rulesets[] = Ruleset::forLayer($moduleConfig)->accesses(...$moduleConfigAccesses);

    $config
        ->paths('./app')
        ->excludeFiles('#.*Test\.php$#')
        ->baseline('./deptrac.baseline.xml')
        ->layers(...$layers)
        ->rulesets(...$rulesets);
};