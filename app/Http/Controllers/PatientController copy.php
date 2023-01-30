<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Initial\Initial;
use App\Models\Patient\Patient;
use App\Models\Student\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{

///   create  patient account  (personal info )
public function register(Request $request){

    $validator= Validator::make($request->all(),
    [
        'id'=>'required|unique:patients,id',
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

     (new TwilioSMSController)->index($patient->verification_key  ,$patient->phone  ,$request->firstName ,1);

    return response()->json(['message'=>"account created, please verifiy your account",
                                'access_token'=> $access_token],200);

 //    return response()->json(['patient'=>$patient,
  //     'type'=>'patients'
   // ],200);

}



  //  login
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
                     $patient = Patient::find($request->user_name);

               if($patient->verified == 'yes' ){
                     // return info
                   $next_treatments = (new PatientController)->get_dental_data($patient->id);
                   $initial= DB::table('initials')->select('day','date','start_time','end_time')->where('id',$patient->initial_id)->first();


                     return response()->json(['patient'=>$patient,
                                          'type'=>'patients',
                                           'initial'=>$initial,
                                          'next_treatments'=>$next_treatments
                                         ],200);
                }else{
                    DB::table('patients')->where('id',$patient->id)->update(['verification_key'=>Str::random(6)])  ;
                    $patient = Patient::find($request->user_name);

                    (new TwilioSMSController)->index($patient->verification_key  ,$patient->phone  , $patient->name ,1 );

                    return response()->json(['message'=>"We have sent a verification code to your phone, please verifiy your account.",
                                             'access_token'=> $access_token]);
                }

          }else{
              return response()->json(['message'=>'password not correct' ]);
         }
      }else{
          return response()->json(['message'=>'user name not exist' ]);
      }





  }

// logout
public function logout(Request $request)
{      // check if there is an access token in header
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();


    // logout
    if($patient){
        DB::table('patients')->where('access_token','=' ,$request->access_token)->update(['access_token'=>null])  ;

        return response()->json(['messages'=>'logout' ]);

      }else{
          return response()->json(['messages'=>'no token']);
      }



}

public function change_password(Request $request)
{
    $patient =  DB::table('patients')->where('access_token' ,'=',$request->access_token)->first() ;
     //  if login
    if($patient){

        $validator= Validator::make($request->all(),
        [
            'old_password'=>'required',
            'password'=>'required|min:8|confirmed',

       ]);

       if ($validator->fails()) {
        return response()->json(['msg'=> $validator->errors() ],404);
       }

        if(Hash::check($request->old_password, $patient->password) ){

            $password= bcrypt($request->password);
         DB::table('patients')->where('id' ,'=', $patient->id)->update(['password'=> $password])  ;

        return response()->json(['msg'=>' password changed' ],200);

        }else{
        return response()->json(['msg'=>'old password not correct' ],404);
         }


    }else{
        return response()->json(['msg'=>'you are not logged in' ],404);
    }

}


public function mobile_verification(Request $request){
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if($patient){
         if($patient->verification_key == $request->key){
              DB::table('patients')->where('id' ,'=', $patient->id)->update(['verified'=> 'yes'])  ;
              $patient=DB::table('patients')->select('*')
              ->where('access_token','=',$request->access_token)
              ->first();
             return response()->json(['patient'=>$patient,
              'type'=>'patients'
              ],200);

         }else{
        return response()->json(['messages'=>'incorrect verification code' ]);
         }
    }else{
        return response()->json(['messages'=>'no token' ]);
    }

}




// xray image  version1
public function set_image(Request $request)
{   // update image
         $image = Storage::putFile('patient', $request->file('image'));
         DB::table('diseases')->where('patient_id' ,'=', $request->id)->update(['image'=>$image])  ;

         return response()->json(['image'=>'image updated'],200);

}


//======================================================================================================
//======================================================================================================
//======================================================================================================
//======================================================================================================
//  بطل الهن لازمة

public function forget_password($user_name){
    $patient= DB::table('patients')->select('*')->where('id' ,$user_name)->first() ;
    if( $patient){
        DB::table('patients')->where('id',$patient->id)->update(['verification_key'=>Str::random(6)])  ;
         $patient= DB::table('patients')->select('*')->where('id' ,$user_name)->first() ;

      $x=  (new TwilioSMSController)->index($patient->verification_key  ,$patient->phone  ,$patient->name , 2);
     // return response()->json(['x'=>$x,]);
        return "Please enter the verification code to be able to change your password";
      //  return response()->json(['message'=>"Please enter the verification code to be able to change your password",]);
    }else{
        return 'user id not exist' ;
       // return response()->json(['message'=>'user id not exist' ]);
  }


}

public function password_verification(Request $request){
    $patient= DB::table('patients')->select('*')->where('id' ,$request->user_name)->first() ;
    if( $patient){
        if($patient->verification_key == $request->key){
            return response()->json(['message'=>"verified successfully",]);
           }else{
            return response()->json(['message'=>'incorrect verification key' ]);
           }
     }else{
        return response()->json(['message'=>'user id not exist' ]);
    }


}


public function change_forgotten_password(Request $request){
    $patient= DB::table('patients')->select('*')->where('id' ,$request->user_name)->first() ;

   if($patient){

       $validator= Validator::make($request->all(),
       ['password'=>'required|min:8|confirmed', ]);

      if ($validator->fails()) {
       return response()->json(['msg'=> $validator->errors() ],404);
      }

        $password= bcrypt($request->password);
        DB::table('patients')->where('id' ,'=', $patient->id)->update(['password'=> $password])  ;
        return response()->json(['msg'=>' password changed' ],200);


   }else{
       return response()->json(['msg'=>'you are not logged in' ],404);
   }

}

//======================================================================================================
//======================================================================================================
//======================================================================================================
//======================================================================================================


//  show initial clinics
public function show_initial($access_token){

    // check if  loggedin$
      $patient=DB::table('patients')->select('*')
      ->where('access_token','=',$access_token)
      ->first();


      if($patient){
          $initials=DB::table('initials')->select('*')->get();

         if($initials){
              return response()->json(['initials'=>$initials ]);
          }else{
              return response()->json(['messages'=>'no initials' ]);
           }

      }else{
          return response()->json(['messages'=>'no token' ]);
      }


}





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









public function select_initial(Request $request){

    // check if  loggedin
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if($patient){
                // if clinic exist
        $initial=DB::table('initials')->select('seats')->where('id',$request->id)->first();
           if($initial){
               if($initial->seats > 0){
                      if($patient->initial_id == null){

                         DB::table('initials')->where('id' ,'=',$request->id )->update(['seats'=> $initial->seats-1 ])  ;
                         DB::table('patients')->where('id' ,'=',$patient->id )
                         ->update(['initial_id'=>$request->id , 'bookings_num'=>$patient->bookings_num+1])  ;

                         return response()->json(['messages'=>'updated'  , 'bookings_num'=>$patient->bookings_num+1]);
                      }else{
                        return response()->json(['messages'=>'you are already has a initial clinic' ]);
                      }

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




 public function delete_initial(Request $request){


    // check if  loggedin
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if($patient){
                // if clinic exist
        $initial=DB::table('initials')->select('seats')->where('id',$patient->initial_id)->first();
           if($initial){
               if($initial->seats < 7){
                        if($patient->initial_id != null){
                         DB::table('initials')->where('id' ,'=',$patient->initial_id )->update(['seats'=> $initial->seats+1])  ;
                         DB::table('patients')->where('id' ,'=',$patient->id )
                         ->update(['initial_id'=>null])  ;

                        }

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

 public function show_treatments($access_token){

    // check if  loggedin
    $patient=DB::table('patients')->select('*')
    ->where('access_token','=',$access_token)
    ->first();

    if($patient){
                // if clinic exist
        $treatments=DB::table('diseases')
        ->join('treatments','treatments.disease_id','diseases.id')
        ->select('diseases.*','treatments.*')
        ->where('diseases.patient_id',$patient->id)
        ->get();
           if($treatments){

            return response()->json(['treatments'=>$treatments ]);

          }else{
                    return response()->json(['messages'=>'no treatments' ]);
                 }

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





 public function get_dental_data($id){

        $treatments=DB::table('diseases')
        ->join('treatments','treatments.disease_id','diseases.id')
        ->join('registerations','treatments.registeration_id','registerations.id')
        ->join('students','registerations.student_id','students.id')
        ->join('clinics','registerations.clinic_id','clinics.id')
        ->select('treatments.start_date','treatments.end_date' ,'treatments.status',
        'students.name' ,'students.phone',
        'clinics.day','clinics.start_time','clinics.hall')
        ->where('diseases.patient_id',$id)
        ->where('treatments.status','=','not completed')
        ->get();
        return  $treatments;


 }




public function get_my_initial($patient_id){
      $patient = Patient::find($patient_id);
      if($patient->initial_id != null){
        $initial = Initial::find($patient->initial_id);

       if(now()->format('Y-m-d') < ($initial->date) ){
        return response()->json(['initial'=>$initial ]);

       }if (now()->format('Y-m-d') == ($initial->date)) {
          if(now()->format('H:i:s') <  $initial->start_time){
             return response()->json(['initial'=>$initial ]);
          }
       }
    DB::table('patients')->where('id',$patient->id)->update(['initial_id' => null , 'bookings_num'=>0]);



  }
}




public function update_initial(Request $request){
  $delete= (new PatientController)->delete_initial($request);
 $select=  (new PatientController)->select_initial($request);
 return response()->json(['delete'=>$delete->original['messages'] ,'select'=>$select->original['messages'] ]);
}






public function get_stars_topics(){
    $topics=DB::table('stars')->select('id','topics')->get();
 return response()->json(['topics'=>$topics]);
}

public function stars_evaluation(Request $request){
    $topic=DB::table('stars')->find($request->id);

    DB::table('stars')->where('id' ,'=',$request->id )->update(['sum'=> $topic->sum+$request->value,
                                                                       'clients_num'=>$topic->clients_num+1])  ;
 return response()->json(['stars'=>'evaluated']);
}






}
