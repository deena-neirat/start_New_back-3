<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Admin\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Initial\Initial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{

    public function store(Request $request)
    {

       $validator= Validator::make($request->all(),
           [
               'user_name'=>'required|unique:doctors,user_name',
               'name'=>'required',
               'ar_name'=>'required',
               'email'=>'required',
               'password'=>'required|min:8',

           ]);
           if ($validator->fails()) {
             return response()->json(['msg'=> $validator->errors() ],404);
            }
       // bcrypt  &  access_token
           $password= bcrypt($request->password);

       // create and login
       $admin= Admin::create([
           'user_name'=>$request->user_name,
           'name'=>$request->name,
           'ar_name'=>$request->ar_name,
           'email'=>$request->email,
           'password'=>$password,
           'image'=>null,
           'access_token'=>null,
          ]);

          return response()->json(['message'=>'created' ]);

    }


    public function login(Request $request)
    {

           $admin =  DB::table('admins')->where('user_name' ,'=', $request->user_name)->first() ;

            if($admin){

              if(Hash::check($request->password, $admin->password)  ){


                      //  set access token
                    $access_token= Str::random(64);
                  DB::table('admins')->where('user_name' ,'=', $request->user_name)->update(['access_token'=> $access_token])  ;

                  // get student after change the access
                  $admin =  DB::table('admins')->where('user_name' ,'=', $request->user_name)->first() ;
                  if( $admin->image != null){
                       $admin->image=asset("storage").'/'.$admin->image;
                  }


                    return response()->json(['admin'=>$admin,
                         'type'=>'admins',
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
       $admin=DB::table('admins')->select('*')
       ->where('access_token','=',$request->access_token)
       ->first();

      // logout
       if($admin){
             DB::table('admins')->where('access_token','=' ,$request->access_token)->update(['access_token'=>null])  ;

             return response()->json(['msg'=>'logout' ]);

           }else{
               return response()->json(['msg'=>'no token' ]);
           }


    }

    public function change_password(Request $request)
    {
        $admin =  DB::table('admins')->where('access_token' ,'=',$request->access_token)->first() ;
         //  if login
        if($admin){

            $validator= Validator::make($request->all(),
            [
                'old_password'=>'required',
                'password'=>'required|min:8|confirmed',

           ]);

           if ($validator->fails()) {
            return response()->json(['msg'=> $validator->errors() ],404);
           }

            if(Hash::check($request->old_password, $admin->password) ){

                $password= bcrypt($request->password);
             DB::table('admins')->where('id' ,'=', $admin->id)->update(['password'=> $password])  ;

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

            $admin =  DB::table('admins')->where('access_token' ,'=', $request->access_token)->first() ;
              if($admin==null){
                 return response()->json(['msg'=>'no admin']);
                        }
          // image validation
            $validator= Validator::make($request->all(),
              [ 'image'=>'required|image', ]);

              if ($validator->fails()) {
                return response()->json(['msg'=> $validator->errors() ],404);
             }

             // update image
             $image = Storage::putFile('admin', $request->file('image'));
             DB::table('admins')->where('id' ,'=', $admin->id)->update(['image'=> $image])  ;

             return response()->json(['image'=>'image updated'],200);

    }




    //   start , end date --> then add an initial clinic
    public function add_initial(Request $request){


        $admin =  DB::table('admins')->where('access_token' ,'=',$request->access_token)->first() ;
         //  if login
        if($admin){

        $start_date=$request->start_date;
        $end_date=$request->end_date;

        $period = CarbonPeriod::create($start_date, $end_date );

        // Iterate over the period
        foreach ($period as $date) {
            $thisdate= $date->format('Y-m-d ' );
            $timestamp = strtotime($date);
            $day = date('D', $timestamp);
            $start_times=['08:30:00','09:30:00','10:30:00','11:30:00','01:30:00'];
            $end_times=['09:20:00','10:20:00','11:20:00','01:20:00','02:20:00'];

            if($day !='Fri'){
                for($i=0;$i <1 ;$i++){
                    $initial= Initial::create([
                        'day'=>$day,
                        'start_time'=>$start_times[$i],
                        'end_time'=>$end_times[$i],
                        'seats'=>'7',
                        'date'=>$thisdate
                    ]);
                                    }// for

                  }//if


        }
        return response()->json(['msg'=>'initials added'  ]);

        }else{
            return response()->json(['msg'=>'you are not logged in ' ],404);

        }


    }




    public function get_sentiments_result(){

        $avg_stars = DB::table('sentiments')->avg('value');
        return response()->json(['values avg'=>$avg_stars  ]);
    }


    public function show_stars_evaluation(){
        $topics=DB::table('stars')->select('*')->get();

        foreach($topics  as $topic){
    $topic->evaluation= ($topic->sum / $topic->clients_num);
        }
        return response()->json(['topics'=>$topics]);
    }


}
