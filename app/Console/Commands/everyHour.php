<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Http\Controllers\AdminController;
class everyHour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hour:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'End Poll';

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
     * @return int
     */
    public function handle()
    {
        $admin=new AdminController;
         $poll=DB::table('questions')->select('created_at')
            ->where('status', 1)->first();
            $t1=strtotime($poll->created_at);
            $t2=strtotime("now");
            $t=$t2-$t1;
            $t=$t/(3600);
            if($t>23){
                 DB::table('questions')
            ->where('status', 1)
            ->update(['status' => 0]);
            $admin->sendNotification("poll end","codecoy","poll");
            }
        return 0;
    }
}
