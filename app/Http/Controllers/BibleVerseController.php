<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BibleVerseController extends Controller
{
    public function send(Request $request)
    {
        Log::info('BibleVerseController@send called', [
            'request_data' => $request->all()
        ]);

        // Validation
        $request->validate([
            'book' => 'required|string|max:100',
            'chapter' => 'required|integer',
            'verse_number' => 'required|integer',
            'comment' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'member_ids' => 'required|array',
        ]);

        Log::info('Validation passed for BibleVerseController@send');

        try {
            // Fetch members
            $members = User::whereIn('id', $request->member_ids)
                ->where('role', 'member')
                ->get();

            Log::info('Fetched members', [
                'count' => $members->count(),
                'member_ids' => $members->pluck('id')->toArray()
            ]);

            if ($members->isEmpty()) {
                Log::warning('No members found for provided IDs', [
                    'provided_ids' => $request->member_ids
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No members found with the selected IDs.'
                ], 404);
            }

            
           // Build email content
$content = "<p>📖 <strong>Bible Verse</strong></p>";
$content .= "<p>Book: <strong>{$request->book}</strong><br>";
$content .= "Chapter: <strong>{$request->chapter}</strong><br>";
$content .= "Verse: <strong>{$request->verse_number}</strong></p>";
$content .= "<p>Text: {$request->verse_text}</p>"; // <-- Verse text from pastor
if ($request->comment) {
    $content .= "<p>Comment: {$request->comment}</p>";
}



            foreach ($members as $member) {
                try {
                    Mail::send([], [], function ($message) use ($member, $content, $request) {
                        $message->to($member->email)
                                ->subject('Bible Verse')
                                ->html($content); // use html() for Laravel 9/10+

                        // Attach image if exists
                        if ($request->hasFile('image')) {
                            $message->attach($request->file('image')->getRealPath(), [
                                'as' => $request->file('image')->getClientOriginalName(),
                                'mime' => $request->file('image')->getMimeType(),
                            ]);
                        }
                    });

                    Log::info('Bible verse sent successfully', [
                        'member_id' => $member->id,
                        'email' => $member->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send Bible verse to member', [
                        'member_id' => $member->id,
                        'email' => $member->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bible verse sent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('BibleVerseController@send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the Bible verse.'
            ], 500);
        }
    }
}
