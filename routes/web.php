<?php

use Illuminate\Support\Facades\Route;

Route::get('/payment-success', function () {
    dd("Payment success");
});

Route::get('/payment-failed', function () {
    dd("Payment failed");
});