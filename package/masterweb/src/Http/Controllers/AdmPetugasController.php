<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\SatuSehat;
use Smt\Masterweb\Models\SatuSehatPractitioner;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AdmPetugasController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

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
        $practitioner->name_petugas = $petugas->nama;// Use name from API response
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
   * Sync all existing practitioners' names from SatuSehat API
   * This will update name_satu_sehat_practitioner for all practitioners that have code_satu_sehat_practitioner
   */
  public function syncAllPractitionerNames()
  {
    // Only sync if VERSION_SATUSEHAT = prd
    if (config('services.satu_sehat.version') !== 'prd') {
      return response()->json([
        'status' => false,
        'message' => 'SatuSehat sync hanya bisa dilakukan di production (VERSION_SATUSEHAT = prd)'
      ], 400);
    }

    try {
      // Get token from ms_satusehat_setting
      $satuSehat = SatuSehat::first();
      if (!$satuSehat || empty($satuSehat->token)) {
        return response()->json([
          'status' => false,
          'message' => 'Token SatuSehat tidak ditemukan'
        ], 400);
      }

      // Get base URL from env
      $baseUrl = config('services.satu_sehat.base_uri');
      if (empty($baseUrl)) {
        return response()->json([
          'status' => false,
          'message' => 'BASE_URL_SATUSEHAT tidak ditemukan di env'
        ], 400);
      }

      // Get all practitioners that have code
      $practitioners = SatuSehatPractitioner::whereNotNull('code_satu_sehat_practitioner')
        ->where('code_satu_sehat_practitioner', '!=', '')
        ->get();

      if ($practitioners->isEmpty()) {
        return response()->json([
          'status' => true,
          'message' => 'Tidak ada practitioner yang perlu di-sync',
          'total' => 0,
          'success' => 0,
          'failed' => 0
        ]);
      }

      $client = new Client([
        'headers' => [
          'Authorization' => 'Bearer ' . $satuSehat->token,
          'Accept' => 'application/json',
        ],
        'timeout' => 30,
      ]);

      $successCount = 0;
      $failedCount = 0;
      $results = [];

      foreach ($practitioners as $practitioner) {
        $code = $practitioner->code_satu_sehat_practitioner;

        try {
          // Get Practitioner by ID to get full name (not censored)
          $getUrl = rtrim($baseUrl, '/') . '/Practitioner/' . $code;

          Log::info('SatuSehat: Fetching practitioner by ID for sync', [
            'code' => $code,
            'current_name' => $practitioner->name_satu_sehat_practitioner
          ]);

          $getResponse = $client->request('GET', $getUrl);

          if ($getResponse->getStatusCode() === 200) {
            $getBody = json_decode($getResponse->getBody(), true);

            // Get name from API response (from name[0].text) - this should be full name, not censored
            $apiName = $getBody['name'][0]['text'] ?? '';
            $apiNameNormalized = $this->normalizeName($apiName);

            

            if (!empty($apiNameNormalized)) {
              $oldName = $practitioner->name_satu_sehat_practitioner;
              $practitioner->name_satu_sehat_practitioner = $apiNameNormalized;
              $practitioner->save();

              $successCount++;
              $results[] = [
                'code' => $code,
                'old_name' => $oldName,
                'new_name' => $apiNameNormalized,
                'status' => 'success'
              ];

              Log::info('SatuSehat: Successfully synced practitioner name', [
                'code' => $code,
                'old_name' => $oldName,
                'new_name' => $apiNameNormalized
              ]);
            } else {
              $failedCount++;
              $results[] = [
                'code' => $code,
                'error' => 'Nama tidak ditemukan di response API',
                'status' => 'failed'
              ];
            }
          } else {
            $failedCount++;
            $results[] = [
              'code' => $code,
              'error' => 'API request failed with status: ' . $getResponse->getStatusCode(),
              'status' => 'failed'
            ];
          }
        } catch (RequestException $e) {
          $failedCount++;
          $results[] = [
            'code' => $code,
            'error' => $e->getMessage(),
            'status' => 'failed'
          ];
          Log::error('SatuSehat: Failed to sync practitioner', [
            'code' => $code,
            'error' => $e->getMessage()
          ]);
        } catch (\Exception $e) {
          $failedCount++;
          $results[] = [
            'code' => $code,
            'error' => $e->getMessage(),
            'status' => 'failed'
          ];
          Log::error('SatuSehat: Unexpected error syncing practitioner', [
            'code' => $code,
            'error' => $e->getMessage()
          ]);
        }
      }

      return response()->json([
        'status' => true,
        'message' => 'Sync selesai',
        'total' => $practitioners->count(),
        'success' => $successCount,
        'failed' => $failedCount,
        'results' => $results
      ]);

    } catch (\Exception $e) {
      Log::error('SatuSehat: Error in syncAllPractitionerNames', [
        'error' => $e->getMessage()
      ]);

      return response()->json([
        'status' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
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

  public function getRolesByLab(Request $request)
  {
    try {
      $labIds = $request->input('lab_ids', []);
      if (!is_array($labIds)) {
        $labIds = $labIds ? [$labIds] : [];
      }
      
      // Get target lab ID to filter roles for (if specified)
      $targetLabId = $request->input('target_lab_id', null);

      $roles = [];
      $hasNonLab = in_array('NON_LAB', $labIds);
      $labCodes = [];
      $targetLabCode = null;

      // Get target lab code if specified
      if ($targetLabId && $targetLabId !== 'NON_LAB') {
        $targetLab = Laboratorium::find($targetLabId);
        if ($targetLab && $targetLab->kode_laboratorium) {
          $targetLabCode = $targetLab->kode_laboratorium;
        }
      }

      // Get lab codes for selected labs
      foreach ($labIds as $labId) {
        if ($labId === 'NON_LAB') {
          continue;
        }
        $lab = Laboratorium::find($labId);
        if ($lab && $lab->kode_laboratorium) {
          $labCodes[] = $lab->kode_laboratorium;
        }
      }

      // Get all verification activities
      $allActivities = VerificationActivity::orderBy('name')->get();

      foreach ($allActivities as $activity) {
        $shouldInclude = false;

        // If target lab is specified, filter roles for that specific lab
        if ($targetLabId) {
          if ($targetLabId === 'NON_LAB') {
            // For NON_LAB, only show role id=1 (Pendaftaran/Registrasi)
            if ($activity->id == 1 && !empty($activity->register) && $activity->register !== '-' && $activity->register !== 'NULL') {
              $shouldInclude = true;
            }
          } else if ($targetLabCode) {
            // For specific lab, show perlab roles + role id=1 if NON_LAB is also selected
            $columnName = $this->getLabColumnName($targetLabCode);
            if ($columnName) {
              // If NON_LAB is also selected and this is role id=1, include it
              if ($hasNonLab && $activity->id == 1 && !empty($activity->register) && $activity->register !== '-' && $activity->register !== 'NULL') {
                $shouldInclude = true;
              }
              // For perlab roles (id > 1), check corresponding column
              if ($activity->id > 1) {
                $columnValue = $activity->$columnName;
                if (!empty($columnValue) && $columnValue !== '-' && $columnValue !== 'NULL') {
                  $shouldInclude = true;
                }
              }
            }
          }
        } else {
          // No target lab specified, return roles for all selected labs
          // For NON_LAB, check register column (id=1 for Pendaftaran/Registrasi)
          if ($hasNonLab) {
            if ($activity->id == 1 && !empty($activity->register) && $activity->register !== '-' && $activity->register !== 'NULL') {
              $shouldInclude = true;
            }
          }

          // For specific labs, check corresponding column (mikro/kimia/klinik)
          foreach ($labCodes as $labCode) {
            $columnName = $this->getLabColumnName($labCode);
            if ($columnName) {
              // If NON_LAB is also selected and this is role id=1, include it for all labs
              if ($hasNonLab && $activity->id == 1 && !empty($activity->register) && $activity->register !== '-' && $activity->register !== 'NULL') {
                $shouldInclude = true;
                break;
              }
              // For perlab roles (id > 1), check corresponding column
              if ($activity->id > 1) {
                $columnValue = $activity->$columnName;
                if (!empty($columnValue) && $columnValue !== '-' && $columnValue !== 'NULL') {
                  $shouldInclude = true;
                  break;
                }
              }
            }
          }
        }

        if ($shouldInclude) {
          $roles[] = [
            'id' => $activity->id,
            'name' => $activity->name
          ];
        }
      }

      return response()->json(['roles' => $roles]);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Error fetching roles: ' . $e->getMessage()], 500);
    }
  }

  public function index()
  {
    $listPetugas = Petugas::query()->select(['id_petugas', 'nama', 'nik', 'nip', 'gelar', 'password'])->get();
    return view("masterweb::module.admin.petugas.index", compact("listPetugas"));
  }

  public function edit($id)
  {
    try {
      $petugas = Petugas::query()->where("id_petugas", $id)->first();

      if (!$petugas){
        return redirect("elits-petugas")->with("error", "Petugas not found");
      }

      $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
      // Hanya tampilkan peran perlab (id > 1), exclude Pendaftaran/Registrasi (id=1)
      $verificationActivities = VerificationActivity::where('id', '>', 1)->orderBy('name')->get();
      return view("masterweb::module.admin.petugas.edit", compact("petugas", "laboratoriums", "verificationActivities"));

    }catch (\Exception $exception){
      return redirect("elits-petugas")->with("error", "An error occurred while getting Petugas");
    }
  }

  public function update($id, Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nik' => [
        'nullable',
        'string',
        function ($attribute, $value, $fail) {
          if ($value && (!ctype_digit($value) || strlen($value) !== 16)) {
            $fail('NIK harus 16 digit angka.');
          }
        },
        Rule::unique('ms_petugas', 'nik')->ignore($id, 'id_petugas')->where(function ($query) {
          return $query->whereNotNull('nik');
        }),
      ],
      'nip' => 'nullable|string|max:50',
      'gelar' => 'nullable|string|max:100',
      'password' => 'nullable|string',
      'lab_id' => ['nullable', 'array'],
      'lab_id.*' => ['nullable', function ($attribute, $value, $fail) {
        if ($value && $value !== 'NON_LAB' && !\Smt\Masterweb\Models\Laboratorium::where('id_laboratorium', $value)->exists()) {
          $fail('Laboratorium yang dipilih tidak valid.');
        }
      }],
      'role_per_lab' => 'nullable|array',
      'role_per_lab.*' => 'nullable|array',
      'role_per_lab.*.*' => [
        'exists:ms_verification_activities,id',
        function ($attribute, $value, $fail) use ($request) {
          // Extract lab_id from attribute path (e.g., "role_per_lab.NON_LAB.0" or "role_per_lab.{lab_id}.0")
          $parts = explode('.', $attribute);
          if (count($parts) >= 2) {
            $labId = $parts[1];
            $hasNonLab = ($labId === 'NON_LAB');
            
            // Check if NON_LAB is selected in lab_id
            $labIds = $request->input('lab_id', []);
            $nonLabSelected = in_array('NON_LAB', $labIds);
            
            // Check if NON_LAB has role id=1 in role_per_lab
            $rolePerLab = $request->input('role_per_lab', []);
            $nonLabHasRegister = false;
            if (isset($rolePerLab['NON_LAB']) && is_array($rolePerLab['NON_LAB'])) {
              $nonLabHasRegister = in_array('1', $rolePerLab['NON_LAB']) || in_array(1, $rolePerLab['NON_LAB']);
            }
            
            // If NON_LAB is selected, allow id=1 (Pendaftaran/Registrasi)
            // If NON_LAB is selected AND has role id=1, also allow id=1 for other labs (will be added by backend)
            // Otherwise, only allow perlab roles (id > 1)
            if ($value && (int)$value == 1) {
              // Allow role id=1 for NON_LAB
              if ($hasNonLab) {
                return; // Allow
              }
              // Allow role id=1 for other labs if NON_LAB is selected and has role id=1
              if ($nonLabSelected && $nonLabHasRegister) {
                return; // Allow
              }
              // Otherwise, reject
              $fail('Hanya peran perlab yang dapat dipilih. Pendaftaran/Registrasi hanya tersedia untuk Non Lab.');
            }
            // For other roles (id > 1), always allow
          }
        },
      ],
      'is_kepala_lab' => 'nullable|boolean',
    ]);

    try {
      $petugas = Petugas::query()->where("id_petugas", $id)->first();

      if (!$petugas) {
        return redirect("elits-petugas")->with("error", "Petugas not found");
      }

      // Store old values for verification activities update
      $oldLabId = $petugas->lab_id;
      $oldRoles = $petugas->role ?? [];
      $oldNama = $petugas->nama;

      $petugas->nama = $validated['nama'];
      $petugas->nik = $validated['nik'] ?? null;
      $petugas->nip = $validated['nip'] ?? null;
      $petugas->gelar = $validated['gelar'] ?? null;
      $petugas->password = $validated['password'] ?? null;
      // Handle lab_id - can be array or single value, filter out NON_LAB
      $labIds = $validated['lab_id'] ?? [];
      if (!is_array($labIds)) {
        $labIds = $labIds ? [$labIds] : [];
      }
      // Filter out NON_LAB and convert to array, or null if empty
      $labIds = array_filter($labIds, function($id) {
        return $id !== 'NON_LAB' && $id !== null && $id !== '';
      });
      $petugas->lab_id = !empty($labIds) ? array_values($labIds) : null;
      
      // Handle role_per_lab - combine all roles from all labs
      $allRoles = [];
      $hasRegisterRole = false;

      
      if (isset($validated['role_per_lab']) && is_array($validated['role_per_lab'])) {
        // Check if NON_LAB has role id=1 (Pendaftaran/Registrasi)
        if (isset($validated['role_per_lab']['NON_LAB']) && is_array($validated['role_per_lab']['NON_LAB'])) {
          if (in_array('1', $validated['role_per_lab']['NON_LAB']) || in_array(1, $validated['role_per_lab']['NON_LAB'])) {
            $hasRegisterRole = true;
          }
        }

        
        // Collect all roles from all labs
        foreach ($validated['role_per_lab'] as $labId => $roles) {
          if (is_array($roles)) {
            foreach ($roles as $roleId) {
              if (!empty($roleId) && !in_array($roleId, $allRoles)) {
                $allRoles[] = $roleId;
              }
            }
          }
        }
        
        // If register role (id=1) is selected for NON_LAB, add it to ALL labs in system (KIM, MBI, KLI)
        if ($hasRegisterRole) {
          // Get all labs with codes KIM, MBI, KLI
          $allSystemLabs = Laboratorium::whereIn('kode_laboratorium', ['KIM', 'MBI', 'KLI'])->get();
        
          // Add all system labs to lab_id if not already there
          foreach ($allSystemLabs as $systemLab) {
            $systemLabId = $systemLab->id_laboratorium;
            
            // Add to lab_id if not already there
            if (!in_array($systemLabId, $labIds)) {
              $labIds[] = $systemLabId;
            }
            
            // Add to role_per_lab
            if (!isset($validated['role_per_lab'][$systemLabId])) {
              $validated['role_per_lab'][$systemLabId] = [];
            }
            if (!in_array('1', $validated['role_per_lab'][$systemLabId]) && !in_array(1, $validated['role_per_lab'][$systemLabId])) {
              $validated['role_per_lab'][$systemLabId][] = '1';
            }
            
            // Also add to allRoles if not already there
            if (!in_array('1', $allRoles) && !in_array(1, $allRoles)) {
              $allRoles[] = '1';
            }
          }
        }
      }
      $petugas->lab_id = !empty($labIds) ? array_values($labIds) : null;
      $petugas->role = !empty($allRoles) ? array_values($allRoles) : [];
      $petugas->is_kepala_lab = $request->has('is_kepala_lab') ? 1 : 0;
      
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
      // Process role_per_lab to save roles per lab correctly
      if (isset($validated['role_per_lab']) && is_array($validated['role_per_lab'])) {
        // Get all system labs for role id=1 from NON_LAB
        $allSystemLabs = [];
        if ($hasRegisterRole) {
          $allSystemLabs = Laboratorium::whereIn('kode_laboratorium', ['KIM', 'MBI', 'KLI'])->pluck('id_laboratorium')->toArray();
        }
        
        // First, handle NON_LAB role id=1 - save to all system labs (KIM, MBI, KLI) and register column
        if ($hasRegisterRole && !empty($allSystemLabs)) {
          // Add role id=1 to all system labs (KIM, MBI, KLI) - only once
          $this->addToVerificationActivities($petugas->nama, $allSystemLabs, ['1']);
          // Also add to register column (for NON_LAB)
          $this->addToVerificationActivities($petugas->nama, null, ['1']);
        }
        
        // Then, process each lab with its perlab roles (id > 1)
        foreach ($validated['role_per_lab'] as $labId => $roles) {
          if (!is_array($roles) || empty($roles)) {
            continue;
          }
          
          // Skip NON_LAB (already processed above)
          if ($labId === 'NON_LAB') {
            continue;
          }
          
          // For specific labs, save only perlab roles (id > 1)
          // Role id=1 for system labs is already saved above, so skip it here
          $labRoles = [];
          foreach ($roles as $roleId) {
            // Only include perlab roles (id > 1)
            // Skip role id=1 as it's already handled for all system labs above
            if ($roleId != '1' && $roleId != 1) {
              $labRoles[] = $roleId;
            }
          }
          
          if (!empty($labRoles)) {
            $this->addToVerificationActivities($petugas->nama, [$labId], $labRoles);
          }
        }
      }

      return redirect("elits-petugas")->with("success", "Petugas successfully updated");

    } catch (\Exception $exception) {
      return redirect("elits-petugas")->with("error", "An error occurred while updating Petugas");
    }
  }

  public function create()
  {
    $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
    // Hanya tampilkan peran perlab (id > 1), exclude Pendaftaran/Registrasi (id=1)
    $verificationActivities = VerificationActivity::where('id', '>', 1)->orderBy('name')->get();
    return view("masterweb::module.admin.petugas.add", compact('laboratoriums', 'verificationActivities'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nik' => ['nullable', 'string', function ($attribute, $value, $fail) {
        if ($value && (!ctype_digit($value) || strlen($value) !== 16)) {
          $fail('NIK harus 16 digit angka.');
        }
      }, Rule::unique('ms_petugas', 'nik')->where(function ($query) {
        return $query->whereNotNull('nik');
      })],
      'nip' => 'nullable|string|max:50',
      'gelar' => 'nullable|string|max:100',
      'password' => 'nullable|string',
      'lab_id' => ['nullable', 'array'],
      'lab_id.*' => ['nullable', function ($attribute, $value, $fail) {
        if ($value && $value !== 'NON_LAB' && !\Smt\Masterweb\Models\Laboratorium::where('id_laboratorium', $value)->exists()) {
          $fail('Laboratorium yang dipilih tidak valid.');
        }
      }],
      'role_per_lab' => 'nullable|array',
      'role_per_lab.*' => 'nullable|array',
      'role_per_lab.*.*' => [
        'exists:ms_verification_activities,id',
        function ($attribute, $value, $fail) use ($request) {
          // Extract lab_id from attribute path (e.g., "role_per_lab.NON_LAB.0" or "role_per_lab.{lab_id}.0")
          $parts = explode('.', $attribute);
          if (count($parts) >= 2) {
            $labId = $parts[1];
            $hasNonLab = ($labId === 'NON_LAB');
            
            // Check if NON_LAB is selected in lab_id
            $labIds = $request->input('lab_id', []);
            $nonLabSelected = in_array('NON_LAB', $labIds);
            
            // Check if NON_LAB has role id=1 in role_per_lab
            $rolePerLab = $request->input('role_per_lab', []);
            $nonLabHasRegister = false;
            if (isset($rolePerLab['NON_LAB']) && is_array($rolePerLab['NON_LAB'])) {
              $nonLabHasRegister = in_array('1', $rolePerLab['NON_LAB']) || in_array(1, $rolePerLab['NON_LAB']);
            }
            
            // If NON_LAB is selected, allow id=1 (Pendaftaran/Registrasi)
            // If NON_LAB is selected AND has role id=1, also allow id=1 for other labs (will be added by backend)
            // Otherwise, only allow perlab roles (id > 1)
            if ($value && (int)$value == 1) {
              // Allow role id=1 for NON_LAB
              if ($hasNonLab) {
                return; // Allow
              }
              // Allow role id=1 for other labs if NON_LAB is selected and has role id=1
              if ($nonLabSelected && $nonLabHasRegister) {
                return; // Allow
              }
              // Otherwise, reject
              $fail('Hanya peran perlab yang dapat dipilih. Pendaftaran/Registrasi hanya tersedia untuk Non Lab.');
            }
            // For other roles (id > 1), always allow
          }
        },
      ],
      'is_kepala_lab' => 'nullable|boolean',
    ]);

    try {
      $petugas = new Petugas();
      $petugas->nama = $validated['nama'];
      $petugas->nik = $validated['nik'] ?? null;
      $petugas->nip = $validated['nip'] ?? null;
      $petugas->gelar = $validated['gelar'] ?? null;
      $petugas->password = $validated['password'] ?? null;
      // Handle lab_id - can be array or single value, filter out NON_LAB
      $labIds = $validated['lab_id'] ?? [];
      if (!is_array($labIds)) {
        $labIds = $labIds ? [$labIds] : [];
      }
      // Filter out NON_LAB and convert to array, or null if empty
      $labIds = array_filter($labIds, function($id) {
        return $id !== 'NON_LAB' && $id !== null && $id !== '';
      });
      $petugas->lab_id = !empty($labIds) ? array_values($labIds) : null;
      
      // Handle role_per_lab - combine all roles from all labs
      $allRoles = [];
      $hasRegisterRole = false;
      
      if (isset($validated['role_per_lab']) && is_array($validated['role_per_lab'])) {
        // Check if NON_LAB has role id=1 (Pendaftaran/Registrasi)
        if (isset($validated['role_per_lab']['NON_LAB']) && is_array($validated['role_per_lab']['NON_LAB'])) {
          if (in_array('1', $validated['role_per_lab']['NON_LAB']) || in_array(1, $validated['role_per_lab']['NON_LAB'])) {
            $hasRegisterRole = true;
          }
        }
        
        // Collect all roles from all labs
        foreach ($validated['role_per_lab'] as $labId => $roles) {
          if (is_array($roles)) {
            foreach ($roles as $roleId) {
              if (!empty($roleId) && !in_array($roleId, $allRoles)) {
                $allRoles[] = $roleId;
              }
            }
          }
        }
        
        // If register role (id=1) is selected for NON_LAB, add it to ALL labs in system (KIM, MBI, KLI)
        if ($hasRegisterRole) {
          // Get all labs with codes KIM, MBI, KLI
          $allSystemLabs = Laboratorium::whereIn('kode_laboratorium', ['KIM', 'MBI', 'KLI'])->get();
          
          // Add all system labs to lab_id if not already there
          foreach ($allSystemLabs as $systemLab) {
            $systemLabId = $systemLab->id_laboratorium;
            
            // Add to lab_id if not already there
            if (!in_array($systemLabId, $labIds)) {
              $labIds[] = $systemLabId;
            }
            
            // Add to role_per_lab
            if (!isset($validated['role_per_lab'][$systemLabId])) {
              $validated['role_per_lab'][$systemLabId] = [];
            }
            if (!in_array('1', $validated['role_per_lab'][$systemLabId]) && !in_array(1, $validated['role_per_lab'][$systemLabId])) {
              $validated['role_per_lab'][$systemLabId][] = '1';
            }
            
            // Also add to allRoles if not already there
            if (!in_array('1', $allRoles) && !in_array(1, $allRoles)) {
              $allRoles[] = '1';
            }
          }
        }
      }
      $petugas->lab_id = !empty($labIds) ? array_values($labIds) : null;
      $petugas->role = !empty($allRoles) ? array_values($allRoles) : [];
      $petugas->is_kepala_lab = $request->has('is_kepala_lab') ? 1 : 0;
      
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

      Log::info('Petugas created', [
        'id' => $petugas->id_petugas,
        'nama' => $petugas->nama,
        'lab_id' => $petugas->lab_id,
        'role' => $petugas->role,
        'role_is_array' => is_array($petugas->role),
        'role_empty' => empty($petugas->role)
      ]);

      // Update verification activities if roles are provided
      // Process role_per_lab to save roles per lab correctly
      if (isset($validated['role_per_lab']) && is_array($validated['role_per_lab'])) {
        try {
          // Get all system labs for role id=1 from NON_LAB
          $allSystemLabs = [];
          if ($hasRegisterRole) {
            $allSystemLabs = Laboratorium::whereIn('kode_laboratorium', ['KIM', 'MBI', 'KLI'])->pluck('id_laboratorium')->toArray();
          }
          
          // First, handle NON_LAB role id=1 - save to all system labs (KIM, MBI, KLI) and register column
          if ($hasRegisterRole && !empty($allSystemLabs)) {
            // Add role id=1 to all system labs (KIM, MBI, KLI) - only once
            $this->addToVerificationActivities($petugas->nama, $allSystemLabs, ['1']);
            // Also add to register column (for NON_LAB)
            $this->addToVerificationActivities($petugas->nama, null, ['1']);
          }
          
          // Then, process each lab with its perlab roles (id > 1)
          foreach ($validated['role_per_lab'] as $labId => $roles) {
            if (!is_array($roles) || empty($roles)) {
              continue;
            }
            
            // Skip NON_LAB (already processed above)
            if ($labId === 'NON_LAB') {
              continue;
            }
            
            // For specific labs, save only perlab roles (id > 1)
            // Role id=1 for system labs is already saved above, so skip it here
            $labRoles = [];
            foreach ($roles as $roleId) {
              // Only include perlab roles (id > 1)
              // Skip role id=1 as it's already handled for all system labs above
              if ($roleId != '1' && $roleId != 1) {
                $labRoles[] = $roleId;
              }
            }
            
            if (!empty($labRoles)) {
              $this->addToVerificationActivities($petugas->nama, [$labId], $labRoles);
            }
          }
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

      return redirect("elits-petugas")->with("success", "Petugas successfully created");
    }catch (\Exception $exception){
      return redirect("elits-petugas")->with("error", "An error occurred while creating Petugas");
    }
  }

  public function destroy($id)
  {
    try {
      $petugas = Petugas::find($id);
      if (!$petugas) {
        return redirect("elits-petugas")->with("error", "Petugas not found");
      }

      Log::info('Petugas destroy: Starting', [
        'id' => $petugas->id_petugas,
        'nama' => $petugas->nama,
        'lab_id' => $petugas->lab_id,
        'role' => $petugas->role,
        'role_is_array' => is_array($petugas->role),
        'role_empty' => empty($petugas->role)
      ]);

      // Remove from verification activities before deleting
      // Bisa lab_id ada atau NULL (untuk non-lab seperti pendaftaran, kepala lab, keuangan)
      if (!empty($petugas->role)) {
        try {
          $this->removeFromVerificationActivities($petugas->nama, $petugas->lab_id, $petugas->role);
        } catch (\Exception $e) {
          Log::error('Error removing from verification activities: ' . $e->getMessage());
          Log::error('Stack trace: ' . $e->getTraceAsString());
        }
      } else {
        Log::warning('Petugas destroy: No roles to remove', [
          'nama' => $petugas->nama,
          'lab_id' => $petugas->lab_id
        ]);
      }

      // Also remove from all columns as fallback (in case lab_id changed or data inconsistent)
      $this->removeNameFromAllVerificationActivities($petugas->nama);

      $petugas->delete();

      Log::info('Petugas destroy: Successfully deleted', ['id' => $id]);

      return redirect("elits-petugas")->with("success", "Petugas successfully deleted");
    } catch (\Exception $exception) {
      Log::error('Petugas destroy: Exception', [
        'id' => $id,
        'message' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString()
      ]);
      return redirect("elits-petugas")->with("error", "An error occurred while deleting Petugas");
    }
  }

  /**
   * Remove name from all verification activities columns as fallback
   */
  private function removeNameFromAllVerificationActivities($petugasName)
  {
    $columns = ['mikro', 'kimia', 'klinik', 'register'];
    $activities = VerificationActivity::all();
    
    foreach ($activities as $activity) {
      foreach ($columns as $column) {
        $existingNames = $activity->$column;
        if ($existingNames && $existingNames !== '-' && $existingNames !== 'NULL') {
          // Parse names using smart parsing to handle names with commas
          $namesArray = $this->parseNames($existingNames);
          $originalCount = count($namesArray);
          
          // Normalize petugasName for comparison
          $petugasNameNormalized = $this->normalizeName($petugasName);
          $namesArray = array_filter($namesArray, function($name) use ($petugasNameNormalized) {
            // Normalize name and compare case-insensitively
            $nameNormalized = $this->normalizeName($name);
            return strtolower($nameNormalized) !== strtolower($petugasNameNormalized);
          });
          
          if (count($namesArray) < $originalCount) {
            $activity->$column = !empty($namesArray) ? implode(', ', $namesArray) : null;
            $activity->save();
            
            Log::info('removeNameFromAllVerificationActivities: Removed name', [
              'activityId' => $activity->id,
              'column' => $column,
              'petugasName' => $petugasName
            ]);
          }
        }
      }
    }
  }
}