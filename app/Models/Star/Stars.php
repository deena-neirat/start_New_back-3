<?php

namespace App\Models\Star;

use Illuminate\Database\Eloquent\Model;
use App\Models\Registeration\Registeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Star extends Model
{
    use HasFactory;
    protected $fillable=['topics','sum','clients_num'];


}

