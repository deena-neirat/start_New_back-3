<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Doctor\Doctor;
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



   public function get_doctor_clinics($id)
   {
       $clinics =  DB::table('doctors')
       ->join('clinics', 'clinics.doctor_id', 'doctors.id')
       ->join('courses','courses.id','clinics.course_id')
       ->select( 'courses.id as course_id', 'courses.name','courses.level',
       'clinics.id as clinic_id','clinics.day','clinics.start_time',
       'clinics.end_time' ,'courses.image')
       ->where('doctors.id' ,'=',$id)->get() ;

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
   public function get_clinic_student(Request $request)
   {


    $doctor =  DB::table('doctors')->where('access_token' ,'=',$request->access_token)->first() ;
    //  if login
      if($doctor){
                    // get clinic students by clinnic_id

         $students =DB::table('students')
         ->join('registerations','students.id','=','registerations.student_id')
         ->join('clinics','clinics.id','=','registerations.clinic_id')
         ->select('students.id as student_id','students.name as student_name',
         'registerations.id as registeration_id','registerations.clinic_id as clinic_id','clinics.course_id')
         ->where('clinic_id',$request->clinic_id)->get();

       //  if there is no student
         if($students==null){
              return response()->json(['msg'=>'no students']);
                          }

       // return clinic students
         return response()->json(['student'=>$students],200);

           }else{
        return response()->json(['msg'=>'you are not logged in ' ],404);
          }

   }


// get students treatments with status in selected clinic
   public function get_student_treatments(Request $request)
{

      $doctor =  DB::table('doctors')->where('access_token' ,'=',$request->access_token)->first() ;

     if($doctor){
        $treatments =  DB::table('students')
        ->join('registerations', 'students.id', '=', 'registerations.student_id')
        ->join('clinics', 'registerations.clinic_id', '=', 'clinics.id')
        ->join('courses', 'clinics.course_id', '=', 'courses.id')
        ->join('treatments', 'treatments.registeration_id', '=', 'registerations.id')
        ->join('requirements', 'requirements.id', '=', 'treatments.requirement_id')
        ->join('diseases', 'diseases.id', '=', 'treatments.disease_id')
        ->join('patients', 'patients.id', '=', 'diseases.patient_id')
        ->select('requirements.name as req_name','treatments.id as treatment_id','treatments.*','diseases.*','patients.name as patient_name',
        'patients.date_of_birth','patients.gender','patients.address','patients.phone')
        ->where('students.id' ,'=',$request->student_id)
        ->where('clinics.id' ,'=',$request->clinic_id)->get() ;  //,

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


// change the treatment status
   public function update_treatment_status(Request $request)
{

    $doctor =  DB::table('doctors')->where('access_token' ,'=',$request->access_token)->first() ;

    if($doctor){
        $treatment =  DB::table('treatments')
        ->where('registeration_id' ,'=', $request->reg_id)
        ->where('requirement_id','=', $request->req_id)
        ->update(['status'=> $request->status]);
        return response()->json(['msg'=> 'updated'],200);

    }else{
        return response()->json(['msg'=>'should be loggedin' ],404);
   }


}



// for all students reqs in selected clinic
public function get_students_req(Request $request){

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
        'treatments.status','treatments.registeration_id as reg_id')
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
                  }
            //  }
          }
         }

         $student->reqs =  $student_req;
    }  // foreach




    return response()->json(['students'=>$students ]);


}




// show all reqs for selected student in selected clinic

public function get_student_req(Request $request){

    //clinic _id , course_id
    //get clinic students with----------- reg_id
    $student=DB::table('students')
    ->join('registerations','registerations.student_id','students.id')
    ->join('clinics','registerations.clinic_id','clinics.id')
    ->select('students.id','students.name','registerations.id as reg_id')
    ->where('clinics.course_id',$request->course_id)
    ->where('clinics.id',$request->clinic_id)
    ->where('students.id','=', $request->student_id)
    ->first();



        $reqs=DB::table('requirements')
        ->select('requirements.id','requirements.name','requirements.course_id')
        ->where('course_id',$request->course_id)
        ->get();

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
         $student->reqs = $student_req;




    return response()->json(['student'=>$student]);


}
}
