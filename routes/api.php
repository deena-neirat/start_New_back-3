<?php

use App\Mail\TestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\TwilioSMSController;
use App\Http\Controllers\SecretarieController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\RadiographerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/logout',[AuthController::class,'logout']);  //
Route::post('/login',[AuthController::class,'login']);  //
Route::get('/store/{user_name}/{type}',[AuthController::class,'store']);  //

Route::post('/forget_password',[AuthController::class,'forget_password']);
Route::post('/password_verification',[AuthController::class,'password_verification']);
Route::post('/change_forgotten_password',[AuthController::class,'change_forgotten_password']);
Route::post('/get_name',[AuthController::class,'get_name']);
Route::post('/get_profile',[AuthController::class,'get_profile']);
Route::post('/get_image',[AuthController::class,'get_image']);
Route::post('/set_image',[AuthController::class,'set_image']);
Route::post('/change_password',[AuthController::class,'change_password']);




//Route::get('/students',[StudentController::class,'index']);
Route::get('/student/{id}',[StudentController::class,'get_student_profile']);   // get student profile to show in index page
Route::post('/student/store',[StudentController::class,'store']);  // create student
Route::get('/student/get_student_courses/{id}',[StudentController::class,'get_student_courses']);   // get student profile to show in index page
Route::post('/student/get_course_info',[StudentController::class,'get_course_info']);   // get student profile to show in index page
Route::post('/student/edit_image',[StudentController::class,'edit_image']);
Route::post('/student/update_image',[StudentController::class,'update_image']);  // create student
Route::post('/student/change_password',[StudentController::class,'change_password']);  // create student
Route::post('/student/login',[StudentController::class,'login']);  // create student
Route::post('/student/logout',[StudentController::class,'logout']);  // create student
Route::post('/student/edit',[StudentController::class,'edit']);  // get student data to update it
Route::post('/student/get_req_status',[StudentController::class,'get_req_status']);  // get student data to update it
Route::get('/student/forget_password',[StudentController::class,'forget_password']);  // get student data to update it





Route::post('/patient/register',[PatientController::class,'register']);  // create student
Route::post('/patient/login',[PatientController::class,'login']);  // login
Route::post('/patient/mobile_verification', [PatientController::class, 'mobile_verification']);
Route::post('/patient/show_selected_date_initials',[PatientController::class,'show_selected_date_initials']);
Route::post('/patient/select_initial',[PatientController::class,'select_initial']);
Route::delete('/patient/delete_initial/{access_token}',[PatientController::class,'delete_initial']);
Route::get('/patient/get_next_treatments/{access_token}',[PatientController::class,'get_next_treatments']);
Route::get('/patient/get_reserved_initials/{access_token}',[PatientController::class,'get_reserved_initials']);
Route::get('/patient/get_next_initial/{access_token}',[PatientController::class,'get_next_initial']);

Route::post('/patient/get_selected_file',[PatientController::class,'get_selected_file']);
Route::get('/patient/get_patient_files/{access_token}',[PatientController::class,'get_patient_files']);
Route::post('/patient/send_comment',[PatientController::class,'send_comment']);
Route::post('/patient/update_initial',[PatientController::class,'update_initial']);
Route::get('/patient/get_stars_topics/{access_token}',[PatientController::class,'get_stars_topics']);
Route::post('/patient/stars_evaluation',[PatientController::class,'stars_evaluation']);
Route::post('/patient/cancel_treatment',[PatientController::class,'cancel_treatment']);


Route::post('/patient/sendSMS', [TwilioSMSController::class, 'index']);







Route::post('/assistant/store',[AssistantController::class,'store']);  // create student
Route::post('/assistant/create_patient_file',[AssistantController::class,'create_patient_file']);  // logout
Route::post('/assistant/get_level_courses',[AssistantController::class,'get_level_courses']);  // logout
Route::post('/assistant/get_course_sections',[AssistantController::class,'get_course_sections']);  // logout
Route::post('/assistant/show_section_students',[AssistantController::class,'show_section_students']);



Route::post('/assistant/show_patient_files',[AssistantController::class,'show_patient_files']);  // logout
Route::post('/assistant/add_treatments',[AssistantController::class,'add_treatments']);
Route::post('/assistant/add_treatment',[AssistantController::class,'add_treatment']);



Route::post('/secretarie/store',[SecretarieController::class,'store']);  // create Secretarie
Route::post('/secretarie/login',[SecretarieController::class,'login']);  // login secretarie
Route::get('/secretarie/show_initial_appointments/{access_token}',[SecretarieController::class,'show_initial_appointments']);  // login secretarie
Route::post('/secretarie/search_initial',[SecretarieController::class,'search_initial']);  // login secretarie
Route::post('/secretarie/change_password',[SecretarieController::class,'change_password']);  // create Secretarie
Route::post('/secretarie/update_image',[SecretarieController::class,'update_image']);  // create Secretarie
Route::get('/secretarie/download_file/{access_token}',[SecretarieController::class,'download_file']);  // login secretarie
Route::get('/secretarie/send_appointments/{access_token}',[SecretarieController::class,'send_appointments']);  // login secretarie

//

Route::post('/radiographer/store',[RadiographerController::class,'store']);  // create Secretarie
Route::post('/radiographer/login',[RadiographerController::class,'login']);  // login secretarie
Route::post('/radiographer/logout',[RadiographerController::class,'logout']);  // login secretarie
Route::post('/radiographer/change_password',[RadiographerController::class,'change_password']);  // create Secretarie
Route::post('/radiographer/update_image',[RadiographerController::class,'update_image']);  // create Secretarie
Route::post('/radiographer/show_patient_files',[RadiographerController::class,'show_patient_files']);  // login secretarie
Route::post('/radiographer/set_patient_image',[RadiographerController::class,'set_patient_image']);  // login secretarie



//doctors
Route::post('/doctor/store',[DoctorController::class,'store']);  // create Secretarie
Route::post('/doctor/login',[DoctorController::class,'login']);  // login secretarie
Route::post('/doctor/logout',[DoctorController::class,'logout']);  // login secretarie
Route::post('/doctor/change_password',[DoctorController::class,'change_password']);  // create Secretarie
Route::post('/doctor/edit_image',[DoctorController::class,'edit_image']);
Route::post('/doctor/update_image',[DoctorController::class,'update_image']);
Route::post('/doctor/get_clinic_student',[DoctorController::class,'get_clinic_student']);  // create Secretarie
Route::post('/doctor/get_student_treatments',[DoctorController::class,'get_student_treatments']);
Route::post('/doctor/update_treatment_status',[DoctorController::class,'update_treatment_status']);
Route::get('/doctor/get_doctor_clinics/{id}',[DoctorController::class,'get_doctor_clinics']);
Route::post('/doctor/get_students_req',[DoctorController::class,'get_students_req']);
Route::post('/doctor/get_student_req',[DoctorController::class,'get_student_req']);

//get_students_req


Route::post('/admin/store',[AdminController::class,'store']);  // create Secretarie
Route::post('/admin/login',[AdminController::class,'login']);  // login secretarie
Route::post('/admin/logout',[AdminController::class,'logout']);  // login secretarie
Route::post('/admin/change_password',[AdminController::class,'change_password']);  // create Secretarie
Route::post('/admin/update_image',[AdminController::class,'update_image']);
Route::post('/admin/add_initial',[AdminController::class,'add_initial']);
Route::get('/admin/get_sentiments_result',[AdminController::class,'get_sentiments_result']);
Route::get('/show_stars_evaluation',[AdminController::class,'show_stars_evaluation']);  // get student data to update it








Route::post('/course/create',[CourseController::class,'create']);
Route::get('/course/get_courses',[CourseController::class,'get_courses']);

Route::post('/requirement/create',[RequirementController::class,'create']);
Route::get('/requirement/get_requirement',[RequirementController::class,'get_requirement']);



