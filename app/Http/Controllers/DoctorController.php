<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Doctor\Doctor;
use App\Models\Disease\Disease;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
//doctors
   public function store(Request $request)
   {

      $validator= Validator::make($request->all(),
          [
              'user_name'=>'required|unique:doctors,user_name',
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
      $doctor= Doctor::create([
          'user_name'=>$request->user_name,
          'name'=>$request->name,
          'ar_name'=>$request->ar_name,
          'email'=>$request->email,
          'password'=>$password,
          'image'=>null,
          'access_token'=>null,
         ]);
         (new AuthController)->store( $request->user_name ,"doctors");

         return response()->json(['message'=>'created' ]);

   }


public function get_courses($access_token){
    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$access_token)->first();
       if($doctor == null){
            return response()->json(['messages'=>"no token"]);
       }
    $courses =  DB::table('doctors')
    ->join('clinics', 'clinics.doctor_id', 'doctors.id')
    ->join('courses','courses.id','clinics.course_id')
    ->select( 'courses.id as course_id', 'courses.name','courses.level','courses.image')
    ->where('clinics.doctor_id' ,'=',$doctor->id)
    ->get() ;

    return response()->json(['courses'=>$courses]);

}


   public function get_course_clinics(Request $request)
   {

    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
    if($doctor == null){
         return response()->json(['messages'=>"no token"]);
    }

       $clinics =  DB::table('doctors')
       ->join('clinics', 'clinics.doctor_id', 'doctors.id')
       ->join('courses','courses.id','clinics.course_id')
       ->select(  'courses.name','courses.level',
       'clinics.id as clinic_id','clinics.day','clinics.start_time',
       'clinics.end_time' ,'courses.image')
       ->where('clinics.doctor_id' ,'=',$doctor->id)
       ->where('clinics.course_id' ,'=',$request->course_id)->get() ;

       if($clinics==null){
           return response()->json(['msg'=>'there is no clinic']);
                       }
              foreach($clinics as $clinic){
                        if( $clinic->image != null){
                            $clinic->image=asset("storage").'/'.$clinic->image;
                       }
                      }

       return response()->json(['clinics'=>$clinics],200);

   }



   // get  clinic student
   public function get_clinic_students(Request $request)
   {

    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
       if($doctor == null){
            return response()->json(['messages'=>"no token"]);
       }
                    // get clinic students by clinnic_id

         $students =DB::table('students')
         ->join('registerations','students.id','=','registerations.student_id')
         ->join('clinics','clinics.id','=','registerations.clinic_id')
         ->select('students.id as student_id','students.name as student_name',
         'registerations.id as registeration_id')
         ->where('clinic_id',$request->clinic_id)->get();   //,'registerations.clinic_id as clinic_id','clinics.course_id'

         $clinic = DB::table('clinics')
         ->join('courses','courses.id' , 'clinics.course_id')
         ->select('courses.name' ,'clinics.section','clinics.id')
         ->where('clinics.id',$request->clinic_id)->first();
       //  if there is no student
         if($students==null){
              return response()->json(['msg'=>'no students']);
                          }

       // return clinic students
         return response()->json(['clinic'=>$clinic,'students'=>$students ],200);



   }




// show all reqs for selected student in selected clinic

public function get_student_req(Request $request){

    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
    if($doctor == null){
         return response()->json(['messages'=>"no token"]);
    }

    $course=DB::table('courses')
      ->join('clinics','courses.id','clinics.course_id')
      ->select('courses.id')
      ->where('clinics.id',$request->clinic_id)
      ->first();
    //clinic _id , course_id
    //get clinic students with----------- reg_id
    $student=DB::table('students')
    ->join('registerations','registerations.student_id','students.id')
    ->join('clinics','registerations.clinic_id','clinics.id')
    ->select('students.id','students.name','registerations.id as reg_id')
    ->where('clinics.course_id',$course->id)
    ->where('clinics.id',$request->clinic_id)
    ->where('students.id','=', $request->student_id)
    ->first();
   //clinic_id


        $reqs=DB::table('requirements')
        ->select('requirements.id','requirements.name','requirements.course_id')
        ->where('course_id',$course->id)
        ->get();

        $treatments=DB::table('requirements')
        ->leftJoin('treatments','treatments.requirement_id','requirements.id')
        ->select('requirements.id as req_id','requirements.name','requirements.course_id',
        'treatments.status','treatments.registeration_id as reg_id',
        'treatments.disease_id as disease_id','treatments.id as treatment_id')
        ->where('course_id',$course->id)
        ->where('treatments.registeration_id','=', $student->reg_id)
        ->get();

        $student_req= $reqs;
        foreach($student_req as $req){$req->status=null;}

        foreach( $student_req as $req){    // course req
          foreach( $treatments as $treatment){   //student treatments
        //      if( $student->reg_id == $treatment->reg_id){
                  if($req->id == $treatment->req_id){
                      $req->status= $treatment->status;
                      $req->treatment_id= $treatment->treatment_id;
                      $req->disease_id= $treatment->disease_id;

                  }
            //  }
          }
         }
         $student->reqs = $student_req;




    return response()->json(['student'=>$student]);

}





// change the treatment status
   public function update_treatment_status(Request $request)
{

    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
    if($doctor == null){
         return response()->json(['messages'=>"no token"]);
    }

        $treatment =  DB::table('treatments')
        ->where('registeration_id' ,'=', $request->reg_id)
        ->where('requirement_id','=', $request->req_id)
        ->update(['status'=> $request->status]);
        return response()->json(['msg'=> 'updated'],200);




}




// for all students reqs in selected clinic
public function get_students_req(Request $request){
    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
       if($doctor == null){
            return response()->json(['messages'=>"no token"]);
       }
    //clinic _id , course_id
    //get clinic students with----------- reg_id

    $course_id= DB::table('courses')
    ->join('clinics','courses.id','clinics.course_id')
    ->where('clinics.id',$request->clinic_id)
    ->select('courses.id')
    ->first()->id;

    $students=DB::table('students')
    ->join('registerations','registerations.student_id','students.id')
    ->join('clinics','registerations.clinic_id','clinics.id')
    ->select('students.id','students.name','registerations.id as reg_id')
    ->where('clinics.course_id',$course_id)
    ->where('clinics.id',$request->clinic_id)
    ->get();



    foreach(  $students as   $student){
        $reqs=DB::table('requirements')
        ->select('requirements.id','requirements.name','requirements.course_id')
        ->where('course_id',$course_id)
        ->get();

        $treatments=DB::table('requirements')
        ->leftJoin('treatments','treatments.requirement_id','requirements.id')
        ->select('requirements.id as req_id','requirements.name','requirements.course_id',
        'treatments.status','treatments.registeration_id as reg_id',
        'treatments.disease_id as disease_id','treatments.id as treatment_id')
        ->where('course_id',$course_id)
        ->where('treatments.registeration_id','=', $student->reg_id)
        ->get();

        $student_req= $reqs;
        foreach($student_req as $req){$req->status=null;}

          // course req
          foreach( $treatments as $treatment){   //student treatments
            foreach( $student_req as $req){
        //      if( $student->reg_id == $treatment->reg_id){
                  if($req->id == $treatment->req_id){
                      $req->status= $treatment->status;
                      $req->disease_id= $treatment->disease_id;
                      $req->treatment_id= $treatment->treatment_id;

                  }
            //  }
          }
         }

         $student->reqs =  $student_req;
    }  // foreach




    return response()->json(['students'=>$students ]);


}




public function get_selected_file(Request $request){

    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
       if($doctor == null){
            return response()->json(['messages'=>"no token"]);
       }

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



}



public function get_over_view(Request $request){
    $doctor =  DB::table('doctors')->select('*')->where('access_token' ,'=',$request->access_token)->first();
       if($doctor == null){
            return response()->json(['messages'=>"no token"]);
       }
    //clinic _id , course_id
    //get clinic students with----------- reg_id

    $course_id= DB::table('courses')
    ->join('clinics','courses.id','clinics.course_id')
    ->where('clinics.id',$request->clinic_id)
    ->select('courses.id')
    ->first()->id;

    $students=DB::table('students')
    ->join('registerations','registerations.student_id','students.id')
    ->join('clinics','registerations.clinic_id','clinics.id')
    ->select('students.id','students.name','registerations.id as reg_id')
    ->where('clinics.course_id',$course_id)
    ->where('clinics.id',$request->clinic_id)
    ->get();

    $number_of_stutents = count($students);

    $over_view_reqs=DB::table('requirements')
    ->select('requirements.id','requirements.name')
    ->where('course_id',$course_id)
    ->get();

    foreach($over_view_reqs as $over_view_req){
         $over_view_req->completed =0;
         $over_view_req->not_completed =0;
         $over_view_req->null =$number_of_stutents;

         foreach(  $students as   $student){
            $student_req=DB::table('registerations')
            ->join('treatments','treatments.registeration_id','registerations.id')
            ->select('treatments.status')
            ->where('registerations.id',$student->reg_id)
            ->where('treatments.requirement_id',$over_view_req->id)
            ->where('treatments.status','!=',"canceled")
            ->first();

            if( $student_req != null){
                if($student_req->status == "completed"){
                    $over_view_req->completed +=1;
                    $over_view_req->null -=1;
                }elseif($student_req->status == "not completed"){
                    $over_view_req->not_completed +=1;
                    $over_view_req->null -=1;
                }
            }




         }

    }


    return response()->json(['over_view'=>$over_view_reqs ]);


}


}
