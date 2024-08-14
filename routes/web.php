<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TempImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => 'admin.guest'], function () {

        Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');

        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
    });
    Route::group(['middleware' => 'admin.auth'], function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');

        Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');

        //Category Routes

        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');

        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');

        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');

        //Sub_Category Routes
        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');

        Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');

        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');

        Route::get('/sub-categories/{subcategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');

        Route::put('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');

        Route::delete('/sub-categories/{category}', [SubCategoryController::class, 'destroy'])->name('sub-categories.delete');


        //Brands Routes
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');

        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');

        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');

        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');

        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');

        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.delete');

        //Products Routes
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');

        Route::get('/product-subcategories', [ProductSubCategoryController::class, 'index'])->name('product-subcategories.index');

        Route::post('/products', [ProductController::class, 'store'])->name('products.store');

        // temp-images.create
        Route::post('/upload-temp-image', [TempImageController::class, 'create'])->name('temp-images.create');

        Route::match(['get', 'post'],'/getslug', function(Request $request){
            $slug = '';
            if(!empty($request->title)){
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status' => true,
                'slug' => $slug,
            ]);
        })->name('getslug');
    });
});
