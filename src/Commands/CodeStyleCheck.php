<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;

class CodeStyleCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:code-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '代码风格检查';

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
        system('vendor\bin\phpcs --standard=customer.xml app');
    }
}
