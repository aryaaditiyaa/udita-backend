<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Activities extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $appends = [
        'proposal_file_url',
        'documentation_file_url',
    ];

    protected $fillable = [
        'proposal_id', 'documentation_id', 'category', 'name', 'description', 'status', 'proposal_id', 'student_activity_unit_id', 'started_at', 'ended_at'
    ];

    protected $dates = [
        'started_at',
        'ended_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('Y-MM-DD HH:mm:ss');
    }

    public function getProposalFileUrlAttribute()
    {
        if ($this->attributes['proposal_id'] != null) {
            return 'http://192.168.100.5/udita/public' . Storage::url(Proposal::find($this->attributes['proposal_id'])->file_path);
        } else {
            return null;
        }
    }

    public function getDocumentationFileUrlAttribute()
    {
        if ($this->attributes['documentation_id'] != null) {
            return 'http://192.168.100.5/udita/public' . Storage::url(Documentation::find($this->attributes['documentation_id'])->file_path);
        } else {
            return null;
        }
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function documentation(){
        return $this->belongsTo(Documentation::class);
    }
}
