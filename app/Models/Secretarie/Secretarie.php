<?php

namespace App\Models\Secretarie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secretarie extends Model
{
    use HasFactory;
    protected $fillable=['user_name','name','ar_name','email','password','image','access_token','verification_key'];

}
