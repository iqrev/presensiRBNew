<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FaceReferenceController;
use App\Http\Controllers\Admin\OfficeLocationController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\AttendanceHistoryController;
use App\Http\Controllers\Employee\LeaveRequestController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Auth Routes
// ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Consent Routes
    Route::get('/consent', [LoginController::class, 'showConsentForm'])->name('consent.show');
    Route::post('/consent', [LoginController::class, 'storeConsent'])->name('consent.store');

    // Protected photo access (private storage)
    Route::get('/photos/{path}', [PhotoController::class, 'show'])->name('photos.show');
});

// ─────────────────────────────────────────────
// Public Kiosk Routes (No Login Required)
// ─────────────────────────────────────────────
Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/absensi/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin')
    ->middleware('throttle:5,10'); // 5x per 10 menit
Route::post('/absensi/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout')
    ->middleware('throttle:5,10');

// ─────────────────────────────────────────────
// Employee Routes (Disabled for Kiosk Mode MVP)
// ─────────────────────────────────────────────
// Route::middleware(['auth', 'employee.status', 'consent', 'role:karyawan'])->prefix('')->group(function () {
//     Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('employee.dashboard');
//     Route::get('/riwayat', [AttendanceHistoryController::class, 'index'])->name('attendance.history');
//     Route::get('/izin', [LeaveRequestController::class, 'index'])->name('leave.index');
//     Route::get('/izin/buat', [LeaveRequestController::class, 'create'])->name('leave.create');
//     Route::post('/izin', [LeaveRequestController::class, 'store'])->name('leave.store');
//     Route::get('/izin/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');
// });

// ─────────────────────────────────────────────
// Admin / HR Routes
// ─────────────────────────────────────────────
Route::middleware(['auth', 'employee.status', 'role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Employees
    Route::resource('karyawan', EmployeeController::class);
    Route::get('/karyawan/{employee}/foto-wajah', [FaceReferenceController::class, 'index'])->name('face.index');
    Route::post('/karyawan/{employee}/foto-wajah', [FaceReferenceController::class, 'store'])->name('face.store');
    Route::delete('/foto-wajah/{faceReference}', [FaceReferenceController::class, 'destroy'])->name('face.destroy');
    
    // Tes Wajah
    Route::get('/tes-wajah', [\App\Http\Controllers\Admin\FaceTestController::class, 'index'])->name('face.test');
    Route::post('/tes-wajah/match', [\App\Http\Controllers\Admin\FaceTestController::class, 'match'])->name('face.test.match');

    // Office Location & Settings
    Route::resource('lokasi', OfficeLocationController::class);
    Route::get('/pengaturan', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan', [SystemSettingController::class, 'update'])->name('settings.update');

    // Leave requests
    Route::get('/izin', [AdminLeaveController::class, 'index'])->name('leave.index');
    Route::patch('/izin/{leaveRequest}/approve', [AdminLeaveController::class, 'approve'])->name('leave.approve');
    Route::patch('/izin/{leaveRequest}/reject', [AdminLeaveController::class, 'reject'])->name('leave.reject');

    // Reports
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/export-excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/laporan/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
});
