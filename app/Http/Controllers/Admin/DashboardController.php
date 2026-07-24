<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipRegistration as IR;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Models\DailyReport;
use App\Models\LeaveRequest;
use App\Models\PendingTask;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function edit()
    {
        $config = [
            'company_name'    => config('app.company_name'),
            'company_address' => config('app.company_address'),
            'company_city'    => config('app.company_city'),
            'leader_name'     => config('app.company_leader_name'),
            'leader_title'    => config('app.company_leader_title'),
        ];

        return view('admin.skl_editor', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'    => 'required|string|max:100',
            'company_address' => 'required|string|max:255',
            'company_city'    => 'required|string|max:100',
            'leader_name'     => 'required|string|max:100',
            'leader_title'    => 'required|string|max:100',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'stamp'           => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // 🔹 Upload logo & stempel
        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public/images/logos', 'logo_seveninc.png');
        }

        if ($request->hasFile('stamp')) {
            $request->file('stamp')->storeAs('public/images/logos', 'stamp.png');
        }

        // 🔹 Backup .env sebelum menulis ulang
        $envPath = base_path('.env');
        $backupPath = storage_path('app/backups/env-backup-'.now()->format('Ymd_His').'.env');
        if (File::exists($envPath)) {
            File::copy($envPath, $backupPath);
        }

        // 🔹 Edit isi .env dengan regex aman
        $envContent = File::get($envPath);
        $replacements = [
            'APP_COMPANY_NAME'         => $request->company_name,
            'APP_COMPANY_ADDRESS'      => $request->company_address,
            'APP_COMPANY_CITY'         => $request->company_city,
            'APP_COMPANY_LEADER_NAME'  => $request->leader_name,
            'APP_COMPANY_LEADER_TITLE' => $request->leader_title,
        ];

        foreach ($replacements as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=\"{$value}\"";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envPath, $envContent);

        // 🔹 Bersihkan & reload cache konfigurasi
        Artisan::call('config:clear');
        Artisan::call('config:cache');

        return back()->with('success', '✅ Data SKL berhasil diperbarui & disimpan!');
    }


    /**
     * GET /admin/user/{user}/daily-reports
     * Tampilkan semua Daily Report milik user (dengan filter optional).
     */
    public function showReports(User $user, Request $request)
    {

        $q         = trim($request->get('q', ''));
        $from      = $request->get('from');   // format: Y-m-d
        $to        = $request->get('to');     // format: Y-m-d

        $reports = DailyReport::with('user')
            ->where('user_id', $user->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('activities', 'like', "%{$q}%")
                       ->orWhere('challenges', 'like', "%{$q}%");
                });
            })
            ->when($from, fn($query) => $query->whereDate('date', '>=', $from))
            ->when($to,   fn($query) => $query->whereDate('date', '<=', $to))
            ->orderByDesc('date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.reports', [
            'user'    => $user,
            'reports' => $reports,
            'filters' => compact('q', 'from', 'to'),
        ]);
    }

    /**
     * GET /admin/user/{user}/leave-requests
     * Tampilkan semua Leave Request milik user (dengan filter optional).
     */
    public function showLeaves(User $user, Request $request)
    {

        $q         = trim($request->get('q', ''));
        $from      = $request->get('from');      // Y-m-d
        $to        = $request->get('to');        // Y-m-d
        $type      = $request->get('type');      // sick/personal/other (opsional)
        $status    = $request->get('status');    // kalau kamu punya kolom status (approved/pending/rejected)

        $leaves = LeaveRequest::with('user')
            ->where('user_id', $user->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('reason', 'like', "%{$q}%")
                       ->orWhere('leave_type', 'like', "%{$q}%");
                });
            })
            ->when($from,   fn($query) => $query->whereDate('leave_date', '>=', $from))
            ->when($to,     fn($query) => $query->whereDate('leave_date', '<=', $to))
            ->when($type,   fn($query) => $query->where('leave_type', $type))
            ->when($status, fn($query) => $query->where('status', $status)) // hapus jika tidak ada kolom status
            ->orderByDesc('leave_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.leaves', [
            'user'   => $user,
            'leaves' => $leaves,
            'filters'=> compact('q', 'from', 'to', 'type', 'status'),
        ]);
    }

    /**
     * GET /admin/user/{user}/pending-tasks
     * Tampilkan semua Pending Task milik user (dengan filter optional).
     */
    public function showTasks(User $user, Request $request)
    {

        $q      = trim($request->get('q', ''));
        $from   = $request->get('from');   // Y-m-d
        $to     = $request->get('to');     // Y-m-d

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $tasks */
        $tasks = PendingTask::with('user')
            ->where('user_id', $user->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($from, fn($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to,   fn($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(15);

        $tasks->appends($request->query());
        
        return view('admin.users.tasks', [
            'user'  => $user,
            'tasks' => $tasks,
            'filters' => compact('q', 'from', 'to'),
        ]);
    }

    public function index()
    {
        $allReports = DailyReport::with('user')->latest('date')->get();
        $allLeaves  = LeaveRequest::with('user')->latest('leave_date')->get();
        $allTasks   = PendingTask::with('user')->latest()->get();

        // Ringkasan hitungan (berdasarkan workflow status)
        $counts = [
            'total'     => IR::count(),
            'waiting'   => IR::where('internship_status', IR::STATUS_WAITING)->count(),
            'pending'   => IR::where('internship_status', IR::STATUS_PENDING)->count(),
            'accepted'  => IR::where('internship_status', IR::STATUS_ACCEPTED)->count(),
            'active'    => IR::where('internship_status', IR::STATUS_ACTIVE)->count(),
            'completed' => IR::where('internship_status', IR::STATUS_COMPLETED)->count(),
            'exited'    => IR::where('internship_status', IR::STATUS_EXITED)->count(),
            'rejected'  => IR::where('internship_status', IR::STATUS_REJECTED)->count(),
        ];

        $recentInterns = IR::latest('created_at')->limit(5)->get();

        // ===========================
        // Line chart: total pendaftar per bulan (6 bulan terakhir, termasuk bulan ini)
        // ===========================
        $end   = Carbon::now()->endOfMonth();
        $start = (clone $end)->subMonths(5)->startOfMonth();

        // Buat list bulan untuk label & kunci (YYYY-MM)
        $months = collect();
        for ($d = $start->copy(); $d <= $end; $d->addMonth()) {
            $months->push($d->copy());
        }
        $labels = $months->map->format('M Y')->all();   // contoh: ['Mar 2025', ...]
        $keys   = $months->map->format('Y-m')->all();   // contoh: ['2025-03', ...]

        // Grouping total pendaftar per bulan berdasarkan created_at
        $grouped = IR::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->pluck('c', 'ym') // ['2025-03' => 12, ...]
            ->all();

        // Susun sesuai urutan bulan, isi 0 jika tidak ada
        $totals = array_map(fn ($k) => $grouped[$k] ?? 0, $keys);

        $chart = [
            'labels' => $labels,
            'total'  => $totals,
        ];

        $users = \App\Models\User::with('internshipRegistration')
        ->latest()
        ->limit(10) // tampilkan 10 terbaru (ubah sesuai kebutuhan)
        ->get();

        $me = auth()->user();

        $reportsMine = $me->dailyReports()->exists()
            ? $me->dailyReports()->orderByDesc('date')->limit(5)->get()
            : DailyReport::orderByDesc('date')->limit(5)->get();

        $leaveMine = $me->leaveRequests()->exists()
            ? $me->leaveRequests()->orderByDesc('leave_date')->limit(5)->get()
            : LeaveRequest::orderByDesc('leave_date')->limit(5)->get();

        $tasksMine = $me->pendingTasks()->exists()
            ? $me->pendingTasks()->latest()->limit(5)->get()
            : PendingTask::latest()->limit(5)->get();

        return view('dashboard.index', compact(
            'counts',
            'chart',
            'users',
            'allReports',
            'allLeaves',
            'allTasks',
            'recentInterns'
        ));
    }
}
