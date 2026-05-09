<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| System Area Routes  (/system/*)
|--------------------------------------------------------------------------
| Routes for the system administration area.
| Access is restricted to authenticated system administrators.
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — user & auth management
|   App\Http\Controllers\SessionDb\ — session management
|   App\Http\Controllers\FotoDB\    — photo catalogue management
|   App\Http\Controllers\FotoBlobDb\— blob/media management
*/

Route::middleware(['web', 'auth'])->prefix('system')->name('system.')->group(function () {
    //
});
