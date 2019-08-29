<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;

class CodeDetector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:code-detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重复代码检测';

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
        system('vendor\bin\phpcpd app');
    }
}
