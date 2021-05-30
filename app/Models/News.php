<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'student_activity_unit_id', 'title', 'body', 'image_path'
    ];

    protected $appends = [
        'image_url',
        'author_name'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getImageUrlAttribute()
    {
        if ($this->attributes['image_path'] != null) {
            return 'http://192.168.100.5/udita/public' . Storage::url($this->attributes['image_path']);
        } else {
            return null;
        }
    }

    public function getAuthorNameAttribute()
    {
        return News::find($this->attributes['id'])->user->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
