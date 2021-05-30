<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReadAllNotificationsStatus extends Model
{
    use HasFactory;

    protected $table = 'user_read_all_notifications_status';

    protected $fillable = ([
        'user_id', 'is_read'
    ]);
}
