<?php

namespace App\Http\Controllers;

use App\Actions\ShortLinks\ListUserShortLinksAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, ListUserShortLinksAction $listUserShortLinks): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('dashboard', [
            'shortLinks' => $listUserShortLinks->handle($user),
        ]);
    }
}
