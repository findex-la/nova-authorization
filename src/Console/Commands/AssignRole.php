<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authorization:assign-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign an existing role to an user.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $userClass = config('auth.providers.users.model');
            $users = $userClass::all();
            $userOptions = $users->pluck('name')->toArray();
            $selectedUser = $this->choice('What user do you want to use?', $userOptions);
            $user = $users->where('name', $selectedUser)->first();

            $roles = Role::all();
            $roleOptions = $roles->pluck('name')->toArray();
            $selectRole = $this->choice('What role do you want to assign?', $roleOptions);
            $role = $roles->where('name', $selectRole)->first();

            $user->assignRole($role);

            $this->info('Role has been successfully assigned.');
        } catch (Exception $ex) {
            $this->error('Something went wrong, operation not completed.');
        }
    }
}
