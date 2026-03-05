<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'role' => ['required', 'in:worker,company'],
        ])->validate();

        $user = User::create([
            'name' => null,
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $input['role'],
        ]);

        Role::firstOrCreate(['name' => $input['role'], 'guard_name' => 'web']);
        $user->assignRole($input['role']);

        return $user;
    }
}
