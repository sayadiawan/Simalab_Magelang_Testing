<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Smt\Masterweb\Helpers\Smt;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\VerificationActivitySample;

class SignatureController extends Controller
{
    public function signatureProgress($id, $isClinic)
    {

      $activity = VerificationActivity::query()->get();

      if ($isClinic == 1){
        $item = PermohonanUjiKlinik2::find($id);
        $nomorSampel = $item->noregister_permohonan_uji_klinik;
        $tanggalRegister = $item->tglregister_permohonan_uji_klinik;

        $verificationActivitySamples = VerificationActivitySample::where('is_klinik', $id);
      }else {
        $item = Sample::find($id);
        $nomorSampel = $item->codesample_samples;
        $tanggalRegister = $item->date_sending;

        $verificationActivitySamples = VerificationActivitySample::where('id_sample', $id);
      }

      $verificationActivitySamples = $verificationActivitySamples->join('ms_verification_activities', 'id_verification_activity', '=', 'ms_verification_activities.id')->get();

      $listVerifications = [];
      foreach ($verificationActivitySamples as  $verificationActivitySample){
        $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
      }

      $listActivity = [];
      foreach ($activity as $act){
        $listActivity[$act->id] = $act;
      }

      ksort($listVerifications);

      if (count($listVerifications) == 6){
        $listVerificationsTemp = $listVerifications;
        $listVerifications[2] = $listVerifications[6];
        $listVerifications[3] = $listVerificationsTemp[2];
        $listVerifications[4] = $listVerificationsTemp[3];
        $listVerifications[5] = $listVerificationsTemp[4];
        $listVerifications[6] = $listVerificationsTemp[5];

        unset($listVerificationsTemp);
      }

      return view('masterweb::module.admin.laboratorium.template.signature_progress', compact('item', 'listVerifications', 'listActivity', 'isClinic', 'nomorSampel', 'tanggalRegister'));
    }

  public function signatureProgressDummy($id, $isClinic)
  {
    $item = PermohonanUjiKlinik2::find($id);

    $activity = VerificationActivity::query()->get();

    if ($isClinic == 1){
      $verificationActivitySamples = VerificationActivitySample::where('is_klinik', $id);
    }else {
      $verificationActivitySamples = VerificationActivitySample::where('id_sample', $id);
    }

    $verificationActivitySamples = $verificationActivitySamples->join('ms_verification_activities', 'id_verification_activity', '=', 'ms_verification_activities.id')->get();

    $listVerifications = [];
    foreach ($verificationActivitySamples as  $verificationActivitySample){
      $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
    }

    $listActivity = [];
    foreach ($activity as $act){
      $listActivity[$act->id] = $act;
    }

    ksort($listVerifications);

    return view('masterweb::module.admin.laboratorium.template.signature_progress_dummy', compact('item', 'listVerifications', 'listActivity', 'isClinic'));
  }

  public function verifySignatureView()
  {
    return view('masterweb::module.admin.laboratorium.signature.index');
  }

  public function verifySignature(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'document' => 'required|file|mimes:pdf|max:2048'
    ], [
      'document.required' => 'File PDF wajib diunggah.',
      'document.file' => 'Unggahan harus berupa file.',
      'document.mimes' => 'Hanya file dengan format PDF yang diperbolehkan.',
      'document.max' => 'Ukuran file maksimum adalah 2MB.',
    ]);

    if ($validator->fails()){
      return response()->json([
        'success' => false,
        'errors' => $validator->errors()->first('document'),
      ], 400);
    }

    $data = $request->file('document');
    $fileContent = file_get_contents($data->getPathname());

    if (!$data->isValid()) {
      return response()->json([
        'success' => false,
        'errors' => 'Terjadi kesalahan pada file upload.',
      ], 400);
    }

    $result = Smt::verifySignBSRE2($data->getPathname());

    return response()->json([
      'success' => true,
      'result' => $result,
      'file' => base64_encode($fileContent),
    ]);
  }
}
