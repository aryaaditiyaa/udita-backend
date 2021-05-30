<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SauMemberRegistration extends Model
{
    use HasFactory;

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y');
    }

    protected $fillable = [
        'user_id', 'student_activity_unit_id', 'status'
    ];

    protected $appends = [
        'student_activity_unit_name',
        'applicant_name'
    ];

    public function getApplicantNameAttribute()
    {
        return SauMemberRegistration::find($this->attributes['id'])->user->name;
    }

    public function getStudentActivityUnitNameAttribute()
    {
        return SauMemberRegistration::find($this->attributes['id'])->student_activity_unit->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student_activity_unit()
    {
        return $this->belongsTo(StudentActivityUnit::class);
    }
}
