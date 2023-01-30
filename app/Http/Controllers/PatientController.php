<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Disease\Disease;
use App\Models\Initial\Initial;
use App\Models\Patient\Patient;
use App\Models\Student\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation\Reservation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{

///   create  patient account  (personal info )
// done
public function register(Request $request){

    $validator= Validator::make($request->all(),
    [
        'id'=>'required|unique:patients,id',
        'id'=>'required|unique:users,user_name',
        'firstName'=>'required',
        'middleName'=>'required',
        'lastName'=>'required',
        'gender'=>'required',
        'phone'=>'required|min:10|max:13',
        'date_of_birth'=>'required',
        'address'=>'required',
        'password'=>'required|min:8|confirmed',


    ]);

    if ($validator->fails()) {
        return response()->json(['msg'=> $validator->errors() ]);
       }

    // number().required().min(10).max(10

    $password= bcrypt($request->password);
   // $access_token= Str::random(64);

    $name= $request->firstName .' '. $request->middleName .' '. $request->lastName;
    $access_token=Str::random(64);

    $patient= Patient::create([
        'id'=>$request->id,
        'name'=>$name,
        'ar_name'=>$name,
        'gender'=>$request->gender,
        'phone'=>$request->phone,
        'date_of_birth'=>$request->date_of_birth,
        'address'=>$request->address,
        'password'=>$password,
        'initial_id'=>null,
        'access_token'=>$access_token,
        'verification_key'=>Str::random(6),

  ]);



     (new AuthController)->store($request->id  ,"patients");

     (new TwilioSMSController)->index($patient->verification_key  ,$patient->phone  ,$request->firstName ,2);

    return response()->json(['message'=>"account created, please verifiy your account",
                                'id'=> $request->id],200);

 //    return response()->json(['patient'=>$patient,
  //     'type'=>'patients'
   // ],200);

}



  //  done
  public function login(Request $request)
  {
      // check if patient id is exist
      $patient = Patient::find($request->user_name);


      //  if  yes
      if($patient){
                     //check password
          if(Hash::check($request->password, $patient->password)){


                    // get patient info   &&
                     $access_token= Str::random(64);
                     DB::table('patients')->where('id',$request->user_name)->update(['access_token'=> $access_token])  ;
                    $patient =  DB::table('patients')->select("*")->where('id',$request->user_name)->first()  ;

                 if($patient->verified == 'yes' ){
                  return response()->json(['access_token'=>$patient->access_token,
                                           'type'=>'patients',
                                            ],200);
                                            //->withHeaders(["access_token"=>$request->header("access_token")]);
                }else{
                    DB::table('patients')->where('id',$patient->id)->update(['verification_key'=>Str::random(6)])  ;
                    $patient = Patient::find($request->user_name);

                    (new TwilioSMSController)->index($patient->verification_key  ,$patient->phone  , $patient->name ,1 );

                    return response()->json(['message'=>"We have sent a verification code to your phone, please verifiy your account.",
                                             //'access_token'=> $access_token,
                                             'id'=>$request->user_name
                                            ]);
                }

          }else{
              return response()->json(['message'=>'password not correct' ]);
         }
      }else{
          return response()->json(['message'=>'user name not exist' ]);
      }





  }





// done
public function mobile_verification(Request $request){
    $patient=DB::table('patients')->select('*')
    ->where('id',$request->id)
    ->first();

    if($patient){
         if($patient->verification_key == $request->key){
              DB::table('patients')->where('id' ,'=', $patient->id)->update(['verified'=> 'yes'])  ;
              $patient=DB::table('patients')->select('*')
              ->where('id','=',$request->id)
              ->first();
             return response()->json(['access_token'=>$patient->access_token,
              'type'=>'patients'
              ],200);

         }else{
        return response()->json(['messages'=>'incorrect verification code' ]);
         }
    }else{
        return response()->json(['messages'=>'no token' ]);
    }

}






//+++++++++++++++++++++++++++++++++++++
//================================================
//================================================



//           show avilable initial clinic for selected date
public function show_selected_date_initials(Request $request){

    // check if  loggedin$
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();
    if($patient){

      $selcted_date = $request->date;
       $initials=DB::table('initials')
                  ->select('*')
                  ->whereDate('date','=',$selcted_date)
                  ->where('seats' ,'>','0')
                  ->get();

                  if(count($initials) == 0){
              return response()->json(['messages'=>"There are no clinics on this date"]);
                  }
              return response()->json(['initials'=>$initials]);
    }else{
        return response()->json(['messages'=>'no token' ]);
    }

}




//        reserve initial clinic
public function select_initial(Request $request){

    // check if  loggedin
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();


    if($patient){
        $old_initial = (new PatientController)->get_next_initial($request->access_token)->original['initial'];
      //  $initial=DB::table('initials')->select('seats')->where('id',$request->id)->first();

   if($old_initial != null){
           if($request->id == $old_initial->id ){
            return response()->json(['messages'=>'you have already booked an appointment at this clinic' ]);
           }
        (new PatientController)->delete_initial($request->access_token);
     }

                // if clinic exist
        $initial=DB::table('initials')->select('*')->where('id',$request->id)->first();
           if($initial){
               if($initial->seats > 0){

                         DB::table('initials')->where('id' ,'=',$request->id )->update(['seats'=> $initial->seats-1 ])  ;
                         DB::table('patients')->where('id' ,'=',$patient->id )
                         ->update([ 'bookings_num'=>$patient->bookings_num+1])  ;
                         //Reservation::create(['']);
                         DB::table('reservations')->insert(['initial_id'=>$request->id,
                                                             'patient_id'=>$patient->id
                                                           ])  ;
                            $name= explode(' ', $patient->name);
                            $ar_name= explode(' ', $patient->ar_name);

                         (new TwilioSMSController)->initial_sms($initial  ,$patient->phone  ,$ar_name[0] ,2);
                        // (new TwilioSMSController)->index("dt34Eb"  ,$patient->phone  ,$ar_name[0] ,4);

                          return response()->json(['messages'=>'updated'  , 'bookings_num'=>$patient->bookings_num+1]);


               }else{
                      return response()->json(['messages'=>'no seats' ]);
                    }


            }else{
                    return response()->json(['messages'=>'no initials' ]);
                 }

    }else{
        return response()->json(['messages'=>'no token' ]);
    }



}




 //  delete  initial clinic reservation  from  patient table
 public function delete_initial( $access_token){


    // check if  loggedin
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();

    if($patient){
   $initial = (new PatientController)->get_next_initial($access_token)->original['initial'];


           if($initial){
               if($initial->seats < 7){

                         DB::table('initials')->where('id' ,'=',$initial->id )->update(['seats'=> $initial->seats+1])  ;
                         DB::table('reservations')->where('id' ,'=', $initial->reservation_id )
                         ->update(['status'=>'deleted'])  ;


                          return response()->json(['messages'=>'deleted' ]);


               }else{
                      return response()->json(['messages'=>'no seats to deleted' ]);
                    }


            }else{
                    return response()->json(['messages'=>'no initials' ]);
                 }

    }else{
        return response()->json(['messages'=>'no token' ]);
    }


 }




  //  change it to post method

  public function get_next_treatments($access_token){

    // check if  loggedin
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();

    if($patient){
                // if clinic exist
        $treatments=DB::table('treatments')
        ->join('registerations','treatments.registeration_id','registerations.id')
        ->join('students','registerations.student_id','students.id')
        ->join('clinics','registerations.clinic_id','clinics.id')
        ->join('diseases','treatments.disease_id','diseases.id')
        ->select('students.name','students.phone','treatments.*',
        'clinics.start_time','clinics.end_time','clinics.day',
        'clinics.hall' ,'diseases.patient_id',)
        ->where('diseases.patient_id',$patient->id)
        ->get();

            $next_treatments = [];
           if($treatments){

               foreach($treatments as $treatment){
                     //  التاريخ بعده ما اجا
                if(now()->format('Y-m-d') < ($treatment->end_date) ){
                    $next_treatments[]=$treatment;
                    // اجا التاريخ بس ما خلص الوقت
                   }elseif (now()->format('Y-m-d') == ($treatment->end_date)) {
                    if(now()->format('H:i:s') <  $treatment->end_time){
                           $next_treatments[]=$treatment;
                                        }
                    }//elseif

            }//foreach


            return response()->json(['treatments'=>$next_treatments]);

          }else{
                    return response()->json(['messages'=>'no treatments' ]);
                 }

    }else{
        return response()->json(['messages'=>'no token' ]);
    }

 }




public function get_reserved_initials($access_token){
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();
    if($patient){
        $initials=DB::table('reservations')
        ->leftjoin('diseases','diseases.reservation_id','reservations.id')
        ->leftjoin('initials','reservations.initial_id','initials.id')
        ->select('reservations.*','diseases.id as disease_id',
        'initials.date','initials.day','initials.start_time','initials.end_time',)
        ->where('reservations.patient_id','=',$patient->id)
        ->where('status','=','reserved')
        ->orderBy('id', 'DESC')
        ->get();

        return response()->json(['initials'=>$initials ]);


    }else{
         return response()->json(['messages'=>'no token' ]);
    }
}


public function get_next_initial($access_token){
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();
    if($patient){
        $initial=DB::table('reservations')
        ->join('initials','reservations.initial_id','initials.id')
        ->select('initials.*','reservations.id as reservation_id')
        ->where('reservations.patient_id','=',$patient->id)
        ->where('status','=','reserved')
        ->orderBy('reservations.id', 'DESC')
        ->first();
         $date_time=$initial->date.' '.$initial->end_time;

        $firstDate = Carbon::parse($date_time);
        if($firstDate->greaterThan(now()) ){
            return response()->json(['initial'=> $initial ]);

        }
        return response()->json(['initial'=>null ]);



    }else{
         return response()->json(['messages'=>'no token' ]);
    }
}


//============================================




// get all info about  selectedd disease file

public function get_selected_file(Request $request){

    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();
    if($patient){

  // disease  treatments   ///  reg  ->> student
  $treatments=DB::table('treatments')
  ->join('registerations','treatments.registeration_id','registerations.id')
  ->join('students','registerations.student_id','students.id')
  ->join('clinics','registerations.clinic_id','clinics.id')
   ->select('students.name','students.phone','treatments.*',
  'clinics.start_time','clinics.end_time','clinics.day',
  'clinics.hall')
  ->where('treatments.disease_id',$request->disease_id)
  ->get();

    $disease = Disease::find($request->disease_id);
    if($disease->image != null){
        $disease->image = asset("storage").'/'.$disease->image;

    }

  return response()->json(['treatments'=>$treatments,
                             'disease'=>$disease]);


    }else{
        return response()->json(['messages'=>'no token' ]);
   }
}




// get  general info about all diseases
public function get_patient_files($access_token){
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();
    if($patient){
        $diseases =DB::table('diseases')
        ->select('diseases.created_at','diseases.id')
        ->where('patient_id',$patient->id)
        ->get();

    foreach($diseases as $disease){
        $treatments =DB::table('treatments')
        ->select('treatments.description','treatments.status')
        ->where('treatments.disease_id',$disease->id)
        ->get();
        $disease->treatments=$treatments;
    }



        return response()->json(['messages'=>$diseases ]);

    }else{
        return response()->json(['messages'=>'no token' ]);
   }
}






 public function send_comment(Request $request){

    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    $validator= Validator::make( $request->all() , [ 'txt'=>'required',]);

    if ($validator->fails()) {
        return response()->json(['msg'=> $validator->errors() ],404);
       }

    if($patient){
      return Http::post('http://127.0.0.1:5000/result', ['txt'=>$request->txt]);

    }else{
        return response()->json(['messages'=>'no token' ]);
    }
 }






public function update_initial(Request $request){
  $delete= (new PatientController)->delete_initial($request);
 $select=  (new PatientController)->select_initial($request);
 return response()->json(['delete'=>$delete->original['messages'] ,'select'=>$select->original['messages'] ]);
}






public function get_stars_topics($access_token){
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();
   if( $patient){
    $topics=DB::table('stars')->select('id','topic')->get();
     return response()->json(['topics'=>$topics]);
    }else{
    return response()->json(['messages'=>'no token' ]);
   }
}






public function stars_evaluation(Request $request){
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();
   if( $patient){
       $topic=DB::table('stars')->find($request->id);
        DB::table('stars')->where('id' ,'=',$request->id )->update(['sum'=> $topic->sum+$request->value,
                                                                       'clients_num'=>$topic->clients_num+1])  ;
       return response()->json(['stars'=>'evaluated']);
   }else{
    return response()->json(['messages'=>'no token' ]);
   }

}




public function cancel_treatment(Request $request){

    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if( $patient){
        $treatment=DB::table('treatments')
        ->where('id','=',$request->treatment_id)
        ->update(['status'=>'canceled']);
    return response()->json(['messages'=>"canceled"]);

    }
}





}
