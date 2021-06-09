<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Documentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'activity_id', 'student_activity_unit_id', 'file_path'
    ];

    protected $appends = [
        'file_url',
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
}
