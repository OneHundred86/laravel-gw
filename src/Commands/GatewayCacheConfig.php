<?php

namespace Oh86\GW\Commands;

use Oh86\GW\Config\GatewayConfig;
use Illuminate\Console\Command;

class GatewayCacheConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gw:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'gen gateway cache config';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $configFilePath = config('gw.config_file');
        GatewayConfig::genCacheConfig($configFilePath);
        $this->info("gen gateway cache config success");
    }
}
