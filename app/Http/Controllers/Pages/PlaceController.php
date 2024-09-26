<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;


class PlaceController extends Controller
{
    public function uploadPlaceData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:places',
            'category' => 'required|string|in:nature,seas,historical',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'images.*' => 'required|string|url', // Validate each image URL
            'physical_disability_accessible' => 'required|boolean',
            'rate' => 'required|numeric|min:0|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Store image URLs as comma-separated string
        $imagesString = implode(',', $request->images);

        // Create place record
        $place = Place::create([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'images' => $imagesString,
            'category' => $request->category,
            'physical_disability_accessible' => $request->physical_disability_accessible,
            'rate' => $request->rate,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Place created successfully',
            'data' => [
                'name' => $place->name,
                'location' => $place->location,
                'description' => $place->description,
                'images' => explode(',', $place->images), // Convert images string back to an array
                'category' => $place->category,
                'physical_disability_accessible' => $place->physical_disability_accessible,
                'rate' => $place->rate
            ],
        ], 201);
    }




    public function getAllPlaces()
    {
        $places = Place::select('id', 'images', 'name', 'location', 'rate', 'physical_disability_accessible')->get();

        // Transform each place to include only the first image URL
        $places->transform(function ($place) {
            $images = explode(',', $place->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $place->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($place->images); // Remove the original 'images' attribute
            return $place;
        });

        return response()->json([
            'status' => true,
            'data' => $places,
        ]);
    }



    public function getPlaceById($id)
    {
        $place = Place::find($id);

        if (!$place) {
            return response()->json([
                'status' => false,
                'message' => 'Place not found',
            ], 404);
        }

        $images = explode(',', $place->images); // Split images string into an array of URLs
        unset($place->images);


        return response()->json([
            'status' => true,
            'data' => [
                'place' => $place,
                'images' => $images,
            ],
        ]);
    }

    public function searchPlaces($name)
    {
        // Retrieve entertainment venues matching the search name with selected columns
        $place = Place::select('id', 'images', 'name', 'location', 'rate', 'physical_disability_accessible')
            ->where('name', 'like', '%' . $name . '%')
            ->get();

        // Transform each place venue to include only the first image URL
        $place->transform(function ($ent) {
            $images = explode(',', $ent->images); // Split images string into an array of URLs
            $firstImage = isset ($images[0]) ? $images[0] : null; // Get the first URL from the array
            $ent->image = $firstImage; // Add a new 'image' attribute with the first URL
            unset ($ent->images); // Remove the original 'images' attribute
            return $ent;
        });

        return response()->json([
            'status' => true,
            'data' => $place,
        ]);
    }

    public function recommendPlaces()
    {
        $pythonScript = 'C:\Users\Mostafa\Desktop\RS_L3.py';
        $argument1 = 'Cairo';
        $argument2 = '1';

        $command = 'python3 ' . $pythonScript;
        $output = exec($command);

        echo $output;
    }

    public function rateAndCommentPlace(Request $request, $id)
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
            $place = Place::find($id);

            // Check if the hotel exists
            if (!$place) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hotel not found',
                ], 404);
            }

            // Create a new comment record
            $review = new Review;
            $review->user_id = auth()->id(); // Assuming the user is authenticated
            $review->place_id = $id;
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
    public function getAllPlaceComments($id)
{
    try {
        // Find the hotel by ID
        $place = Place::find($id);

        // Check if the place exists
        if (!$place) {
            return response()->json([
                'status' => false,
                'message' => 'place not found',
            ], 404);
        }

        $comments = Review::where('place_id', $id)
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
public function getTopRatedPlaces()
{
    // استخراج الأماكن ذات التقييم الأعلى من 8 مع الحقول المطلوبة فقط
    $places = Place::select('id', 'images', 'name', 'location', 'rate')
        ->where('rate', '>', 8.5)
        ->get();

    // تحويل البيانات للحصول على الصورة الأولى فقط
    $transformedPlaces = $places->map(function ($place) {
        $images = explode(',', $place->images); // تقسيم سلسلة الصور إلى مصفوفة من الروابط
        return [
            'id' => $place->id,
            'name' => $place->name,
            'location' => $place->location,
            'image' => $images[0] ?? null, // الحصول على الرابط الأول أو null إذا لم يكن هناك صور
        ];
    });

    // إرجاع النتيجة كاستجابة JSON
    return response()->json([
        'status' => true,
        'data' => $transformedPlaces,
    ]);
}
public function getRandomPlaces(Request $request)
    {
        // تحديد عدد الأماكن العشوائية المراد استرجاعها (الافتراضي 10)
        $limit = $request->input('limit', 5);

        // جلب الأماكن بشكل عشوائي
        $places = Place::inRandomOrder()->take($limit)->get(['id', 'name', 'location', 'images']);

        // تحويل الصور لتضمين الرابط الأول فقط
        $places->transform(function ($place) {
            $images = explode(',', $place->images); // تقسيم سلسلة الصور إلى مصفوفة من الروابط
            $place->image = $images[0] ?? null; // الحصول على الرابط الأول أو null إذا لم يكن هناك صور
            unset($place->images); // إزالة الخاصية الأصلية 'images'
            return $place;
        });

        // إرجاع النتيجة كاستجابة JSON
        return response()->json([
            'status' => true,
            'data' => $places,
        ]);
    }

}
