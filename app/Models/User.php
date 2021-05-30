<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
        'phone_number', 'role', 'student_activity_unit_id', 'profile_photo_path'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
        'student_activity_unit_name'
    ];

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->attributes['profile_photo_path'] != null) {
            return 'http://192.168.100.5/udita/public' . Storage::url($this->attributes['profile_photo_path']);
        } else {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->attributes['name']) . '&color=7F9CF5&background=EBF4FF';
        }
    }

    public function getStudentActivityUnitNameAttribute()
    {
        if ($this->attributes['student_activity_unit_id'] != null) {
            return StudentActivityUnit::whereId($this->attributes['student_activity_unit_id'])->value('name');
        } else {
            return null;
        }
    }
}
