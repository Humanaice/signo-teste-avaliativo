<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('main');
});

Route::get('/create', function () {
    return view('polls/create');
});

Route::get('/edit/{id}', function ($id) {
    return view('polls/edit', ['pollId' => $id]);
});