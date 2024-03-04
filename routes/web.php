<?php

use App\Http\Controllers\ConverterController;
use App\Http\Controllers\YoutubeVideoDownloader;
use Illuminate\Support\Facades\Route;


Route::get('/', [YoutubeVideoDownloader::class, 'index']);
Route::get('/convert', [ConverterController::class, 'convertAndStoreVideo']);

