<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetAppLanguage
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Cek bahasa pilihan di Session (jika user pernah pilih manual)
        if (session()->has('locale')) {
            App::setLocale(session('locale'));
        }
        // 2. Jika tidak ada di session, baca bahasa dari Browser user
        else {
            $userLanguage = $request->getPreferredLanguage(['id', 'en']); // Hanya izinkan id atau en
            App::setLocale($userLanguage);
        }

        return $next($request);
    }
}
