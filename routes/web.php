<?php

use Illuminate\Support\Facades\Route;

// Public / Guest
use App\Http\Controllers\InternshipRegistrationController as PublicRegController;

// Auth & Admin
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InternController;
use App\Http\Controllers\Admin\InternPageController;
use App\Http\Controllers\Admin\InternApiController;
use App\Http\Controllers\Admin\CertificateGeneratorController;
use App\Http\Controllers\SKLController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\SuratPenilaianController;
// App
use App\Http\Controllers\UserController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LoaController;
use App\Http\Controllers\MembercardController;
use App\Http\Controllers\InternAssessmentController;

// Pemagang area
use App\Http\Controllers\Pemagang\DashboardController as PemagangDashboard;
use App\Http\Controllers\Pemagang\RegistrationController as PemagangRegistration;
use App\Http\Controllers\Pemagang\DocumentController as PemagangDocument;
use App\Http\Controllers\Pemagang\SettingsController as PemagangSettings;


/*
|---------------------------------------------------------------------- 
| Web Routes 
|---------------------------------------------------------------------- 
| - Root -> login (auth/admin-login.blade.php) 
| - User login -> user.dashboard, Admin login -> admin/dashboard (atur di AuthController) 
| - Form internship dilindungi auth + submitted page 
| - Admin area: prefix URL 'admin' + prefix nama 'admin.' agar rapi & tidak bentrok
|---------------------------------------------------------------------- 
*/


// Demo bermacam macam tampilan
Route::view('/zombie-survival', 'cobacoba')->name('zombie.survival');

/* =================== ROOT -> LOGIN VIEW =================== */
Route::get('/', [AuthController::class, 'showLoginForm'])
    ->name('login')
    ->middleware('guest');

/* =================== AUTH (GUEST) =================== */
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->name('user.login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->name('user.login.submit');

Route::get('/register', [AuthController::class, 'showRegisterForm'])
    ->name('user.register')
    ->middleware('guest');

Route::post('/register', [AuthController::class, 'register'])
    ->name('user.register.submit');

// Logout (user & admin)
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('user.logout');

/* =================== USER ROUTES =================== */
// HANYA SATU route bernama user.dashboard (hindari duplikasi!)
Route::get('/user/dashboard', [UserController::class, 'index'])
    ->name('user.dashboard')
    ->middleware('auth');

// Edit profil user
Route::get('/user/edit-profile', [PublicRegController::class, 'editProfile'])
    ->name('user.editProfile')
    ->middleware('auth');

Route::post('/user/update-profile', [PublicRegController::class, 'updateProfile'])
    ->name('user.updateProfile')
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    // Halaman untuk melihat profil pengguna
    Route::get('/user/profile', [UserController::class, 'editUserProfile'])->name('user.profile');
    
    // Menangani pembaruan profil pengguna
    Route::post('/user/profile/update', [UserController::class, 'updateUserProfile'])->name('user.profile.update');

    Route::put('/user/profile/update', [UserController::class, 'updateUserProfile'])->name('user.profile.update');


    // Daftar Magang Form
    Route::get('/user/internship/form', [PublicRegController::class, 'showForm'])->name('user.internship.form');

    // Halaman Dashboard Completed
    Route::get('/user/dashboard/completed', [UserController::class, 'userCompleted'])->name('user.dashboard.completed');

    // Routes untuk Menampilkan dan Menyimpan Laporan Harian
    Route::get('/user/daily-report', [UserController::class, 'dailyReport'])->name('user.dailyReport'); // Menampilkan laporan harian
    Route::post('/user/daily-report', [UserController::class, 'storeDailyReport'])->name('user.storeDailyReport'); // Menyimpan laporan harian

    // Routes untuk Menampilkan dan Menyimpan Permintaan Izin
    Route::get('/user/leave-request', [UserController::class, 'leaveRequest'])->name('user.leaveRequest'); // Menampilkan permintaan izin
    Route::post('/user/leave-request', [UserController::class, 'storeLeaveRequest'])->name('user.storeLeaveRequest'); // Menyimpan permintaan izin

    // Routes untuk Menampilkan dan Menyimpan Tugas Pending
    Route::get('/user/pending-tasks', [UserController::class, 'pendingTasks'])->name('user.pendingTasks'); // Menampilkan tugas pending
    Route::post('/user/pending-tasks', [UserController::class, 'storePendingTask'])->name('user.storePendingTask'); // Menyimpan tugas pending

    Route::get('/dashboard-active', [UserController::class, 'userActive'])->name('user.dashboard-active');

});


Route::middleware(['auth'])->group(function () {
    Route::post('/user/loa/generate', [LoaController::class, 'generate'])
        ->name('user.loa.generate');

    Route::get('/user/loa/preview/{id}', [LoaController::class, 'preview'])
        ->name('user.loa');
});


/* =================== INTERNSHIP (USER) =================== */
// Form & store harus login
Route::get('/internship', [PublicRegController::class, 'create'])
    ->name('internship.form')
    ->middleware('auth');

Route::post('/internship/store', [PublicRegController::class, 'store'])
    ->name('internship.store')
    ->middleware('auth');

// Submitted page (butuh login)
Route::view('/internship/submitted', 'pages.internship.submitted-page')
    ->name('internship.submitted')
    ->middleware('auth');

// Shortcut ke tabel internship (halaman admin)
Route::get('/internship/table', fn () => redirect()->route('admin.interns.index'))
    ->name('internship.table')
    ->middleware('auth');

// ========================
// Dokumen Kelulusan (SKL & LOA)
// ========================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Halaman daftar SKL dan LOA
    Route::get('/documents/loas', [DocumentController::class, 'listLoas'])->name('documents.loas');
    Route::get('/documents/skls', [DocumentController::class, 'listSkls'])->name('documents.skls');
});


Route::middleware(['auth', 'role:pemagang'])->prefix('user/documents')->name('user.')->group(function () {

    // Download dinamis (pemagang COMPLETED; admin boleh untuk user lain dengan ?user_id=)
    Route::get('/skl/download', [SKLController::class, 'download'])->name('skl.download');

    // Generate LOA (POST) → body: intern_id
    Route::post('/loa', [\App\Http\Controllers\LoaController::class, 'generate'])
        ->name('loa.generate');

});

Route::post('/user/feedback', [FeedbackController::class, 'submit'])
    ->name('user.feedback.submit');

Route::middleware(['auth', 'role:admin'])->prefix('admin/documents')->name('admin.documents.')->group(function () {
    Route::get('/skl/{intern}', [\App\Http\Controllers\Admin\InternController::class, 'showSKL'])
        ->name('skl.show');
    Route::get('/loa/{intern}', [\App\Http\Controllers\Admin\InternController::class, 'showLOA'])
        ->name('loa.show');
});

Route::middleware(['auth','role:pemagang'])->group(function () {
    Route::get('/user/riwayat-magang', [\App\Http\Controllers\UserController::class, 'riwayatMagang'])
        ->name('user.riwayatMagang');
});

/* ===== Admin Login (untuk middleware RoleMiddleware) ===== */
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])
    ->name('admin.login')
    ->middleware('guest');

/* =================== ADMIN ROUTES =================== */
// Semua nama route akan diawali 'admin.' (mis: 'admin.dashboard.index')
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin', 'prevent-back'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/', fn () => redirect()->route('admin.dashboard.index'))->name('home');

    // Users CRUD -> admin.users.*
    Route::resource('users', AdminUserController::class);
    Route::resource('certificate', CertificateController::class);

    Route::get('/certificate/index', [CertificateController::class, 'index'])->name('certificate'); // Add route for viewing certificates list

    // Interns (pages + status + certificates) -> admin.interns.*
    Route::prefix('interns')->name('interns.')->group(function () {
        Route::get('/', [InternPageController::class, 'index'])->name('index');
        Route::get('/active', [InternPageController::class, 'active'])->name('active');
        Route::get('/completed', [InternPageController::class, 'completed'])->name('completed');
        Route::get('/exited', [InternPageController::class, 'exited'])->name('exited');
        Route::get('/pending', [InternPageController::class, 'pending'])->name('pending');
        Route::get('accepted', [InternPageController::class, 'accepted'])->name('accepted');
        Route::get('rejected', [InternPageController::class, 'rejected'])->name('rejected');

        // Update status & data
        Route::patch('/{intern}/status', [InternController::class, 'updateStatus'])->name('status.update');
        Route::patch('/bulk/status', [InternController::class, 'bulkUpdateStatus'])->name('status.bulk');
        Route::patch('/{intern}', [InternController::class, 'update'])->name('update');

        // Sertifikat default
        Route::get('/{intern}/certificate', [InternController::class, 'certificate'])->name('certificate');
        Route::get('/{intern}/certificate.pdf', [InternController::class, 'certificatePdf'])->name('certificate.pdf');
        

        // Sertifikat AreaKerjaCom
        Route::get('/{intern}/certificate/areakerjacom/preview', [InternController::class, 'certificateAreaKerjaCom'])->name('certificate.areakerjacom.preview');
        Route::get('/{intern}/certificate/areakerjacom', [InternController::class, 'certificateAreaKerjaComPdf'])->name('certificate.areakerjacom');
        Route::get('/{intern}/certificate/areakerjacom.pdf', [InternController::class, 'certificateAreaKerjaComPdf'])->name('certificate.areakerjacom.pdf');

        // Template dinamis
        Route::get('/{intern}/certificate/{template}.pdf', [InternController::class, 'certificatePdfDynamic'])
            ->name('certificate.dynamic')
            ->whereIn('template', ['certmagangjogjacom', 'certareakerjacom', 'certtipisinicom']);

        // Hapus intern
        Route::delete('/{intern}', [InternController::class, 'destroy'])->name('destroy');
    });

    // API Select interns & JSON -> admin.interns.search, admin.interns.api
    Route::get('/interns/search', [InternApiController::class, 'search'])->name('interns.search');
    Route::get('/interns.json',   [InternApiController::class, 'index'])->name('interns.api');

    // Certificate generator lama (opsional)
    Route::get('/certificate/form',          [CertificateGeneratorController::class, 'showForm'])->name('certificate.form');
    Route::post('/certificate/preview',      [CertificateGeneratorController::class, 'generatePreview'])->name('certificate.generatePreview');
    Route::get('/certificate/download/{id}', [CertificateGeneratorController::class, 'generatePDF'])->name('certificate.generatePDF');
    Route::post('/download-pdf',             [CertificateGeneratorController::class, 'generatePDF'])->name('download.pdf');

    // Bulk certificate custom -> admin.certificate.bulk.*
    Route::prefix('certificate/bulk')->name('certificate.bulk.')->group(function () {
        Route::get('/interns',  [CertificateController::class, 'bulkCreateInterns'])->name('interns.create');
        Route::post('/interns', [CertificateController::class, 'bulkStoreInterns'])->name('interns.store');

        Route::get('/external',  [CertificateController::class, 'bulkCreateExternal'])->name('external.create');
        Route::post('/external', [CertificateController::class, 'bulkStoreExternal'])->name('external.store');
    });

    // External create/store -> admin.certificate.external.*
    Route::get('/certificate/external/create', [CertificateController::class, 'createExternal'])->name('certificate.external.create');
    Route::post('/certificate/external',        [CertificateController::class, 'storeExternal'])->name('certificate.external.store');

    // Download PDF satu sertifikat -> admin.certificate.pdf
    Route::get('/certificate/{certificate}/pdf', [CertificateController::class, 'downloadPdf'])->name('certificate.pdf');

    // Bulk ZIP -> admin.certificate.external.bulk
    Route::post('/certificate/external/bulk-zip', [CertificateController::class, 'externalBulkZip'])->name('certificate.external.bulk');

    // Bulk download tanpa simpan DB -> admin.certificate.external.bulkDownload
    Route::post('/certificate/external/bulk-download', [CertificateController::class, 'externalBulkDownloadFromForm'])
        ->name('certificate.external.bulkDownload');

    // Resource Certificate -> admin.certificate.*
    Route::resource('certificate', CertificateController::class);

    // Upload assets (bg/logo/ttd) -> admin.uploads.*
    Route::post('/uploads/backgrounds', [CertificateController::class, 'uploadBackground'])->name('uploads.backgrounds.store');
    Route::post('/uploads/logos',       [CertificateController::class, 'uploadLogo'])->name('uploads.logos.store');
    Route::post('/uploads/signatures',  [CertificateController::class, 'uploadSignature'])->name('uploads.signatures.store');

    Route::get('/user/{user}/daily-reports', [DashboardController::class, 'showReports'])->name('user.dailyReports');
    Route::get('/user/{user}/leave-requests', [DashboardController::class, 'showLeaves'])->name('user.leaveRequests');
    Route::get('/user/{user}/pending-tasks', [DashboardController::class, 'showTasks'])->name('user.pendingTasks');

    Route::get('/skl/editor', [SKLController::class, 'edit'])->name('skl.editor');
    Route::post('/skl/editor', [SKLController::class, 'update'])->name('skl.update');
    // Preview untuk panel editor (dipanggil dari iframe)
    Route::get('/skl/preview', [SKLController::class, 'preview'])->name('skl.preview');

});


Route::middleware(['auth', 'role:admin'])->prefix('admin/skl')->group(function () {
    Route::get('/edit', [SKLController::class, 'edit'])->name('admin.skl.edit');
    Route::post('/update', [SKLController::class, 'update'])->name('admin.skl.update');
    Route::get('/preview', [SKLController::class, 'preview'])->name('admin.skl.preview');
});

Route::get('/skl-preview', function () {
    return view('user.skl');
});

Route::middleware(['auth'])->group(function () {
    // Editor & Settings
    Route::get('/admin/loa/editor', [LoaController::class, 'edit'])->name('admin.loa.editor');
    Route::put('/admin/loa', [LoaController::class, 'update'])->name('admin.loa.update');

    // CRUD sederhana data pemagang (opsional jika sudah ada halaman lain)
    Route::get('/admin/loa/interns', [LoaController::class, 'indexInterns'])->name('admin.loa.interns');
    Route::post('/admin/loa/generate', [LoaController::class, 'generate'])->name('admin.loa.generate'); // single
    Route::post('/admin/loa/generate-batch', [LoaController::class, 'generateBatch'])->name('admin.loa.generateBatch'); // multiple

    // Preview (tanpa simpan)
    Route::get('/user/loa/preview', [LoaController::class, 'preview'])->name('user.loa.preview');
});

Route::middleware(['auth', 'role:admin']) // Menambahkan middleware untuk autentikasi dan role admin
    ->prefix('admin/feedback') // Menambahkan prefix URL
    ->name('admin.feedback.') // Menambahkan prefix nama route
    ->group(function () {
        Route::get('/', [FeedbackController::class, 'index'])->name('index'); // Menampilkan daftar feedback
        Route::get('{id}/edit', [FeedbackController::class, 'edit'])->name('edit'); // Menampilkan halaman edit feedback
        Route::post('{id}/update', [FeedbackController::class, 'update'])->name('update'); // Proses update feedback
        Route::delete('{id}', [FeedbackController::class, 'destroy'])->name('destroy'); // Menghapus feedback
    });

Route::post('/membercard/download', [MembercardController::class, 'downloadMembercard'])->name('membercard.download');

Route::prefix('admin')->name('admin.')->group(function () {
    // index
    Route::get('membercards', [MembercardController::class, 'index'])
        ->name('membercards.index');

    // show by code (public admin view for a membercard)
    Route::get('membercards/{code}', [MembercardController::class, 'show'])
        ->name('membercards.show');

    // edit & update by code
    Route::get('membercards/{code}/edit', [MembercardController::class, 'edit'])
        ->name('membercards.edit');

    // prefer PUT/PATCH for update
    Route::put('membercards/{code}', [MembercardController::class, 'update'])
        ->name('membercards.update');

    // optional fallback if your forms send POST instead of PUT
    Route::post('membercards/{code}', [MembercardController::class, 'update'])
        ->name('membercards.update.post');

    // destroy by code
    Route::delete('membercards/{code}', [MembercardController::class, 'destroy'])
        ->name('membercards.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('generate-pdf', [SuratPenilaianController::class, 'showForm'])->name('generateForm');
    Route::post('generate-pdf', [SuratPenilaianController::class, 'generatePdf'])->name('generatePdf');
});


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/interns')
    ->group(function () {

    // === CRUD Assessment ===
    Route::get('/assessment/list', [InternAssessmentController::class, 'index'])->name('interns.assessment.index');
    Route::get('/assessment/create', [InternAssessmentController::class, 'create'])->name('interns.assessment.create');
    Route::post('/assessment/store', [InternAssessmentController::class, 'store'])->name('interns.assessment.store');

    // === PDF Routes ===
    Route::get('/assessment/{id}/pdf', [InternAssessmentController::class, 'downloadPdf'])->name('interns.assessment.pdf');
    Route::get('/assessment/{id}/preview', [InternAssessmentController::class, 'previewPDF'])->name('interns.assessment.preview');

    // === Settings Routes ===
    Route::get('/assessment/settings', [InternAssessmentController::class, 'createAssessment'])->name('interns.assessment.settings');
    Route::post('/assessment/settings/save', [InternAssessmentController::class, 'saveSettings'])->name('interns.assessment.settings.save');
    Route::post('/assessment/settings/update', [InternAssessmentController::class, 'updateSettings'])->name('interns.assessment.settings.update');

    // === AJAX Route untuk Aspek Berdasarkan Divisi ===
    Route::get('/ajax/aspek', [InternAssessmentController::class, 'getAspekByDivision'])->name('ajax.aspek');

    Route::get('/assessment/{id}/edit', [InternAssessmentController::class, 'edit'])->name('interns.assessment.edit');
    Route::put('/interns/assessment/{id}', [InternAssessmentController::class, 'update'])->name('interns.assessment.update');
    Route::delete('/assessment/{id}', [InternAssessmentController::class, 'destroy'])->name('interns.assessment.destroy');

});


// tetap ada route log-download (tidak di dalam admin group, jika publik)
Route::post('/log-download', [MembercardController::class, 'logDownload'])->name('log.download');
Route::view('/loa-preview', 'user.loa');

/* =================== PEMAGANG ROUTES (UI Baru) =================== */
Route::middleware(['auth'])->prefix('pemagang')->name('pemagang.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [PemagangDashboard::class, 'index'])->name('dashboard');

    // Form Pendaftaran
    Route::get('/daftar', [PemagangRegistration::class, 'showForm'])->name('registration.form');
    Route::post('/daftar', [PemagangRegistration::class, 'store'])->name('registration.store');
    Route::post('/daftar/draft', [PemagangRegistration::class, 'saveDraft'])->name('registration.draft');

    // Dokumen Saya
    Route::get('/dokumen', [PemagangDocument::class, 'index'])->name('documents');

    // Pengaturan Akun
    Route::get('/pengaturan', [PemagangSettings::class, 'index'])->name('settings');
    Route::put('/pengaturan', [PemagangSettings::class, 'update'])->name('settings.update');
});