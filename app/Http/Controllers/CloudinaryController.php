<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class CloudinaryController extends Controller
{
  public function uploadProfile(Request $request)
  {

    $user = Auth::user();


    $request->validate([
      'profile_image' => 'required|image|max:2048'
    ]);



    // Upload new profile image to Cloudinary
    $uploadedFile = Cloudinary::uploadApi()->upload(
      $request->file(key: 'profile_image')->getRealPath(),
      ['folder' => 'profile_images']
    );

    // Save only the secure_url in DB
    $user->update(['profile_image' => $uploadedFile['secure_url']]);

    return response()->json([
      'message' => 'Profile image updated successfully!',
      'url' => $uploadedFile['secure_url'],
    ]);

  }

  // public function uploadImage(Request $request)
  // {
  //   try {
  //     $file = $request->file('image');
  //     if (!$file->isValid()) {
  //       throw new \Exception('Invalid file upload.');
  //     }
  //     $uploadedFile = Cloudinary::upload($file->getRealPath(), [
  //       'folder' => 'profile_images',
  //       'resource_type' => 'image',
  //     ]);

  //     return response()->json([
  //       'success' => true,
  //       'image' => $uploadedFile->getSecurePath(),
  //     ]);
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       'success' => false,
  //       'message' => $e->getMessage(),
  //     ], 500);
  //   }
  // }
  public function uploadImage(Request $request)
{
    try {
        $file = $request->file('image');

        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        $path = $file->store('profile_images', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'image' => $url,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

  public function userUploadProfile(Request $request)
  {

    $user = Auth::user();


    $request->validate([
      'profile_image' => 'required|image|max:2048'
    ]);



    // Upload new profile image to Cloudinary
    $uploadedFile = Cloudinary::uploadApi()->upload(
      $request->file('profile_image')->getRealPath(),
      ['folder' => 'profile_images']
    );

    // Save only the secure_url in DB
    $user->update(['profile_image' => $uploadedFile['secure_url']]);

    return response()->json([
      'message' => 'Profile image updated successfully!',
      'url' => $uploadedFile['secure_url'],
    ]);

  }



}