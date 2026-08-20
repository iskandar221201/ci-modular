<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\WsServer;

class WsServeCommand extends BaseCommand
{
    protected $group       = 'websocket';
    protected $name        = 'ws:serve';
    protected $description = 'Start the WebSocket server (Ratchet)';
    protected $usage       = 'php spark ws:serve';

    public function run(array $params): void
    {
        if (!class_exists(\Ratchet\Server\IoServer::class)) {
            CLI::write('Ratchet is not installed. Run: composer require cboden/ratchet', 'red');
            exit(EXIT_ERROR);
        }

        CLI::write('Starting WebSocket server...', 'green');

        $server = new WsServer();
        $server->run();
    }
}
