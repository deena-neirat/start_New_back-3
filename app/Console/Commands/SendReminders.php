<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\TwilioSMSController;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send_reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command send reminders for patient before his initial clinic';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now();
        $today->day++;
        $tomorrow =$today->format('Y-m-d');

                $tomorrow_reservations=DB::table('patients')
                ->join('reservations','reservations.patient_id','patients.id')
                ->join('initials','reservations.initial_id','initials.id')
                ->select('initials.*','reservations.id as reservation_id',
                            'patients.name' ,'patients.phone')
                ->where('status','=','reserved')
                ->whereDate('date','=',$tomorrow)
                ->orderBy('reservations.id', 'DESC')
                ->get();


         //   return response()->json(['initials'=> $initials ]);
      if($tomorrow_reservations){
           foreach($tomorrow_reservations as $initial){
               $name= explode(' ', $initial->name);

              (new TwilioSMSController)->initial_sms($initial  ,$initial->phone  ,$name[0] ,3);
           }

         }
    }




    
}
