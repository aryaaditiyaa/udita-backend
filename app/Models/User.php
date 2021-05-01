<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'profile_photo_url'
    ];

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->attributes['profile_photo_path'] != null) {
            return url('http://192.168.100.5/udita/public') . '/storage/' . str_replace("\\", "/", $this->attributes['profile_photo_path']);
        } else {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->attributes['name']) . '&color=7F9CF5&background=EBF4FF';
        }
    }
}
