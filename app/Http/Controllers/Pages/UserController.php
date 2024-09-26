<?php

namespace App\Http\Controllers\Pages;

use App\Models\HotelReservation;
use App\Models\CarReservation;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOTP;


class UserController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function createUser(Request $request)
    {
        try {
            // Validate user input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'required|string|max:255',
                'account_type' => 'required|string|in:natural,tour guide,With a mobility disability,with a hearing disability,Other',
                'image' => 'sometimes|string|url|nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 400);
            }




            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'account_type' => $request->account_type,
                'image' => $request->image, // Use the $imageName variable here
            ]);

            // Return success response with token
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 201);
        } catch (\Throwable $th) {
            // Catch any unexpected errors
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            // Check if the user is authenticated and has a valid access token
            $user = auth()->user();

            if ($user && $user->currentAccessToken()) {
                // Revoke the current user's access token
                $request->user()->currentAccessToken()->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'User logged out successfully',
                ], 200);
            } else {
                // User not authenticated or no valid token
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated or no valid access token',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function getAllUsers()
    {
        // Retrieve all places from the database
        $users = User::all();

        return response()->json([
            'status' => true,
            'data' => $users,
        ]);
    }
    public function updateUser(Request $request)
{
    try {
        // Retrieve the authenticated user
        $user = auth()->user();

        // Validate user input including the image
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'phone_number' => 'sometimes|string|max:255|nullable',
            'account_type' => 'sometimes|string|in:hearing_disability,physical_disability,normal,tour_guide',
            'image' => 'sometimes|string|url|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Update user data if provided in the request
        if ($request->filled('image')) {
            // If the image is provided as a URL, directly update the image field
            $user->image = $request->image;
        }

        // Update other user data if provided
        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->filled('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        if ($request->filled('account_type')) {
            $user->account_type = $request->account_type;
        }

        // Save the updated user
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user,
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
