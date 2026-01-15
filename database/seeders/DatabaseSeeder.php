<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = ['Super Admin', 'Manager', 'Staff'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Organization Data
        $companies = ['TechCorp', 'SalesInc'];
        foreach ($companies as $name) Company::firstOrCreate(['name' => $name]);

        $divisions = ['IT', 'HR', 'Sales', 'Marketing'];
        foreach ($divisions as $name) Division::firstOrCreate(['name' => $name]);

        $branches = ['New York', 'London', 'Tokyo', 'Jakarta'];
        foreach ($branches as $name) Branch::firstOrCreate(['name' => $name]);

        $positions = ['Director', 'Manager', 'Staff', 'Intern'];
        foreach ($positions as $name) Position::firstOrCreate(['name' => $name]);

        // 3. Default User
        $user = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Super Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Assign Role if not exists
        if (!$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }

        // Assign UserInfo
        $roleId = Role::where('name', 'Super Admin')->first()->id;
        $companyId = Company::first()->id;
        $divisionId = Division::first()->id;
        $branchId = Branch::first()->id;
        $positionId = Position::first()->id;

        UserInfo::updateOrCreate(
            ['user_id' => $user->id],
            [
                'role_id' => $roleId,
                'company_id' => $companyId,
                'division_id' => $divisionId,
                'branch_id' => $branchId,
                'position_id' => $positionId,
            ]
        );

        $this->call(UserSeeder::class);
    }
}
