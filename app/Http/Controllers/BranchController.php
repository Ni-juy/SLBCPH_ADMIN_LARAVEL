<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::with('parent_branch');
        if ($request->has('archived') && $request->archived == '1') {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }
        return $query->get();
    }

    public function archive($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->is_archived = true;
        $branch->save();

        // ✅ Logging
        $this->logAction("Archive Branch", 'Archived branch "' . $branch->name . '" at "' . $branch->address . '"');

        return response()->json(['success' => true, 'message' => 'Branch archived successfully.']);
    }

    public function getBranchesForDonation()
    {
        $branches = Branch::whereIn('branch_type', ['Organized', 'Main'])
            ->where('is_archived', false)
            ->get(['id', 'name', 'branch_type']);

        return response()->json($branches);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) {
                    return $query->where('is_archived', false);
                }),
            ],
            'address' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) {
                    return $query->where('is_archived', false);
                }),
            ],
            'branch_type' => 'nullable|string|max:50',
        ], [
            'name.unique' => 'A branch with the same name already exists.',
            'address.unique' => 'A branch with the same address already exists.',
        ]);

        // Prevent creating another Main branch
        if (strtolower($request->branch_type) === 'main') {
            $mainExists = Branch::whereRaw('LOWER(branch_type) = ?', ['main'])->exists();
            if ($mainExists) {
                return response()->json([
                    'error' => 'A Main branch already exists. Only one Main branch is allowed.'
                ], 422);
            }
        }

        $branch = Branch::create([
            'name' => $request->name,
            'address' => $request->address,
            'branch_type' => $request->branch_type,
            'extension_of' => $request->extension_of,
        ]);

        // ✅ Logging
        $this->logAction("Add Branch", 'Added branch "' . $branch->name . '" at "' . $branch->address . '"');

        return response()->json($branch, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) use ($id) {
                    return $query->where('is_archived', false)
                                 ->where('id', '!=', $id);
                }),
            ],
            'address' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) use ($id) {
                    return $query->where('is_archived', false)
                                 ->where('id', '!=', $id);
                }),
            ],
            'branch_type' => 'nullable|string|max:50',
        ], [
            'name.unique' => 'Another branch with the same name already exists.',
            'address.unique' => 'Another branch with the same address already exists.',
        ]);

        $branch = Branch::findOrFail($id);
        $oldName = $branch->name;
        $oldAddress = $branch->address;

        // ❌ Prevent setting multiple Main branches
        if (strtolower($request->branch_type) === 'main') {
            $mainExists = Branch::whereRaw('LOWER(branch_type) = ?', ['main'])
                ->where('id', '!=', $branch->id)
                ->exists();

            if ($mainExists) {
                return response()->json([
                    'error' => 'A Main branch already exists. Only one Main branch is allowed.'
                ], 422);
            }
        }

        $updateData = [
            'name' => $request->name,
            'address' => $request->address,
            'branch_type' => $request->branch_type,
        ];

        // ✅ If promoted to Organized, unlink from parent
        if (strtolower($request->branch_type) === 'organized') {
            $updateData['extension_of'] = null;
        }

        $branch->update($updateData);

        // ✅ Logging
        $this->logAction("Edit Branch", 'Edited branch "' . $oldName . '" at "' . $oldAddress . '" to "' . $branch->name . '" at "' . $branch->address . '"');

        return response()->json($branch);
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branchName = $branch->name;
        $branchAddress = $branch->address;
        $branch->delete();

        // ✅ Logging
        $this->logAction("Delete Branch", 'Deleted branch "' . $branchName . '" at "' . $branchAddress . '"');

        return response()->json(['success' => true]);
    }

    public function unarchive($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->is_archived = false;
        $branch->save();

        // ✅ Logging
        $this->logAction("Unarchive Branch", 'Unarchived branch "' . $branch->name . '" at "' . $branch->address . '"');

        return response()->json(['success' => true, 'message' => 'Branch unarchived successfully.']);
    }

    /**
     * Helper to log system actions with proper user info
     */
    private function logAction($action, $details)
    {
        $user = Auth::user();

        if ($user) {
            $userName = trim(
                ($user->first_name ?? '') . ' ' .
                ($user->middle_name ?? '') . ' ' .
                ($user->last_name ?? '')
            );
            $userRole = $user->role ?? 'Unknown';
        } else {
            $userName = 'System';
            $userRole = 'System';
        }

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: ' . $action .
            ' | Details: ' . $details . PHP_EOL,
            FILE_APPEND
        );
    }
}
