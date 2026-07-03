<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class RegisterUserAction
{
    public function handle(RegisterUserRequest $request): User
    {
        $user = User::query()->create($request->safe()->only([
            'name',
            'email',
            'password',
        ]));

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        Log::info('User registered successfully.', [
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
