<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'activity_id', 'is_attended'
    ];

    protected $appends = [
        'activity_detail',
        'user_detail'
    ];

    public function getActivityDetailAttribute()
    {
        return Activities::find($this->attributes['activity_id']);
    }

    public function activities()
    {
        return $this->belongsTo(Activities::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y HH:mm:ss');
    }

    public function getUserDetailAttribute()
    {
        return Attendance::find($this->attributes['id'])->user;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
