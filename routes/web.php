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

use App\Http\Controllers\DN_Controller;

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

Route::get('/add-new-tagih-sales-dn', [DN_Controller::class, 'index'])->name('add-new-tagih-sales-dn')->middleware('auth');
Route::get('/list-tr-tagih-sales-dn-d-date', [DN_Controller::class, 'index_list_tr_tagih_sales_DN_d_date'])->name('list-tr-tagih-sales-dn-d-date')->middleware('auth');
Route::get('/edit-tagih-sales-dn', [DN_Controller::class, 'index_edit_tagih_sales_dn'])->name('list-tr-tagih-sales-dn-d-date')->middleware('auth');
Route::get('/page-kwitansi', [DN_Controller::class, 'index_kwitansi'])->name('page-kwitansi')->middleware('auth');


Route::get('/get_client', [DN_Controller::class, 'get_client'])->middleware('auth');
Route::get('/get_vehicle', [DN_Controller::class, 'get_vehicle'])->middleware('auth');
Route::get('/get_business', [DN_Controller::class, 'get_business'])->middleware('auth');
Route::get('/data_for_chart_1', [DN_Controller::class, 'data_for_chart_1'])->middleware('auth');
Route::get('/get_header_dn_tagih', [DN_Controller::class, 'get_header_dn_tagih'])->middleware('auth');
Route::get('/dn_tagih/get_table_add_tagih_sales_dn', [DN_Controller::class, 'get_table_add_tagih_sales_dn'])->middleware('auth');
Route::get('/dn_tagih/get_table_for_edit_dn_tgih', [DN_Controller::class, 'get_table_for_edit_dn_tgih'])->middleware('auth');
Route::get('/dn_tagih/get_table_list_tr_tagih_sales_DN_d_date', [DN_Controller::class, 'get_table_list_tr_tagih_sales_DN_d_date'])->middleware('auth');
Route::get('/cetak-pdf/dn-tagih-inv', [DN_Controller::class, 'cetakPDF_inv'])->middleware('auth');
Route::get('/cetak-pdf/dn-tagih-kwitansi', [DN_Controller::class, 'cetakPDF_kwitansi'])->middleware('auth');
Route::get('/cetak-pdf/dn-tagih-inv-wt', [DN_Controller::class, 'cetakPDF_inv_for_water_tanker'])->middleware('auth');
Route::post('/dn-tagih/store', [DN_Controller::class, 'store_dn_tagih'])->middleware('auth');
Route::post('/dn-tagih/update/po-code', [DN_Controller::class, 'update_dn_tagih_po_code'])->middleware('auth');
Route::post('/dn-tagih/update/details-data', [DN_Controller::class, 'update_dn_tagih_detail'])->middleware('auth');
Route::post('/dn-tagih/store-kwitansi', [DN_Controller::class, 'store_kwitansi'])->middleware('auth');


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

