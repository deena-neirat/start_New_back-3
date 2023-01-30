<?php

namespace App\Models\Patient;

use App\Models\Disease\Disease;
use App\Models\Initial\Initial;
use App\Models\Reservation\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;
    protected $fillable=['name','ar_name','id','password','date_of_birth','gender','bookings_num','phone','image','address','verification_key','verified','access_token'];




    public function disease()
    {
      return $this->hasMany(Disease::class);
    }
    public function patient_initial()
    {
      return $this->hasMany(Reservation::class);
    }
    public function initial()
    {
      return $this->belongsTo(Initial::class);
    }
}

