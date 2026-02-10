<?php

namespace Modules\WebService\App\Console\Commands;

use Illuminate\Console\Command;

class TestSeniorSoap extends Command
{
    protected $signature = 'webservice:test-senior {operation?} {--params=} {--user=} {--password=} {--auth_type=}';

    protected $description = 'Test Senior SOAP call (dev only)';

    public function handle()
    {
        $operation = $this->argument('operation') ?? 'consultarUsuario';
        $params = $this->option('params') ? json_decode($this->option('params'), true) : [];

        $callOptions = [];
        if ($this->option('user')) {
            $callOptions['user'] = $this->option('user');
        }
        if ($this->option('password')) {
            $callOptions['password'] = $this->option('password');
        }
        if ($this->option('auth_type')) {
            $callOptions['auth_type'] = $this->option('auth_type');
        }

        try {
            $client = app('senior.soap');
            $resp = $client->call($operation, $params, $callOptions);
            $this->info(json_encode($resp));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
