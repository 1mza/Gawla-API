<?php

namespace App\Http\Controllers\Pages;

use App\Models\Entertainment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EntertainmentController extends Controller
{
    public function uploadEntertainmentData(Request $request)
    {
        // Validation rules for entertainment data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:entertainments',
            'category' => 'required|in:مأكولات بحريه,مشويات و كشري,سوبرماركت',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'rate' => 'required|numeric|min:0|max:10',
            'images.*' => 'required|string|url', // Validate each image URL
            'physical_disability_accessible' => 'required|boolean',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Store image URLs as comma-separated string
        $imagesString = implode(',', $request->images);

        // Create entertainment record
        $entertainment = Entertainment::create([
            'name' => $request->name,
            'category' => $request->category,
            'location' => $request->location,
            'description' => $request->description,
            'rate' => $request->rate,
            'images' => $imagesString, // Store image URLs as a string
            'physical_disability_accessible' => $request->physical_disability_accessible,
        ]);
        $images = explode(',', $entertainment->images); // Split images string into an array of URLs

        unset($entertainment->images);

        return response()->json([
            'status' => true,
            'data' => [
                'hotel' => $entertainment,
                'images' => $images,
            ],
        ]);
    }

    public function getByCategory($category)
    {
        // Retrieve entertainment venues by category
        $entertainment = Entertainment::where('category', $category)->get();

        // Transform each entertainment venue to include separate image URLs
        $entertainment = Entertainment::select('id', 'images', 'name', 'location')->get();

        // Transform each place to include only the first image URL
        $entertainment->transform(function ($entertainment) {
            $images = explode(',', $entertainment->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $entertainment->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($entertainment->images); // Remove the original 'images' attribute
            return $entertainment;
        });

        return response()->json([
            'status' => true,
            'data' => $entertainment,
        ]);
    }

    public function searchEntertainments($name)
{
    // Retrieve entertainment venues matching the search name with selected columns
    $entertainment = Entertainment::select('id', 'images', 'name', 'location')
        ->where('name', 'like', '%' . $name . '%')
        ->get();

    // Transform each entertainment venue to include only the first image URL
    $entertainment->transform(function ($ent) {
        $images = explode(',', $ent->images); // Split images string into an array of URLs
        $firstImage = isset($images[0]) ? $images[0] : null; // Get the first URL from the array
        $ent->image = $firstImage; // Add a new 'image' attribute with the first URL
        unset($ent->images); // Remove the original 'images' attribute
        return $ent;
    });

    return response()->json([
        'status' => true,
        'data' => $entertainment,
    ]);
}



    public function getEntById($id)
    {
        // Retrieve the entertainment from the database based on the ID
        $ent = Entertainment::find($id);

        if (!$ent) {
            return response()->json([
                'status' => false,
                'message' => 'Entertainment not found',
            ], 404);
        }

        $images = explode(',', $ent->images); // Split images string into an array of URLs
        unset($ent->images);

        return response()->json([
            'status' => true,
            'data' => [
                'entertainment' => $ent,
                'images' => $images,
            ],
        ]);
    }
}
