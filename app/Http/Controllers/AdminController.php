<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transparency;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\EmailVerificationService;
use \App\Models\Branch;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminCredentialsMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; 


class AdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'Admin')->get(['id', 'first_name', 'middle_name', 'last_name', 'email']);
        return response()->json($admins);
    }

    public function create()
    {
        return view('admin.addadmin');
    }


    public function updateTransparency(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        DB::beginTransaction();

        try {
            $transparency = Transparency::firstOrNew([
                'branch_id' => $branchId
            ]);

            $transparency->pdf_link = $request->pdf_link;
            $transparency->save(); 

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transparency PDF link updated successfully.',
                'branch_id' => $branchId,
                'pdf_link' => $request->pdf_link,
                'data' => $transparency
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    


public function store(Request $request, EmailVerificationService $emailVerifier)
{
    \Log::info("📥 Admin store request received", [
        'super_admin_id' => Auth::id(),
        'email'          => $request->email,
        'input'          => $request->except(['password']),
    ]);

 
    $validator = Validator::make($request->all(), [
        'first_name'     => 'required|string|max:255',
        'middle_name'    => 'nullable|string|max:255',
        'last_name'      => 'required|string|max:255',
        'gender'         => 'required|in:Male,Female',
        'contact_number' => 'required|digits:11',
        'email'          => 'required|email|unique:users,email',
        'birthdate'      => 'required|date|before_or_equal:today',
        'baptism_date'   => 'required|date|before_or_equal:today',
        'salvation_date' => 'required|date|before_or_equal:today',
        'address'        => 'required|string|max:500',
    ]);

    if ($validator->fails()) {
        \Log::warning("⚠️ Validation failed for admin store", [
            'errors' => $validator->errors()->toArray(),
            'input'  => $request->except(['password']),
        ]);

        return response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated = $validator->validated();
    \Log::info("✅ Validation passed", $validated);

    
    try {
        \Log::info("🔍 Verifying admin email", ['email' => $request->email]);
        $result = $emailVerifier->verify($request->email);
        \Log::info("🔎 Email verification result", ['email' => $request->email, 'result' => $result]);

        if (!$result) {
            \Log::warning("❌ Email verification failed", ['email' => $request->email]);
            return response()->json([
                'message' => 'Email verification failed. Please use a valid email address.',
            ], 422);
        }
    } catch (\Throwable $e) {
        \Log::error("❌ Email verification service error", [
            'email'     => $request->email,
            'exception' => $e->getMessage(),
        ]);
        return response()->json(['message' => 'Email verification service unavailable.'], 503);
    }

   
    $firstInitial  = strtolower(substr($validated['first_name'], 0, 1));
    $middleInitial = strtolower($validated['middle_name'] ? substr($validated['middle_name'], 0, 1) : '');
    $lastName      = strtolower(str_replace(' ', '', $validated['last_name']));
    $username      = $firstInitial . $middleInitial . $lastName;

    $originalUsername = $username;
    $counter = 1;
    while (User::where('username', $username)->exists()) {
        $username = $originalUsername . $counter;
        $counter++;
    }
    \Log::info("👤 Generated admin username", ['username' => $username, 'email' => $request->email]);

    $password = Str::random(10);
    \Log::info("🔑 Generated admin password", ['email' => $request->email]);


    DB::beginTransaction();
    try {
        $superAdmin = Auth::user();
        \Log::info("🧾 Creating admin record", ['super_admin_id' => $superAdmin->id]);

        $admin = User::create([
            'role'           => 'Admin',
            'first_name'     => $validated['first_name'],
            'middle_name'    => $validated['middle_name'],
            'last_name'      => $validated['last_name'],
            'gender'         => $validated['gender'],
            'contact_number' => $validated['contact_number'],
            'email'          => $validated['email'],
            'username'       => $username,
            'password'       => Hash::make($password),
            'address'        => $validated['address'],
            'birthdate'      => $validated['birthdate'],
            'baptism_date'   => $validated['baptism_date'],
            'salvation_date' => $validated['salvation_date'],
            'status'         => 'Pending',
        ]);

        \Log::info("✅ Admin created", ['admin_id' => $admin->id, 'email' => $admin->email]);

       
        $token = Str::random(60);
        DB::table('email_verifications')->insert([
            'user_id'       => $admin->id,
            'token'         => $token,
            'plain_password'=> $password,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        \Log::info("📝 Email verification token created", ['admin_id' => $admin->id, 'token' => $token]);

       
        $verifyUrl = url("/verify-email/{$token}");
        \Log::info("✉️ Sending admin verification email", ['email' => $admin->email, 'verifyUrl' => $verifyUrl]);

        try {
            Mail::to($admin->email)->send(new \App\Mail\AdminVerifyEmailMail($admin, $verifyUrl));
            \Log::info("📧 Admin verification email sent successfully", ['email' => $admin->email]);
        } catch (\Throwable $mailEx) {
            \Log::error("❌ Failed to send admin verification email", [
                'email'     => $admin->email,
                'exception' => $mailEx->getMessage(),
            ]);
            DB::rollBack();
            return response()->json(['message' => 'Failed to send verification email.'], 500);
        }

        DB::commit();

        return response()->json([
            'message' => 'Admin added successfully. Verification email sent.',
            'admin'   => $admin,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error("❌ Admin store fatal error", [
            'exception' => $e->getMessage(),
            'trace'     => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => 'Server error. Please contact support.'], 500);
    }
}



    public function manageAdmins()
{
    $admins = User::where('role', 'Admin')->get(); 
    $locations = [
            'Alaminos' => [
                'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Del Carmen', 'Palma', 'San Agustin', 'San Andres', 'San Benito', 'San Gregorio', 'San Ildefonso', 'San Juan', 'San Miguel', 'San Roque', 'Santa Rosa'
            ],
            'Bay' => [
                'Bitin', 'Calo', 'Dila', 'Maitim', 'Masaya', 'Paciano Rizal', 'Puypuy', 'San Antonio', 'San Isidro', 'Santa Cruz', 'Santo Domingo', 'Tagumpay', 'Tranca', 'San Agustin', 'SanNicolas'
            ],
           'Binan' => [
            'Binan', 'Bungahan', 'Canlalay', 'Casile', 'De La Paz', 'Ganado', 'Langkiwa', 'Loma', 'Malaban', 'Malamig', 'Mampalasan', 'Platero', 'Poblacion', 'San Antonio', 'San Francisco', 'San Jose', 'San Vicente', 'Santo Domingo', 'Santo Nino', 'Santo Tomas', 'Soro-Soro', 'Timbao', 'Tubigan', 'Zapote'
            ],
            'Cabuyao' => [
                'Baclaran', 'Banay-Banay', 'Banlic', 'Barangay Dos', 'Barangay Tres', 'Barangay Uno', 'Bigaa', 'Butong', 
                'Diezmo', 'Gulod', 'Mamatid', 'Marinig', 'Niugan', 'Pulo', 'Sala'
            ],
            'Calamba' => [
                'Bagong Kalsada', 'Banadero', 'Banlic', 'Barandal', 'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Barangay V', 'Barangay VI', 'Barangay VII', 'Batino', 'Bubuyan', 'Bucal', 'Bunggo', 'Burol', 'Camaligan', 'Canlubang', 'Halang', 'Hornalan', 'Kay-Anlog', 'La Mesa', 'Laguerta', 'Lawa', 'Lecheria', 'Lingga', 'Looc', 'Mabato', 
                'Majada Out', 'Makiling', 'Mapagong', 'Masili', 'Maunong', 'Mayapa', 'Milagrosa', 'Paciano Rizal', 'Palingon', 'Palo-Alto', 'Pansol', 'Parian', 'Prinza', 'Punta', 'Puting Lupa', 'Real', 'Saimsim', 'Sampiruhan', 'San Cristobal', 'San Jose', 'San Juan', 'Sirang Lupa', 
                'Sucol', 'Turbina', 'Ulango', 'Uwisan'
            ],
            'Calauan' => [
                'Balayhangin', 'Bangyas', 'Dayap', 'Hanggan', 'Imok', 'Kanluran', 'Lamot I', 'Lamot II', 'Limao', 'Mabacan', 'Masiit', 'Paliparan', 
                'Perez', 'Prinza', 'San Isidro', 'Santo Tomas', 'Silangan'
            ],
            'Cavinti' => [
                'Anglas', 'Bangco', 'Bukal', 'Bulajo', 'Cansuso', 
                'Duhat', 'Inao-Awan', 'Kanluran Tagaongan', 'Labayo', 'Layasin', 'Layug', 'Mahipon', 'Paowin', 'Poblacion', 'Silangan Talaongan', 'Sisilmin', 'Sumucab', 'Tibatib', 'Udia'
            ],
            'Famy' => [
                'Asana', 'Bagong Sigsigan', 'Bagong Pag-Asa', 'Balitoc', 'Banaba', 'Batuhan', 'Bulihan', 'Caballero', 'Calumpang', 'Cuebang Bato', 'Damayan', 'Kapatalan', 'Kataypuanan', 'Liyang', 'Maate', 'Magdalo', 'Mayatba', 'Minayutan', 'Salangbato', 'Tunhac'
            ],
            'Kalayaan' => [
                'Longos', 'San Antonio', 'San Juan'
            ],
            'Liliw' => [
                'Bagong Anyo', 'Bayate', 'Bongkol', 'Bubukal', 'Cabuyao', 'Calumpang', 'Culoy', 'Dagatan', 'Daniw', 'Dita', 'Ibabang Palina', 
                'Ibabang San Roque', 'Ibabang Sungi', 'Ibabang Taykin', 'Ilayang Palina', 'Ilayang San Roque', 'Ilayang Sungi', 'Ilayang Taykin', 'Kanlurang Bukal', 'Laguan', 'Luquin', 'Malabo-Kalantukan', 'Masikap', 'Maslun', 'Mojon','Novaliches', 'Oples', 'Pag-Asa', 'Palayan', 'Risal', 'San Isidro', 'Silangang Bukal', 'Tuy-Baanan'
            ],
            'Los Banos' => [
                'Anos', 'Bagong Silang', 'Bambang', 'Batong Malake', 'Baybayin', 'Bayog', 'Lalakay', 'Maahas', 'Malinta', 
                'Mayondon', 'Putho-Tuntungin', 'San Antonio', 'Tadlak', 'Timugan'
            ],
            'Luisiana' => [
                'Barangay Zone I', 'Barangay Zone II', 'Barangay Zone III', 'Barangay Zone IV', 'Barangay Zone V', 
                'Barangay Zone VI', 'Barangay Zone VII', 'Barangay Zone VIII', 'De La Paz', 'San Antonio', 'San Buenaventura', 'San Diego', 'San Isidro', 'San Jose', 'San Juan', 'San Luis', 'San Pablo', 'San Pedro', 'San Rafael', 'San Roque', 'San Salvador', 'Santo Domingo', 'Santo Tomas'        
            ],
            'Lumban' => [
                'Bagong Silang', 'Balimbingan', 'Balubad', 'Caliraya', 'Conception', 'Lewin', 'Maracta', 'Maytalang I', 
                'Maytalang II', 'Primera Parang', 'Primera Pulo', 'Salac', 'Santo Nino', 'Segunda Parang', 'Segunda Pulo', 'Wawa'
            ],
            'Mabitac' => [
                'Amuyong', 'Bayanihan', 'Lambac', 'Libis ng Nayon', 'Lucong', 'Maligaya', 'Masikap', 'Matalatala', 
                'Nanguma', 'Numero', 'Paagahan', 'Pag-asa', 'San Antonio', 'San Miguel', 'Sinagtala'
            ],
            'Magdalena' => [
                'Alipit', 'Baanan', 'Balanac', 'Bucal', 'Buenavista', 'Bungkol', 'Buo', 'Burlungan', 'Cigaras', 'Halayhayin', 'Ibabang Atingay', 'Ibabang Butnong', 'Ilayang Atingay', 'Ilayang Butnong', 'Ilog', 'Malaking Ambling', 'Malinao', 'Maravilla', 'Munting Ambling', 'Poblacion', 'Sabang', 'Salasad', 'Tanawan', 'Tipunan'
            ],
            'Majayjay' => [
                'Amonoy', 'Bakia', 'Balanac', 'Balayong', 'Banilad', 'Banti', 'Bitaoy', 'Botocan', 'Bukal', 'Burgos', 'Burol', 'Coralao', 'Gagalot', 'Ibabang Banga', 'Ibabang Bayucain', 'Ilayang Banga', 'Ilayang Bayucain', 'Isabang', 'Malinao', 'May-it', 'Munting Kawayan', 'Olla', 'Oobi', 'Origuel', 'Panalaban', 'Pangil', 'Panglan', 'Piit', 'Pook', 'Rizal', 'San Francisco', 'San Isidro', 'San Miguel', 'San Roque', 'Santa Catalina', 'Suba', 'Talortor', 'Tanawan', 'Taytay', 'Villa Nogales'
            ],
            'Nagcarlan' => [
                'Abo', 'Alibungbungan', 'Alumbrado', 'Balayong', 'Balimbing', 'Balinacon', 'Bambang', 'Banago', 'Banca-banca', 'Bangcuro', 'Banilad', 'Bayaquitos', 'Buboy', 'Buenavista', 'Buhanginan', 'Bukal', 'Bunga', 'Cabuyew', 'Calumpang', 'Kanluran Kabubuhayan', 'Kanluran Lazaan', 'Labangan', 'Lagulo', 'Lawaguin', 'Maiit', 'Malaya', 'Malinao', 'Manaol', 'Maravilla', 'Nagcalbang', 'Oples', 'Palayan', 'Palina', 'Poblacion I', 'Poblacion II', 'Poblacion III', 'Sabang', 'San Francisco', 'Santa Lucia', 'Sibulan', 'Silangan Ilaya', 'Silangan Kabubuhayan', 'Silangan Lazaan', 'Silangan Napapatid', 'Sinipian', 'Sulsuguin', 'Talahib', 'Talangan', 'Taytay', 'Tipacan', 'Wakat', 'Yukos'
            ],
            'Paete' => [
                'Anibong', 'Barangay I', 'Barangay II', 'Biñan', 'Buboy', 'Cabanbanan', 'Calusiche', 'Dingin', 'Lambac', 'Layugan', 'Magdapio', 'Maulawin', 'Pinagsanjan', 'Sabang', 'Sampaloc', 'San Isidro'
            ],
            'Pagsanjan' => [
                'Anibong', 'Barangay I', 'Barangay II', 'Biñan', 'Buboy', 'Cabanbanan', 'Calusiche', 'Dingin', 'Lambac', 'Layugan', 'Magdapio', 'Maulawin', 'Pinagsanjan', 'Sabang', 'Sampaloc', 'San Isidro'
            ],
            'Pakil' => [
                'Banilan', 'Baño', 'Burgos', 'Casa Real', 'Casinsin', 'Dorado', 'Gonzales', 'Kabulusan', 'Matikiw', 'Rizal', 'Saray', 'Taft', 'Tavera'
            ],
            'Pangil' => [
                'Balian', 'Dambo', 'Galalan', 'Isla', 'Mabato-Azufre', 'Natividad', 'San Jose', 'Sulib'
            ],
            'Pila' => [
                'Aplaya', 'Bagong Pook', 'Bukal', 'Bulilan Norte', 'Bulilan Sur', 'Concepcion', 'Labuin', 'Linga', 'Masico', 'Mojon', 'Pansol', 'Pinagbayanan', 'San Antonio', 'San Miguel', 'Santa Clara Norte', 'Santa Clara Sur', 'Tubuan'
            ],
            'Rizal' => [
                'Antipolo', 'East Poblacion', 'Entablado', 'Laguan', 'Paule 1', 'Paule 2', 'Pook', 'Tala', 'Talaga', 'Tuy', 'West Poblacion'
            ],
            'San Pablo' => [
                'Atisan', 'Bagong Bayan II-A', 'Bagong Pook VI-C', 'Barangay I-A', 'Barangay I-B', 'Barangay II-A', 'Barangay II-B', 'Barangay II-C', 'Barangay II-D', 'Barangay II-E', 'Barangay II-F', 'Barangay III-A', 'Barangay III-B', 'Barangay III-C', 'Barangay III-D', 'Barangay III-E', 'Barangay III-F', 'Barangay IV-A', 'Barangay IV-B', 'Barangay IV-C', 'Barangay V-A', 'Barangay V-B', 'Barangay V-C', 'Barangay V-D', 'Barangay VI-A', 'Barangay VI-B', 'Barangay VI-D', 'Barangay VI-E', 'Barangay VII-A', 'Barangay VII-B', 'Barangay VII-C', 'Barangay VII-D', 'Barangay VII-E', 'Bautista', 'Concepcion', 'Del Remedio', 'Dolores', 'San Antonio 1', 'San Antonio 2', 'San Bartolome', 'San Buenaventura', 'San Crispin', 'San Cristobal', 'San Diego', 'San Francisco', 'San Gabriel', 'San Gregorio', 'San Ignacio', 'San Isidro', 'San Joaquin', 'San Jose', 'San Juan', 'San Lorenzo', 'San Lucas 1', 'San Lucas 2', 'San Marcos', 'San Mateo', 'San Miguel', 'San Nicolas', 'San Pedro', 'San Rafael', 'San Roque', 'San Vicente', 'Santa Ana', 'Santa Catalina', 'Santa Cruz', 'Santa Elena', 'Santa Felomina', 'Santa Isabel', 'Santa Maria', 'Santa Maria Magdalena', 'Santa Monica', 'Santa Veronica', 'Santiago I', 'Santiago II', 'Santisimo Rosario', 'Santo Angel', 'Santo Cristo', 'Santo Niño', 'Soledad'
            ],
            'San Pedro' => [
               'Bagong Silang', 'Calendola', 'Chrysanthemum', 'Cuyab', 'Estrella', 'Fatima', 'G.S.I.S.', 'Landayan', 'Langgam', 'Laram', 'Magsaysay', 'Maharlika', 'Narra', 'Nueva', 'Pacita 1', 'Pacita 2', 'Poblacion', 'Riverside', 'Rosario', 'Sampaguita Village', 'San Antonio', 'San Lorenzo Ruiz', 'San Roque', 'San Vicente', 'Santo Niño', 'United Bayanihan', 'United Better Living'
            ],
            'Santa Cruz' => [
               'Alipit', 'Bagumbayan', 'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Barangay V', 'Bubukal', 'Calios', 'Duhat', 'Gatid', 'Jasaan', 'Labuin', 'Malinao', 'Oogong', 'Pagsawitan', 'Palasan', 'Patimbao', 'San Jose', 'San Juan', 'San Pablo Norte', 'San Pablo Sur', 'Santisima Cruz', 'Santo Angel Central', 'Santo Angel Norte', 'Santo Angel Sur'
            ],
            'Santa Maria' => [
                'Adia', 'Bagong Pook', 'Bagumbayan', 'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Bubukal', 'Cabooan', 'Calangay', 'Cambuja', 'Coralan', 'Cueva', 'Inayapan', 'Jose Laurel, Sr.', 'Jose Rizal', 'Kayhakat', 'Macasipac', 'Masinao', 'Mataling-ting', 'Pao-o', 'Parang ng Buho', 'Santiago', 'Talangka', 'Tungkod'
            ],
            'Santa Rosa' => [
                'Aplaya', 'Balibago', 'Caingin', 'Dila', 'Dita', 'Don Jose', 'Ibaba', 'Kanluran', 'Labas', 'Macabling', 'Malitlit', 'Malusak', 'Market Area', 'Pook', 'Pulong Santa Cruz', 'Santo Domingo', 'Sinalhan', 'Tagapo'
            ],
            'Siniloan' => [
                'Acevida', 'Bagong Pag-asa', 'Bagumbarangay', 'Buhay', 'G. Redor', 'Gen. Luna', 'Halayhayin', 'J. Rizal', 'Kapatalan', 'Laguio', 'Liyang', 'Llavac', 'Macatad', 'Magsaysay', 'Mayatba', 'Mendiola', 'P. Burgos', 'Pandeno', 'Salubungan', 'Wawa'
            ],
            'Victoria' => [
                'Banca-banca', 'Daniw', 'Masapang', 'Nanhaya', 'Pagalangan', 'San Benito', 'San Felix', 'San Francisco', 'San Roque'
            ],
        ];
       return view('superadmin.manageadmins', compact('admins', 'locations'));
}

    public function assignBranch(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $admin = User::find($request->admin_id);
        $admin->branch_id = $request->branch_id;
        $admin->save();

      

        return response()->json(['success' => true, 'message' => 'Branch assigned successfully.']);
    }

public function setAsSuperAdmin(Request $request)
{
    $request->validate([
        'admin_id' => 'required|exists:users,id',
    ]);

    $currentSuperAdmin = auth()->user();
    $newSuperAdmin = User::findOrFail($request->admin_id);

    if ($currentSuperAdmin->role !== 'Super Admin') {
        return response()->json([
            'success' => false,
            'message' => 'Only Super Admin can perform this action.'
        ], 403);
    }

    if ($currentSuperAdmin->id === $newSuperAdmin->id) {
        return response()->json([
            'success' => false,
            'message' => 'You are already the Super Admin.'
        ], 400);
    }

    \DB::transaction(function () use ($currentSuperAdmin, $newSuperAdmin) {
      
        $currentSuperAdmin->update([
            'role' => 'Admin',
            'branch_id' => null, 
        ]);

        
        $newSuperAdmin->update([
            'role' => 'Super Admin',
            'branch_id' => null,
        ]);
    });

 
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return response()->json([
        'success' => true,
        'logout' => true,
        'message' => "{$newSuperAdmin->name} is now the Super Admin. You have been logged out."
    ]);
}




    public function archive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $request->ids)->update(['status' => 'Archived']);

        return response()->json(['success' => true]);
    }

    public function unarchive(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:users,id',
    ]);

    
    User::whereIn('id', $request->ids)
        ->update([
            'status' => 'Active',
            'skip_attendance_check' => 1,
        ]);

    return response()->json(['success' => true]);
}


    public function superAdminDashboard()
    {
        $totalBranches = Branch::count();
        $totalAdmins   = User::where('role', 'Admin')->count();
        $totalMembers  = User::where('role', 'Member')->count();

        $branchTypeCounts = Branch::selectRaw('LOWER(branch_type) as branch_type, COUNT(*) as count')
            ->groupByRaw('LOWER(branch_type)')
            ->pluck('count', 'branch_type');

        return view('superadmin.sadashboard', [
            'totalBranches'     => $totalBranches,
            'totalAdmins'       => $totalAdmins,
            'totalMembers'      => $totalMembers,
            'branchTypeCounts'  => $branchTypeCounts,
        ]);
    }

    public function getAdminDetails($id)
    {
        $admin = User::with('branch')->findOrFail($id);

        return response()->json([
            'id' => $admin->id,
            'first_name' => $admin->first_name,
            'middle_name' => $admin->middle_name,
            'last_name' => $admin->last_name,
            'gender' => $admin->gender,
            'contact_number' => $admin->contact_number,
            'email' => $admin->email,
            'address' => $admin->address,
            'branch' => $admin->branch ? $admin->branch->name : 'N/A',
            'birthdate' => $admin->birthdate,
            'baptism_date' => $admin->baptism_date,
            'salvation_date' => $admin->salvation_date,
            'status' => $admin->status,
        ]);
    }

    public function showUploadForm() {
    return view('admin.upload'); // Blade form for admin
}




}
