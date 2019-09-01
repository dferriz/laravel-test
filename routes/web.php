<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//
//Route::get('/', function () {
//    return view('main.index');
//});

Auth::routes();

Route::get('/', 'MainController@index')->name('main');
Route::get('/page/{page}', 'MainController@index')->name('main.index');
Route::get('/product/{product}', 'ProductController@index')->name('product.index');
Route::get('/wishlist', 'WishlistController@index')->name('wishlist.index');
Route::get('/wishlist/show/{user}', 'WishlistController@showFriend')->name('wishlist.showFriend');
Route::get('/wishlist/{product}/add', 'WishlistController@addProduct')->name('wishlist.addProduct');
Route::get('/wishlist/{product}/remove', 'WishlistController@removeProduct')->name('wishlist.removeProduct');
Route::get('/wishlist/toggle-share', 'WishlistController@toggleShare')->name('wishlist.toggleShare');

