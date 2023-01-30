<?php

namespace App\Models\Treatment;

use App\Models\Disease\Disease;
use App\Models\Requirement\Requirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory;
    protected $fillable=['registeration_id','requirement_id','disease_id','tooth','status','description','start_date','end_date'];

    public function disease()
    {
      return $this->belongsTo(Disease::class);
    }
    public function requirement()
    {
      return $this->belongsTo(Requirement::class);
    }
}
