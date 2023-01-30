<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class deleteTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usertable:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete users_table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('recent_users')->delete();

    }
}
