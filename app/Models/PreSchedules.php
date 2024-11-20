<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreSchedules extends Model
{
    use SoftDeletes;

    protected $table = 'pre_schedules';

    protected $fillable = array('matchmaker_id','client_ids','start_time','duration');
}
