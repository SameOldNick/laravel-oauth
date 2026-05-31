<?php

namespace Workbench\App\Actions\Fortify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers as CreatesNewUsersContract;
use Workbench\App\Models\User;

class CreateNewUser implements CreatesNewUsersContract
{
    private bool $skipPasswordValidation = false;

    public function skipsPasswordValidation(): bool
    {
        return $this->skipPasswordValidation;
    }

    /**
     * Skip password validation.
     *
     * @return $this
     */
    public function skipPasswordValidation(): static
    {
        $this->skipPasswordValidation = true;

        return $this;
    }

    /**
     * Don't skip validation.
     *
     * @return $this
     */
    public function dontSkipPasswordValidation(): static
    {
        $this->skipPasswordValidation = false;

        return $this;
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
        ];

        if (! $this->skipsPasswordValidation()) {
            $rules['password'] = 'required|string|min:8';
        }

        Validator::make($input, $rules)->validate();

        return $this->createUser($input);
    }

    /**
     * Create user with given input.
     */
    protected function createUser(array $input): User
    {
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        return $user;
    }
}
