<?php

namespace App\Http\Controllers\Pages;

use App\Models\TourismCompany;
use App\Models\HotelReservation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;

class TourismController extends Controller
{
    public function uploadCompanyData(Request $request)
    {
        // Validation rules for company data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tourism_companies',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'offers' => 'required|string',
            'phone' => 'required|numeric',
            'images.*' => 'required|string|url', // Validate each image URL
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Store image URLs as JSON array
        $imagesString = implode(',', $request->images);

        // Create company record
        $company = TourismCompany::create([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'offers' => $request->offers,
            'phone' => $request->phone,
            'images' => $imagesString,
        ]);

        $images = explode(',', $company->images); // Split images string into an array of URLs

        // Remove the 'images' attribute from the hotel object
        unset($company->images);

        return response()->json([
            'status' => true,
            'data' => [
                'hotel' => $company,
                'images' => $images,
            ],
        ]);
    }


    public function getAllCompanies()
    {
        $company = TourismCompany::select('id', 'images', 'name', 'location')
            ->get();

        // Transform each company venue to include only the first image URL
        $company->transform(function ($ent) {
            $images = explode(',', $ent->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $ent->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($ent->images); // Remove the original 'images' attribute
            return $ent;
        });

        return response()->json([
            'status' => true,
            'data' => $company,
        ]);
    }

    public function getCompanyById($id)
    {
        $company = TourismCompany::find($id);

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'company not found',
            ], 404);
        }

        $images = explode(',', $company->images); // Split images string into an array of URLs
        unset($company->images);


        return response()->json([
            'status' => true,
            'data' => [
                'company' => $company,
                'images' => $images,
            ],
        ]);
    }


    public function searchCompanies($name)
    {
        // Retrieve entertainment venues matching the search name with selected columns
        $company = TourismCompany::select('id', 'images', 'name', 'location')
            ->where('name', 'like', '%' . $name . '%')
            ->get();

        // Transform each company venue to include only the first image URL
        $company->transform(function ($ent) {
            $images = explode(',', $ent->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $ent->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($ent->images); // Remove the original 'images' attribute
            return $ent;
        });

        return response()->json([
            'status' => true,
            'data' => $company,
        ]);
    }
    public function rateAndCommentCompany(Request $request, $id)
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
            $company = TourismCompany::find($id);

            // Check if the hotel exists
            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found',
                ], 404);
            }

            // Create a new comment record
            $review = new Review;
            $review->user_id = auth()->id(); // Assuming the user is authenticated
            $review->tourism_company_id = $id;
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
    public function getAllCompanyComments($id)
    {
        try {
            // Find the hotel by ID
            $company = TourismCompany::find($id);

            // Check if the company exists
            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'company not found',
                ], 404);
            }

            $comments = Review::where('tourism_company_id', $id)
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
