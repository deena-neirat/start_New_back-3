<?php

namespace App\Models\Reservation;

use App\Models\Disease\Disease;
use App\Models\Initial\Initial;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient_Initial extends Model
{
    use HasFactory;
    protected $fillable=['id','patient_id','initial_id','status','created_at'];


    public function initial()
    {
      return $this->belongsTo(Initial::class);
    }
    public function patient()
    {
      return $this->belongsTo(Patient::class);
    }
    public function disease()
    {
      return $this->hasMany(Disease::class);
    }

}

