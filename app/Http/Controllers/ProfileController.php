<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return view('profile', [
            'user'    => $user,
            'current' => $user->currentAccess(),
            'pending' => $user->pendingAccess(),
            'history' => $user->accessRequests()->take(10)->get(),
        ]);
    }
}
