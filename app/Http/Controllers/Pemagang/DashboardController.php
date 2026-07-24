<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil registrasi terakhir milik user ini
        $registration = IR::where('user_id', $user->id)
            ->latest('id')
            ->first();

        // Tentukan step progress untuk stepper di UI
        // Step: 1=Submitted, 2=Under Review, 3=Diterima/Ditolak
        $progressStep = 1;
        $statusLabel  = 'Belum Mendaftar';
        $statusColor  = 'gray';

        if ($registration) {
            $statusLabel = match($registration->internship_status) {
                IR::STATUS_WAITING   => 'Under Review',
                IR::STATUS_PENDING   => 'Under Review',
                IR::STATUS_ACCEPTED  => 'Diterima',
                IR::STATUS_ACTIVE    => 'Aktif Magang',
                IR::STATUS_COMPLETED => 'Selesai',
                IR::STATUS_REJECTED  => 'Ditolak',
                IR::STATUS_EXITED    => 'Keluar',
                default              => ucfirst($registration->internship_status),
            };

            $progressStep = match($registration->internship_status) {
                IR::STATUS_WAITING, IR::STATUS_PENDING => 2,
                IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE,
                IR::STATUS_COMPLETED, IR::STATUS_REJECTED,
                IR::STATUS_EXITED => 3,
                default => 1,
            };
            $statusColor = match($registration->internship_status) {
                IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE, IR::STATUS_COMPLETED => 'green',
                IR::STATUS_REJECTED, IR::STATUS_EXITED                       => 'red',
                default                                                       => 'yellow',
            };
        }

        return view('pemagang.dashboard', compact(
            'user',
            'registration',
            'progressStep',
            'statusLabel',
            'statusColor'
        ));
    }
}
