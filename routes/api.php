<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MRF\ApprovalController as MRFApproveController;
use App\Http\Controllers\MRF\IssueController as MRFIssueController;
use App\Http\Controllers\MRF\MaterialRequisitionController as MRFController;
use App\Http\Controllers\MRF\VerificationController as MRFVerifyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PR\ApprovalController;
use App\Http\Controllers\PR\PurchaseRequestController;
use App\Http\Controllers\PR\VerificationController as PRVerificationController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductCategoryFilterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductItemController;
use App\Http\Controllers\RequestForQuotationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockBalanceController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WorksheetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => 'v1/auth'], function () {
    Route::post('/login', [LoginController::class, 'authenticate']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store']);
    Route::post('/reset-password/{token}', [ResetPasswordController::class, 'store']);
    Route::post('is-authenticated', function () {
        return response()->noContent(auth('sanctum')->check() ? 200 : 401);
    });
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'v1'], function () {

    Route::post('users/{user}/resend-invite', [UserController::class, 'resendInvite']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::apiResource('customers', CustomerController::class);

    Route::get('product-categories', [ProductCategoryController::class, 'index']);
    Route::apiResource('products', ProductController::class);
    Route::get('warehouses', [WarehouseController::class, 'index']);

    Route::get('products/{product}/melded-balances', [ProductCategoryFilterController::class, 'productBalances']);
    Route::apiResource('product-categories.products', ProductCategoryFilterController::class);
    Route::get('stock-balances', [StockBalanceController::class, 'index']);
    Route::get('stock-balances/{stock_balance}', [StockBalanceController::class, 'show']);
    Route::patch('stock-balances/{stock_balance}', [StockBalanceController::class, 'update']);

    Route::apiResource('product-items', ProductItemController::class);
    Route::apiResource('worksheets', WorksheetController::class);


    Route::get('material-requisitions/verification', [MRFVerifyController::class, 'index']);
    Route::get('material-requisitions/{material_requisition}/verification',
        [MRFVerifyController::class, 'show']);
    Route::post('material-requisitions/{material_requisition}/verification',
        [MRFVerifyController::class, 'store']);

    Route::get('material-requisitions/approval', [MRFApproveController::class, 'index']);
    Route::get('material-requisitions/{material_requisition}/approval',
        [MRFApproveController::class, 'show']);
    Route::post('material-requisitions/{material_requisition}/approval',
        [MRFApproveController::class, 'store']);

    Route::get('material-requisitions/issue', [MRFIssueController::class, 'index']);
    Route::get('material-requisitions/{material_requisition}/issue',
        [MRFIssueController::class, 'show']);
    Route::post('material-requisitions/{material_requisition}/issue',
        [MRFIssueController::class, 'store']);

    Route::apiResource('material-requisitions', MRFController::class)
        ->only(['index', 'show', 'store', 'delete']);

    Route::get('purchase-requests/verification', [PRVerificationController::class, 'index']);
    Route::get('purchase-requests/{purchase_request}/verification',
        [PRVerificationController::class, 'show']);
    Route::post('purchase-requests/{purchase_request}/verification',
        [PRVerificationController::class, 'store']);

    Route::get('purchase-requests/approval', [ApprovalController::class, 'index']);
    Route::get('purchase-requests/{purchase_request}/approval',
        [ApprovalController::class, 'show']);
    Route::post('purchase-requests/{purchase_request}/approval',
        [ApprovalController::class, 'store']);

    Route::apiResource('purchase-requests', PurchaseRequestController::class)
        ->only(['index', 'show', 'store', 'delete']);

    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('unit-of-measures', UnitOfMeasureController::class);
    Route::apiResource('request-for-quotation', RequestForQuotationController::class);

    Route::get('currencies', [CurrencyController::class, 'index']);
});

Route::fallback(function () {
    return response(['error' => 'Resource not found'], 404);
});
