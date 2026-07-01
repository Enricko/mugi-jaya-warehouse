<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($module = $request->get('module')) {
            $query->where('module', $module);
        }
        if ($userId = $request->get('user')) {
            $query->where('user_id', $userId);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return view('audit-log.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'actions' => AuditLog::distinct()->pluck('action'),
            'modules' => AuditLog::distinct()->pluck('module'),
            'users' => \App\Models\User::orderBy('full_name')->get(),
            'actionFilter' => $action ?? null,
            'moduleFilter' => $module ?? null,
        ]);
    }
}
