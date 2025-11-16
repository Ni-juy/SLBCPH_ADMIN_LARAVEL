<?php



namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;



class SystemLogsController extends Controller
{

    /**
     * Clean logs older than 90 days from the system.log file.
     *
     * @return void
     */
    public function cleanOldLogs()
    {
        $logPath = storage_path('logs/system.log');

        if (!File::exists($logPath)) {
            return;
        }

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filteredLines = [];

        $now = new \DateTime();
        $threshold = $now->modify('-90 days');

        foreach ($lines as $line) {
            if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                $logDate = \DateTime::createFromFormat('Y-m-d H:i:s', $matches[1]);
                if ($logDate && $logDate >= $threshold) {
                    $filteredLines[] = $line;
                }
            }
        }

        // Overwrite the log file with filtered lines
        File::put($logPath, implode(PHP_EOL, $filteredLines));
    }

    public function index(Request $request)
    {
        $logPath = storage_path('logs/system.log');
        $logs = [];

        if (File::exists($logPath)) {
            $lines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -300);

            foreach ($lines as $line) {
                if (preg_match('/^\[(.*?)\]\s*User:\s*(.*?)\s*\|\s*Role:\s*(.*?)\s*\|\s*Action:\s*(.*?)\s*\|\s*Details:\s*(.*)$/', $line, $matches)) {
                    $logs[] = [
                        'datetime' => $matches[1],
                        'user' => $matches[2],
                        'role' => $matches[3],
                        'action' => $matches[4],
                        'details' => $matches[5],
                    ];
                }
            }

            $logs = array_reverse($logs);
        }

        // Apply filters
        if ($request->has('search')) {
            $search = strtolower($request->input('search'));
            $logs = array_filter($logs, fn($log) => str_contains(strtolower($log['user']), $search));
        }

        if ($request->filled('date')) {
            $dateFilter = $request->input('date');
            $logs = array_filter($logs, fn($log) => str_starts_with($log['datetime'], $dateFilter));
        }

        // Paginate
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $offset = ($currentPage - 1) * $perPage;
        $pagedLogs = array_slice($logs, $offset, $perPage);

        $logs = new LengthAwarePaginator($pagedLogs, count($logs), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('superadmin.systemlogs', compact('logs'));
    }
}