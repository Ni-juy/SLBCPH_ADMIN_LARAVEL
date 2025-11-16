<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaithTrack;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class FaithTrackController extends Controller
{
    // 📋 Display records by branch
   public function index()
{
    $branchId = auth()->user()->branch_id;

    // Separate pagination for faith and track
    $faithLogs = FaithTrack::select('name', 'date_shared', 'address', 'contact_number', 'id', 'branch_id', 'type', 'tracks_given', 'created_at', 'updated_at')
        ->where('branch_id', $branchId)
        ->where('type', 'faith')
        ->orderByDesc('date_shared')
        ->groupBy('name', 'date_shared', 'address', 'contact_number', 'id', 'branch_id', 'type', 'tracks_given', 'created_at', 'updated_at')
        ->paginate(5, ['*'], 'faith_page');

    $trackLogs = FaithTrack::where('branch_id', $branchId)
        ->where('type', 'track')
        ->orderByDesc('date_shared')
        ->paginate(5, ['*'], 'track_page');

    return view('admin.faithtracks', compact('faithLogs', 'trackLogs'));
}


    // 📝 Store new faith or track record
    public function store(Request $request)
{
    $type = $request->input('type');

    $rules = [
        'type' => 'required|in:faith,track',
        'date_shared' => 'required|date',
    ];

    if ($type === 'faith') {
        $rules = array_merge($rules, [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|digits:11',
            'date_shared' => [
                'required',
                'date',
                Rule::unique('faith_tracks')->where(function ($query) use ($request) {
                    return $query->where('name', $request->name)
                        ->where('contact_number', $request->contact_number)
                        ->where('date_shared', $request->date_shared)
                        ->where('type', 'faith')
                        ->where('branch_id', auth()->user()->branch_id);
                }),
            ],
        ]);
    } elseif ($type === 'track') {
        $rules['tracks_given'] = 'required|integer|min:1';
    }

    try {
        $validated = $request->validate($rules);
    } catch (ValidationException $e) {
        if (request()->ajax()) {
            $errors = $e->validator->errors();
            $errorMessage = 'Validation failed';

            if ($errors->has('contact_number')) {
                $errorMessage = 'The contact number must be exactly 11 digits.';
            } elseif ($errors->has('date_shared')) {
                $errorMessage = 'This faith record already exists for that date.';
            }

            return response()->json([
                'success' => false,
                'errors' => $errors->toArray(),
                'message' => $errorMessage
            ], 422);
        }

        // Check if it's an 11-digit error
        if ($e->validator->errors()->has('contact_number')) {
            return redirect()->back()
                ->withInput()
                ->with('number_error', 'The contact number must be exactly 11 digits.');
        }

        // Check if it's a duplicate error
        if ($e->validator->errors()->has('date_shared')) {
            return redirect()->back()
                ->withInput()
                ->with('duplicate_error', 'This faith record already exists for that date.');
        }

        // Fallback: generic error
        return redirect()->back()
            ->withInput()
            ->with('error', 'Validation failed.');
    }

    $validated['branch_id'] = auth()->user()->branch_id;

    FaithTrack::create($validated);

    return redirect()->back()->with('success', ucfirst($type) . ' record added successfully.');
}


    // Delete multiple records by checkbox
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('selected_ids');

        if ($ids && is_array($ids)) {
            FaithTrack::whereIn('id', $ids)
                ->where('branch_id', auth()->user()->branch_id)
                ->delete();

            return redirect()->back()->with('success', 'Selected records deleted successfully.');
        }

        return redirect()->back()->with('error', 'No records selected for deletion.');
    }

    //  Show edit form for a specific record
    public function edit($id)
    {
        $record = FaithTrack::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

        if (request()->ajax()) {
            return response()->json($record);
        }

        return view('admin.faithtracks_edit', compact('record'));
    }

    // ✏️ Update a specific record
    public function update(Request $request, $id)
    {
        $record = FaithTrack::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

        $type = $record->type;

        $rules = [
            'date_shared' => 'required|date',
        ];

        if ($type === 'faith') {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'contact_number' => 'required|digits:11',
                'date_shared' => [
                    'required',
                    'date',
                    Rule::unique('faith_tracks')->where(function ($query) use ($request, $id) {
                        return $query->where('name', $request->name)
                            ->where('contact_number', $request->contact_number)
                            ->where('date_shared', $request->date_shared)
                            ->where('type', 'faith')
                            ->where('branch_id', auth()->user()->branch_id)
                            ->where('id', '!=', $id);
                    }),
                ],
            ]);
        } elseif ($type === 'track') {
            $rules['tracks_given'] = 'required|integer|min:1';
        }

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            // Check if it's an 11-digit error
            if ($e->validator->errors()->has('contact_number')) {
                return redirect()->back()
                    ->withInput()
                    ->with('number_error', 'The contact number must be exactly 11 digits.');
            }

            // Check if it's a duplicate error
            if ($e->validator->errors()->has('date_shared')) {
                return redirect()->back()
                    ->withInput()
                    ->with('duplicate_error', 'This faith record already exists for that date.');
            }

            // Fallback: generic error
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validation failed.');
        }

        $record->update($validated);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' record updated successfully.'
            ]);
        }

        return redirect()->route('faithtracks.index')->with('success', ucfirst($type) . ' record updated successfully.');
    }

public function batchUploadFaith(Request $request)
{
    $request->validate(['file' => 'required|mimes:xlsx,xls|max:2048']);
    $branchId = auth()->user()->branch_id;
    $errors = [];
    $added = 0;

    Log::info("Faith batch upload started by user ".auth()->id()." for branch $branchId");

    $spreadsheet = IOFactory::load($request->file('file'));
    $sheet = $spreadsheet->getActiveSheet();

    // Numeric indexing to avoid undefined keys
    $rows = $sheet->toArray(null, true, true, false);

    foreach ($rows as $index => $row) {
        if ($index < 3) { // skip header/note/sample
            Log::info("Skipping row $index (header/note/sample)");
            continue;
        }

        // Normalize values safely
        $name = trim((string)($row[0] ?? ''));
        $address = trim((string)($row[1] ?? ''));
        $contact_number = trim((string)($row[2] ?? ''));
        $rawDate = trim((string)($row[3] ?? ''));

        // Skip completely empty row
        if ($name === '' && $address === '' && $contact_number === '' && $rawDate === '') {
            Log::info("Row $index skipped: empty row");
            continue;
        }

        try {
            $dateObj = null;

            // Attempt Excel numeric date
            if (is_numeric($rawDate) && $rawDate > 0) {
                $dateObj = Carbon::instance(ExcelDate::excelToDateTimeObject($rawDate));
            } elseif ($rawDate !== '') {
                $dateObj = $this->parseDateString($rawDate);
            }

            if (!$dateObj) {
                throw new \Exception("Unable to parse date: '$rawDate'");
            }

            if ($dateObj->greaterThan(Carbon::now())) {
                $errors[] = "Row ".($index+1).": Date cannot be in the future.";
                Log::warning("Row ".($index+1)." skipped: future date '$rawDate'");
                continue;
            }

            FaithTrack::create([
                'name' => $name,
                'address' => $address,
                'contact_number' => $contact_number,
                'date_shared' => $dateObj->format('Y-m-d'),
                'type' => 'faith',
                'branch_id' => $branchId
            ]);

            $added++;
            Log::info("Row ".($index+1)." added: $name, date='$rawDate'");

        } catch (\Exception $e) {
            $errors[] = "Row ".($index+1).": ".$e->getMessage();
            Log::error("Row ".($index+1)." failed: ".$e->getMessage()." | Original value: '$rawDate'");
        }
    }

    Log::info("Faith batch upload finished. Added: $added, Errors: ".count($errors));

    $message = "$added faith records uploaded.";
    if (count($errors)) $message .= ' Some rows failed: '.implode(' | ', $errors);

    return redirect()->back()->with('success', $message);
}

public function batchUploadTracks(Request $request)
{
    $request->validate(['file' => 'required|mimes:xlsx,xls|max:2048']);
    $branchId = auth()->user()->branch_id;

    $spreadsheet = IOFactory::load($request->file('file'));
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, false); // numeric keys

    $errors = [];
    $added = 0;

    Log::info("Track batch upload started by user ".auth()->id()." for branch $branchId");

    foreach ($rows as $index => $row) {
        if ($index < 3) { // skip header/note/sample
            Log::info("Skipping row $index (header/note/sample)");
            continue;
        }

        $rawDate = trim((string)($row[0] ?? ''));
        $tracks_given = trim((string)($row[1] ?? ''));

        if ($rawDate === '' && $tracks_given === '') {
            Log::info("Row $index skipped: empty row");
            continue;
        }

        try {
            $dateObj = null;

            if (is_numeric($rawDate) && $rawDate > 0) {
                $dateObj = Carbon::instance(ExcelDate::excelToDateTimeObject($rawDate));
            } else {
                $dateObj = $this->parseDateString($rawDate);
            }

            if (!$dateObj) throw new \Exception("Unable to parse date: '$rawDate'");

            if ($dateObj->greaterThan(Carbon::now())) {
                $errors[] = "Row ".($index+1).": Date cannot be in the future.";
                Log::warning("Row ".($index+1)." skipped: future date '$rawDate'");
                continue;
            }

            FaithTrack::create([
                'date_shared' => $dateObj->format('Y-m-d'),
                'tracks_given' => (int)$tracks_given,
                'type' => 'track',
                'branch_id' => $branchId
            ]);

            $added++;
            Log::info("Row ".($index+1)." added: $tracks_given tracks on '$rawDate'");

        } catch (\Exception $e) {
            $errors[] = "Row ".($index+1).": ".$e->getMessage();
            Log::error("Row ".($index+1)." failed: ".$e->getMessage()." | Original value: '$rawDate'");
        }
    }

    Log::info("Track batch upload finished. Added: $added, Errors: ".count($errors));

    $message = "$added track records uploaded.";
    if (count($errors)) $message .= ' Some rows failed: '.implode(' | ', $errors);

    return redirect()->back()->with('success', $message);
}

/**
 * Parse date string with multiple formats including Japanese style.
 */
private function parseDateString(string $dateStr): ?Carbon
{
    $dateStr = trim($dateStr);

    // Clean and normalize common separators
    $clean = preg_replace('/[年月]/u', '-', $dateStr);
    $clean = preg_replace('/日/u', '', $clean);
    $clean = preg_replace('/[^\d\-\/\.]/', '', $clean);
    $clean = preg_replace('/-+/', '-', $clean);
    $clean = trim($clean, '-');

    // Try default parse
    try { return Carbon::parse($clean); } catch (\Exception $e) {}

    // Try multiple formats
    $formats = ['Y-m-d','Y/m/d','Y.m.d','m-d-Y','m/d/Y','m.d.Y','d-m-Y','d/m/Y','d.m.Y','Y年m月d日','Y年m月j日'];
    foreach ($formats as $f) {
        try {
            $d = Carbon::createFromFormat($f, $dateStr);
            if ($d) return $d;
        } catch (\Exception $e) { continue; }
    }

    // Japanese style fallback
    if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/u', $dateStr, $matches)) {
        return Carbon::createFromDate($matches[1], $matches[2], $matches[3])->startOfDay();
    }

    return null; // failed to parse
}
public function downloadFaithTemplate()
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Shared Faith');

    // Header row
    $headers = ['Name','Address','Contact Number','Date Shared'];
    $sheet->fromArray([$headers], NULL, 'A1');

    // Style header
    $sheet->getStyle('A1:D1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // Column widths
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(40);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(15);

    // Hint row
    $sheet->setCellValue('A2', 'Note: Do not enter future dates.');
    $sheet->mergeCells('A2:D2');
    $sheet->getStyle('A2')->applyFromArray([
        'font' => ['italic' => true, 'color' => ['rgb' => 'FF0000']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
    ]);

    // Sample row
    $sample = ['Juan Dela Cruz','123 Main St, Manila','09171234567', now()->format('Y-m-d')];
    $sheet->fromArray([$sample], NULL, 'A3');

    // Freeze headers
    $sheet->freezePane('A4');

    $writer = new Xlsx($spreadsheet);
    $fileName = 'faith_template.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    $writer->save("php://output");
    exit;
}


public function downloadTrackTemplate()
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Tracks Given');

    // Header row
    $headers = ['Date Given','Tracks Given'];
    $sheet->fromArray([$headers], NULL, 'A1');

    // Style header
    $sheet->getStyle('A1:B1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // Column widths
    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(20);

    // Hint row
    $sheet->setCellValue('A2', 'Note: Do not enter future dates.');
    $sheet->mergeCells('A2:B2');
    $sheet->getStyle('A2')->applyFromArray([
        'font' => ['italic' => true, 'color' => ['rgb' => 'FF0000']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
    ]);

    // Sample row
    $sample = [now()->format('Y-m-d'), 5];
    $sheet->fromArray([$sample], NULL, 'A3');

    // Freeze headers
    $sheet->freezePane('A4');

    $writer = new Xlsx($spreadsheet);
    $fileName = 'track_template.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    $writer->save("php://output");
    exit;
}


}

