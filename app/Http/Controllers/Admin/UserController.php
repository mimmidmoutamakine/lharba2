<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $users = User::query()
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            }))
            ->withCount(['accessRequests as approved_count' => fn ($qb) => $qb->where('status', 'approved')])
            ->orderByDesc('is_admin')
            ->orderByDesc('created_at')
            ->paginate(40);

        return view('admin.users.index', compact('users', 'q'));
    }

    public function toggleAdmin(Request $request, User $user)
    {
        // Prevent self-demote (so we never get locked out without admins)
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'ماتقدرش تبدل صلاحيتك ديال راسك.']);
        }

        $user->is_admin = ! $user->is_admin;
        $user->save();

        return back()->with('ok', $user->is_admin ? "{$user->name} ولا أدمن." : "تنحت من {$user->name} صلاحية الأدمن.");
    }
}
