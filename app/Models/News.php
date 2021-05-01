<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'body', 'image_path'
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
            return url('http://192.168.100.5/udita/public') . '/storage/' . str_replace("\\", "/", $this->attributes['image_path']);
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
