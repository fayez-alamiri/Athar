<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale', 'ar');

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        View::share('currentLocale', $locale);
        View::share('currentDir', $dir);
        View::share('textDirection', $dir);
        View::share('brandName', config("brand.name.{$locale}", config('brand.technical_name', 'ATHAR')));
        View::share('brandSubtitle', config("brand.wordmark_subtitle.{$locale}", ''));

        return $next($request);
    }
}