<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Helpers\NotificationInboxService;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Feed dropdown: sync event baru → unread badge → 20 item + worklist ringkas.
     */
    public function index(NotificationInboxService $inbox)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'unread' => 0,
                'total' => 0,
                'items' => [],
                'groups' => [],
                'worklist' => [],
            ]);
        }

        return response()->json($inbox->feed($user));
    }

    /**
     * Halaman semua notifikasi (histori + filter).
     */
    public function page(Request $request, NotificationInboxService $inbox)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $filter = $request->get('filter'); // unread|read|null
        $page = (int) $request->get('page', 1);
        $data = $inbox->history($user, $page, 30, $filter);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        return view('masterweb::module.admin.notifications.index', [
            'initial' => $data,
            'filter' => $filter,
            'privilegeName' => optional($user->getlevel)->name,
        ]);
    }

    public function markRead(Request $request, NotificationInboxService $inbox, $id)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $ok = $inbox->markRead($user, (string) $id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $ok]);
        }

        return redirect()->back();
    }

    public function markAllRead(Request $request, NotificationInboxService $inbox)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $count = $inbox->markAllRead($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'marked' => $count]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi ditandai terbaca.');
    }
}
