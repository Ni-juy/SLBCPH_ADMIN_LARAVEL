<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\CloudinaryController;
class AdminProfileController extends Controller
{

    private function validateGeneral(Request $req, $user)
    {
        return $req->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|digits:11',
            'address' => 'nullable|string|max:255',
        ]);
    }

private function handleImageUpload(Request $req, $user): void
{
    $req->validate(['profile_image' => 'required|image|max:2048']);

    // If user already has an image on Cloudinary, you can optionally destroy it
    if ($user->profile_image) {
        // Extract the public_id from the stored URL (optional if you want delete functionality)
        $publicId = pathinfo(parse_url($user->profile_image, PHP_URL_PATH), PATHINFO_FILENAME);
        CloudinaryController::destroy($publicId);
    }

    // Upload to Cloudinary
    $uploadedFileUrl = CloudinaryController::upload(
        $req->file('profile_image')->getRealPath(),
        ['folder' => 'profile_images'] // optional folder in Cloudinary
    )->getSecurePath();

    // Save Cloudinary URL in DB
    $user->update(['profile_image' => $uploadedFileUrl]);
}






    // private function handleImageUpload(Request $req, $user): void
    // {
    //     $req->validate(['profile_image' => 'required|image|max:2048']);

    //     if ($user->profile_image) {
    //         Storage::disk('public')->delete($user->profile_image);
    //     }

    //     $path = $req->file('profile_image')
    //         ->store('profile_images', 'public');

    //     $user->update(['profile_image' => $path]);
    // }

    private function handlePasswordChange(Request $req, $user)
    {
        $req->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($req->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($req->new_password)]);
        return back()->with('success', 'Password updated successfully.');
    }


    public function show()
    {
        return view('admin.adminprofile', ['user' => Auth::user()]);
    }

    public function update(Request $req)
    {
        $user = Auth::user();
        $this->validateGeneral($req, $user);
        $user->update($req->only([
            'username',
            'email',
            'first_name',
            'middle_name',
            'last_name',
            'contact_number',
            'address'
        ]));
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateImage(Request $req)
    {
        $this->handleImageUpload($req, Auth::user());
        return back()->with('success', 'Profile image updated.');
    }

    public function updatePassword(Request $req)
    {
        return $this->handlePasswordChange($req, Auth::user());
    }


    public function showSa()
    {
        // super-admin Blade lives in resources/views/superadmin/profile.blade.php
        return view('superadmin.profile');
    }

    public function updateSa(Request $req)
    {
        $user = Auth::user();
        $this->validateGeneral($req, $user);
        $user->update($req->only([
            'username',
            'email',
            'first_name',
            'middle_name',
            'last_name',
            'contact_number',
            'address'
        ]));
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateImageSa(Request $req)
    {
        $this->handleImageUpload($req, Auth::user());
        return back()->with('success', 'Profile image updated.');
    }

    public function updatePasswordSa(Request $req)
    {
        return $this->handlePasswordChange($req, Auth::user());
    }
}
