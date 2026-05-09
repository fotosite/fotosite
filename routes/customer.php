<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Area Routes  (/customer/*)
|--------------------------------------------------------------------------
| Routes for the customer-facing area.
| Mix of public and authenticated routes.
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — customer profile & auth
|   App\Http\Controllers\FotoDB\    — browsing photos
|   App\Http\Controllers\FotoBlobDb\— downloading/streaming media
*/

Route::middleware('web')->prefix('customer')->name('customer.')->group(function () {
    //
});
