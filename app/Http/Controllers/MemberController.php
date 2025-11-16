<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountInactiveMail;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\SundayServiceAttendance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; 

class MemberController extends Controller
{
    public function index()
    {
        $admin = Auth::user();

        // Only fetch members belonging to the admin's branch
        $members = User::where('role', 'Member')
                       ->where('branch_id', $admin->branch_id)
                       ->get();

        return view('admin.addmember', compact('members'));
    }

    public function showMembers()
    {
        $admin = Auth::user();

        // Cities and their barangays
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

        // Fetch members and pass locations to the view
        $members = User::where('role', 'Member')
            ->where('branch_id', $admin->branch_id)
            ->select('id', 'first_name', 'middle_name', 'last_name', 'gender', 'contact_number', 'address', 'birthdate', 'profile_image', 'status')
            ->paginate(10);

        return view('admin.memberdetails', compact('members', 'locations'));
    }

    public function toggleStatus($id)
{
    try {
        $member = User::findOrFail($id);

        // Toggle Active/Inactive
        $member->status = $member->status === 'Active' ? 'Inactive' : 'Active';
        $member->save();

        // If user became Inactive, send notification email
        if ($member->status === 'Inactive' && !empty($member->email)) {
            try {
                Mail::to($member->email)->send(new AccountInactiveMail($member));
            } catch (\Exception $mailEx) {
                \Log::error("Failed to send inactive email: " . $mailEx->getMessage());
            }
        }

        return response()->json(['success' => true, 'status' => $member->status]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}public function store(Request $request, EmailVerificationService $emailVerifier)
{
    \Log::info("📥 Member store request received", [
        'admin_id' => Auth::id(),
        'email'    => $request->email,
        'input'    => $request->except(['password']),
    ]);

    // Validate Request
    $validator = Validator::make($request->all(), [
        'first_name'     => 'required|string|max:255',
        'middle_name'    => 'nullable|string|max:255',
        'last_name'      => 'required|string|max:255',
        'gender'         => 'required|in:Male,Female',
        'mobile_number'  => 'required|digits:11',
        'email'          => 'required|email|unique:users,email',
        'birthdate'      => 'required|date|before_or_equal:today',
        'baptism_date'   => 'nullable|date|before_or_equal:today',
        'salvation_date' => 'nullable|date|before_or_equal:today',
        'city'           => 'required|string',
        'barangay'       => 'required|string',
        'address'        => 'required|string',
    ]);

    if ($validator->fails()) {
        \Log::warning("⚠️ Validation failed for member store", [
            'errors' => $validator->errors()->toArray(),
        ]);
        return response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    // Internal Email Verification
    try {
        $result = $emailVerifier->verify($request->email);
        if (!$result) {
            \Log::warning("❌ Email verification failed", ['email' => $request->email]);
            return response()->json([
                'message' => 'Email verification failed. Please use a valid email address.',
            ], 422);
        }
    } catch (\Throwable $e) {
        \Log::error("❌ Email verification service error", [
            'email' => $request->email,
            'exception' => $e->getMessage(),
        ]);
        return response()->json(['message' => 'Email verification service unavailable.'], 503);
    }

    // Generate username and password
    $firstInitial  = strtolower(substr($request->first_name, 0, 1));
    $middleInitial = strtolower($request->middle_name ? substr($request->middle_name, 0, 1) : '');
    $lastName      = strtolower(str_replace(' ', '', $request->last_name));
    $username      = $firstInitial . $middleInitial . $lastName;

    $originalUsername = $username;
    $counter = 1;
    while (User::where('username', $username)->exists()) {
        $username = $originalUsername . $counter;
        $counter++;
    }

    \Log::info("👤 Generated username", ['username' => $username, 'email' => $request->email]);

    $password = Str::random(10);

    // Save member as Pending
    DB::beginTransaction();
    try {
        $admin = Auth::user();
        $member = User::create([
            'branch_id'      => $admin->branch_id,
            'role'           => 'Member',
            'first_name'     => $request->first_name,
            'middle_name'    => $request->middle_name,
            'last_name'      => $request->last_name,
            'gender'         => $request->gender,
            'contact_number' => $request->mobile_number,
            'email'          => $request->email,
            'username'       => $username,
            'password'       => Hash::make($password),
            'address'        => $request->address,
            'birthdate'      => $request->birthdate,
            'baptism_date'   => $request->baptism_date,
            'salvation_date' => $request->salvation_date,
            'status'         => 'Pending',
        ]);

        \Log::info("✅ Member created", ['member_id' => $member->id]);

        // Create verification token
        $token = Str::random(60);
        DB::table('email_verifications')->insert([
            'user_id'       => $member->id,
            'token'         => $token,
            'plain_password'=> $password, // dev only
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Send verify email
        $verifyUrl = url("/verify-email/{$token}");
        try {
            Mail::to($member->email)->send(new \App\Mail\VerifyEmailMail($member, $verifyUrl));
            \Log::info("📧 Verification email sent", ['email' => $member->email]);
        } catch (\Throwable $mailEx) {
            \Log::error("❌ Failed to send verification email", [
                'email' => $member->email,
                'exception' => $mailEx->getMessage(),
            ]);
            DB::rollBack();
            return response()->json(['message' => 'Failed to send verification email.'], 500);
        }

        DB::commit();
        return response()->json(['message' => 'Member added successfully. Verification email sent.', 'member' => $member]);
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error("❌ Member store fatal error", [
            'exception' => $e->getMessage(),
            'trace'     => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => 'Server error. Please contact support.'], 500);
    }
}


    public function delete(Request $request)
    {
        // Check if the logged-in user is an Admin
        $admin = Auth::user();
        if ($admin->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Update the status of the selected members to "Archived"
        try {
            User::whereIn('id', $request->ids)->update(['status' => 'Archived']);
            return response()->json(['success' => true, 'message' => 'Members have been archived.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getMemberDetails($id)
    {
        $member = User::findOrFail($id);

          
    // Only count missed events for this member's branch and after they joined
    $missedEventsCount = SundayServiceAttendance::where('member_id', $member->id)
        ->where('branch_id', $member->branch_id)
        ->where('status', 'Missed')
        ->where('service_date', '>=', $member->created_at->toDateString())
        ->count();

        return response()->json([
            'id' => $member->id,
            'first_name' => $member->first_name,
            'middle_name' => $member->middle_name,
            'last_name' => $member->last_name,
            'gender' => $member->gender,
            'contact_number' => $member->contact_number,
            'email' => $member->email,
            'address' => $member->address,
            'birthdate' => $member->birthdate,
            'baptism_date' => $member->baptism_date,
            'salvation_date' => $member->salvation_date,
            'status' => $member->status,
             'missed_events' => $missedEventsCount,
        ]);
    }
    public function membersByBranch(Request $request)
    {
        $admin = Auth::user();
    
        if (!$admin || !$admin->branch_id) {
            return response()->json(['error' => 'Unauthorized or branch not set'], 403);
        }
    
        // Fetch members based on the logged-in admin's branch_id
        $members = User::where('branch_id', $admin->branch_id)
                       ->where('role', 'Member')
                       ->get(['id', 'first_name', 'last_name']); // Return id, first_name, and last_name
    
        return response()->json($members);
    }


}
