<?php

namespace App\Models\Radiographer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radiographer extends Model
{
    use HasFactory;
    protected $fillable=['id','user_name','name','ar_name','email','password','image','access_token','verification_key'];


}
