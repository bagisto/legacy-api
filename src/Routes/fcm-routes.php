<?php

use Illuminate\Support\Facades\Route;
use Webkul\API\Http\Controllers\Admin\NotificationController;

/**
 * FCM Notification routes.
 */
Route::group(['middleware' => ['web', 'admin', 'locale'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('api_notification')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->defaults('_config', [
            'view' => 'api::notification.index'
            ])->name('api.notification.index');

        Route::get('/create', [NotificationController::class, 'create'])->defaults('_config', [
            'view' => 'api::notification.create'
        ])->name('api.notification.create');

        Route::post('/store', [NotificationController::class, 'store'])->defaults('_config', [
            'redirect' => 'api.notification.index'
        ])->name('api.notification.store');

        Route::get('/edit/{id}', [NotificationController::class, 'edit'])->defaults('_config', [
            'view' => 'api::notification.edit'
        ])->name('api.notification.edit');

        Route::put('/edit/{id}', [NotificationController::class, 'update'])->defaults('_config', [
            'redirect' => 'api.notification.index'
        ])->name('api.notification.update');

        Route::post('/delete/{id}', [NotificationController::class, 'delete'])->defaults('_config', [
            'redirect' => 'api.notification.index'
        ])->name('api.notification.delete');

        Route::post('/massdelete', [NotificationController::class, 'massDestroy'])->defaults('_config', [
            'redirect' => 'api.notification.index'
        ])->name('api.notification.mass-delete');

        Route::post('/massupdate', [NotificationController::class, 'massUpdate'])->defaults('_config', [
            'redirect' => 'api.notification.index'
        ])->name('api.notification.mass-update');

        Route::get('/send/{id}', [NotificationController::class, 'sendNotification'])->name('api.notification.send-notification');

        Route::post('/exist', [NotificationController::class, 'exist'])->name('api.notification.cat-product-id');
    });
});
