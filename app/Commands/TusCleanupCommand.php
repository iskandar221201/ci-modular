<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\TusUploader;

class TusCleanupCommand extends BaseCommand
{
    protected $group       = 'tus';
    protected $name        = 'tus:cleanup';
    protected $description = 'Remove expired incomplete TUS uploads';
    protected $usage       = 'php spark tus:cleanup';

    public function run(array $params): void
    {
        $uploader = new TusUploader();
        $result   = $uploader->cleanup();

        CLI::write(sprintf('Deleted %d expired incomplete uploads', $result['deleted']));

        if ($result['errors'] > 0) {
            CLI::write(sprintf('%d errors encountered', $result['errors']), 'red');
            exit(EXIT_ERROR);
        }

        exit(EXIT_SUCCESS);
    }
}
