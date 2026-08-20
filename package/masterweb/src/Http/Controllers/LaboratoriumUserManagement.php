<?php

namespace Smt\Masterweb\Http\Controllers;

use Illuminate\Http\Request;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\SatuSehat;
use Smt\Masterweb\Models\SatuSehatPractitioner;
use Ramsey\Uuid\Uuid;
use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Smt\Masterweb\Exports\UsersExport;

use Maatwebsite\Excel\Facades\Excel;


class LaboratoriumUserManagement extends Controller
{
  /**
   * Get column name in ms_verification_activities based on lab code
   */
  private function getLabColumnName($labCode)
  {
    $mapping = [
      'MBI' => 'mikro',
      'KIM' => 'kimia',
      'KLI' => 'klinik',
    ];
    return $mapping[$labCode] ?? null;
  }

  /**
   * Check if petugas is for klinik or pendaftaran (register)
   */
  private function isKlinikOrPendaftaran($labId)
  {
    // Pendaftaran: lab_id is null
    if ($labId === null) {
      return true;
    }

    // Klinik: check if any lab_id has kode_laboratorium = 'KLI'
    $labIdsArray = is_array($labId) ? $labId : [$labId];
    foreach ($labIdsArray as $id) {
      $lab = Laboratorium::find($id);
      if ($lab && $lab->kode_laboratorium === 'KLI') {
        return true;
      }
    }

    return false;
  }

  /**
   * Fetch code_satu_sehat_practitioner and name from SatuSehat API
   * Step 1: Search by NIK to get code (id)
   * Step 2: Get Practitioner by ID to get full name (not censored)
   * Returns array with 'code' and 'name' keys, or null if not found
   */
  private function fetchSatuSehatPractitionerCode($nik, $nama)
  {
    // Only fetch if VERSION_SATUSEHAT = prd
    if (config('services.satu_sehat.version') !== 'prd') {
      Log::info('SatuSehat: VERSION_SATUSEHAT is not prd, skipping fetch', ['version' => config('services.satu_sehat.version')]);
      return null;
    }

    // Check if NIK is provided
    if (empty($nik)) {
      Log::warning('SatuSehat: NIK is empty, cannot fetch practitioner code', ['nama' => $nama]);
      return null;
    }

    try {
      // Get token from ms_satusehat_setting
      $satuSehat = SatuSehat::first();
      if (!$satuSehat || empty($satuSehat->token)) {
        Log::error('SatuSehat: Token not found in ms_satusehat_setting');
        return null;
      }

      // Get base URL from env
      $baseUrl = config('services.satu_sehat.base_uri');
      if (empty($baseUrl)) {
        Log::error('SatuSehat: BASE_URL_SATUSEHAT not found in env');
        return null;
      }

      $client = new Client([
        'headers' => [
          'Authorization' => 'Bearer ' . $satuSehat->token,
          'Accept' => 'application/json',
        ],
        'timeout' => 30,
      ]);

      // Step 1: Search by NIK to get code (id)
      $identifier = 'https://fhir.kemkes.go.id/id/nik|' . $nik;
      $searchUrl = rtrim($baseUrl, '/') . '/Practitioner?identifier=' . urlencode($identifier);

      Log::info('SatuSehat: Searching practitioner by NIK', ['nik' => $nik, 'url' => $searchUrl]);

      $searchResponse = $client->request('GET', $searchUrl);

      if ($searchResponse->getStatusCode() !== 200) {
        Log::error('SatuSehat: Search by NIK failed', [
          'status_code' => $searchResponse->getStatusCode(),
          'nik' => $nik,
          'nama' => $nama
        ]);
        return null;
      }

      $searchBody = json_decode($searchResponse->getBody(), true);

      // Check if entry exists and has data
      if (!isset($searchBody['entry']) || !is_array($searchBody['entry']) || count($searchBody['entry']) === 0) {
        Log::warning('SatuSehat: No entry found in search response', ['nik' => $nik, 'nama' => $nama]);
        return null;
      }

      $resource = $searchBody['entry'][0]['resource'] ?? null;
      
      if (!$resource || !isset($resource['id'])) {
        Log::warning('SatuSehat: No ID found in search response', ['nik' => $nik, 'nama' => $nama]);
        return null;
      }

      $practitionerCode = $resource['id'];
      Log::info('SatuSehat: Practitioner code found from NIK search', [
        'nik' => $nik,
        'code' => $practitionerCode
      ]);

      // Step 2: Get Practitioner by ID to get full name (not censored)
      $getUrl = rtrim($baseUrl, '/') . '/Practitioner/' . $practitionerCode;

      Log::info('SatuSehat: Fetching practitioner by ID for full name', ['code' => $practitionerCode, 'url' => $getUrl]);

      $getResponse = $client->request('GET', $getUrl);

      if ($getResponse->getStatusCode() !== 200) {
        Log::error('SatuSehat: Get by ID failed', [
          'status_code' => $getResponse->getStatusCode(),
          'code' => $practitionerCode,
          'nik' => $nik
        ]);
        // Still return code even if get by ID fails
        return [
          'code' => $practitionerCode,
          'name' => $nama // Fallback to local name
        ];
      }

      $getBody = json_decode($getResponse->getBody(), true);

      // Get name from API response (from name[0].text) - this should be full name, not censored
      $apiName = $getBody['name'][0]['text'] ?? '';
      $apiNameNormalized = $this->normalizeName($apiName);
      
      // Use API name if available, otherwise use local name
      $finalName = !empty($apiNameNormalized) ? $apiNameNormalized : $this->normalizeName($nama);

      Log::info('SatuSehat: Practitioner code and name fetched successfully', [
        'nik' => $nik,
        'nama' => $nama,
        'code' => $practitionerCode,
        'api_name' => $apiName,
        'api_name_normalized' => $apiNameNormalized,
        'final_name' => $finalName
      ]);

      return [
        'code' => $practitionerCode,
        'name' => $finalName
      ];

    } catch (RequestException $e) {
      Log::error('SatuSehat: API request exception', [
        'message' => $e->getMessage(),
        'nik' => $nik,
        'nama' => $nama
      ]);
      return null;
    } catch (\Exception $e) {
      Log::error('SatuSehat: Unexpected error', [
        'message' => $e->getMessage(),
        'nik' => $nik,
        'nama' => $nama
      ]);
      return null;
    }
  }

  /**
   * Sync petugas with SatuSehat Practitioner
   */
  private function syncSatuSehatPractitioner($petugas)
  {
    // Only sync for klinik or pendaftaran
    if (!$this->isKlinikOrPendaftaran($petugas->lab_id)) {
      Log::info('SatuSehat: Petugas is not klinik or pendaftaran, skipping sync', [
        'nama' => $petugas->nama,
        'lab_id' => $petugas->lab_id
      ]);
      return;
    }

    // Check if code already exists
    if (!empty($petugas->code_satu_sehat_practitioner)) {
      Log::info('SatuSehat: Petugas already has code, skipping fetch', [
        'nama' => $petugas->nama,
        'code' => $petugas->code_satu_sehat_practitioner
      ]);
      return;
    }

    // Fetch code and name from API
    $practitionerData = $this->fetchSatuSehatPractitionerCode($petugas->nik, $petugas->nama);
    
    if ($practitionerData && isset($practitionerData['code'])) {
      $code = $practitionerData['code'];
      $apiName = $practitionerData['name'] ?? $petugas->nama; // Use API name if available, fallback to petugas nama
      
      // Update petugas with code
      $petugas->code_satu_sehat_practitioner = $code;
      $petugas->save();

      // Check if practitioner exists in ms_satusehat_practitioner
      $practitioner = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $code)->first();
      
      if (!$practitioner) {
        // Create new practitioner record with name from API
        $practitioner = new SatuSehatPractitioner();
        $practitioner->name_petugas = $petugas->nama; // Use petugas nama
        $practitioner->name_satu_sehat_practitioner = $apiName; // Use name from API response
        $practitioner->code_satu_sehat_practitioner = $code;
        $practitioner->save();

        Log::info('SatuSehat: Created new practitioner record', [
          'nama_petugas' => $petugas->nama,
          'api_name' => $apiName,
          'code' => $code
        ]);
      } else {
        // Update name_satu_sehat_practitioner with API name if different (prioritize API name)
        if ($practitioner->name_satu_sehat_practitioner !== $apiName) {
          $oldName = $practitioner->name_satu_sehat_practitioner;
          $practitioner->name_satu_sehat_practitioner = $apiName; // Use name from API response
          $practitioner->save();

          Log::info('SatuSehat: Updated practitioner name from API', [
            'old_name' => $oldName,
            'new_name' => $apiName,
            'code' => $code
          ]);
        }
      }
    }
  }

  /**
   * Normalize name: replace comma with space and clean up multiple spaces
   */
  private function normalizeName($name)
  {
    // Replace comma with space, then clean up multiple spaces
    $normalized = preg_replace('/\s+/', ' ', str_replace(',', ' ', $name));
    return trim($normalized);
  }

  /**
   * Parse names from comma-separated string, handling names that contain commas
   * Format: "Name1, Name2, Name3" where separator is ", " (comma + space)
   */
  private function parseNames($namesString)
  {
    if (empty($namesString) || $namesString === '-' || $namesString === 'NULL') {
      return [];
    }

    // Split by ", " (comma + space) which is the standard separator
    $parts = explode(', ', $namesString);
    
    // Normalize each name (replace comma with space, clean multiple spaces)
    $names = [];
    foreach ($parts as $part) {
      $normalized = $this->normalizeName($part);
      if (!empty($normalized)) {
        $names[] = $normalized;
      }
    }
    
    return array_values($names);
  }

  /**
   * Add name to verification activities
   * Supports multiple lab_id (array) and multiple roleIds
   */
  private function addToVerificationActivities($petugasName, $labId, $roleIds)
  {
    if (!$roleIds || empty($roleIds)) {
      Log::info('addToVerificationActivities: Role IDs is empty', ['roleIds' => $roleIds]);
      return;
    }

    // Handle multiple lab_id (array) or single lab_id
    $labIdsArray = [];
    if ($labId === null) {
      // Non-lab (pendaftaran, kepala lab, keuangan) - use register column
      $labIdsArray = [null];
    } else {
      $labIdsArray = is_array($labId) ? $labId : [$labId];
    }

    $roleIdsArray = is_array($roleIds) ? $roleIds : [$roleIds];
    
    Log::info('addToVerificationActivities: Processing', [
      'petugasName' => $petugasName,
      'labIds' => $labIdsArray,
      'roleIds' => $roleIdsArray
    ]);
    
    // Process each lab_id
    foreach ($labIdsArray as $currentLabId) {
      $columnName = 'register'; // Default untuk non-lab
      
      if ($currentLabId) {
        $lab = Laboratorium::find($currentLabId);
        if ($lab) {
          $columnName = $this->getLabColumnName($lab->kode_laboratorium);
          if (!$columnName) {
            Log::warning('addToVerificationActivities: Column name not found for lab', ['labId' => $currentLabId, 'kode' => $lab->kode_laboratorium]);
            continue;
          }
        } else {
          Log::warning('addToVerificationActivities: Lab not found', ['labId' => $currentLabId]);
          continue;
        }
      }

      // Process each role for this lab
      foreach ($roleIdsArray as $roleId) {
        $activity = VerificationActivity::find($roleId);
        if ($activity) {
          $existingNames = $activity->$columnName;
          // Parse names using ", " (comma + space) as separator to handle names with commas
          $namesArray = $existingNames ? $this->parseNames($existingNames) : [];
          
          // Normalize petugasName for comparison
          $petugasNameNormalized = $this->normalizeName($petugasName);
          
          // Add name if not already exists (case-insensitive comparison)
          $nameExists = false;
          foreach ($namesArray as $existingName) {
            // Normalize existing name for comparison
            $existingNameNormalized = $this->normalizeName($existingName);
            if (strtolower($existingNameNormalized) === strtolower($petugasNameNormalized)) {
              $nameExists = true;
              break;
            }
          }
          
          if (!$nameExists) {
            // Use normalized name (comma replaced with space, multiple spaces cleaned)
            $namesArray[] = $petugasNameNormalized;
            // Use ", " as separator (comma + space) to maintain format consistency
            $activity->$columnName = implode(', ', $namesArray);
            
            Log::info('addToVerificationActivities: Updating activity', [
              'roleId' => $roleId,
              'columnName' => $columnName,
              'newValue' => $activity->$columnName
            ]);
            
            $saved = $activity->save();
            
            if (!$saved) {
              Log::error('addToVerificationActivities: Failed to save activity', [
                'roleId' => $roleId,
                'columnName' => $columnName
              ]);
            }
          } else {
            Log::info('addToVerificationActivities: Name already exists', [
              'roleId' => $roleId,
              'petugasName' => $petugasName
            ]);
          }
        } else {
          Log::warning('addToVerificationActivities: Activity not found', ['roleId' => $roleId]);
        }
      }
    }
  }

  /**
   * Remove name from verification activities
   * Supports multiple lab_id (array) and multiple roleIds
   */
  private function removeFromVerificationActivities($petugasName, $labId, $roleIds)
  {
    if (!$roleIds || empty($roleIds)) {
      Log::info('removeFromVerificationActivities: Role IDs is empty', ['roleIds' => $roleIds]);
      return;
    }

    // Handle multiple lab_id (array) or single lab_id
    $labIdsArray = [];
    if ($labId === null) {
      // Non-lab (pendaftaran, kepala lab, keuangan) - use register column
      $labIdsArray = [null];
    } else {
      $labIdsArray = is_array($labId) ? $labId : [$labId];
    }

    $roleIdsArray = is_array($roleIds) ? $roleIds : [$roleIds];
    
    Log::info('removeFromVerificationActivities: Processing', [
      'petugasName' => $petugasName,
      'labIds' => $labIdsArray,
      'roleIds' => $roleIdsArray
    ]);
    
    // Process each lab_id
    foreach ($labIdsArray as $currentLabId) {
      $columnName = 'register'; // Default untuk non-lab
      
      if ($currentLabId) {
        $lab = Laboratorium::find($currentLabId);
        if ($lab) {
          $columnName = $this->getLabColumnName($lab->kode_laboratorium);
          if (!$columnName) {
            Log::warning('removeFromVerificationActivities: Column name not found for lab', ['labId' => $currentLabId, 'kode' => $lab->kode_laboratorium]);
            continue;
          }
        } else {
          Log::warning('removeFromVerificationActivities: Lab not found', ['labId' => $currentLabId]);
          continue;
        }
      }

      // Process each role for this lab
      foreach ($roleIdsArray as $roleId) {
        $activity = VerificationActivity::find($roleId);
        if ($activity) {
          $existingNames = $activity->$columnName;
          if ($existingNames && $existingNames !== '-' && $existingNames !== 'NULL') {
            // Parse names using smart parsing to handle names with commas
            $namesArray = $this->parseNames($existingNames);
            $originalCount = count($namesArray);
            
            Log::info('removeFromVerificationActivities: Before removal', [
              'roleId' => $roleId,
              'columnName' => $columnName,
              'existingNames' => $existingNames,
              'namesArray' => $namesArray,
              'petugasName' => $petugasName
            ]);
            
            // Normalize petugasName for comparison
            $petugasNameNormalized = $this->normalizeName($petugasName);
            $namesArray = array_filter($namesArray, function($name) use ($petugasNameNormalized) {
              // Normalize name and compare case-insensitively
              $nameNormalized = $this->normalizeName($name);
              return strtolower($nameNormalized) !== strtolower($petugasNameNormalized);
            });
            
            $newCount = count($namesArray);
            
            Log::info('removeFromVerificationActivities: After removal', [
              'roleId' => $roleId,
              'originalCount' => $originalCount,
              'newCount' => $newCount,
              'removed' => $originalCount > $newCount
            ]);
            
            $activity->$columnName = !empty($namesArray) ? implode(', ', $namesArray) : null;
            
            Log::info('removeFromVerificationActivities: Updating activity', [
              'roleId' => $roleId,
              'columnName' => $columnName,
              'newValue' => $activity->$columnName
            ]);
            
            $saved = $activity->save();
            
            if (!$saved) {
              Log::error('removeFromVerificationActivities: Failed to save activity', [
                'roleId' => $roleId,
                'columnName' => $columnName
              ]);
            } else {
              Log::info('removeFromVerificationActivities: Successfully saved', [
                'roleId' => $roleId,
                'columnName' => $columnName
              ]);
            }
          } else {
            Log::info('removeFromVerificationActivities: No existing names to remove', [
              'roleId' => $roleId,
              'columnName' => $columnName,
              'existingNames' => $existingNames
            ]);
          }
        } else {
          Log::warning('removeFromVerificationActivities: Activity not found', ['roleId' => $roleId]);
        }
      }
    }
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {

    //get auth user
    $user = Auth()->user();
    $level = $user->privilege->level;


    if ($level == "LAB") {
      $users = User::where('ms_privilege.is_elits', '=', '1') // SOLAB - Pengambil Sample LAB
      ->where('ms_privilege.level', '!=', 'elits-dev')  
      ->join('ms_privilege', function ($join) {
          $join->on('ms_privilege.id', '=', 'ms_users.level')
            ->where('ms_privilege.is_elits', '=', '1')
            ->whereNull('ms_privilege.deleted_at')
            ->whereNull('ms_users.deleted_at');
        })
        ->select('ms_privilege.name as privilege', 'ms_users.*')
        ->get();
    } else if ($level == "elits-dev" || $level == "admin") {
      $users = User::join('ms_privilege', function ($join) {
        $join->on('ms_privilege.id', '=', 'ms_users.level')
          ->where('ms_privilege.is_elits', '=', '1')
          ->whereNull('ms_privilege.deleted_at')
          ->whereNull('ms_users.deleted_at');
      })
        ->select('ms_privilege.name as privilege', 'ms_users.*')
        ->get();
    } else {
      $users = User::join('ms_privilege', function ($join) {
        $join->on('ms_privilege.id', '=', 'ms_users.level')
          ->where('ms_privilege.is_elits', '=', '1')
          ->whereNull('ms_privilege.deleted_at')
          ->whereNull('ms_users.deleted_at');
      })
        ->select('ms_privilege.name as privilege', 'ms_users.*')
        ->get();
    }



    return view('masterweb::module.admin.laboratorium.users.list', compact('user', 'users'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //get auth user

    $user = Auth()->user();
    $level = $user->privilege->level;


    if ($level == "LAB") {
      $user = Auth()->user();
      $privileges = \Smt\Masterweb\Models\Privileges::where('is_elits', '=', '1')
      ->where('ms_privilege.level', '!=', 'elits-dev')  
      ->where('ms_privilege.level', '!=', 'admin')
      ->get();
    } else if ($level == "elits-dev" || $level == "admin") {
      $user = Auth()->user();
      $privileges = \Smt\Masterweb\Models\Privileges::where('is_elits', '=', '1')->get();
    } else {
      return abort(404);
    }
    $laboratories = \Smt\Masterweb\Models\Laboratorium::all();
    $petugasList = \Smt\Masterweb\Models\Petugas::orderBy('nama')->get();
    $verificationActivities = \Smt\Masterweb\Models\VerificationActivity::orderBy('name')->get();
    return view('masterweb::module.admin.laboratorium.users.add', compact('user', 'privileges', 'laboratories', 'petugasList', 'verificationActivities'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    DB::beginTransaction();
    try {
      $user = new User;
      //uuid
      $uuid4 = Uuid::uuid4();

      $user->id = $uuid4->toString();
      $user->name = $request->post('name');
      if ($request->get('level') == "01f62b38-fce5-43bf-9088-9d4a33a496da") {
        $user->root_firebase = $request->post('root_firebase');
      }
      $user->username = $request->post('username');
      $user->email = $request->get('email');
      $user->phone = $request->get('phone');
      $user->level = $request->get('level');
      $user->nip_users = $request->get('nip_users');
      $user->laboratory_users = $request->get('laboratory_users');
      $user->password = Hash::make('elits');

      // Handle petugas connection
      $petugasAction = $request->get('petugas_action', 'none'); // 'existing', 'new', 'none'
      
      if ($petugasAction === 'existing') {
        // Connect to existing petugas
        $idPetugas = $request->get('id_petugas');
        if ($idPetugas) {
          $petugas = Petugas::find($idPetugas);
          if ($petugas) {
            $user->id_petugas = $idPetugas;
          }
        }
      } elseif ($petugasAction === 'new') {
        // Create new petugas - sama seperti AdmPetugasController@store
        $petugas = new Petugas();
        $petugas->nama = $request->post('name');
        $petugas->nip = $request->get('nip_users');
        $petugas->nik = $request->get('nik_petugas');
        $petugas->gelar = $request->get('gelar_petugas');
        $petugas->password = null; // Password BSRE disabled
        
        // Handle lab_id from petugas form (can be multiple)
        $labIdsPetugas = $request->get('lab_id_petugas', []);
        if (!is_array($labIdsPetugas)) {
          $labIdsPetugas = $labIdsPetugas ? [$labIdsPetugas] : [];
        }
        // Filter out NON_LAB and convert to array, or null if empty
        $labIdsPetugas = array_filter($labIdsPetugas, function($id) {
          return $id !== 'NON_LAB' && $id !== null && $id !== '';
        });
        $petugas->lab_id = !empty($labIdsPetugas) ? array_values($labIdsPetugas) : null;
        
        // Handle role from petugas form (can be multiple)
        $rolesPetugas = $request->get('role_petugas', []);
        if (!is_array($rolesPetugas)) {
          $rolesPetugas = $rolesPetugas ? [$rolesPetugas] : [];
        }
        // If no role selected but user is ANALIS, auto-set role 3
        if (empty($rolesPetugas)) {
          $level = $request->get('level');
          $privilege = \Smt\Masterweb\Models\Privileges::find($level);
          if ($privilege && ($privilege->level === 'ANLS' || $privilege->level === 'ALAB')) {
            // Role 3 = Input/Output Hasil Klinik (Analis)
            $rolesPetugas = ['3'];
          }
        }
        $petugas->role = !empty($rolesPetugas) ? array_values($rolesPetugas) : [];
        
        // Handle is_kepala_lab
        $petugas->is_kepala_lab = $request->has('is_kepala_lab_petugas') ? 1 : 0;
        
        $petugas->save();

        // Sync with SatuSehat Practitioner (only for klinik or pendaftaran)
        try {
          $this->syncSatuSehatPractitioner($petugas);
        } catch (\Exception $e) {
          Log::error('SatuSehat: Error syncing practitioner on create', [
            'error' => $e->getMessage(),
            'petugas_id' => $petugas->id_petugas,
            'nama' => $petugas->nama
          ]);
        }

        Log::info('Petugas created from user management', [
          'id' => $petugas->id_petugas,
          'nama' => $petugas->nama,
          'lab_id' => $petugas->lab_id,
          'role' => $petugas->role,
          'role_is_array' => is_array($petugas->role),
          'role_empty' => empty($petugas->role)
        ]);

        // Update verification activities if roles are provided
        // Bisa lab_id ada atau NULL (untuk non-lab seperti pendaftaran, kepala lab, keuangan)
        if (!empty($petugas->role)) {
          try {
            $this->addToVerificationActivities($petugas->nama, $petugas->lab_id, $petugas->role);
          } catch (\Exception $e) {
            Log::error('Error updating verification activities: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Petugas: ' . $petugas->nama . ', Lab ID: ' . ($petugas->lab_id ?? 'NULL') . ', Roles: ' . json_encode($petugas->role));
          }
        } else {
          Log::warning('Petugas created but no roles provided', [
            'nama' => $petugas->nama,
            'lab_id' => $petugas->lab_id
          ]);
        }
        
        $user->id_petugas = $petugas->id_petugas;
      }

      if ($request->hasFile('photo')) {
        $file = $request->file('photo')->store('photo/', 'public');
        $user->photo = basename($file);
      }
      
      $user->save();

      DB::commit();
      return redirect()->route('elits-users.index')->with(['status' => 'User succesfully inserted']);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->withInput()
        ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
    }



    //return redirect()->route('module.admin.users.index')->with('status', 'User succesfully inserted');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //get auth user

    $user = Auth()->user();
    $level = $user->privilege->level;

    $users = User::findOrFail($id);

    if ($level == "LAB") {
      $user = Auth()->user();
      $privileges = \Smt\Masterweb\Models\Privileges::where('is_elits', '=', '1')
      ->where('ms_privilege.level', '!=', 'elits-dev')  
      ->where('ms_privilege.level', '!=', 'admin')// SOLAB - Pengambil Sample LAB
        ->get();
    } else if ($level == "elits-dev" || $level == "admin") {
      $user = Auth()->user();
      $privileges = \Smt\Masterweb\Models\Privileges::where('is_elits', '=', '1')->get();
    } else {
      return abort(404);
    }

    $laboratories = \Smt\Masterweb\Models\Laboratorium::all();
    $petugasList = \Smt\Masterweb\Models\Petugas::orderBy('nama')->get();
    $verificationActivities = \Smt\Masterweb\Models\VerificationActivity::orderBy('name')->get();
    return view('masterweb::module.admin.laboratorium.users.edit', compact('user', 'users', 'privileges', 'id', 'laboratories', 'petugasList', 'verificationActivities'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    DB::beginTransaction();
    try {
      $user = \Smt\Masterweb\Models\User::findOrFail($id);
      $user->name = $request->post('name');
      $user->username = $request->post('username');
      // $user->email = $request->get('email');
      $user->phone = $request->get('phone');

      $user->nip_users = $request->get('nip_users');
      $user->level = $request->get('level');
      $user->laboratory_users = $request->get('laboratory_users');

      // Handle petugas connection
      $petugasAction = $request->get('petugas_action', 'none'); // 'existing', 'new', 'none', 'update'
      
      if ($petugasAction === 'existing') {
        // Connect to existing petugas
        $idPetugas = $request->get('id_petugas');
        if ($idPetugas) {
          $petugas = Petugas::find($idPetugas);
          if ($petugas) {
            $user->id_petugas = $idPetugas;
          }
        } else {
          // Disconnect petugas
          $user->id_petugas = null;
        }
      } elseif ($petugasAction === 'new') {
        // Create new petugas - sama seperti AdmPetugasController@store
        $petugas = new Petugas();
        $petugas->nama = $request->post('name');
        $petugas->nip = $request->get('nip_users');
        $petugas->nik = $request->get('nik_petugas');
        $petugas->gelar = $request->get('gelar_petugas');
        $petugas->password = null; // Password BSRE disabled
        
        // Handle lab_id from petugas form (can be multiple)
        $labIdsPetugas = $request->get('lab_id_petugas', []);
        if (!is_array($labIdsPetugas)) {
          $labIdsPetugas = $labIdsPetugas ? [$labIdsPetugas] : [];
        }
        // Filter out NON_LAB and convert to array, or null if empty
        $labIdsPetugas = array_filter($labIdsPetugas, function($id) {
          return $id !== 'NON_LAB' && $id !== null && $id !== '';
        });
        $petugas->lab_id = !empty($labIdsPetugas) ? array_values($labIdsPetugas) : null;
        
        // Handle role from petugas form (can be multiple)
        $rolesPetugas = $request->get('role_petugas', []);
        if (!is_array($rolesPetugas)) {
          $rolesPetugas = $rolesPetugas ? [$rolesPetugas] : [];
        }
        // If no role selected but user is ANALIS, auto-set role 3
        if (empty($rolesPetugas)) {
          $level = $request->get('level');
          $privilege = \Smt\Masterweb\Models\Privileges::find($level);
          if ($privilege && ($privilege->level === 'ANLS' || $privilege->level === 'ALAB')) {
            // Role 3 = Input/Output Hasil Klinik (Analis)
            $rolesPetugas = ['3'];
          }
        }
        $petugas->role = !empty($rolesPetugas) ? array_values($rolesPetugas) : [];
        
        // Handle is_kepala_lab
        $petugas->is_kepala_lab = $request->has('is_kepala_lab_petugas') ? 1 : 0;
        
        $petugas->save();

        // Sync with SatuSehat Practitioner (only for klinik or pendaftaran)
        try {
          $this->syncSatuSehatPractitioner($petugas);
        } catch (\Exception $e) {
          Log::error('SatuSehat: Error syncing practitioner on create', [
            'error' => $e->getMessage(),
            'petugas_id' => $petugas->id_petugas,
            'nama' => $petugas->nama
          ]);
        }

        Log::info('Petugas created from user management (update)', [
          'id' => $petugas->id_petugas,
          'nama' => $petugas->nama,
          'lab_id' => $petugas->lab_id,
          'role' => $petugas->role,
          'role_is_array' => is_array($petugas->role),
          'role_empty' => empty($petugas->role)
        ]);

        // Update verification activities if roles are provided
        // Bisa lab_id ada atau NULL (untuk non-lab seperti pendaftaran, kepala lab, keuangan)
        if (!empty($petugas->role)) {
          try {
            $this->addToVerificationActivities($petugas->nama, $petugas->lab_id, $petugas->role);
          } catch (\Exception $e) {
            Log::error('Error updating verification activities: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Petugas: ' . $petugas->nama . ', Lab ID: ' . ($petugas->lab_id ?? 'NULL') . ', Roles: ' . json_encode($petugas->role));
          }
        } else {
          Log::warning('Petugas created but no roles provided', [
            'nama' => $petugas->nama,
            'lab_id' => $petugas->lab_id
          ]);
        }
        
        $user->id_petugas = $petugas->id_petugas;
      } elseif ($petugasAction === 'update' && $user->id_petugas) {
        // Update existing petugas - sama seperti AdmPetugasController@update
        $petugas = Petugas::find($user->id_petugas);
        if ($petugas) {
          // Store old values for verification activities update
          $oldLabId = $petugas->lab_id;
          $oldRoles = $petugas->role ?? [];
          $oldNama = $petugas->nama;

          $petugas->nama = $request->post('name');
          $petugas->nip = $request->get('nip_users');
          $petugas->nik = $request->get('nik_petugas');
          $petugas->gelar = $request->get('gelar_petugas');
          
          // Handle lab_id from petugas form (can be multiple)
          $labIdsPetugas = $request->get('lab_id_petugas', []);
          if (!is_array($labIdsPetugas)) {
            $labIdsPetugas = $labIdsPetugas ? [$labIdsPetugas] : [];
          }
          // Filter out NON_LAB and convert to array, or null if empty
          $labIdsPetugas = array_filter($labIdsPetugas, function($id) {
            return $id !== 'NON_LAB' && $id !== null && $id !== '';
          });
          $petugas->lab_id = !empty($labIdsPetugas) ? array_values($labIdsPetugas) : null;
          
          // Handle role from petugas form (can be multiple)
          $rolesPetugas = $request->get('role_petugas', []);
          if (!is_array($rolesPetugas)) {
            $rolesPetugas = $rolesPetugas ? [$rolesPetugas] : [];
          }
          $petugas->role = !empty($rolesPetugas) ? array_values($rolesPetugas) : [];
          
          // Handle is_kepala_lab
          $petugas->is_kepala_lab = $request->has('is_kepala_lab_petugas') ? 1 : 0;
          
          $petugas->save();

          // Sync with SatuSehat Practitioner (only for klinik or pendaftaran)
          // Only sync if lab changed to klinik/pendaftaran or if code is empty
          $labChangedToKlinikOrPendaftaran = $this->isKlinikOrPendaftaran($petugas->lab_id) && 
                                             !$this->isKlinikOrPendaftaran($oldLabId);
          
          if ($labChangedToKlinikOrPendaftaran || (empty($petugas->code_satu_sehat_practitioner) && $this->isKlinikOrPendaftaran($petugas->lab_id))) {
            try {
              $this->syncSatuSehatPractitioner($petugas);
            } catch (\Exception $e) {
              Log::error('SatuSehat: Error syncing practitioner on update', [
                'error' => $e->getMessage(),
                'petugas_id' => $petugas->id_petugas,
                'nama' => $petugas->nama
              ]);
            }
          }

          // Handle verification activities update
          // Normalize arrays for comparison
          $oldLabIdNormalized = is_array($oldLabId) ? $oldLabId : ($oldLabId ? [$oldLabId] : null);
          $newLabIdNormalized = is_array($petugas->lab_id) ? $petugas->lab_id : ($petugas->lab_id ? [$petugas->lab_id] : null);
          
          // Remove old name from old roles/lab if lab or roles changed or name changed
          $labChanged = json_encode($oldLabIdNormalized) !== json_encode($newLabIdNormalized);
          $rolesChanged = json_encode($oldRoles) !== json_encode($petugas->role);
          $nameChanged = $oldNama !== $petugas->nama;
          
          if (!empty($oldRoles) && ($labChanged || $rolesChanged || $nameChanged)) {
            $this->removeFromVerificationActivities($oldNama, $oldLabId, $oldRoles);
          }

          // Add new name to new roles/lab (if provided)
          // Bisa lab_id ada atau NULL (untuk non-lab seperti pendaftaran, kepala lab, keuangan)
          if (!empty($petugas->role)) {
            $this->addToVerificationActivities($petugas->nama, $petugas->lab_id, $petugas->role);
          }
        }
      } elseif ($petugasAction === 'disconnect') {
        // Disconnect petugas
        $user->id_petugas = null;
      }

      if ($request->file('photo')) {
        if ($user->photo && file_exists(storage_path('app/public/photo/' . $user->photo))) {
          unlink(storage_path('app/public/photo/' . $user->photo));
        }
        $file = $request->file('photo')->store('photo/', 'public');
        $user->photo = basename($file);
      }
      
      $user->save();
      
      DB::commit();
      return redirect()->route('elits-users.index', [$id])->with('status', 'User succesfully updated');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->withInput()
        ->with('error', 'Gagal mengupdate data: ' . $e->getMessage());
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $user = \Smt\Masterweb\Models\User::findOrFail($id);
    $user->delete();
    return redirect()->route('elits-users.index', [$id])->with('status', 'User succesfully updated');
  }

  public function reset_password($id)
  {

    $user = \Smt\Masterweb\Models\User::findOrFail($id);
    $user->password = Hash::make('elits');
    $user->save();
    return redirect()->route('elits-users.index', [$id])->with('status', 'User succesfully reset password');
  }

  public function getUsersBySelect2(Request $request)
  {
    $search = $request->search;

    if (isset($request->search)) {
      $data = User::orderby('name', 'asc')
        ->select('id', 'nip_users', 'name')
        ->where('name', 'like', '%' . $search . '%')
        ->orwhere('nip_users', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    } else {
      $data = User::orderby('name', 'asc')
        ->select('id', 'nip_users', 'name')
        ->limit(10)
        ->get();
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id,
        "text" => $item->name,
      );
    }

    return response()->json($response);
  }

  public function getDokterBySelect2(Request $request)
  {
    $search = $request->search;

    if (isset($request->search)) {
      $data = User::orderby('name', 'asc')
        ->select('id', 'nip_users', 'name')
        ->whereHas('getlevel', function ($query) {
          $query->where('level', 'DKTR')
            ->orwhere('id', '706fbb4c-3131-4f61-9946-15b6240297d0');
        })
        ->where('name', 'like', '%' . $search . '%')
        ->orwhere('nip_users', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    } else {
      $data = User::orderby('name', 'asc')
        ->select('id', 'nip_users', 'name')
        ->whereHas('getlevel', function ($query) {
          $query->where('level', 'DKTR')
            ->orwhere('id', '706fbb4c-3131-4f61-9946-15b6240297d0');
        })
        ->limit(10)
        ->get();
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id,
        "text" => $item->name,
      );
    }

    return response()->json($response);
  }
}