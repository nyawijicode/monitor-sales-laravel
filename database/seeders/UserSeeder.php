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
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password'); // Default password for all generated users

        // Get all organizational units
        $companies = Company::all();
        $divisions = Division::all();
        $branches = Branch::all();
        $positions = Position::all();

        // Ensure we have at least one company and branch for assignment
        $defaultCompany = $companies->first() ?? Company::create(['name' => 'Default Company']);
        $defaultBranch = $branches->first() ?? Branch::create(['name' => 'Headquarters']);

        foreach ($divisions as $division) {
            foreach ($positions as $position) {
                // Generate a unique identifier for the user
                $divSlug = Str::slug($division->name);
                $posSlug = Str::slug($position->name);

                $username = "{$divSlug}.{$posSlug}";
                $email = "{$username}@example.com";
                $name = "{$division->name} {$position->name}";

                // check if user exists
                if (User::where('email', $email)->exists()) {
                    continue;
                }

                $user = User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'is_active' => true,
                ]);

                // Determine Role based on Position
                // Simple logic: Director/Manager -> Manager role, others -> Staff role
                $roleName = in_array($position->name, ['Director', 'Manager']) ? 'Manager' : 'Staff';
                $role = Role::where('name', $roleName)->first();

                if ($role) {
                    $user->assignRole($role);
                }

                // Create UserInfo
                UserInfo::create([
                    'user_id' => $user->id,
                    'company_id' => $defaultCompany->id,
                    'division_id' => $division->id,
                    'branch_id' => $defaultBranch->id,
                    'position_id' => $position->id,
                    'role_id' => $role ? $role->id : null,
                ]);

                $this->command->info("Created user: {$name} ({$email})");
            }
        }
    }
}
