<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Disease\Disease;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use App\Models\Assistant\Assistant;
use App\Models\Treatment\Treatment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DoctorController;

class AssistantController extends Controller
{

 ///   create  assistant account  (personal info )
public function store(Request $request){

    $validator= Validator::make($request->all(),
    [
        'user_name'=>'required|unique:assistants,user_name',
        'email'=>'required|unique:assistants,email',
        'name'=>'required',
        'ar_name'=>'required',
        'password'=>'required|min:8|confirmed',
   ]);
    $password= bcrypt($request->password);
   // $access_token= Str::random(64);

    $name= $request->firstName .' '. $request->middleName .' '. $request->lastName;

    $assistant= Assistant::create([
        'user_name'=>$request->user_name,
        'name'=>$request->name,
        'ar_name'=>$request->ar_name,
        'email'=>$request->email,
        'password'=>$password,
        'image'=>null,
        'access_token'=>null,
       ]);


     return response()->json(['assistant'=>$assistant,
       'type'=>'assistants'
    ],200);
}







// create disease file for patient
public function create_patient_file(Request $request){


    $assistant=DB::table('assistants')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    $reservation=DB::table('reservations')->select('id')
    ->where('patient_id','=',$request->patient_id)
    ->where('status','reserved')
    ->orderBy('reservations.id', 'DESC')
    ->first()->id;
   // return response()->json(['message'=>$reservation]);


    //  if  yes
    if($assistant != null){

        $validator= Validator::make($request->all(),
        [
            'patient_id'=>'required',
            'Chief_Complaint'=>'required',
            'health_changes'=>'required',
            'physician_care'=>'required',
            'serious_illnesses_or_operations'=>'required',
             'pregnant'=>'required',
             'Heart_Failur'=>'required',
             'Heart_Attack'=>'required',
             'Angina'=>'required',
             'Pacemaker'=>'required',
             'Congential_Heart_Disease'=>'required',
             'Other_Heart_Disease'=>'required',
             'Anemia'=>'required',
             'Hemophilia'=>'required',
             'Lcukaemia'=>'required',
             'Blood_Transfusion'=>'required',
             'Other_Blood_Disease'=>'required',
             'Asthma'=>'required',
             'Chronic_Obstructive_Pulmonary_Disease'=>'required',
             'Gastro_ocsophagcal_reflux'=>'required',
             'Hepatitits'=>'required',
             'Liver_disease'=>'required',
             'Epilepsy'=>'required',
             'Parkinsons_disease'=>'required',
             'Kidney_Failur'=>'required',
             'Dialysis'=>'required',
             'Drug_Allergy'=>'required',
             'Food_Allergy'=>'required',
             'Cancer'=>'required',
             'Medicines_currently_used'=>'required',
             'smoke'=>'required',
             'cigarette_kind'=>'required',
             'cigarette_frequently'=>'required',
             'dental_treatment_problem'=>'required',
             'face_jaw_teeth_injury'=>'required',
             'dry_mouth'=>'required',
             'local_anesthetic_reaction'=>'required',
             'clench_on_teeth'=>'required',
             'hard_to_breathe'=>'required',
             'sleep_scared'=>'required',
             'people_nervous'=>'required',
             'nightmares'=>'required',
             'Thumb_succing'=>'required',
             'Toungue_thrust'=>'required',
             'Nail_biting'=>'required',
             'Other_Habits'=>'required',
             'TMJ'=>'required',
             'Lymph_node'=>'required',
             'Patient_profile'=>'required',
             'Lip_Competency'=>'required',
             'Incisol_classification'=>'required',
             'Overjet'=>'required',
             'Overbite'=>'required',
             'Hard_Palate'=>'required',
             'mucosa'=>'required',
             'Floor_of_mouth'=>'required',
             'Lips'=>'required',
             'Tongue'=>'required',
             'Gums_and_Tissues'=>'required',
             'Saliva'=>'required',
             'Natural_Teeth'=>'required',
             'Dentures'=>'required',
             'Oral_Cleanliness'=>'required',
             'Dental_Pain'=>'required',


 ]);


          if ($validator->fails()) {
              return response()->json(['msg'=> $validator->errors() ],404);
              }




              $disease= Disease::create([
                'patient_id'=>$request->patient_id,
                'reservation_id'=>$reservation,
                'Chief_Complaint'=>$request->Chief_Complaint,
                'health_changes'=>$request->health_changes,
                'physician_care'=>$request->physician_care,
                'serious_illnesses_or_operations'=>$request->serious_illnesses_or_operations,
                 'pregnant'=>$request->pregnant ,
                 'Heart_Failur'=>$request->Heart_Failur ,
                 'Heart_Attack'=>$request->Heart_Attack ,
                 'Angina'=>$request->Angina ,
                 'Pacemaker'=>$request->Pacemaker ,
                 'Congential_Heart_Disease'=>$request->Congential_Heart_Disease ,
                 'Other_Heart_Disease'=>$request->Other_Heart_Disease ,
                 'Anemia'=>$request->Anemia ,
                 'Hemophilia'=>$request->Hemophilia ,
                 'Lcukaemia'=>$request->Lcukaemia ,
                 'Blood_Transfusion'=>$request->Blood_Transfusion ,
                 'Other_Blood_Disease'=>$request->Other_Blood_Disease ,
                 'Asthma'=>$request->Asthma ,
                 'Chronic_Obstructive_Pulmonary_Disease'=>$request->Chronic_Obstructive_Pulmonary_Disease ,
                 'Gastro_ocsophagcal_reflux'=>$request->Gastro_ocsophagcal_reflux ,
                 'Hepatitits'=>$request->Hepatitits ,
                 'Liver_disease'=>$request->Liver_disease ,
                 'Epilepsy'=>$request->Epilepsy ,
                 'Parkinsons_disease'=>$request->Parkinsons_disease ,
                 'Kidney_Failur'=>$request->Kidney_Failur ,
                 'Dialysis'=>$request->Dialysis ,
                 'Drug_Allergy'=>$request->Drug_Allergy ,
                 'Food_Allergy'=>$request->Food_Allergy ,
                 'Cancer'=>$request->Cancer ,
                 'Medicines_currently_used'=>$request->Medicines_currently_used ,
                 'smoke'=>$request->smoke ,
                 'cigarette_kind'=>$request->cigarette_kind ,
                 'cigarette_frequently'=>$request->cigarette_frequently ,
                 'dental_treatment_problem'=>$request->dental_treatment_problem ,
                 'face_jaw_teeth_injury'=>$request->face_jaw_teeth_injury ,
                 'dry_mouth'=>$request->dry_mouth ,
                 'local_anesthetic_reaction'=>$request->local_anesthetic_reaction ,
                 'clench_on_teeth'=>$request->clench_on_teeth ,
                 'hard_to_breathe'=>$request->hard_to_breathe ,
                 'sleep_scared'=>$request->sleep_scared ,
                 'people_nervous'=>$request->people_nervous ,
                 'nightmares'=>$request->nightmares ,
                 'Thumb_succing'=>$request->Thumb_succing ,
                 'Toungue_thrust'=>$request->Toungue_thrust ,
                 'Nail_biting'=>$request->Nail_biting ,
                 'Other_Habits'=>$request->Other_Habits ,
                 'TMJ'=>$request->TMJ ,
                 'Lymph_node'=>$request->Lymph_node ,
                 'Patient_profile'=>$request->Patient_profile ,
                 'Lip_Competency'=>$request->Lip_Competency ,
                 'Incisol_classification'=>$request->Incisol_classification ,
                 'Overjet'=>$request->Overjet ,
                 'Overbite'=>$request->Overbite ,
                 'Hard_Palate'=>$request->Hard_Palate ,
                 'mucosa'=>$request->mucosa ,
                 'Floor_of_mouth'=>$request->Floor_of_mouth ,
                 'Lips'=>$request->Lips ,
                 'Tongue'=>$request->Tongue ,
                 'Gums_and_Tissues'=>$request->Gums_and_Tissues ,
                 'Saliva'=>$request->Saliva ,
                 'Natural_Teeth'=>$request->Natural_Teeth ,
                 'Dentures'=>$request->Dentures ,
                 'Oral_Cleanliness'=>$request->Oral_Cleanliness ,
                 'Dental_Pain'=>$request->Dental_Pain ,
                'image'=>null,

               ]);


              return response()->json(['message'=>'file created']);


    }else{
        return response()->json(['message'=>'no token' ],404);
    }

}




public function get_level_courses(Request $request){
    $assistant=DB::table('assistants')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();
    if(  $assistant){

        $courses=DB::table('courses')
        ->select('id','name')
        ->where('level','=',$request->level)
        ->get();
        return response()->json(['courses'=>$courses]);

   }else{
    return response()->json(['message'=>'no token' ],404);
   }
}

public function get_course_sections(Request $request){
    $assistant=DB::table('assistants')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if(  $assistant){
        $sections=DB::table('clinics')
        ->select('id','day' ,'start_time','end_time')
        ->where('course_id','=',$request->course_id)
        ->get();
        return response()->json(['sections'=>$sections]);
    }else{
        return response()->json(['message'=>'no token' ],404);
       }
}


public function show_section_students(Request $request){
    $assistant=DB::table('assistants')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();
    if(  $assistant){
    return (new DoctorController)->get_students_req( $request);
     }else{
        return response()->json(['message'=>'no token' ],404);

     }

}




public function add_treatments(Request $request)
{


$assistant=DB::table('assistants')->select('*')
->where('access_token','=',$request->access_token)
->first();
 if(  $assistant){

              $treatments = $request->treatments;

              foreach($treatments  as $treatment){
                $x=[];
      $x['patient_id']=$treatment['patient_id'];
      $x['reg_id']=$treatment['reg_id'];
      $x['req_id']=$treatment['req_id'];
      $x['tooth']=$treatment['tooth'];
      $x['start_date']=$treatment['start_date'];
      $x['end_date']=$treatment['end_date'];
      $x['description']=$treatment['description'];


      $y=(new AssistantController)->add_treatment($treatment);
      if($y !="treatment created"){
         return response()->json(['messages'=>$y]);
      }

              }
             
               return response()->json(['messages'=>"created successfully"]);


   }else{
    return response()->json(['messages'=>'no token' ],404);
  }

}



public function add_treatment( $request){



             $diseases=DB::table('diseases')->select('*')
              ->where('patient_id','=',$request['patient_id'])
              ->get();

              $disease_id =0;
              foreach($diseases as $disease){
                $disease_id=$disease->id;
              }

             $treatment= Treatment::create([
                'disease_id'=>$disease_id,
                'registeration_id'=>$request['reg_id'],
                'requirement_id'=>$request['req_id'],
                'tooth'=>$request['tooth'],
                'tooth_id'=>$request['tooth_id'],
                'start_date'=>$request['start_date'],
                'end_date'=>$request['end_date'],
                'status'=>'not completed',
                'description'=>$request['description'],


               ]);

                     return "treatment created";
                return response()->json(['messages'=>'treatment created' ],200);


}



//Storage::delete($secretarie->image);
// search about patient files by patient id   //  with treatments

public function show_patient_files(Request $request){

    $assistant=DB::table('assistants')->select('*')
    ->where('access_token','=',$request->access_token)
    ->first();

    if($assistant){
         $patient = Patient::find($request->patient_id);

         if($patient){
            $files=DB::table('diseases')->select('*')
            ->where('patient_id','=',$request->patient_id)
            ->get();


            foreach($files as $file){
                if( $file->image != null){
                    $file->image=asset("storage").'/'.$file->image;
                    $file->file_treatments= $this->show_file_treatments($file->id)->original['treatments'];

                }
            }

           return response()->json(['files'=>$files ]);

         }else{
           return response()->json(['message'=>'patient not exist' ],404);
         }



      }else{
        return response()->json(['message'=>'no token' ],404);
      }


}



//  get treatments for one file
public function show_file_treatments($id){

    $treatments=DB::table('treatments')->select('*')
    ->where('disease_id','=',$id)
    ->get();
    return response()->json(['treatments'=>$treatments]);

}



}// end of class
