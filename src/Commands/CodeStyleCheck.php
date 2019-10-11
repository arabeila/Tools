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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        system('vendor\bin\phpcs --standard=customer.xml app');
    }
}
