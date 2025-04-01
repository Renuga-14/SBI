<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\LinkController;
use App\Http\Controllers\Dashboard\SourceController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\DashboardController;


              //main dashboard
Route::get('/', function () {
    return view('dashboard.dashboard');
});

                  //show dashboard
Route::get('/dashboard', [DashboardController::class, 'showcounts'])->name('dashboard');
Route::get('/products', [ProductController::class, 'showproduct'])->name('products');
Route::get('/source', [SourceController::class, 'showsource'])->name('source');
Route::get('/links', [LinkController::class, 'showlink'])->name('links');

                 //addbutton
Route::get('/addproduct', [ProductController::class, 'addproduct'])->name('addproduct');
Route::get('/addsource', [ProductController::class, 'addsource'])->name('addsource');


//Route::get('/dashboard', [ProductController::class, 'dashboard'])->name('dashboard');
//Route::get('/links', [ProductController::class, 'links'])->name('links');

                //create & store
Route::get('/source/create', [SourceController::class, 'createsource'])->name('source.create');
Route::post('/source/store', [SourceController::class, 'storesource'])->name('source.store');
Route::get('/product/create', [ProductController::class, 'createproduct'])->name('product.create');
Route::post('/product/store', [ProductController::class, 'storeproduct'])->name('product.store');

               //excel export
Route::get('products/export/', [ProductController::class, 'export'])->name('products.export');
Route::get('sources/export/', [SourceController::class, 'export'])->name('sources.export');
