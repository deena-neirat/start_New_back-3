<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Mail\TestEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use App\Models\Student\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\SecretarieController;
use App\Http\Controllers\RadiographerController;

class AuthController extends Controller
{

  public function login(Request $request)
 {
        // detrmine the user type
          $usertype=DB::table('users')->select('type')->where('user_name','=',$request->user_name)->first();
          $type=$usertype->type;


        if($type == null){
            return response()->json(['msg'=>"user not exist"]);
        }

        if($type =="patients"){
            return (new PatientController)->login( $request);
        }else{

            $user =  DB::table($type)->where('user_name' ,'=', $request->user_name)->first() ;
             if($user){


                 if(Hash::check($request->password, $user->password)  ){

                      $access_token= Str::random(64);
                      DB::table($type)->where('user_name' ,'=', $request->user_name)->update(['access_token'=> $access_token])  ;


                      return response()->json(['access_token'=>$access_token,
                                             'type'=>$type],200);
                    }else{
                       
                        return response()->json(['msg'=>'password not correct' ],404);
                    }

                }else{
                   return response()->json(['msg'=>'user name not exist' ],404);
                }
        }


}


public function store($user_name , $type){
        $user= User::create([
            'user_name'=>$user_name,
            'type'=>$type,

           ]);
}


     // send a code to change password

public function forget_password(Request $request){
    $user_name =$request->user_name;
    $usertype=DB::table('users')->select('*')->where('user_name','=',$request->user_name)->first();
   if( $usertype){
        $type=$usertype->type;
        if($type != 'patients'){
            DB::table($type)->where('user_name',$user_name)->update(['verification_key'=>Str::random(6)])  ;
            $user= DB::table($type)->select('*')->where('user_name' ,$user_name)->first() ;

            //'rasha.21102000@gmail.com' //donya.waleed2000@gmail.com   $user->email
            Mail::to('rasha.21102000@gmail.com')
            ->send(new TestEmail( $user->name , $user->verification_key))
            ;
            return response()->json(['message'=>"Please enter the verification code to be able to change your password",
                                     'user_name'=>$user_name]);

             return "email sends";
         }else{

              return response()->json(['message'=>(new PatientController)->forget_password( $user_name),
                                       'user_name'=>$user_name ]);

         }

   }else{
    return response()->json(['message'=>"user not exist",]);
   }
}




// check the reseved code
public function password_verification(Request $request){

       $user_name =$request->user_name;
       $usertype=DB::table('users')->select('*')->where('user_name','=',$request->user_name)->first();

    if( $usertype){
        $type=$usertype->type;

        if($type != 'patients'){
            $user= DB::table($type)->select('*')->where('user_name' ,$user_name)->first() ;
          }else{
            $user= DB::table('patients')->select('*')->where('id' ,$user_name)->first() ;
          }
            //return response()->json(['message'=>$user->verification_key,]);

        if($user->verification_key == $request->key){
            return response()->json(['message'=>"verified successfully",
                                     'user_name'=>$user_name]);
           }else{
            return response()->json(['message'=>'incorrect verification key',
                                     'user_name'=>$user_name ]);
           }



   }else{
    return response()->json(['message'=>"user not exist",]);
   }

}




//change  the oldpassword
public function change_forgotten_password(Request $request){
    $user_name =$request->user_name;
    $usertype=DB::table('users')->select('*')->where('user_name','=',$request->user_name)->first();

  if( $usertype){
     $type=$usertype->type;


     $validator= Validator::make($request->all(),
     ['password'=>'required|min:8|confirmed', ]);

    if ($validator->fails()) {
     return response()->json(['msg'=> $validator->errors() ]);
    }
    $password= bcrypt($request->password);



     if($type != 'patients'){
        $user= DB::table($type)->select('*')->where('user_name' ,$user_name)->first() ;
     }else{
        $user= DB::table('patients')->select('*')->where('id' ,$user_name)->first() ;
     }

      DB::table($type)->where('id' ,'=', $user->id)->update(['password'=> $password])  ;
        return response()->json(['msg'=>' password changed' ],200);





   }else{
    return response()->json(['message'=>"user not exist",]);
   }

}






// get the first and the third name  to show in navbar
public function get_name (Request $request){

    $user=DB::table($request->type)->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

   $name= explode(' ', $user->name);
   $user_name= "";
  for($i=0 ; $i < count($name) ; $i++ ){
    if($i != 1)
      $user_name = $user_name." ".$name[$i];
  }

    if($user){
            if($user->image){
                $user->image=  asset("storage").'/'.$user->image;
            }
        return response()->json(['user_name'=>$user_name,
                                 'user_image'=>$user->image]);
    }
}





public function get_profile(Request $request){

    if($request->type =="patients"){
        $user=DB::table($request->type)
        ->select('id','name','date_of_birth','address','phone','image')
        ->where('access_token','=',$request->access_token)
        ->first();
    }elseif($request->type =="students"){
        $user=DB::table($request->type)
        ->select('id','user_name','name','level','gpa','phone','email' , 'image')
         ->where('access_token','=',$request->access_token)
        ->first();
    }else{
        $user=DB::table($request->type)
        ->select('user_name','name','phone','email','image')
         ->where('access_token','=',$request->access_token)
        ->first();
    }

    if($user){
        if($user->image){
            $user->image=  asset("storage").'/'.$user->image;
        }
        return response()->json(['user_name'=>$user,
                                 'type'=>$request->type]);
    }
}


public function get_image(Request $request)
{

    $user =  DB::table($request->type)->where('access_token' ,'=', $request->access_token)->first() ;

    if($user==null){
        return response()->json(['msg'=>'no token']);
                    }
                    if($user->image != null){
                        $user->image= asset("storage").'/'.$user->image;
                    }

    return response()->json(['image'=>$user->image,],200);

}



public function set_image(Request $request)
{


        $user =  DB::table($request->type)->where('access_token' ,'=', $request->access_token)->first() ;

        if($user==null){
             return response()->json(['msg'=>'no token']);
                    }
      // image validation
        $validator= Validator::make($request->all(),
          [ 'image'=>'required|image', ]);

          if ($validator->fails()) {
            return response()->json(['msg'=> $validator->errors() ],400);
         }


         // update image
         $image = Storage::putFile($request->type, $request->file('image'));
         if($user->image != null){
            Storage::delete($request->type);
         }

         DB::table($request->type)->where('id' ,'=', $user->id)->update(['image'=> $image])  ;
         return response()->json(['image'=>'image updated'],200);

}



public function change_password(Request $request)
{
    $user =  DB::table($request->type)->where('access_token' ,'=',$request->access_token)->first() ;
     //  if login
    if($user){

        $validator= Validator::make($request->all(),
        [
            'old_password'=>'required',
            'password'=>'required|min:8|confirmed',

       ]);

       if ($validator->fails()) {
        return response()->json(['msg'=> $validator->errors() ],404);
       }

        if(Hash::check($request->old_password, $user->password) ){

            $password= bcrypt($request->password);
         DB::table($request->type)->where('id' ,'=', $user->id)->update(['password'=> $password])  ;

        return response()->json(['msg'=>' password changed' ],200);

        }else{
        return response()->json(['msg'=>'old password not correct' ],404);
         }


    }else{
        return response()->json(['msg'=>'you are not logged in' ],404);
    }

}




}
