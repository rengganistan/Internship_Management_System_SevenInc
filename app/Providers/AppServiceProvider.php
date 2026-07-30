<?php

namespace App\Providers;

use App\Models\InternshipRegistration as IR;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('pemagang.layouts.app', function ($view) {
            $user = Auth::user();
            $notifications = collect();

            if ($user) {
                $registration = IR::where('user_id', $user->id)->latest('id')->first();
                $status = $registration?->internship_status;

                if (in_array($status, [IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE, IR::STATUS_COMPLETED], true)) {
                    $notifications->push([
                        'title' => 'LOA telah tersedia',
                        'message' => 'Surat keputusan magang sudah dapat diunduh.',
                        'url' => route('pemagang.documents'),
                    ]);
                }

                if ($status === IR::STATUS_COMPLETED) {
                    $notifications->push([
                        'title' => 'SKL siap diunduh',
                        'message' => 'Surat Keterangan Lulus sudah tersedia untuk Anda.',
                        'url' => route('pemagang.documents'),
                    ]);
                    $notifications->push([
                        'title' => 'Surat Penilaian siap diunduh',
                        'message' => 'Nilai magang Anda sudah dapat diunduh.',
                        'url' => route('pemagang.documents'),
                    ]);
                    $notifications->push([
                        'title' => 'Sertifikat Magang siap',
                        'message' => 'Sertifikat magang Anda sudah dapat diunduh.',
                        'url' => route('pemagang.documents'),
                    ]);
                    $notifications->push([
                        'title' => 'Membercard Digital siap',
                        'message' => 'Membercard alumni magang sudah dapat dilihat.',
                        'url' => route('pemagang.membercard'),
                    ]);
                }
            }

            $view->with([
                'notifications' => $notifications,
                'notificationCount' => $notifications->count(),
            ]);
        });
    }
}
