<?php

use Illuminate\Support\Facades\Route;

// This is the main route file for the application.
// It defines the routes for the application and maps them to their respective views.
Route::get('/', function () {
    return view('main');
});

// This route handles the creation of a new poll.
Route::get('/create', function () {
    return view('polls/create');
});

// This route handles the editing of an existing poll.
// It takes a poll ID as a parameter and passes it to the view.
Route::get('/edit/{id}', function ($id) {
    return view('polls/edit', ['pollId' => $id]);
});