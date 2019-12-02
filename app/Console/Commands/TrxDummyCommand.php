<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\AccessLog;

class TrxDummyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trx:dummy {row}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dummy transaction';

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
        $row = $this->argument('row');
        factory(AccessLog::class, $row)->create();
        $this->info('Created '.$row.' rows');
    }
}
