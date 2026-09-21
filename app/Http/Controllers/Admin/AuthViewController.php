<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AuthViewController extends Controller
{
    /**
     * Tampilkan halaman login admin.
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }
}
