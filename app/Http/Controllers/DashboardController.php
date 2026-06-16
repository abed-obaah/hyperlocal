<?php

namespace App\Http\Controllers;

/**
 * Serves the built admin/restaurant dashboard SPA (in public/dashboard).
 * Any /dashboard/* path returns the SPA shell so client-side routing works.
 * Invokable (not a closure) so `php artisan route:cache` stays valid.
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        return response()->file(public_path('dashboard-assets/index.html'));
    }
}
