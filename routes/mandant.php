<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mandant Area Routes  (/mandant/*)
|--------------------------------------------------------------------------
| Routes for the mandant (tenant) management area.
| Access is restricted to authenticated mandant users.
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — mandant user management
|   App\Http\Controllers\FotoDB\    — mandant photo management
|   App\Http\Controllers\FotoBlobDb\— mandant media management
*/

Route::middleware(['web', 'auth'])->prefix('mandant')->name('mandant.')->group(function () {
    //
});
