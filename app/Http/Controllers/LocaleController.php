<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        $request->session()->put('locale', $locale);
        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }
        App::setLocale($locale);

        Cookie::queue(Cookie::make(
            name: 'locale',
            value: $locale,
            minutes: 60 * 24 * 365,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: false,
            raw: false,
            sameSite: 'Lax'
        ));

        return redirect()->back(fallback: route('quotes.index'))
            ->withHeaders([
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            ]);
    }
}