<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalLog extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'name', 'is_approved'];

    protected $appends = ['proposal'];

    public function getProposalAttribute()
    {
        return Proposal::find($this->attributes['proposal_id']);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::parse($date)->locale('id')->isoFormat('D MMMM Y HH:mm:ss');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
