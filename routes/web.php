<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\userController;
use App\Http\Controllers\SKU_Controller;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GantipwController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PodDetController;
use App\Http\Controllers\PodSummController;
use App\Http\Controllers\LastMileController;
use App\Http\Controllers\DispatchTrackController;
use App\Http\Controllers\GudangController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => 'auth'], function () {

    Route::get('/', [HomeController::class, 'home']);
	Route::get('dashboard', function () {
		return view('dashboard');
	})->name('dashboard');

	Route::get('billing', function () {
		return view('billing');
	})->name('billing');

	Route::get('profile', function () {
		return view('profile');
	})->name('profile');

	Route::get('rtl', function () {
		return view('rtl');
	})->name('rtl');

	// Route::get('user-management', function () {
	// 	return view('laravel-examples/user-management');
	// })->name('user-management');

	Route::get('tables', function () {
		return view('tables');
	})->name('tables');

    Route::get('virtual-reality', function () {
		return view('virtual-reality');
	})->name('virtual-reality');

    Route::get('static-sign-in', function () {
		return view('static-sign-in');
	})->name('sign-in');

    Route::get('static-sign-up', function () {
		return view('static-sign-up');
	})->name('sign-up');

    Route::get('/logout', [SessionsController::class, 'destroy']);
	Route::get('/user-profile', [InfoUserController::class, 'create']);
	Route::post('/user-profile', [InfoUserController::class, 'store']);
    Route::get('/login', function () {
		return view('dashboard');
	})->name('sign-up');

    // POD (Proof of Delivery) — read-only report
    Route::prefix('pod')->name('pod.')->group(function () {
        Route::get('/summary',          [PodSummController::class, 'index'])->name('summary.index');
        Route::get('/detail',            [PodDetController::class,  'index'])->name('detail.index');
        Route::get('/detail/calculate',  [PodDetController::class,  'calculate'])->name('detail.calculate');
        Route::get('/detail/export',     [PodDetController::class,  'export'])->name('detail.export');
        Route::get('/detail/row-detail', [PodDetController::class,  'rowDetail'])->name('detail.row-detail');
    });

    // Last Mile
    Route::get('/lastmile', [LastMileController::class, 'index'])->name('lastmile.index');
    Route::get('/lastmile/invoices', [LastMileController::class, 'invoices'])->name('lastmile.invoices');
    Route::get('/lastmile/cancel-detail', [LastMileController::class, 'cancelDetail'])->name('lastmile.cancel-detail');

    // Dispatch Track
    Route::get('/dispatch-track', [DispatchTrackController::class, 'index'])->name('dispatch-track.index');
    Route::get('/dispatch-track/detail', [DispatchTrackController::class, 'detail'])->name('dispatch-track.detail');

    // Global DB switch (POD / Last Mile / Dispatch Track)
    Route::get('/set-report-db/{db}', function ($db) {
        if (in_array($db, ['hgs', 'tgu'], true)) {
            session(['report_db' => $db]);
        }
        return redirect()->back();
    })->name('set-report-db')->where('db', 'hgs|tgu');

    // Gudang
    Route::get('/gudang/rekap-stock-rack', [GudangController::class, 'rekapStockRack'])->name('gudang.rekap-stock-rack');
    Route::get('/gudang/price-list', [GudangController::class, 'priceList'])->name('gudang.price-list');
    Route::post('/gudang/price-list', [GudangController::class, 'priceListUpdate'])->name('gudang.price-list.update');
    Route::get('/gudang/price-list/create', [GudangController::class, 'priceListCreate'])->name('gudang.price-list.create');
    Route::post('/gudang/price-list/store',  [GudangController::class, 'priceListStore'])->name('gudang.price-list.store');
    Route::get('/gudang/price-list/lookup-sku',      [GudangController::class, 'priceListLookupSku'])->name('gudang.price-list.lookup-sku');
    Route::get('/gudang/price-list/lookup-pricemode',[GudangController::class, 'priceListLookupPriceMode'])->name('gudang.price-list.lookup-pricemode');
    Route::get('/gudang/price-list/lookup-unit',     [GudangController::class, 'priceListLookupUnit'])->name('gudang.price-list.lookup-unit');

    // Track In / Out
    Route::get('/gudang/track-in-out', [GudangController::class, 'trackInOut'])->name('gudang.track-in-out');
    Route::get('/gudang/track-in-out/detail', [GudangController::class, 'trackInOutDetail'])->name('gudang.track-in-out.detail');
    Route::get('/gudang/track-in-out/print', [GudangController::class, 'trackInOutPrint'])->name('gudang.track-in-out.print');
    Route::get('/gudang/track-in-out/export', [GudangController::class, 'trackInOutExport'])->name('gudang.track-in-out.export');
    Route::get('/gudang/track-in-out/export-row-detail', [GudangController::class, 'trackInOutExportRowDetail'])->name('gudang.track-in-out.export-row-detail');
    Route::get('/gudang/track-in-out/export-out-row-detail', [GudangController::class, 'trackInOutExportOutRowDetail'])->name('gudang.track-in-out.export-out-row-detail');
    Route::get('/gudang/track-in-out/tallysheet-detail', [GudangController::class, 'trackInOutTallysheetDetail'])->name('gudang.track-in-out.tallysheet-detail');
    Route::get('/gudang/track-in-out/btb-detail', [GudangController::class, 'trackInOutBtbDetail'])->name('gudang.track-in-out.btb-detail');
    Route::get('/gudang/track-in-out/putaway-detail', [GudangController::class, 'trackInOutPutawayDetail'])->name('gudang.track-in-out.putaway-detail');
    Route::get('/gudang/track-in-out/out-request-detail', [GudangController::class, 'trackInOutOutRequestDetail'])->name('gudang.track-in-out.out-request-detail');
    Route::get('/gudang/track-in-out/out-picking-detail', [GudangController::class, 'trackInOutOutPickingDetail'])->name('gudang.track-in-out.out-picking-detail');
    Route::get('/gudang/track-in-out/out-bkb-detail', [GudangController::class, 'trackInOutOutBkbDetail'])->name('gudang.track-in-out.out-bkb-detail');
    Route::get('/gudang/track-in-out/out-dispatch-detail', [GudangController::class, 'trackInOutOutDispatchDetail'])->name('gudang.track-in-out.out-dispatch-detail');
    Route::get('/gudang/track-in-out/out-pod-detail', [GudangController::class, 'trackInOutOutPodDetail'])->name('gudang.track-in-out.out-pod-detail');
    Route::get('/gudang/track-in-out/out-btbrv-detail', [GudangController::class, 'trackInOutOutBtbRvDetail'])->name('gudang.track-in-out.out-btbrv-detail');
    Route::get('/gudang/track-in-out/out-payment-detail', [GudangController::class, 'trackInOutOutPaymentDetail'])->name('gudang.track-in-out.out-payment-detail');

    Route::get('/gudang/kartu-stock', [GudangController::class, 'kartuStock'])->name('gudang.kartu-stock');
    Route::get('/gudang/kartu-stock/rack-options', [GudangController::class, 'kartuStockRackOptions'])->name('gudang.kartu-stock.rack-options');
    Route::get('/gudang/kartu-stock/export', [GudangController::class, 'kartuStockExport'])->name('gudang.kartu-stock.export');
});

Route::get('/Employee-management', [userController::class, 'index'])->name('Employee-management')->middleware('auth');
Route::get('/user-activation', [userController::class, 'index_atf_user'])->name('user-activation')->middleware('auth');
Route::get('/user-activation/get_user', [userController::class, 'get_user'])->middleware('auth');

Route::post('/update-activation/{id}', [UserController::class, 'updateActivation'])->name('update.activation');

Route::get('/Employee-management/add-employee', [userController::class, 'index_add_emp'])->name('Employee-management')->middleware('auth');
Route::get('/Employee-management/table_user_data', [userController::class, 'table_user_data'])->name('Employee-management-table-user')->middleware('auth');
Route::post('/Employee-management/add-employe', [userController::class, 'store_emp'])->name('Employee-management-add-employe')->middleware('auth');
Route::post('/Employee-management/delete-employe', [userController::class, 'delete_emp'])->name('Employee-management-delete-employe')->middleware('auth');
Route::get('/Employee-management/edit-employee', [userController::class, 'index_edit_emp'])->name('Employee-management')->middleware('auth');
Route::get('/Employee-management/get-employe-data', [userController::class, 'get_emp_data'])->name('Employee-management')->middleware('auth');

Route::get('/SKU-management', [SKU_Controller::class, 'index'])->name('SKU-management')->middleware('auth');
Route::get('/SKU-management/table_sku', [SKU_Controller::class, 'table_sku'])->name('SKU-management-table_sku')->middleware('auth');
Route::get('/chart_sales', [SKU_Controller::class, 'chart_sales'])->name('chart_sales');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store']);
	Route::get('/login/forgot-password', [ResetController::class, 'create']);
	Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
	Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
	Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');

});
Route::post('/check-login', [App\Http\Controllers\AuthController::class, 'checkLogin']);

Route::get('/login', function () {
    return view('session/login-session');
})->name('login');

Route::get('/phpinfo', function () {
    phpinfo();
});

// Route untuk mengubah foto profil
Route::post('/profile/update-photo', [ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');
Route::get('/profile/information', [ProfileController::class, 'get_user_info']);
Route::post('/profil-info-update', [ProfileController::class, 'updateProfilInfo'])->name('profil-info.update');

// Route untuk mengubah nama pengguna
Route::post('/profile/update-name', [ProfileController::class, 'updateName'])->name('profile.updateName');
// web.php
Route::post('/upload-gambar', [ProfileController::class, 'upload'])->name('upload.gambar');
Route::post('change-password-2', [GantipwController::class, 'changePassword'])
    ->name('changePassword2')
    ->middleware('auth');


use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

Route::get('/cek-session', function () {
    $lifetimeInMinutes = config('session.lifetime'); // contoh: 120 menit
    $lastActivity = Session::get('last_activity_time');

    if (!$lastActivity) {
        $now = Carbon::now();
        Session::put('last_activity_time', $now);
        $remainingSeconds = $lifetimeInMinutes * 60;
    } else {
        $lastActivity = Carbon::parse($lastActivity);
        $now = Carbon::now();
        $elapsedSeconds = $now->diffInSeconds($lastActivity);
        $remainingSeconds = max(0, ($lifetimeInMinutes * 60) - $elapsedSeconds);
        Session::put('last_activity_time', $now);
    }

    $minutes = floor($remainingSeconds / 60);
    $seconds = $remainingSeconds % 60;
    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;

    dd([
        'Sisa Jam' => $hours,
        'Sisa Menit' => $minutes,
        'Sisa Detik' => $seconds,
        'Total Detik' => $remainingSeconds,
    ]);
});
