<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Radiographer\Radiographer;
use Illuminate\Support\Facades\Validator;

class RadiographerController extends Controller
{

    public function store(Request $request)
    {

       $validator= Validator::make($request->all(),
           [
               'user_name'=>'required|unique:radiographers,user_name',
               'name'=>'required',
               'ar_name'=>'required',
               'email'=>'required',
               'password'=>'required|min:8|confirmed',

           ]);

           if ($validator->fails()) {
            return response()->json(['msg'=> $validator->errors() ],404);
           }
       // bcrypt  &  access_token
           $password= bcrypt($request->password);

       // create and login
       $radiographer= Radiographer::create([
           'user_name'=>$request->user_name,
           'name'=>$request->name,
           'ar_name'=>$request->ar_name,
           'email'=>$request->email,
           'password'=>$password,
           'image'=>null,
           'access_token'=>null,
          ]);
          (new AuthController)->store( $request->user_name ,"radiographers");

          return response()->json(['message'=>'created' ]);



    }


    public function login(Request $request)
    {

           $radiographer =  DB::table('radiographers')->where('user_name' ,'=', $request->user_name)->first() ;

            if($radiographer){

              if(Hash::check($request->password, $radiographer->password)  ){

             //     if($radiographer->access_token != null){
               //          return response()->json(['message'=>'already logged in' ]);
                 //      }
                      //  set access token
                    $access_token= Str::random(64);
                  DB::table('radiographers')->where('user_name' ,'=', $request->user_name)->update(['access_token'=> $access_token])  ;

                  // get student after change the access
                  $radiographer =  DB::table('radiographers')->where('user_name' ,'=', $request->user_name)->first() ;
                  if( $radiographer->image != null){
                       $radiographer->image=asset("storage").'/'.$radiographer->image;
                  }
                  // get student initials

                    return response()->json(['radiographer'=>$radiographer,
                         'type'=>'radiographers',
                         ],200);

                }else{
                    return response()->json(['msg'=>'password not correct' ],404);
               }
            }else{
                return response()->json(['msg'=>'user name not exist' ],404);
            }



    }



    public function logout(Request $request)
 {   // check if exist
    $radiographer=DB::table('radiographers')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

   // logout
    if($radiographer){
          DB::table('radiographers')->where('access_token','=' ,$request->access_token)->update(['access_token'=>null])  ;

          return response()->json(['msg'=>'logout' ]);

        }else{
            return response()->json(['msg'=>'no token' ]);
        }


 }




 public function change_password(Request $request)
 {
     $radiographer =  DB::table('radiographers')->where('access_token' ,'=',$request->access_token)->first() ;
      //  if login
     if($radiographer){

         $validator= Validator::make($request->all(),
         [
             'old_password'=>'required',
             'password'=>'required|min:8|confirmed',

        ]);

        if ($validator->fails()) {
         return response()->json(['msg'=> $validator->errors() ],404);
        }

         if(Hash::check($request->old_password, $radiographer->password) ){

             $password= bcrypt($request->password);
          DB::table('radiographers')->where('id' ,'=', $radiographer->id)->update(['password'=> $password])  ;

         return response()->json(['msg'=>' password changed' ],404);

         }else{
         return response()->json(['msg'=>'old password not correct' ],404);
          }


     }else{
         return response()->json(['msg'=>'you are not logged in ' ],404);
     }

 }



 public function update_image(Request $request)
 {
     // if login
     if(!isset( $request->access_token)){
        return response()->json(['msg'=>'there is no token' ]);
       }

         $radiographer =  DB::table('radiographers')->where('access_token' ,'=', $request->access_token)->first() ;
           if($radiographer==null){
              return response()->json(['msg'=>'no radiographer']);
                     }
       // image validation
         $validator= Validator::make($request->all(),
           [ 'image'=>'required|image', ]);

           if ($validator->fails()) {
             return response()->json(['msg'=> $validator->errors() ],404);
          }

          // update image
          $image = Storage::putFile('radiographer', $request->file('image'));
          if($radiographer->image != null){
            Storage::delete($radiographer->image);
          }
          DB::table('radiographers')->where('id' ,'=', $radiographer->id)->update(['image'=> $image])  ;

          return response()->json(['image'=>'image updated'],200);

 }



// search about patient files to upload image
 public function show_patient_files(Request $request){

    $radiographer=DB::table('radiographers')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if($radiographer){
         $patient = Patient::find($request->patient_id);
        if( $patient){
               return response()->json(['patient'=>$patient ]);
        }else{
           return response()->json(['message'=>'patient id not exist' ],404);
         }

      }else{
        return response()->json(['message'=>'no token' ],404);
      }


}


// upload image for patient file
public function set_patient_image(Request $request)
{   // update image
    $radiographer=DB::table('radiographers')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if($radiographer){

        $validator= Validator::make($request->all(),
        ['image'=>'required|image','patient_id'=>'required']);
        if ($validator->fails()) {
            return response()->json(['msg'=> $validator->errors() ],400);
           }

        // get the klast file
        $files=DB::table('diseases')->select('*')
        ->where('patient_id','=',$request->patient_id)
        ->get();
        $i=0;
        foreach($files as $file){ $i++;}  $i--;
        $file_id=$files[$i]->id;
     //   return response()->json(['files'=> $file_id],200);



         $image = Storage::putFile('patient', $request->file('image'));
         DB::table('diseases')->where('id' ,'=', $file_id)->update(['image'=>$image])  ;

         return response()->json(['image'=>'image updated'],200);


      }else{
        return response()->json(['message'=>'no token' ],401);
      }


}




}
