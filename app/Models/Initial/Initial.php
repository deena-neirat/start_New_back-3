<?php

namespace App\Models\Initial;

use App\Models\Patient\Patient;
use App\Models\Reservation\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Initial extends Model
{
    use HasFactory;
    protected $fillable=['id','day','start_time','end_time','seats','date'];

    public function reservation()
    {
      return $this->hasMany(Reservation::class);
    }
    public function patient()
    {
      return $this->hasMany(Patient::class);
    }

}
