<?php

namespace App\Models\Requirement;

use App\Models\Course\Course;
use App\Models\Treatment\Treatment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;
    protected $fillable=['name','description','course_id'];


  public function course()
{
  return $this->belongsTo(Course::class);
}
public function registeration()
{
  return $this->hasMany(Treatment::class);
}
}
