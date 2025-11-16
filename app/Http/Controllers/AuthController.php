<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AuthController extends Controller
{

    public function upload_profile(Request $request)
    {
        if (!$request->hasFile('profile_image')) {
            return response()->json([
                'success' => false,
                'message' => 'No image file found in the request.'
            ], 400);
        }

        if (!$request->file('profile_image')->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded file is not valid.'
            ], 400);
        }

        $userId = $request->input('userId');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            // ✅ Debug logging before upload
            \Log::info('Uploading file to Cloudinary...', [
                'name' => $request->file('profile_image')->getClientOriginalName(),
                'size' => $request->file('profile_image')->getSize(),
                'type' => $request->file('profile_image')->getMimeType(),
            ]);

            $uploadedFile = Cloudinary::uploadApi()->upload(
                $request->file('profile_image')->getRealPath(),
                ['folder' => 'profile_images']
            );
            // Optionally update the DB
            $user->update(['profile_image' => $uploadedFile['secure_url']]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully!',
                'url' => $uploadedFile['secure_url'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Cloudinary upload failed:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Cloudinary upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function login(Request $request)
    {
        /* Keys for this user + IP */
        $baseKey = 'login:' . strtolower($request->username) . '|' . $request->ip();
        $triesKey = $baseKey . ':tries';
        $lockKey = $baseKey . ':lockout-until';

        /* ───── 0️⃣  Clear expired lock instantly ───── */
        if ($until = Cache::get($lockKey)) {
            if (Carbon::now()->gte($until)) {
                Cache::forget($lockKey);
            } else {
                $secs = Carbon::now()->diffInSeconds($until);
                return response()->json([
                    'message' => 'Too many login attempts',
                    'retry_after' => $secs,
                    'recommend_reset' => true,
                ], 429);
            }
        }

        /* ───── 1️⃣  Fetch user ───── */
        $user = User::where('username', $request->username)
            ->where('role', 'Member')
            ->first();

        /* ───── 1a️⃣ Check status ───── */
        if ($user && in_array($user->status, ['Pending', 'Archived'])) {
            return response()->json([
                'message' => "Your account is currently '{$user->status}' and cannot log in.",
                'status' => $user->status,
            ], 403);
        }

        /* ───── 2️⃣  Check credentials ───── */
        if (!$user || !Hash::check($request->password, $user->password)) {
            $attempts = Cache::increment($triesKey);
            if (!$attempts) {
                $attempts = 1;
                Cache::put($triesKey, 1, 3600);
            } else {
                Cache::put($triesKey, $attempts, 3600);
            }

            if ($attempts % 3 === 0) {
                $minutes = max(1, intdiv($attempts, 3));
                $until = Carbon::now()->addMinutes($minutes);
                Cache::put($lockKey, $until, $minutes * 60);

                return response()->json([
                    'message' => "Too many attempts. Try again in $minutes minute(s).",
                    'retry_after' => $minutes * 60,
                    'recommend_reset' => $attempts >= 10,
                ], 429);
            }

            return response()->json([
                'message' => "Invalid credentials. " . (3 - ($attempts % 3)) . " attempt(s) left before timeout.",
                'attempts' => $attempts,
                'next_lock_in' => 3 - ($attempts % 3),
            ], 401);
        }

        /* ───── 3️⃣  Success ───── */
        Cache::forget($triesKey);
        Cache::forget($lockKey);

        $token = $user->createToken('authToken', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
                'address' => $user->address,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'terms_accepted_at' => $user->terms_accepted_at,
                'requires_terms_acceptance' => is_null($user->terms_accepted_at),
            ],
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // add to the top of the controller if not already imported

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'username' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'contact_number' => 'nullable|digits:11',
            'address' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'baptism_date' => 'nullable|date',
            'salvation_date' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            //  status left out on purpose
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'username',
            'email',
            'first_name',
            'middle_name',
            'last_name',
            'contact_number',
            'address',
            'birthdate',
            'baptism_date',
            'salvation_date',
            'gender'
        ]);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user->update($data);

        Log::info('Profile updated', [
            'user_id' => $user->id,
            'by_ip' => $request->ip(),
            'fields' => array_keys($data),
        ]);

        return response()->json(['message' => 'Profile updated successfully!']);
    }



    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User  not authenticated.'], 401);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        // Update the password using the query builder
        User::where('id', $user->id)->update(['password' => Hash::make($request->new_password)]);

        $user = Auth::user();
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Change Password | Details: Changed password' . PHP_EOL,
            FILE_APPEND
        );
        return response()->json(['message' => 'Password changed successfully!']);
    }

    public function acceptTerms(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $user->terms_accepted_at = now();
        $user->save();

        // Log the terms acceptance
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Terms Acceptance | Details: Accepted terms and conditions' . PHP_EOL,
            FILE_APPEND
        );

        return response()->json(['message' => 'Terms accepted successfully']);
    }


    public function uploadImage(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $request->validate([
            'profile_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Optional: delete old Cloudinary image if you stored the public_id before
        // if ($user->cloudinary_public_id) {
        //     Cloudinary::destroy($user->cloudinary_public_id);
        // }

        // Upload to Cloudinary
        $uploadedFile = Cloudinary::uploadApi()->upload(
            $request->file('profile_image')->getRealPath(),
            ['folder' => 'profile_images']
        );

        // Save the secure URL (and optionally the public_id if you want to delete later)
        $user->update([
            'profile_image' => $uploadedFile['secure_url'],
            // 'cloudinary_public_id' => $uploadedFile['public_id'] ?? null,
        ]);

        // Log the update for audit
        Log::info('Profile image updated via Cloudinary', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'file' => $uploadedFile['secure_url']
        ]);

        return response()->json([
            'message' => 'Profile image updated successfully!',
            'image_url' => $uploadedFile['secure_url'],
        ]);
    }

}
