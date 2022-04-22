<?php

use App\Http\Controllers\AccountPassword;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerContractController;
use App\Http\Controllers\CustomerContractProductItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerProductItemController;
use App\Http\Controllers\GRN\GoodsReceiptNoteApprovalController;
use App\Http\Controllers\GRN\GoodsReceiptNoteController;
use App\Http\Controllers\GRN\GoodsReceiptNoteInspectionController;
use App\Http\Controllers\InspectionNoteController;
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
use App\Http\Controllers\ProductItemActivityController;
use App\Http\Controllers\ProductItemController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RequestForQuotationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StandbySpareCheckinController;
use App\Http\Controllers\StockBalanceActivityController;
use App\Http\Controllers\StockBalanceController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfile;
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

    Route::apiResource('customers.product-items',
        CustomerProductItemController::class)->only('index');
    Route::apiResource('customers', CustomerController::class);

    Route::get('product-categories', [ProductCategoryController::class, 'index']);
    Route::apiResource('products', ProductController::class);
    Route::get('warehouses', [WarehouseController::class, 'index']);

    Route::get('products/{product}/melded-balances', [ProductCategoryFilterController::class, 'productBalances']);
    Route::apiResource('product-categories.products', ProductCategoryFilterController::class);
    Route::get('stock-balances', [StockBalanceController::class, 'index']);
    Route::get('stock-balances/{stock_balance}', [StockBalanceController::class, 'show']);
    Route::patch('stock-balances/{stock_balance}', [StockBalanceController::class, 'update']);

    Route::apiResource('product-items.activities', ProductItemActivityController::class)
        ->parameters(['activities' => 'product_item_activity'])->except(['show', 'update']);
    Route::apiResource('product-items', ProductItemController::class);
    Route::apiResource('worksheets', WorksheetController::class);


    Route::get('material-requisitions/{material_requisition}/download-mrn-doc',
        [MRFController::class, 'downloadMaterialRequisitionNote']);

    Route::get('material-requisitions/{material_requisition}/download-siv-doc',
        [MRFController::class, 'downloadStoreIssueNote']);

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

    Route::get('purchase-requests/{purchase_request}/download-doc',
        [PurchaseRequestController::class, 'downloadPurchaseRequestDoc']);
    Route::apiResource('purchase-requests', PurchaseRequestController::class)
        ->only(['index', 'show', 'store', 'delete']);

    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('unit-of-measures', UnitOfMeasureController::class);

    Route::get('request-for-quotation/{request_for_quotation}/download-docs',
        [RequestForQuotationController::class, 'downloadRFQDocs']);
    Route::apiResource('request-for-quotation', RequestForQuotationController::class);

    Route::get('currencies', [CurrencyController::class, 'index']);

    Route::get('purchase-orders/{purchase_order}/download-doc',
        [PurchaseOrderController::class, 'downloadPurchaseOrderDocs']);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);

    Route::get('goods-receipt-note/approval', [GoodsReceiptNoteApprovalController::class, 'index']);
    Route::get('goods-receipt-note/{goods_receipt_note}/approval',
        [GoodsReceiptNoteApprovalController::class, 'show']);
    Route::post('goods-receipt-note/{goods_receipt_note}/approval',
        [GoodsReceiptNoteApprovalController::class, 'store']);
    Route::patch('goods-receipt-note/{goods_receipt_note}/approval',
        [GoodsReceiptNoteApprovalController::class, 'update']);
    Route::delete('goods-receipt-note/{goods_receipt_note}/approval',
        [GoodsReceiptNoteApprovalController::class, 'destroy']);

    Route::get('goods-receipt-note/inspection',
        [GoodsReceiptNoteInspectionController::class, 'index']);
    Route::get('goods-receipt-note/{goods_receipt_note}/inspection',
        [GoodsReceiptNoteInspectionController::class, 'show']);

    Route::get('goods-receipt-note/{goods_receipt_note}/download-doc',
        [GoodsReceiptNoteController::class, 'downloadGoodsReceiptNote']);
    Route::get('goods-receipt-note/{goods_receipt_note}/download-rga-doc',
        [GoodsReceiptNoteController::class, 'downloadGoodsRejectedNote']);

    Route::apiResource('goods-receipt-note', GoodsReceiptNoteController::class);

    Route::get('inspection-note/{inspection_note}/download-doc',
        [InspectionNoteController::class, 'downloadInspectionNoteDoc']);

    Route::apiResource('inspection-note', InspectionNoteController::class);

    Route::apiResource('customer-contracts.product-items',
        CustomerContractProductItemController::class)->only('index');

    Route::apiResource('customer-contracts', CustomerContractController::class);

    Route::post('companies/{company}/logo', [CompanyController::class, 'uploadLogo']);
    Route::apiResource('companies', CompanyController::class);

    Route::apiResource('stock-balance-activities', StockBalanceActivityController::class)
        ->only(['show', 'index']);

    Route::get('standby-spare-checkin', [StandbySpareCheckinController::class, 'index']);
    Route::post('standby-spare-checkin', [StandbySpareCheckinController::class, 'store']);

    Route::get('/user-profile/{user}', [UserProfile::class, 'show']);
    Route::patch('/user-profile/{user}', [UserProfile::class, 'update']);
    Route::patch('/account-password/{user}', [AccountPassword::class, 'update']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'v1/analytics'], function () {
    Route::get('worksheets/count-by-customer', [AnalyticsController::class, 'worksheetsCountByCustomer']);
    Route::get('worksheets/count-by-author', [AnalyticsController::class, 'worksheetsCountByAuthor']);
    Route::get('products/count-by-category', [AnalyticsController::class, 'productsCountByCategory']);
    Route::get('products/count-out-of-stock', [AnalyticsController::class, 'productsOutOfStockCount']);
    Route::get('product-items/count-by-location', [AnalyticsController::class, 'productItemsCountByLocation']);
});

Route::fallback(function () {
    return response(['error' => 'Resource not found'], 404);
});
