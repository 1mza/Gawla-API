<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    public function getRecommendation(Request $request)
    {
        // Make the request to the Python API
        $response = Http::post('https://ahmed8312220.pythonanywhere.com/recommendation', [
            'choice' => $request->input('choice'),
            'preferences' => $request->input('preferences'),
            'min_rating' => $request->input('min_rating'),
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            // Get the recommended places from the response data
            $recommendedPlaces = $response->json('recommended_places');

            // Return the recommended places as JSON response
            return response()->json([
                'recommended_places' => $recommendedPlaces,
            ]);
        } else {
            // Return an error response if the request was unsuccessful
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch recommendations from the Python API',
            ], $response->status());
        }
    }
    public function getRecommendationBasedOnLocation(Request $request)
    {
        // Make the request to the Python API
        $response = Http::post('https://az8312220.pythonanywhere.com/recommend-places', [
            'address' => $request->input('address'),
            'preferences' => $request->input('preferences'),
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            // Get the recommendations from the response data
            $recommendations = $response->json('recommendations');

            // Return the recommendations as JSON response
            return response()->json([
                'recommendations' => $recommendations,
            ]);
        } else {
            // Return an error response if the request was unsuccessful
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch recommendations from the Python API',
            ], $response->status());
        }
    }
}
