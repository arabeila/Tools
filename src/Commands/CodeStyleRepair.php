<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;

class CodeStyleRepair extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:code-repair';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修正代码格式';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        system('vendor\bin\phpcbf app');
    }
}
