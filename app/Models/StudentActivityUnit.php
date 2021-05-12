<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentActivityUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'image_path', 'proposal_id', 'status'
    ];

    protected $appends = [
        'image_path'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getImagePathAttribute()
    {
        if ($this->attributes['image_path'] != null) {
            return url(env('APP_URL') . '/udita/public') . '/storage/' . $this->attributes['image_path'];
        } else {
            return null;
        }
    }
}
