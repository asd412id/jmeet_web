<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Meet extends Model
{
    protected $fillable = [
      'uuid',
      'user_id',
      'name',
      'desc',
      'start',
      'end',
      '_token',
      'active',
    ];

    protected $dates = ['start','end','created_at','updated_at'];

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function join_meet()
    {
      return $this->hasMany(JoinMeet::class);
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
