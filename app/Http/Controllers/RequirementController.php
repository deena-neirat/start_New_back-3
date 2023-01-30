<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Requirement\Requirement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RequirementController extends Controller
{

    public function create(Request $request)
    {

        $validator= Validator::make($request->all(),
            [
                'name'=>'required',
                'course_id'=>'required',

            ]);

        // create and login
        $course= Requirement::create([

            'name'=>$request->name,
            'course_id'=>$request->course_id,


           ]);

           return response()->json(['message'=>'Requirement created' ]);


    }


    public function get_requirement(){
        $req =  DB::table('requirements')->get() ;
        return response()->json(['message'=>$req ]);

      }
}
