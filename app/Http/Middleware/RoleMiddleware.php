<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: middleware('role:admin') or middleware('role:pengurus,pelatih')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();

        $allowedRoles = $roles;
        // Pengurus is a superset of anggota, so allow pengurus to access anggota routes if needed.
        if (in_array('anggota', $roles)) {
            $allowedRoles[] = 'pengurus';
        }

        if (!in_array($user->role, $allowedRoles)) {
            // Redirect to the correct dashboard instead of showing an error
            return redirect($this->dashboardFor($user->role));
        }

        return $next($request);
    }

    private function dashboardFor(string $role): string
    {
        return match ($role) {
            'admin'    => route('admin.home'),
            'pengurus' => route('pengurus.home'),
            'pelatih'  => route('pelatih.home'),
            default    => route('anggota.home'),
        };
    }
}
