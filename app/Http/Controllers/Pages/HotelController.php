<?php

namespace App\Http\Controllers\Pages;

use App\Models\Hotel;
use App\Models\HotelReservation;
use App\Models\Place;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;

class HotelController extends Controller
{
    public function uploadHotelData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:hotels',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'images.*' => 'required|string|url', // Validate each image URL
            'rate' => 'required|numeric|min:0|max:10',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Concatenate all image URLs with a delimiter (e.g., comma)
        $imagesString = implode(',', $request->images);

        // Create hotel record
        $hotel = Hotel::create([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'images' => $imagesString,
            'rate' => $request->rate,
            'price' => $request->price,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Hotel created successfully',
            'data' => [
                'name' => $hotel->name,
                'location' => $hotel->location,
                'description' => $hotel->description,
                'images' => explode(',', $hotel->images), // Convert images string back to an array
                'rate' => $hotel->rate,
                'price' => $hotel->price,
            ],
        ], 201);
    }
    public function getAllHotels()
    {
        $hotels = Hotel::filter()->select('id', 'images', 'name', 'location', 'rate', 'disability_accommodation', 'price')->get();

        // Transform each hotel to include only the first image URL
        $hotels->transform(function ($hotel) {
            $images = explode(',', $hotel->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $hotel->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($hotel->images); // Remove the original 'images' attribute
            return $hotel;
        });

        return response()->json([
            'status' => true,
            'data' => $hotels,
        ]);
    }


    public function getHotelById($id)
    {
        $hotel = Hotel::find($id);

        if (!$hotel) {
            return response()->json([
                'status' => false,
                'message' => 'Hotel not found',
            ], 404);
        }

        $images = explode(',', $hotel->images); // Split images string into an array of URLs

        // Remove the 'images' attribute from the hotel object
        unset($hotel->images);

        return response()->json([
            'status' => true,
            'data' => [
                'hotel' => $hotel,
                'images' => $images,
            ],
        ]);
    }




    public function searchHotels($name)
    {
        // Retrieve entertainment venues matching the search name with selected columns
        $hotel = Hotel::select('id', 'images', 'name', 'location', 'rate', 'disability_accommodation', 'price')
            ->where('name', 'like', '%' . $name . '%')
            ->get();

        // Transform each hotel venue to include only the first image URL
        $hotel->transform(function ($ent) {
            $images = explode(',', $ent->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $ent->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($ent->images); // Remove the original 'images' attribute
            return $ent;
        });

        return response()->json([
            'status' => true,
            'data' => $hotel,
        ]);
    }

    public function nearby($id)
    {
        $hotel = Hotel::find($id);

        // Check if the hotel exists
        if (!$hotel) {
            return response()->json(['status' => false, 'message' => 'Hotel not found'], 404);
        }

        $hotelLocation = $hotel->location;

        // Retrieve nearby places based on the location of the hotel
        $nearbyPlaces = Place::where('location', 'like', '%' . $hotelLocation . '%')->get();

        return response()->json(['status' => true, 'data' => $nearbyPlaces]);
    }

    public function reserveHotel(Request $request, $hotelId)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'arrive_date' => 'required|date',
            'leave_date' => 'required|date',
            'num_of_adults' => 'required|integer|min:1',
            'num_of_children' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Check if the hotel exists
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return response()->json([
                'status' => false,
                'message' => 'Hotel not found',
            ], 404);
        }

        // Create a new reservation for the hotel
        $reservation = new HotelReservation();
        $reservation->hotel_id = $hotelId;
        $reservation->name = $request->input('name');
        $reservation->phone_number = $request->input('phone_number');
        $reservation->arrive_date = $request->input('arrive_date');
        $reservation->leave_date = $request->input('leave_date');
        $reservation->num_of_adults = $request->input('num_of_adults');
        $reservation->num_of_children = $request->input('num_of_children');

        // Save the reservation
        if (!$reservation->save()) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create reservation',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Reservation created successfully',
            'data' => $reservation,
        ], 201);
    }

    public function rateAndCommentHotel(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'rating' => 'nullable|numeric|min:1|max:10',
            'comment' => 'nullable|string',
        ]);


        // Check for validation failure
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Find the hotel by ID
            $hotel = Hotel::find($id);

            // Check if the hotel exists
            if (!$hotel) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hotel not found',
                ], 404);
            }

            // Create a new comment record
            $review = new Review;
            $review->user_id = auth()->id(); // Assuming the user is authenticated
            $review->hotel_id = $id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->save();

            return response()->json([
                'status' => true,
                'message' => 'Rating and comment added successfully',
                'data' => $review,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function getAllHotelComments($id)
{
    try {
        // Find the hotel by ID
        $hotel = Hotel::find($id);

        // Check if the hotel exists
        if (!$hotel) {
            return response()->json([
                'status' => false,
                'message' => 'Hotel not found',
            ], 404);
        }

        $comments = Review::where('hotel_id', $id)
        ->whereNotNull('comment')
        ->with('user:id,name,image') // Eager load the user relationship and select only id and name columns
        ->get(['user_id', 'comment', 'created_at']); // Select the desired columns

        return response()->json([
            'status' => true,
            'data' => $comments,
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
}
}
