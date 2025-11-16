<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donation;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

class MemberDonationController extends Controller
{
    // Fetch authenticated member donations
    public function index(Request $request)
    {
        $user = Auth::user();
        $from = $request->query('from');
        $to = $request->query('to');
        $query = Donation::where('user_id', $user->id);
        // Apply date range filter if both dates are provided
        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        }
        // Fetch donations
        $donations = $query->orderBy('date', 'desc')->get();
        return response()->json($donations);
    }

    // Download personal report as PDF
    public function downloadReport(Request $request)
    {
        $user = Auth::user();
        $from = $request->query('from');
        $to = $request->query('to');

        // Validate that both dates are provided
        if (!$from || !$to) {
            return response()->json(['error' => 'Both from and to dates are required.'], 400);
        }

        // Fetch donations for the specified date range
        $donations = Donation::where('user_id', $user->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate total amount donated
        $totalAmount = $donations->sum('amount');

        // Generate PDF using Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Prepare HTML for the PDF
        $html = view('member.donation_report_pdf', [
            'donations' => $donations,
            'totalAmount' => $totalAmount,
            'from' => $from,
            'to' => $to,
            'member' => $user,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('donation_report.pdf', ['Attachment' => true]);
    }
}
