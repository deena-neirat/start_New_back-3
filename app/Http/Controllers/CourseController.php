<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function create(Request $request)
  {
    // Ahmad12345678   //  Aya12345678
      //validation

      $validator= Validator::make($request->all(),
          [
              'name'=>'required',
              'level'=>'required',
              'image'=>'required',
          ]);
          $image = Storage::putFile('student', $request->file('image'));

      // create and login
      $course= Course::create([

          'name'=>$request->name,
          'level'=>$request->level,
          'image'=> $image,

         ]);

         return response()->json(['message'=>'course created' ]);


  }

  public function get_courses(){
    $courses =  DB::table('courses')->get() ;
    return response()->json(['message'=>$courses ]);

  }
}
