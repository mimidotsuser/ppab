<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Traits\AskWithValidation;
use App\Utils\UserUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class createUserAccount extends Command
{
    use AskWithValidation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user via command line';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(User $user)
    {
        $roles = DB::table('roles')->get(['id', 'name']);

        $user->first_name = $this->askWithValidation('Enter the first name', 'required');
        $user->last_name = $this->ask('Enter the last name (optional)');
        $user->email = $this
            ->askWithValidation('Enter user email', 'required|email|unique:users,email');
        $user->password = Hash::make($this->secret('Enter user\'s password'));
        $user->status = UserUtils::Active;

        $roleName = $this->choice('Choose the user\'s role',
            $roles->pluck('name')->toArray(), 0, 3);

        $user->role_id = $roles->where('name', $roleName)->firstOrFail()->id;
        try {
            $user->save();
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
