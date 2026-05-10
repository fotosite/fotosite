<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| System Area Routes  (/system/*)
|--------------------------------------------------------------------------
| Routes for the system administration area.
| Access is restricted to authenticated system administrators.
|
| Access model (kein Modal):
|   - URL ist nur dem System-User bekannt
|   - Kein Link von irgendeiner anderen Seite der Website
|   - NoIndexHeader-Middleware verhindert Indexierung durch Suchmaschinen
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — user & auth management
|   App\Http\Controllers\SessionDb\ — session management
|   App\Http\Controllers\FotoDB\    — photo catalogue management
|   App\Http\Controllers\FotoBlobDb\— blob/media management
*/

Route::middleware(['web', 'auth'])->prefix('system')->name('system.')->group(function () {
    Route::get('/dashboard', function () {
        return view('system.dashboard');
    })->name('dashboard');
});
