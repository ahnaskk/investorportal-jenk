<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Mailboxrow;
use Illuminate\Support\Facades\Auth;

class MailboxController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = Auth::user()->id;

            return $next($request);
        });
    }

    public function index($value = '')
    {
        $mailboxrows = Mailboxrow::where('investor_public', 1)->orWhere('permission_user', $this->user_id)->with('user')->orderByDesc('timestamp')->paginate(50);

        return view('investor.mailbox.index', compact('mailboxrows'));
    }

    public function view($id = '')
    {
        $mailboxrow = Mailboxrow::with('user')->where('investor_public', 1)->orWhere('permission_user', $this->user_id)->find($id);

        return view('investor.mailbox.view', compact('mailboxrow'));
    }
}
