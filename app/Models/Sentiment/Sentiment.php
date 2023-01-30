<?php

namespace App\Models\Sentiment;

use Illuminate\Database\Eloquent\Model;
use App\Models\Registeration\Registeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sentiment extends Model
{
    use HasFactory;
    protected $fillable=['text','value','created_at'];


}

