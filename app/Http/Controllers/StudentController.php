<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Student\Student;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Registeration\Registeration;

class StudentController extends Controller
{




    //logout
    public function logout(Request $request)
{   // check if exist
    $student=DB::table('students')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

   // logout
    if($student){
          DB::table('students')->where('access_token','=' ,$request->access_token)->update(['access_token'=>null])  ;

          return response()->json(['msg'=>'logout' ]);

        }else{
            return response()->json(['msg'=>'no token' ]);
        }


}



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
  public function get_student_courses($id)
  {
      $courses =  DB::table('students')
      ->join('registerations', 'students.id', '=', 'registerations.student_id')
      ->join('clinics', 'registerations.clinic_id', '=', 'clinics.id')
      ->join('courses', 'clinics.course_id', '=', 'courses.id')
      ->select( 'courses.id', 'courses.name','clinics.day','clinics.start_time','clinics.end_time' ,'courses.image')
      ->where('students.id' ,'=',$id)->get() ;

      if($courses==null){
          return response()->json(['msg'=>'no student']);
                      }
                      foreach($courses as $course){
                        if( $course->image != null){
                            $course->image=asset("storage").'/'.$course->image;
                       }
                      }


      return response()->json(['courses'=>$courses],200);

  }


  // get student treatments in selected courses
public function get_course_info(Request $request ){

    if(!isset( $request->access_token)){
        return response()->json(['msg'=>'there is no token' ]);
     }

     $student =  DB::table('students')->where('access_token' ,'=', $request->access_token)->first() ;
     if($student){
        $treatments =  DB::table('students')
        ->join('registerations', 'students.id', '=', 'registerations.student_id')
        ->join('clinics', 'registerations.clinic_id', '=', 'clinics.id')
        ->join('courses', 'clinics.course_id', '=', 'courses.id')
        ->join('treatments', 'treatments.registeration_id', '=', 'registerations.id')
        ->join('requirements', 'requirements.id', '=', 'treatments.requirement_id')
        ->join('diseases', 'diseases.id', '=', 'treatments.disease_id')
        ->join('patients', 'patients.id', '=', 'diseases.patient_id')
        ->select('requirements.name as req_name','treatments.*','diseases.*','patients.name as patient_name',
        'patients.date_of_birth','patients.gender','patients.address','patients.phone')
        ->where('students.id' ,'=',$student->id)
        ->where('courses.id' ,'=',$request->id)->get() ;  //,

  // clinic id + reg id +  treatment id+ dis + patient info
    if($treatments !=null){

       foreach($treatments as $treatment){
            $treatment->image=asset("storage").'/'.$treatment->image;
        }

       return response()->json(['treatments'=>$treatments],200);
     }

     }else{
        return response()->json(['msg'=>'should be loggedin' ],404);
     }


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
      ->select('requirements.id as req_id','requirements.name','requirements.course_id',
      'treatments.status','treatments.registeration_id as reg_id')
      ->where('course_id',$request->course_id)
      ->where('treatments.registeration_id','=', $student->reg_id)
      ->get();

      $student_req= $reqs;
      foreach($student_req as $req){$req->status=null;}

      foreach( $student_req as $req){    // course req
        foreach( $treatments as $treatment){   //student treatments
      //      if( $student->reg_id == $treatment->reg_id){
                if($req->id == $treatment->req_id){
                    $req->status= $treatment->status;
                }
          //  }
        }
       }
      // $student->req= $student_req;

    return response()->json(['student_req'=>$student_req ]);

}





}
