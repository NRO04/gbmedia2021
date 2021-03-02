<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function login(Request $request)
    {
        if(empty(tenant('support_url'))) {
            return abort(404);
        }

        $user = Auth::user();
        $user_email = $user->email;
        $user_document_number = $user->document_number;
        $url = tenant('support_url') . "/scp/login.php";

        return view('adminModules.support.login', ['email' => $user_email, 'password' => $user_document_number, 'url' => $url]);
    }
}
