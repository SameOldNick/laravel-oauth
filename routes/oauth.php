<?php

use SameOldNick\OAuth\Http\Controllers\OAuthFlowController;
use Illuminate\Support\Facades\Route;

Route::group(config('oauth.routes.group', []), function () {
    Route::group(config('oauth.routes.redirect', []), function () {
        Route::get('redirect/{client}', [OAuthFlowController::class, 'redirect']);
    });

    Route::group(config('oauth.routes.callback', []), function () {
        Route::get('callback/{client}', [OAuthFlowController::class, 'callback']);
    });
});
