<?php

namespace App\Http\Controllers;

use Exception;
use Nette\Utils\Random;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TwilioSMSController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index($verification_key ,$phone  ,$firstName , $proccess)
    {  // $phone = "+".str( $phone);
        $receiverNumber = "+".$phone;//"+970569595453";
       // return response()->json(['message'=>  $receiverNumber]);

        $verification_number =$verification_key;// "123456" ;// Str::random(6);
           if($proccess == 1){
            $message = "Hello ".$firstName." Please verify that you own this phone number using the following code : ".$verification_number;

           }elseif($proccess == 2){
            $message = "Hello ".$firstName." Please enter this verification code to be able to change your password : ".$verification_number;
           }elseif($proccess == 3){
            $message = "\n". "مرحبا ".$firstName."\n"." نرجو منك اثبات ملكية رقم الهاتف باستخدام رمز التحقق التالي " .$verification_key;
           }elseif($proccess == 4){
            $message = "\n". "مرحبا ".$firstName."\n"." نرجو تاكيد رغبتك بتغيير كلمةالمرور باستخدام رمز التحقق التالي " .$verification_key;
           }

        try {

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");

            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number,
                'body' => $message]);
             return response()->json(['message'=>  " message sended"]);

          //  dd('SMS Sent Successfully.');

        } catch (Exception $e) {
            return response()->json(['message'=>"Error: ". $e->getMessage()]);

          //  dd("Error: ". $e->getMessage());
        }
    }



    public function initial_sms($initial ,$phone  ,$name , $proccess)
    {  // $phone = "+".str( $phone);
        $receiverNumber = "+".$phone;//"+970569595453";
       // return response()->json(['message'=>  $receiverNumber]);

       // "123456" ;// Str::random(6);
           if($proccess == 1){
            $message = "Hello ".$name." , your reservation has been successfully confirmed.\nYour appointment is on ".$initial->date."  at ".$initial->start_time." o'clock. \nThank you for trusting us.";

           }elseif($proccess == 2){
            $message = "\n". "مرحبا ".$name."\n"."لقد تم ثبيت حجزك بنجاح \n" ."موعدك بتاريخ  ".$initial->date."\n"."في تمام الساعة ".$initial->start_time."\n"."نشكرك على ثقتك بنا";
           }elseif($proccess == 3){
            $message = "\n". "مرحبا ".$name."\n"."نود تذكيرك بأن لديك موعدا غدا بتارخ".$initial->date."\n"."في تمام الساعة ".$initial->start_time."\n"."نشكرك على ثقتك بنا";
           }

        try {

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");

            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number,
                'body' => $message]);
             return response()->json(['message'=>  " message sended"]);

          //  dd('SMS Sent Successfully.');

        } catch (Exception $e) {
            return response()->json(['message'=>"Error: ". $e->getMessage()]);

          //  dd("Error: ". $e->getMessage());
        }
    }





}

