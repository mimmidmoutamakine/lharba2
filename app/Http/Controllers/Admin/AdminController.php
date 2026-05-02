<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LesenTopic;
use App\Models\HoerenTopic;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'lesenCount'  => LesenTopic::count(),
            'hoerenCount' => HoerenTopic::count(),
            'lesenRecent' => LesenTopic::latest()->take(5)->get(),
            'hoerenRecent'=> HoerenTopic::latest()->take(5)->get(),
        ]);
    }

    public function lesenIndex()
    {
        return view('admin.lesen.index', [
            'topics' => LesenTopic::latest()->paginate(20),
        ]);
    }

    public function lesenDestroy(LesenTopic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted.');
    }

    public function lesenToggle(LesenTopic $topic)
    {
        $topic->update(['is_published' => !$topic->is_published]);
        return back()->with('success', 'Status updated.');
    }

    public function hoerenIndex()
    {
        return view('admin.hoeren.index', [
            'topics' => HoerenTopic::latest()->paginate(20),
        ]);
    }

    public function hoerenDestroy(HoerenTopic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted.');
    }
}
