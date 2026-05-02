<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->tab, ['pending', 'approved', 'denied'], true) ? $request->tab : 'pending';

        $requests = AccessRequest::with(['user', 'approver'])
            ->where('status', $tab)
            ->orderByDesc($tab === 'pending' ? 'created_at' : 'decided_at')
            ->paginate(30);

        $counts = [
            'pending'  => AccessRequest::where('status', 'pending')->count(),
            'approved' => AccessRequest::where('status', 'approved')->count(),
            'denied'   => AccessRequest::where('status', 'denied')->count(),
        ];

        return view('admin.access.index', compact('requests', 'tab', 'counts'));
    }

    public function approve(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->isPending()) {
            $accessRequest->update([
                'status'      => AccessRequest::STATUS_APPROVED,
                'approved_by' => $request->user()->id,
                'decided_at'  => now(),
                'admin_note'  => $request->input('note'),
            ]);
        }
        return back()->with('ok', 'الطلب تصادق عليه.');
    }

    public function deny(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->isPending()) {
            $accessRequest->update([
                'status'      => AccessRequest::STATUS_DENIED,
                'approved_by' => $request->user()->id,
                'decided_at'  => now(),
                'admin_note'  => $request->input('note'),
            ]);
        }
        return back()->with('ok', 'الطلب تردّ.');
    }
}
