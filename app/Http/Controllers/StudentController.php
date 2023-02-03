<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Disease\Disease;

use App\Models\Student\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Registeration\Registeration;

class StudentController extends Controller
{

  //      انشاء طالب
  public function store(Request $request)
  {
    // Ahmad12345678   //  Aya12345678
      //validation

      $validator= Validator::make($request->all(),
          [
              'id'=>'required|unique:students,id',
              'user_name'=>'required|unique:students,user_name',
              'name'=>'required',
              'level'=>'required',
              'gpa'=>'required',
              'ar_name'=>'required',
              'password'=>'required|min:8|confirmed',
              'email'=>'required',
              'phone'=>'required',

          ]);

      // bcrypt  &  access_token
          $password= bcrypt($request->password);
        //  $access_token= Str::random(64);

      // create and login
      $student= Student::create([
          'id'=>$request->id,
          'user_name'=>$request->user_name,
          'name'=>$request->name,
          'ar_name'=>$request->ar_name,
          'phone'=>$request->phone,
          'level'=>$request->level,
          'gpa'=>$request->gpa,
          'email'=>$request->email,
          'password'=>$password,
          'image'=>null,
          'access_token'=>null,
         ]);
         (new AuthController)->store( $request->user_name ,"students");

         return response()->json(['message'=>'created' ]);


  }




//  get all student courses
  public function get_student_courses( $access_token)
  {
    $student =  DB::table('students')->where('access_token' ,'=',$access_token)->first() ;
    if($student == null){
      return response()->json(['messages'=>"no token"]);
    }
      $courses =  DB::table('students')
      ->join('registerations', 'students.id', '=', 'registerations.student_id')
      ->join('clinics', 'registerations.clinic_id', '=', 'clinics.id')
      ->join('courses', 'clinics.course_id', '=', 'courses.id')
      ->select( 'courses.id','clinics.section', 'courses.name','clinics.day','clinics.start_time','clinics.end_time' ,'courses.image')
      ->where('students.id' ,'=',$student->id)->get() ;



                      foreach($courses as $course){
                        if( $course->image != null){
                            $course->image=asset("storage").'/'.$course->image;
                       }
                      }


      return response()->json(['courses'=>$courses],200);

  }




// get course req  for loggedin student
public function get_req_status(Request $request){

    $student =  DB::table('students')->where('access_token' ,'=',$request->access_token)->first() ;
   // get  student with reg_id for selected clinic
    $student=DB::table('students')
    ->join('registerations','registerations.student_id','students.id')
    ->join('clinics','registerations.clinic_id','clinics.id')
    ->select('students.id','students.name','registerations.id as reg_id')
    ->where('clinics.course_id',$request->course_id)
    ->where('students.id','=', $student->id)
    ->first();

    //get course req
    $reqs=DB::table('requirements')
    ->select('requirements.id','requirements.name','requirements.course_id')
    ->where('course_id',$request->course_id)
    ->get();
 // get student treatments in this course with treatments status
    $treatments=DB::table('requirements')
      ->leftJoin('treatments','treatments.requirement_id','requirements.id')
      ->leftJoin('diseases','treatments.disease_id','diseases.id')
      ->leftJoin('patients','diseases.patient_id','patients.id')
      ->select('requirements.id as req_id','requirements.name','requirements.course_id',
      'treatments.status','treatments.registeration_id as reg_id',
      'treatments.start_date','treatments.end_date',  'treatments.disease_id' ,'patients.name')
      ->where('course_id',$request->course_id)
      ->where('treatments.registeration_id','=', $student->reg_id)
      ->get();

      $student_req= $reqs;
      foreach($student_req as $req){$req->status=null;}

      foreach( $student_req as $req){    // course req
                    $req->status= null;
                    $req->start_date= null;
                    $req->end_date= null;
                    $req->disease_id= null;
                    $req->patient= null;


       }


      foreach( $student_req as $req){    // course req
        foreach( $treatments as $treatment){   //student treatments
      //      if( $student->reg_id == $treatment->reg_id){
                if($req->id == $treatment->req_id){
                    $req->status= $treatment->status;
                    $req->start_date= $treatment->start_date;
                    $req->end_date= $treatment->end_date;
                    $req->disease_id= $treatment->disease_id;
                    $req->patient= $treatment->name;

                }
          //  }
        }
       }
      // $student->req= $student_req;

    return response()->json(['student_req'=>$student_req ]);

}


//  show patient file

public function get_selected_file(Request $request){

    $student =  DB::table('students')->where('access_token' ,'=',$request->access_token)->first() ;

    if($student){

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



public function get_patient_name($patient_id){
    $patient=DB::table('patients')->select('id','name')
    ->where('id','=',$patient_id)
    ->first();
    if($patient){
        return response()->json(['patient'=>$patient ]);
    }
}


public function show_progress($access_token){
    $student =  DB::table('students')->where('access_token' ,'=',$access_token)->first() ;
    if($student == null){
      return response()->json(['messages'=>"no token"]);
    }

    $courses =  DB::table('students')
    ->join('registerations', 'students.id', '=', 'registerations.student_id')
    ->join('clinics', 'registerations.clinic_id', '=', 'clinics.id')
    ->join('courses', 'clinics.course_id', '=', 'courses.id')
    ->select( 'courses.id as course_id', 'courses.name','registerations.id  as registeration_id')
    ->where('students.id' ,'=',$student->id)
    ->get() ;


    foreach($courses  as $course){

        $reqs= DB::table('courses')
        ->join('requirements', 'requirements.course_id', '=', 'courses.id')
        ->select( 'requirements.name','requirements.id')
        ->where('courses.id' ,'=',$course->course_id)->get() ;

        foreach( $reqs as $req){
            $req->status= DB::table('treatments')
            ->join('requirements', 'requirements.id', '=', 'treatments.requirement_id')
            ->select(  'treatments.status')
            ->where('treatments.registeration_id' ,'=',$course->registeration_id)
            ->first();
        }

        $course->progress = $reqs;

    }





   return response()->json(['progress'=>$courses]);


}


}
