<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use Illuminate\Http\Request;

class AdminViewController extends Controller
{
    public function index()
    {
        $authToken = AuthHelper::getDefaultLoginToken();

        return view('vue.admin', compact('authToken'));
    }
}
