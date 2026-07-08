<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Transforms profile_picture into a full public URL so Vue pages
     * never have to know whether the path starts with /storage/ or not.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user && $user->profile_picture) {
            // Google SSO avatars come as full https:// URLs — leave those alone.
            // Locally uploaded paths look like "profile-pictures/abc.jpg" — prefix them.
            if (!str_starts_with($user->profile_picture, 'http')) {
                $user->profile_picture = Storage::url($user->profile_picture);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
