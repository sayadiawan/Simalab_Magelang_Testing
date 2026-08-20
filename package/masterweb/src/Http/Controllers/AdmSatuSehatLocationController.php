<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\SatuSehatLocation;
use Smt\Masterweb\Models\SatuSehat;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AdmSatuSehatLocationController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $locations = SatuSehatLocation::orderBy('name_satusehat_location')->get();
    return view("masterweb::module.admin.satusehat-location.index", compact("locations"));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view("masterweb::module.admin.satusehat-location.add");
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name_satusehat_location' => 'required|string|max:255',
      'version_satusehat_location' => 'required|in:prd,stg',
      'description' => 'nullable|string|max:500',
      'physical_type_code' => 'nullable|string|max:10',
      'physical_type_display' => 'nullable|string|max:100',
    ]);

    try {
      $location = new SatuSehatLocation();
      $location->name_satusehat_location = $validated['name_satusehat_location'];
      $location->version_satusehat_location = $validated['version_satusehat_location'];
      $location->save();

      // Call API SatuSehat if version is prd and VERSION_SATUSEHAT is prd
      if ($validated['version_satusehat_location'] === 'prd' && config('services.satu_sehat.version') === 'prd') {
        try {
          $apiCode = $this->createLocationInSatuSehat($location, $validated);
          if ($apiCode) {
            $location->kode_satusehat_location = $apiCode;
            $location->save();
          }
        } catch (\Exception $e) {
          Log::error('SatuSehat Location: Error creating location in API', [
            'error' => $e->getMessage(),
            'location_id' => $location->id_satusehat_location,
            'name' => $location->name_satusehat_location
          ]);
        }
      }

      return redirect("elits-satusehat-location")->with("success", "Satu Sehat Location successfully created");
    } catch (\Exception $exception) {
      Log::error('SatuSehat Location: Error creating location', [
        'error' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString()
      ]);
      return redirect("elits-satusehat-location")->with("error", "An error occurred while creating Satu Sehat Location");
    }
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    try {
      $location = SatuSehatLocation::find($id);

      if (!$location) {
        return redirect("elits-satusehat-location")->with("error", "Satu Sehat Location not found");
      }

      return view("masterweb::module.admin.satusehat-location.edit", compact("location"));
    } catch (\Exception $exception) {
      return redirect("elits-satusehat-location")->with("error", "An error occurred while getting Satu Sehat Location");
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update($id, Request $request)
  {
    $validated = $request->validate([
      'name_satusehat_location' => 'required|string|max:255',
      'version_satusehat_location' => 'required|in:prd,stg',
      'description' => 'nullable|string|max:500',
      'physical_type_code' => 'nullable|string|max:10',
      'physical_type_display' => 'nullable|string|max:100',
      'status' => 'nullable|in:active,inactive',
    ]);

    try {
      $location = SatuSehatLocation::find($id);

      if (!$location) {
        return redirect("elits-satusehat-location")->with("error", "Satu Sehat Location not found");
      }

      $location->name_satusehat_location = $validated['name_satusehat_location'];
      $location->version_satusehat_location = $validated['version_satusehat_location'];
      $location->save();

      // Call API SatuSehat if version is prd and VERSION_SATUSEHAT is prd
      if ($validated['version_satusehat_location'] === 'prd' && config('services.satu_sehat.version') === 'prd') {
        try {
          if (!empty($location->kode_satusehat_location)) {
            // Update existing location in API
            $this->updateLocationInSatuSehat($location, $validated);
          } else {
            // Create new location in API if code doesn't exist
            $apiCode = $this->createLocationInSatuSehat($location, $validated);
            if ($apiCode) {
              $location->kode_satusehat_location = $apiCode;
              $location->save();
            }
          }
        } catch (\Exception $e) {
          Log::error('SatuSehat Location: Error updating location in API', [
            'error' => $e->getMessage(),
            'location_id' => $location->id_satusehat_location,
            'name' => $location->name_satusehat_location
          ]);
        }
      }

      return redirect("elits-satusehat-location")->with("success", "Satu Sehat Location successfully updated");
    } catch (\Exception $exception) {
      Log::error('SatuSehat Location: Error updating location', [
        'error' => $exception->getMessage(),
        'location_id' => $id,
        'trace' => $exception->getTraceAsString()
      ]);
      return redirect("elits-satusehat-location")->with("error", "An error occurred while updating Satu Sehat Location");
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    try {
      $location = SatuSehatLocation::find($id);

      if (!$location) {
        return redirect("elits-satusehat-location")->with("error", "Satu Sehat Location not found");
      }

      $location->delete();

      return redirect("elits-satusehat-location")->with("success", "Satu Sehat Location successfully deleted");
    } catch (\Exception $exception) {
      Log::error('SatuSehat Location: Error deleting location', [
        'error' => $exception->getMessage(),
        'location_id' => $id
      ]);
      return redirect("elits-satusehat-location")->with("error", "An error occurred while deleting Satu Sehat Location");
    }
  }

  /**
   * Create location in SatuSehat API (POST)
   */
  private function createLocationInSatuSehat($location, $data)
  {
    try {
      // Get token from ms_satusehat_setting
      $satuSehat = SatuSehat::first();
      if (!$satuSehat || empty($satuSehat->token)) {
        Log::error('SatuSehat Location: Token not found in ms_satusehat_setting');
        return null;
      }

      // Get base URL and org ID from env
      $baseUrl = config('services.satu_sehat.base_uri');
      $orgId = config('services.satu_sehat.org_id');
      
      if (empty($baseUrl) || empty($orgId)) {
        Log::error('SatuSehat Location: BASE_URL_SATUSEHAT or ORG_ID not found in env');
        return null;
      }

      // Build request body
      $requestBody = [
        'resourceType' => 'Location',
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/location/' . $orgId,
            'value' => $data['name_satusehat_location']
          ]
        ],
        'status' => $data['status'] ?? 'active',
        'name' => $data['name_satusehat_location'],
        'description' => $data['description'] ?? $data['name_satusehat_location'] . ' Laboratorium Kesehatan Kabupaten Magelang',
        'mode' => 'instance',
        'physicalType' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
              'code' => $data['physical_type_code'] ?? 'ro',
              'display' => $data['physical_type_display'] ?? 'Room'
            ]
          ]
        ]
      ];

      // Make API request
      $client = new Client();
      $url = rtrim($baseUrl, '/') . '/Location';
      
      $response = $client->request('POST', $url, [
        'headers' => [
          'Authorization' => 'Bearer ' . $satuSehat->token,
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
        ],
        'json' => $requestBody,
        'timeout' => 30,
      ]);

      if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
        $body = json_decode($response->getBody(), true);
        
        // Get id from response
        // Response bisa langsung resource atau dalam entry array
        $locationCode = null;
        if (isset($body['id'])) {
          $locationCode = $body['id'];
        } elseif (isset($body['entry']) && is_array($body['entry']) && count($body['entry']) > 0) {
          $resource = $body['entry'][0]['resource'] ?? null;
          if ($resource && isset($resource['id'])) {
            $locationCode = $resource['id'];
          }
        }
        
        if ($locationCode) {
          Log::info('SatuSehat Location: Location created successfully in API', [
            'location_id' => $location->id_satusehat_location,
            'name' => $location->name_satusehat_location,
            'code' => $locationCode
          ]);
          
          return $locationCode;
        }
      }

      Log::error('SatuSehat Location: API request failed', [
        'status_code' => $response->getStatusCode(),
        'location_id' => $location->id_satusehat_location,
        'name' => $location->name_satusehat_location
      ]);
      return null;

    } catch (RequestException $e) {
      $response = $e->hasResponse() ? $e->getResponse() : null;
      $statusCode = $response ? $response->getStatusCode() : null;
      $body = $response ? $response->getBody()->getContents() : null;
      
      Log::error('SatuSehat Location: API request exception', [
        'message' => $e->getMessage(),
        'status_code' => $statusCode,
        'response_body' => $body,
        'location_id' => $location->id_satusehat_location,
        'name' => $location->name_satusehat_location
      ]);
      return null;
    } catch (\Exception $e) {
      Log::error('SatuSehat Location: Unexpected error', [
        'message' => $e->getMessage(),
        'location_id' => $location->id_satusehat_location,
        'name' => $location->name_satusehat_location
      ]);
      return null;
    }
  }

  /**
   * Update location in SatuSehat API (PUT)
   */
  private function updateLocationInSatuSehat($location, $data)
  {
    try {
      // Get token from ms_satusehat_setting
      $satuSehat = SatuSehat::first();
      if (!$satuSehat || empty($satuSehat->token)) {
        Log::error('SatuSehat Location: Token not found in ms_satusehat_setting');
        return false;
      }

      // Get base URL and org ID from env
      $baseUrl = config('services.satu_sehat.base_uri');
      $orgId = config('services.satu_sehat.org_id');
      
      if (empty($baseUrl) || empty($orgId)) {
        Log::error('SatuSehat Location: BASE_URL_SATUSEHAT or ORG_ID not found in env');
        return false;
      }

      // Build request body
      $requestBody = [
        'resourceType' => 'Location',
        'id' => $location->kode_satusehat_location,
        'identifier' => [
          [
            'system' => 'http://sys-ids.kemkes.go.id/location/' . $orgId,
            'value' => $data['name_satusehat_location']
          ]
        ],
        'status' => $data['status'] ?? 'active',
        'name' => $data['name_satusehat_location'],
        'description' => $data['description'] ?? $data['name_satusehat_location'] . ' Laboratorium Kesehatan Kabupaten Magelang',
        'mode' => 'instance',
        'physicalType' => [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
              'code' => $data['physical_type_code'] ?? 'ro',
              'display' => $data['physical_type_display'] ?? 'Room'
            ]
          ]
        ]
      ];

      // Make API request
      $client = new Client();
      $url = rtrim($baseUrl, '/') . '/Location/' . $location->kode_satusehat_location;
      
      $response = $client->request('PUT', $url, [
        'headers' => [
          'Authorization' => 'Bearer ' . $satuSehat->token,
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
        ],
        'json' => $requestBody,
        'timeout' => 30,
      ]);

      if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
        Log::info('SatuSehat Location: Location updated successfully in API', [
          'location_id' => $location->id_satusehat_location,
          'name' => $location->name_satusehat_location,
          'code' => $location->kode_satusehat_location
        ]);
        
        return true;
      }

      Log::error('SatuSehat Location: API request failed', [
        'status_code' => $response->getStatusCode(),
        'location_id' => $location->id_satusehat_location,
        'name' => $location->name_satusehat_location
      ]);
      return false;

    } catch (RequestException $e) {
      $response = $e->hasResponse() ? $e->getResponse() : null;
      $statusCode = $response ? $response->getStatusCode() : null;
      $body = $response ? $response->getBody()->getContents() : null;
      
      Log::error('SatuSehat Location: API request exception', [
        'message' => $e->getMessage(),
        'status_code' => $statusCode,
        'response_body' => $body,
        'location_id' => $location->id_satusehat_location,
        'name' => $location->name_satusehat_location
      ]);
      return false;
    } catch (\Exception $e) {
      Log::error('SatuSehat Location: Unexpected error', [
        'message' => $e->getMessage(),
        'location_id' => $location->id_satusehat_location,
        'name' => $location->name_satusehat_location
      ]);
      return false;
    }
  }
}

