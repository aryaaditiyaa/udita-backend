<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\ProposalLog;

class ProposalLogController extends Controller
{
    public function index()
    {
        $proposal_log = ProposalLog::with('proposal')
            ->latest()
            ->limit(50)
            ->get();

        return ResponseFormatter::success([
            'proposal_log' => $proposal_log
        ], 'Fetch all successful');
    }
}
