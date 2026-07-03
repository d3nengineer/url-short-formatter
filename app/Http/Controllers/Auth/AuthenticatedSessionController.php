<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, LoginUserAction $loginUser): RedirectResponse
    {
        $loginUser->handle($request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request, LogoutUserAction $logoutUser): RedirectResponse
    {
        $logoutUser->handle($request);

        return redirect()->route('home');
    }
}
