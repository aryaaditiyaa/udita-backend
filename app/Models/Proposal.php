<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'student_activity_unit_id', 'category', 'description', 'status', 'file_path', 'title'
    ];

    protected $appends = [
        'file_url',
        'applicant_name'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getFileUrlAttribute()
    {
        if ($this->attributes['file_path'] != null) {
            return 'http://192.168.100.5/udita/public' . Storage::url($this->attributes['file_path']);
        } else {
            return null;
        }
    }

    public function getApplicantNameAttribute()
    {
        return Proposal::find($this->attributes['id'])->user->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
