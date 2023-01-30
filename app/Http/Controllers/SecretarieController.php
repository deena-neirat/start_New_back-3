<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\TestEmail;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Secretarie\Secretarie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendAppointments;
use SebastianBergmann\ObjectReflector\Exception;
use Symfony\Component\HttpClient\Chunk\FirstChunk;

class SecretarieController extends Controller
{


  //      انشاء مساعد
 public function store(Request $request)
   {
    // Ahmad12345678   //  Aya12345678
      //validation

      $validator= Validator::make($request->all(),
          [
              'user_name'=>'required|unique:secretaries,user_name',
              'name'=>'required',
              'ar_name'=>'required',
              'email'=>'required',
              'password'=>'required|min:8|confirmed',

          ]);

      // bcrypt  &  access_token
          $password= bcrypt($request->password);
        //  $access_token= Str::random(64);

      // create and login
      $secretarie= Secretarie::create([
          'user_name'=>$request->user_name,
          'name'=>$request->name,
          'ar_name'=>$request->ar_name,
          'email'=>$request->email,
          'password'=>$password,
          'image'=>null,
          'access_token'=>null,
         ]);
         (new AuthController)->store( $request->user_name ,"secretaries");

         return response()->json(['message'=>'created' ]);



}



 public function login(Request $request)
{

       $secretarie =  DB::table('secretaries')->where('user_name' ,'=', $request->user_name)->first() ;

        if($secretarie){

          if(Hash::check($request->password, $secretarie->password)  ){


                $access_token= Str::random(64);
              DB::table('secretaries')->where('user_name' ,'=', $request->user_name)->update(['access_token'=> $access_token])  ;

              // get student after change the access
              $secretarie =  DB::table('secretaries')->where('user_name' ,'=', $request->user_name)->first() ;
              if( $secretarie->image != null){
                   $secretarie->image=asset("storage").'/'.$secretarie->image;
              }

                return response()->json(['secretarie'=>$secretarie,

                     'type'=>'secretaries',
                     ],200);

            }else{
                return response()->json(['msg'=>'password not correct' ],404);
           }
        }else{
            return response()->json(['msg'=>'user name not exist' ],404);
        }



}


public function show_initial_appointments($access_token){
    $secretarie =  DB::table('secretaries')->where('access_token' ,'=', $access_token)->first() ;
  if($secretarie){
       $current_date=Carbon::now()->format('Y-m-d');

       $initials =  DB::table('initials')
       ->select('*')
       ->whereDate('date',$current_date)
       ->get() ;

       foreach($initials as $initial){
              $patients =DB::table('reservations')
               ->join('patients','reservations.patient_id','patients.id')
              ->select('patients.id as patient_id','patients.name','patients.gender')
              ->where('reservations.status','!=','deleted')
              ->where('reservations.initial_id',$initial->id)
              ->get() ;
              $initial->patients = $patients ;
        }



       return response()->json(['initials'=>$initials]);


  }else{
    return response()->json(['messages'=>"no token"]);
 }

}




public function search_initial(Request $request ){
    $secretarie =  DB::table('secretaries')->where('access_token' ,'=', $request->access_token)->first() ;
  if($secretarie){

        $date= $request->date;
        $initials =  DB::table('initials')
        ->select('*')
        ->whereDate('date',$request->date)
        ->get() ;


        foreach($initials as $initial){
            $patients =DB::table('reservations')
             ->join('patients','reservations.patient_id','patients.id')
            ->select('patients.id as patient_id','patients.name','patients.gender')
            ->where('reservations.status','!=','deleted')
            ->where('reservations.initial_id',$initial->id)
            ->get() ;
            $initial->patients = $patients ;
      }


         return response()->json(['initials'=>$initials]);


        }else{
            return response()->json(['messages'=>"no token"]);
         }
       //initials

}



public function download_file($access_token){
    // Storage::put('file.txt', 'Your name');

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
               new \PhpOffice\PhpWord\SimpleType\JcTable;
               new \PhpOffice\PhpWord\SimpleType\Jc;


  $secretary = Db::table('secretaries')->select('*')->where('access_token',$access_token)->first();
  if($secretary == null){
    return response()->json(['messages'=>"no token"]);
  }

     $section = $phpWord->addSection();
     $section->addImage(
     'storage\aaup2.png',
     array( 'width' => 500,'height'=> 130,'align'=>'center' ,'topMargin' => -5 , 'spaceAfter' => 0)

     );//center

  //$text='=============================================================================<w:br/>';

 //$section ->addText($text);
   $lineStyle = array('weight' => 1, 'width' => 450, 'height' => 0, 'color' => 000000);
  $section->addLine($lineStyle);
  ///=======================================================================================/


 $date = now()->format('D d-m-Y');
 $date ="Date: " .$date;
 $section->addText($date,  array('bold'=>true, 'size'=>10, 'color'=>'298241'));

 $section->addText("Authorized Visitors" ,array('bold'=>true, 'size'=>20, 'color'=>'EE3A13'),array('align' => 'center'));
 //==================  table ===========================
    //     $section = $phpWord->addSection();

         $styleCell = array('borderTopSize'=>1 ,'borderTopColor' =>'black','borderLeftSize'=>1,
         'borderLeftColor' =>'black','borderRightSize'=>1,'borderRightColor'=>'black','borderBottomSize' =>1,
         'borderBottomColor'=>'black' );

         $TfontStyle = array('bold'=>true, 'italic'=> true, 'size'=>13, 'color'=>'298241',
         'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing'=> 0, 'cellMargin'=>0 );

         $table = $section->addTable('myOwnTableStyle',array('borderSize' => 1,
          'borderColor' => '999999', 'afterSpacing' => 0, 'Spacing'=> 0, 'cellMargin'=>0  ));

          $appointments = ( new SecretarieController)->show_initial_appointments($access_token)->original['initials'];
        //  return response()->json(['appointments'=>$appointments]);
        $table->addRow(-0.5, array('exactHeight' => -5));
        $table->addCell(2500,$styleCell)->addText('Patient ID',$TfontStyle,array('align' => 'center'));
        $table->addCell(2500,$styleCell)->addText('Patient',$TfontStyle,array('align' => 'center'));
        $table->addCell(2500,$styleCell)->addText('Gender',$TfontStyle,array('align' => 'center'));
        $table->addCell(2500,$styleCell)->addText('Time',$TfontStyle,array('align' => 'center'));

           foreach($appointments as $appointment){

           // $table->addRow(-0.5, array('exactHeight' => -5));
           // $table->addCell(2500,$styleCell)->addText($appointment->start_time." " .$appointment->end_time,$TfontStyle);
           // $table->addCell(2000,  $cellRowSpan)->addText('1', null, $cellHCentered);

            foreach($appointment->patients as $patient){
                $table->addRow(-0.5, array('exactHeight' => -5));
                $table->addCell(2500,$styleCell)->addText($patient->patient_id, array(),array('align' => 'center'));
                $table->addCell(2500,$styleCell)->addText($patient->name, array(),array('align' => 'center'));
                $table->addCell(2500,$styleCell)->addText($patient->gender, array(),array('align' => 'center'));
                $table->addCell(2500,$styleCell)->addText($appointment->start_time." " .$appointment->end_time, array(),array('align' => 'center'));

            }


           }


          //==================================================================

          $secretary="secretary: ".$secretary->name;
          $section->addText($secretary,  array('bold'=>true, 'size'=>10, 'color'=>'298241'));


         $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
         try {
             $objWriter->save(storage_path('Authorized Visitors.docx'));
         } catch (Exception $e) {
         }

          response()->download(storage_path('Authorized Visitors.docx'));
          return 'ok';
          return response()->json(['messages'=>"Downloaded successfully"]);

}




public function send_appointments( $access_token){
    $secretarie =  DB::table('secretaries')->where('access_token' ,'=', $access_token)->first() ;
  if($secretarie){
    $result =(new SecretarieController() )->download_file($access_token);

    if($result == 'ok'){
        $email ="rasha.21102000@gmail.com";
        Mail::to($email)->send(new SendAppointments( ),);
        return response()->json(['messages'=>"sended successfully"]);
    }
    return response()->json(['messages'=>"downloading error"]);

  }else{
    return response()->json(['mezssages'=>"no token"]);
 }

}




}
