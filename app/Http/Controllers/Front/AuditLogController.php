<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs with stats and filtering.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AuditLog::query()
                ->with(['user'])
                ->latest('id');

            if ($request->filled('category')) {
                $query->byCategory($request->string('category'));
            }

            if ($request->filled('event_type')) {
                $query->forEvent($request->string('event_type'));
            }

            if ($request->filled('status')) {
                $query->byStatus($request->string('status'));
            }

            if ($request->filled('user_id')) {
                $query->forUser((int) $request->get('user_id'));
            }

            if ($request->filled('ip_address')) {
                $query->byIp($request->string('ip_address'));
            }

            if ($request->filled('date_start') || $request->filled('date_end')) {
                $query->dateBetween($request->get('date_start'), $request->get('date_end'));
            }

            if ($request->filled('search_term')) {
                $query->search($request->string('search_term'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('created_at', function (AuditLog $log) {
                    return '<div class="font-weight-600">' . $log->created_at->format('d/m/Y H:i:s') . '</div>'
                        . '<div class="text-muted small">' . $log->created_at->diffForHumans() . '</div>';
                })
                ->addColumn('user_info', function (AuditLog $log) {
                    $name = e($log->user_name ?: ($log->user?->name ?: 'Sistem/Tamu'));
                    $email = e($log->user_email ?: ($log->user?->email ?: '-'));
                    $role = e(strtoupper($log->role ?: 'GUEST'));

                    return '<div class="font-weight-bold">' . $name . '</div>'
                        . '<div class="text-muted small">' . $email . '</div>'
                        . '<span class="badge badge-light border text-dark mt-1" style="font-size: 10px;">' . $role . '</span>';
                })
                ->editColumn('event_type', function (AuditLog $log) {
                    $badgeClass = $log->event_badge_class;
                    return '<span class="badge ' . $badgeClass . '">' . e($log->event_type) . '</span>'
                        . '<div class="text-muted small mt-1 font-weight-500">' . e($log->category) . '</div>';
                })
                ->editColumn('action', function (AuditLog $log) {
                    return '<div class="text-dark">' . e($log->action) . '</div>'
                        . ($log->auditable_type ? '<div class="text-muted small font-italic mt-1">' . class_basename($log->auditable_type) . ' #' . e($log->auditable_id) . '</div>' : '');
                })
                ->addColumn('network_info', function (AuditLog $log) {
                    $ip = e($log->ip_address ?: '-');
                    $method = e($log->method ?: '-');
                    return '<div class="font-weight-600"><i class="fas fa-network-wired mr-1 text-primary"></i>' . $ip . '</div>'
                        . ($log->method ? '<div class="text-muted small"><span class="badge badge-secondary" style="font-size: 9px;">' . $method . '</span></div>' : '');
                })
                ->editColumn('status', function (AuditLog $log) {
                    return '<span class="badge ' . $log->status_badge_class . '">' . ucfirst(e($log->status)) . '</span>';
                })
                ->addColumn('details', function (AuditLog $log) {
                    return '<button type="button" class="btn btn-sm btn-outline-primary js-view-audit-detail" data-id="' . $log->id . '" data-url="' . route('admin.forensics.audit-logs.show', $log->id) . '">
                        <i class="fas fa-eye"></i> Detail
                    </button>';
                })
                ->rawColumns(['created_at', 'user_info', 'event_type', 'action', 'network_info', 'status', 'details'])
                ->make(true);
        }

        // Statistics
        $today = Carbon::today();
        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::where('created_at', '>=', $today)->count();
        $successfulLogins = AuditLog::where('event_type', 'auth.login')->where('created_at', '>=', $today)->count();
        $failedLogins = AuditLog::where('event_type', 'auth.login_failed')->where('created_at', '>=', $today)->count();
        $dbModifications = AuditLog::where('event_type', 'like', 'model.%')->where('created_at', '>=', $today)->count();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $eventTypes = AuditLog::query()
            ->select('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        return view('audit-log.index', [
            'title' => 'Log Audit & Aktivitas Sistem',
            'stats' => [
                'total' => $totalLogs,
                'today' => $todayLogs,
                'successful_logins_today' => $successfulLogins,
                'failed_logins_today' => $failedLogins,
                'db_modifications_today' => $dbModifications,
            ],
            'users' => $users,
            'eventTypes' => $eventTypes,
        ]);
    }

    /**
     * Display the specified audit log detail.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user');

        return response()->json([
            'id' => $auditLog->id,
            'event_type' => $auditLog->event_type,
            'category' => $auditLog->category,
            'action' => $auditLog->action,
            'status' => $auditLog->status,
            'status_badge' => $auditLog->status_badge_class,
            'event_badge' => $auditLog->event_badge_class,
            'user_name' => $auditLog->user_name ?: ($auditLog->user?->name ?: 'Tamu / Sistem'),
            'user_email' => $auditLog->user_email ?: ($auditLog->user?->email ?: '-'),
            'role' => $auditLog->role ?: 'GUEST',
            'ip_address' => $auditLog->ip_address ?: '-',
            'user_agent' => $auditLog->user_agent ?: '-',
            'url' => $auditLog->url ?: '-',
            'method' => $auditLog->method ?: '-',
            'auditable_type' => $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null,
            'auditable_id' => $auditLog->auditable_id,
            'old_values' => $auditLog->old_values,
            'new_values' => $auditLog->new_values,
            'properties' => $auditLog->properties,
            'created_at_formatted' => $auditLog->created_at->format('d F Y, H:i:s') . ' (' . $auditLog->created_at->diffForHumans() . ')',
        ]);
    }
}
