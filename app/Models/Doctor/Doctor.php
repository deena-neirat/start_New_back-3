<?php

namespace App\Models\Doctor;

use App\Models\Clinic\Clinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable=['user_name','name','ar_name','email','password','image','access_token','verification_key'];

    public function clinic()
    {
      return $this->hasMany(Clinic::class);
    }
}
