<?php

namespace App\Models\Clinic;

use App\Models\Course\Course;
use App\Models\Doctor\Doctor;
use Illuminate\Database\Eloquent\Model;
use App\Models\Registeration\Registeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clinic extends Model
{
    use HasFactory;
    protected $fillable=['name','day','start_time','end_time','dead_line','hall','doctor_id'];

    public function doctor()
    {
      return $this->belongsTo(Doctor::class);
    }
    public function course()
    {
      return $this->belongsTo(Course::class);
    }
    public function registeration()
    {
      return $this->hasMany(Registeration::class);
    }
}
