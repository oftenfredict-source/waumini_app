<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->isOwnerUser()) {
        return redirect()->route('owner.dashboard');
    }

    if (auth()->check() && auth()->user()->isChurchUser()) {
        return redirect()->route('church.dashboard');
    }

    return redirect()->route('church.login');
});
