<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Implicitly grant "SuperAdmin" role all permissions
        Gate::before(function (User $user, string $ability) {
            if ($user->role === Role::SuperAdmin) {
                return true;
            }
        });
    }
}
