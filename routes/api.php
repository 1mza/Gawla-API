<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pages\UserController;
use App\Http\Controllers\Pages\PlaceController;
use App\Http\Controllers\Pages\HotelController;
use App\Http\Controllers\Pages\CarController;
use App\Http\Controllers\Pages\EntertainmentController;
use App\Http\Controllers\Pages\TourismController;
use App\Http\Controllers\RecommendationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Auth::routes([
    'verify'=>true
]);
Route::post('/users', [UserController::class, 'getAllUsers']);
Route::post('/auth/register', [UserController::class, 'createUser']);
Route::post('/auth/login', [UserController::class, 'loginUser']);
Route::middleware(['auth:sanctum'])->group(function () {
    // Logout route
    Route::post('/auth/logout', [UserController::class, 'logout']);
    // Update user route
    Route::post('/auth/update', [UserController::class, 'updateUser']);
    Route::post('/cars/searchcar', [CarController::class, 'searchCar']);

    Route::post('/hotels/{id}/review', [HotelController::class, 'rateAndCommentHotel']);
    Route::get('/hotels/{id}/comments', [HotelController::class, 'getAllHotelComments']);
    Route::post('/places/{id}/review', [PlaceController::class, 'rateAndCommentPlace']);
    Route::post('/companies/{id}/review', [TourismController::class, 'rateAndCommentCompany']);
    Route::get('/companies/{id}/comments', [TourismController::class, 'getAllCompanyComments']);


    Route::get('/places/{id}/comments', [PlaceController::class, 'getAllPlaceComments']);




});

Route::post('/reco', [RecommendationController::class,'getRecommendation']);
Route::post('/reco-location', [RecommendationController::class,'getRecommendationBasedOnLocation']);

#PLACES
Route::post('/addplacedata', [PlaceController::class, 'uploadPlaceData']);
Route::get('/places', [PlaceController::class, 'getAllPlaces']);
Route::get('/places/{id}', [PlaceController::class, 'getPlaceById']);
Route::get('/places/search/{name?}', [PlaceController::class, 'searchPlaces']);
Route::get('/most-visited', [PlaceController::class, 'getTopRatedPlaces']);
Route::get('/random-places', [PlaceController::class, 'getRandomPlaces']);
#HOTELS
Route::post('/uploadhoteldata', [HotelController::class, 'uploadHotelData']);
Route::get('/hotels', [HotelController::class, 'getAllHotels']);
Route::get('/hotels/{id}', [HotelController::class, 'getHotelById']);
Route::get('/hotels/search/{name}', [HotelController::class, 'searchHotels']);
Route::get('/hotels/nearby/{id}', [HotelController::class, 'nearby']);
Route::post('/hotels/{hotelId}/reserve', [HotelController::class, 'reserveHotel']);
#CARS
Route::post('/cars/addcar', [CarController::class, 'addCar']);
Route::get('/cars', [CarController::class, 'getAllCars']);
Route::post('/cars/searchcar', [CarController::class, 'searchCar']);

#ENTERTAINMENT
Route::post('/add-entertainment', [EntertainmentController::class, 'uploadEntertainmentData']);
Route::get('/entertainment/{category}', [EntertainmentController::class, 'getByCategory']);
Route::get('/ent/{id}', [EntertainmentController::class, 'getEntById']);
Route::get('/entertainment/search/{name}', [EntertainmentController::class, 'searchEntertainments']);
#RESERVATIONS
Route::get('/reservations', [UserController::class,'getAllReservations']);
#TOURISM_COMPANIES
Route::post('/uploadcompanydata', [TourismController::class, 'uploadCompanyData']);
Route::get('/companies', [TourismController::class, 'getAllCompanies']);
Route::get('/companies/{id}', [TourismController::class, 'getCompanyById']);
Route::get('/companies/search/{name}', [TourismController::class, 'searchCompanies']);













