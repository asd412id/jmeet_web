<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;

class JoinMeet extends Model
{
  protected $table = 'join_meet';

  protected $fillable = [
    'uuid',
    'user_id',
    'meet_id',
    '_token',
    'user_data',
    'start',
    'end',
  ];

  protected $dates = ['start','end','created_at','updated_at'];
  protected $casts = [
    'user_data' => Json::class
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function meet()
  {
    return $this->belongsTo(Meet::class);
  }

  public function getStartAttribute($value)
  {
    return $value?date("Y-m-d H:i:s",strtotime($value)):null;
  }

  public function getEndAttribute($value)
  {
    return $value?date("Y-m-d H:i:s",strtotime($value)):null;
  }
}
