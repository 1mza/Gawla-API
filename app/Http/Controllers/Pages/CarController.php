<?php

namespace App\Http\Controllers\Pages;

use App\Models\User;
use App\Models\Car;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


use App\Models\CarReservation;
use App\Models\HotelReservation;

class CarController extends Controller
{
    public function addCar(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'model' => 'required|string',
            'registration_number' => 'required|string|unique:cars',
            'seats' => 'nullable|integer',
            'doors' => 'nullable|integer',
            'air_conditioning' => 'nullable|boolean',
            'transmission' => 'nullable|string',
            'fuel_type' => 'nullable|string',
            'fuel_fill_up' => 'nullable|string',
            'price_per_km' => 'nullable|numeric',
            'physical_disability_accessible' => 'nullable|boolean',
            'image' => 'required|string|url', // Validate each image URL
        ]);

        // Check for validation failure
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Handle image upload
        $car = Car::create([
            'model' => $request->model,
            'registration_number' => $request->registration_number,
            'seats' => $request->seats,
            'image' => $request->image,
            'doors' => $request->doors,
            'air_conditioning' => $request->air_conditioning,
            'transmission' => $request->transmission,
            'fuel_type' => $request->fuel_type,
            'fuel_fill_up' => $request->fuel_fill_up,
            'price_per_km' => $request->price_per_km,
            'physical_disability_accessible' => $request->physical_disability_accessible,
        ]);

       // Return success response
        return response()->json([
            'status' => true,
            'message' => 'Car added successfully',
            'data' => $car
        ], 201);
    }
    public function getAllCars()
    {
        return Car::select('id','model', 'image', 'seats', 'doors', 'air_conditioning','price_per_km','transmission','physical_disability_accessible','fuel_fill_up')->get();
    }

    public function searchCar(Request $request)
    {
        try {
            // Retrieve the authenticated user
            $user = auth()->user();

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'back_to_same_location' => 'nullable|boolean',
                'location_of_receipt' => 'nullable|string',
                'location_of_delivery' => 'nullable|string',
                'date_of_receipt' => 'required|date_format:Y-m-d',
                'date_of_return' => 'required|date_format:Y-m-d',
                'need_driver' => 'nullable|boolean',
                'enable_physical_disability' => 'nullable|boolean',
                'car_id' => 'required|exists:cars,id', // Check if car ID exists in the cars table
            ]);

            // Check for validation failure
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Retrieve user details
            $userName = $user->name;
            $phoneNumber = $user->phone_number;

            // Check car availability based on provided dates
            $dateOfReceipt = $request->input('date_of_receipt');
            $dateOfReturn = $request->input('date_of_return');

            // Find the selected car
            $selectedCarId = $request->input('car_id');
            $car = Car::find($selectedCarId);

            // Check if the car exists
            if (!$car) {
                return response()->json([
                    'status' => false,
                    'message' => 'The selected car does not exist',
                ], 404);
            }

            // Check if the car is available for the specified dates
            $isAvailable = $car->reservations()->where(function ($query) use ($dateOfReceipt, $dateOfReturn) {
                $query->whereBetween('arrival_date', [$dateOfReceipt, $dateOfReturn])
                    ->orWhereBetween('return_date', [$dateOfReceipt, $dateOfReturn])
                    ->orWhere(function ($query) use ($dateOfReceipt, $dateOfReturn) {
                        $query->where('arrival_date', '<=', $dateOfReceipt)
                            ->where('return_date', '>=', $dateOfReturn);
                    });
            })->doesntExist();

            if (!$isAvailable) {
                return response()->json([
                    'status' => false,
                    'message' => 'The selected car is already reserved for the specified dates',
                ], 400);
            }

            // Save reservation
            $reservation = new CarReservation();
            $reservation->user_id = $user->id;
            $reservation->car_id = $selectedCarId;
            $reservation->name = $userName;
            $reservation->phone_number = $phoneNumber;
            $reservation->arrival_date = $dateOfReceipt;
            $reservation->return_date = $dateOfReturn;
            // Set other reservation properties as needed
            $reservation->save();

            return response()->json([
                'status' => true,
                'message' => 'Reservation saved successfully',
                'data' => $reservation,
            ], 200);
        } catch (\Throwable $th) {
            // Handle exceptions
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



}
