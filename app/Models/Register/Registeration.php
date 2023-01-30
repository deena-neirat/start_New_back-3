<?php

namespace App\Models\Registeration;

use App\Models\Clinic\Clinic;
use App\Models\Course\Course;
use App\Models\Doctor\Doctor;
use App\Models\Student\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registeration extends Model
{
    use HasFactory;
    protected $fillable=['student_id','clinic_id'];

    public function doctor()
    {
      return $this->belongsTo(Student::class);
    }
    public function course()
    {
      return $this->belongsTo(Clinic::class);
    }
}
