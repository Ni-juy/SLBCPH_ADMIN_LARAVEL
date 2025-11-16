<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\User;

class CertificationController extends Controller
{
    public function index()
    {
        $members = User::where('role', 'member')->get();

        return view('admin.certifications.index', compact('members'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:member,visitor',
            'salvation_date' => 'required|date',
            'baptism_date' => 'required|date',
            'member_id' => 'required_if:mode,member|nullable|exists:users,id',
            'visitor_name' => 'required_if:mode,visitor|nullable|string|max:255',
        ]);

        if ($request->mode === 'member') {
            $member = User::findOrFail($request->member_id);
            $middleInitial = $member->middle_name ? strtoupper(substr($member->middle_name, 0, 1)) . '.' : '';
            $name = "{$member->first_name} {$middleInitial} {$member->last_name}";
        } else {
            $name = $request->visitor_name;
        }

        $data = [
            'name' => $name,
            'salvation_date' => $request->salvation_date,
            'baptism_date' => $request->baptism_date,
        ];

        // Use same background logic as before
        $backgroundPath = public_path('certificates/baptism_template.jpg');
        $background = 'data:image/jpg;base64,' . base64_encode(file_get_contents($backgroundPath));

        $html = view('admin.certifications.templates.baptism', [
            'name' => $data['name'],
            'salvation_date' => $data['salvation_date'],
            'baptism_date' => $data['baptism_date'],
            'background' => $background,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="baptism_certificate.pdf"');
    }
}
