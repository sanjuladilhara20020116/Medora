<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * Return real dashboard data for the administrator.
     */
    public function getAdminDashboard(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),

            /*
            |--------------------------------------------------------------------------
            | Core HMS Statistics
            |--------------------------------------------------------------------------
            |
            | These tables already exist, so these numbers are always real.
            |
            */

            'core_statistics' => [
                'total_users' => User::count(),

                'active_users' => User::where('is_active', true)->count(),

                'active_roles' => DB::table('roles')
                    ->where('is_active', true)
                    ->count(),

                'active_departments' => DB::table('departments')
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->count(),
            ],

            /*
            |--------------------------------------------------------------------------
            | Future HMS Module Statistics
            |--------------------------------------------------------------------------
            |
            | A module only returns a number when its real table exists.
            |
            */

            'module_statistics' => [
                'patients' => $this->tableCount('patients'),
                'doctors' => $this->tableCount('doctors'),
                'appointments' => $this->tableCount('appointments'),
                'lab_requests' => $this->tableCount('lab_requests'),
                'medicines' => $this->tableCount('medicines'),
                'invoices' => $this->tableCount('invoices'),
            ],

            /*
            |--------------------------------------------------------------------------
            | User Role Distribution
            |--------------------------------------------------------------------------
            */

            'role_distribution' => $this->roleDistribution(),

            /*
            |--------------------------------------------------------------------------
            | Recent Authentication Activity
            |--------------------------------------------------------------------------
            */

            'recent_logins' => $this->recentLogins(),
        ];
    }


    /**
     * Count records only if the module's table exists.
     */
    private function tableCount(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [
                'available' => false,
                'value' => null,
            ];
        }

        $query = DB::table($table);

        /*
         * Ignore soft-deleted records when the table supports soft deletes.
         */
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return [
            'available' => true,
            'value' => $query->count(),
        ];
    }


    /**
     * Return the number of users assigned to each active role.
     */
    private function roleDistribution(): array
    {
        return DB::table('roles')
            ->leftJoin('users', function ($join) {
                $join->on('users.role_id', '=', 'roles.id')
                    ->whereNull('users.deleted_at');
            })
            ->where('roles.is_active', true)
            ->select(
                'roles.slug',
                'roles.name',
                DB::raw('COUNT(users.id) as total')
            )
            ->groupBy(
                'roles.id',
                'roles.slug',
                'roles.name'
            )
            ->orderBy('roles.id')
            ->get()
            ->map(function ($role) {
                return [
                    'slug' => $role->slug,
                    'name' => $role->name,
                    'total' => (int) $role->total,
                ];
            })
            ->all();
    }


    /**
     * Return the five most recently logged-in users.
     */
    private function recentLogins(): array
    {
        return User::query()
            ->with('role:id,name,slug')
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit(5)
            ->get([
                'id',
                'role_id',
                'name',
                'username',
                'last_login_at',
            ])
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,

                    'role' => [
                        'name' => $user->role?->name,
                        'slug' => $user->role?->slug,
                    ],

                    'last_login_at' => $user->last_login_at?->toIso8601String(),
                ];
            })
            ->all();
    }
}