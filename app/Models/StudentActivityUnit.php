<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentActivityUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'image_path', 'proposal_id', 'status'
    ];

    protected $appends = [
        'image_url',
        'proposal_file_path',
        'total_members',
//        'total_events',
//        'total_routine_activities'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getProposalFilePathAttribute()
    {
        return 'http://192.168.100.5/udita/public' . Storage::url(StudentActivityUnit::find($this->attributes['id'])->proposal->file_path);
    }

    public function getTotalMembersAttribute()
    {
        return User::where('student_activity_unit_id', $this->attributes['id'])->count();
    }

    public function getImageUrlAttribute()
    {
        if ($this->attributes['image_path'] != null) {
            return 'http://192.168.100.5/udita/public' . Storage::url($this->attributes['image_path']);
        } else {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->attributes['name']) . '&color=7F9CF5&background=EBF4FF';
        }
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
