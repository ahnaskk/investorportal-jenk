<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Admin\Traits\DocumentUploader;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatementController extends Controller
{
    use DocumentUploader;

    public function weekly(Request $request)
    {
        $user_id = $request->user()->id;
        $page_title = 'Statement Report';
        $statements = DB::table('statements')->where('user_id', $user_id)->orderByDesc('to_date')->where('investor_portal', 0)->paginate(50);

        return view('investor.statement.index', compact('statements', 'page_title'));
    }
}
