<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mailboxrow;

class MailboxController extends Controller
{
    public function index($value = '')
    {
        $mailboxrows = Mailboxrow::with('user')->orderByDesc('timestamp')->paginate(50);

        return view('admin.mailbox.index', compact('mailboxrows'));
    }

    public function view($id = '')
    {
        $mailboxrow = Mailboxrow::with('user')->find($id);

        return view('admin.mailbox.view', compact('mailboxrow'));
    }

    public function jobs()
    {
        $jobs = \DB::table('jobs')->get();

        return view('jobs', compact('jobs'));
    }

    public function failed_jobs()
    {
        $failed_jobs = \DB::table('failed_jobs')->get();

        return view('failed_jobs', compact('failed_jobs'));
    }
}
