<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{

  protected $fillable = ['job_title','job_department','description', 'position'];
}
