<?php

namespace App\Models\Course;

use App\Models\Clinic\Clinic;
use App\Models\Requirement\Requirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable=['name','level','image','updated_at','created_at'];


public function requirement()
{
  return $this->hasMany(Requirement::class);
}
public function clinic()
{
  return $this->hasMany(Clinic::class);
}

}
