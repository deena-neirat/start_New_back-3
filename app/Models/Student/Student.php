<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;
use App\Models\Registeration\Registeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;
    protected $fillable=['id','user_name','name','ar_name','phone','email','password','image','level','gpa','access_token','verification_key'];

    public function registeration()
{
  return $this->hasMany(Registeration::class);
}

}

