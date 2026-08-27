<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;



Route::group([], function () {
  // Mobile Sampling Routes (with web middleware for session support)
  Route::group(['middleware' => ['web']], function () {
    // Mobile Menu Selection
    Route::get('/mobile/menu', ['as' => 'mobile.menu', 'uses' => 'MobileMenuController@index']);

    // Mobile Sampling (Kesmas) - new home and scan flow
     Route::post('/mobile/sampling/input-id', ['as' => 'mobile.sampling.inputId', 'uses' => 'MobileSamplingController@inputId']);
    Route::get('/mobile/sampling/scan/{id}', ['as' => 'mobile.sampling.scan', 'uses' => 'MobileSamplingController@scan']);
    Route::get('/mobile/sampling/{id}', ['as' => 'mobile.sampling.index', 'uses' => 'MobileSamplingController@index']);
    Route::post('/mobile/sampling/{id}/login', ['as' => 'mobile.sampling.login', 'uses' => 'MobileSamplingController@login'])->middleware('throttle:20,1');
    Route::get('/mobile/sampling/{id}/drafts', ['as' => 'mobile.sampling.draftList', 'uses' => 'MobileSamplingController@draftList']);
    Route::get('/mobile/sampling/{id}/draft/{draft_id}/edit', ['as' => 'mobile.sampling.draft.edit', 'uses' => 'MobileSamplingController@editDraft']);
    Route::post('/mobile/sampling/{id}/draft/{draft_id}/update', ['as' => 'mobile.sampling.draft.update', 'uses' => 'MobileSamplingController@updateDraft']);
    Route::post('/mobile/sampling/{id}/draft/{draft_id}/verify', ['as' => 'mobile.sampling.draft.verify', 'uses' => 'MobileSamplingController@verifyDraft']);
    Route::delete('/mobile/sampling/{id}/draft/{draft_id}', ['as' => 'mobile.sampling.draft.delete', 'uses' => 'MobileSamplingController@deleteDraft']);
    Route::delete('/mobile/sampling/{id}/draft-group/{group_id}', ['as' => 'mobile.sampling.draftGroup.delete', 'uses' => 'MobileSamplingController@deleteDraftGroup']);
    Route::post('/mobile/sampling/{id}/finish-drafts', ['as' => 'mobile.sampling.finishDrafts', 'uses' => 'MobileSamplingController@finishDrafts']);
    Route::get('/mobile/sampling/{id}/signature', ['as' => 'mobile.sampling.signature', 'uses' => 'MobileSamplingController@signature']);
    Route::post('/mobile/sampling/{id}/save-signature', ['as' => 'mobile.sampling.saveSignature', 'uses' => 'MobileSamplingController@saveSignature']);
    Route::get('/mobile/sampling/{id}/form', ['as' => 'mobile.sampling.form', 'uses' => 'MobileSamplingController@form']);
    Route::post('/mobile/sampling/{id}/store', ['as' => 'mobile.sampling.store', 'uses' => 'MobileSamplingController@store']);
    Route::get('/mobile/sampling/{id}/edit/{sample_id}', ['as' => 'mobile.sampling.edit', 'uses' => 'MobileSamplingController@edit']);
    Route::post('/mobile/sampling/{id}/update/{sample_id}', ['as' => 'mobile.sampling.update', 'uses' => 'MobileSamplingController@update']);
    Route::get('/mobile/sampling/{id}/success', ['as' => 'mobile.sampling.success', 'uses' => 'MobileSamplingController@success']);
    Route::post('/mobile/sampling/{id}/logout', ['as' => 'mobile.sampling.logout', 'uses' => 'MobileSamplingController@logout']);

    // API routes for mobile sampling (no auth required, uses session)
    Route::get('/mobile/sampling/{id}/getbaku_mutu/{sample_type_id}', ['as' => 'mobile.sampling.getbaku_mutu', 'uses' => 'MobileSamplingController@getBakuMutu']);
    Route::get('/mobile/sampling/{id}/getdetail_sample_type/{sample_type_id}', ['as' => 'mobile.sampling.getdetail_sample_type', 'uses' => 'MobileSamplingController@getDetailSampleType']);
    Route::get('/mobile/sampling', ['as' => 'mobile.sampling.home', 'uses' => 'MobileSamplingController@home']);

    // Mobile Testing Routes
    Route::get('/mobile/testing', ['as' => 'mobile.testing.home', 'uses' => 'MobileTestingController@home']);
    Route::post('/mobile/testing/input-id', ['as' => 'mobile.testing.inputId', 'uses' => 'MobileTestingController@inputId']);
    Route::get('/mobile/testing/scan/{id}', ['as' => 'mobile.testing.scan', 'uses' => 'MobileTestingController@scan']);
    Route::get('/mobile/testing/{id}/login', ['as' => 'mobile.testing.login', 'uses' => 'MobileTestingController@login']);
    Route::post('/mobile/testing/{id}/login', ['as' => 'mobile.testing.doLogin', 'uses' => 'MobileTestingController@doLogin'])->middleware('throttle:20,1');
    Route::get('/mobile/testing/{id}/status', ['as' => 'mobile.testing.status', 'uses' => 'MobileTestingController@status']);
    Route::post('/mobile/testing/{id}/store-penerimaan', ['as' => 'mobile.testing.storePenerimaan', 'uses' => 'MobileTestingController@storePenerimaan']);
    Route::get('/mobile/testing/{id}/pemeriksaan/{lab_id}', ['as' => 'mobile.testing.pemeriksaan', 'uses' => 'MobileTestingController@pemeriksaan']);
    Route::post('/mobile/testing/{id}/store-pemeriksaan', ['as' => 'mobile.testing.storePemeriksaan', 'uses' => 'MobileTestingController@storePemeriksaan']);
    Route::get('/mobile/testing/{id}/input-hasil/{lab_id}', ['as' => 'mobile.testing.inputHasil', 'uses' => 'MobileTestingController@inputHasil']);
    Route::post('/mobile/testing/{id}/store-input-hasil', ['as' => 'mobile.testing.storeInputHasil', 'uses' => 'MobileTestingController@storeInputHasil']);
    Route::get('/mobile/testing/{id}/baca-hasil/{lab_id}/{method_id}', ['as' => 'mobile.testing.bacaHasil', 'uses' => 'MobileTestingController@bacaHasil']);
    Route::post('/mobile/testing/{id}/store-baca-hasil/{lab_id}/{method_id}', ['as' => 'mobile.testing.storeBacaHasil', 'uses' => 'MobileTestingController@storeBacaHasil']);
    Route::get('/mobile/testing/{id}/verifikasi-hasil/{lab_id}', ['as' => 'mobile.testing.verifikasiHasil', 'uses' => 'MobileTestingController@verifikasiHasil']);
    Route::post('/mobile/testing/{id}/store-verifikasi-hasil', ['as' => 'mobile.testing.storeVerifikasiHasil', 'uses' => 'MobileTestingController@storeVerifikasiHasil']);
    Route::get('/mobile/testing/{id}/input-tanggal-verifikasi/{lab_id}', ['as' => 'mobile.testing.inputTanggalVerifikasi', 'uses' => 'MobileTestingController@inputTanggalVerifikasi']);
    Route::post('/mobile/testing/{id}/store-tanggal-verifikasi', ['as' => 'mobile.testing.storeTanggalVerifikasi', 'uses' => 'MobileTestingController@storeTanggalVerifikasi']);
    Route::get('/mobile/testing/{id}/pengesahan-hasil/{lab_id}', ['as' => 'mobile.testing.pengesahanHasil', 'uses' => 'MobileTestingController@pengesahanHasil']);
    Route::post('/mobile/testing/{id}/store-pengesahan-hasil', ['as' => 'mobile.testing.storePengesahanHasil', 'uses' => 'MobileTestingController@storePengesahanHasil']);
    Route::get('/mobile/testing/{id}/input-validasi/{lab_id}', ['as' => 'mobile.testing.inputValidasi', 'uses' => 'MobileTestingController@inputValidasi']);
    Route::post('/mobile/testing/{id}/store-validasi', ['as' => 'mobile.testing.storeValidasi', 'uses' => 'MobileTestingController@storeValidasi']);
    Route::get('/mobile/testing/{id}/selesai/{lab_id}', ['as' => 'mobile.testing.selesai', 'uses' => 'MobileTestingController@selesai']);
    Route::post('/mobile/testing/logout', ['as' => 'mobile.testing.logout', 'uses' => 'MobileTestingController@logout']);

    // Mobile Testing Klinik Routes
    Route::get('/mobile/testing-klinik', ['as' => 'mobile.testing.klinik.home', 'uses' => 'MobileTestingKlinikController@home']);
    Route::post('/mobile/testing-klinik/input-id', ['as' => 'mobile.testing.klinik.inputId', 'uses' => 'MobileTestingKlinikController@inputId']);
    Route::get('/mobile/testing-klinik/scan/{id}', ['as' => 'mobile.testing.klinik.scan', 'uses' => 'MobileTestingKlinikController@scan']);
    Route::get('/mobile/testing-klinik/{id}/login', ['as' => 'mobile.testing.klinik.login', 'uses' => 'MobileTestingKlinikController@login']);
    Route::post('/mobile/testing-klinik/{id}/login', ['as' => 'mobile.testing.klinik.doLogin', 'uses' => 'MobileTestingKlinikController@doLogin'])->middleware('throttle:20,1');
    Route::post('/mobile/testing-klinik/logout', ['as' => 'mobile.testing.klinik.logout', 'uses' => 'MobileTestingKlinikController@logout']);
    Route::get('/mobile/testing-klinik/{id}/penerimaan', ['as' => 'mobile.testing.klinik.penerimaan', 'uses' => 'MobileTestingKlinikController@penerimaan']);
    Route::post('/mobile/testing-klinik/{id}/store-penerimaan', ['as' => 'mobile.testing.klinik.storePenerimaan', 'uses' => 'MobileTestingKlinikController@storePenerimaan']);
    Route::get('/mobile/testing-klinik/{id}/pengolah', ['as' => 'mobile.testing.klinik.pengolah', 'uses' => 'MobileTestingKlinikController@pengolah']);
    Route::post('/mobile/testing-klinik/{id}/store-pengolah', ['as' => 'mobile.testing.klinik.storePengolah', 'uses' => 'MobileTestingKlinikController@storePengolah']);
    Route::get('/mobile/testing-klinik/{id}/pemeriksa', ['as' => 'mobile.testing.klinik.pemeriksa', 'uses' => 'MobileTestingKlinikController@pemeriksa']);
    Route::post('/mobile/testing-klinik/{id}/store-pemeriksa', ['as' => 'mobile.testing.klinik.storePemeriksa', 'uses' => 'MobileTestingKlinikController@storePemeriksa']);
    Route::get('/mobile/testing-klinik/{id}/verifikasi', ['as' => 'mobile.testing.klinik.verifikasi', 'uses' => 'MobileTestingKlinikController@verifikasi']);
    Route::post('/mobile/testing-klinik/{id}/store-verifikasi', ['as' => 'mobile.testing.klinik.storeVerifikasi', 'uses' => 'MobileTestingKlinikController@storeVerifikasi']);
    Route::get('/mobile/testing-klinik/{id}/status', ['as' => 'mobile.testing.klinik.status', 'uses' => 'MobileTestingKlinikController@status']);

    // Mobile Dokter Routes
    Route::get('/mobile/dokter', ['as' => 'mobile.dokter.home', 'uses' => 'MobileDokterController@home']);
    Route::post('/mobile/dokter/input-id', ['as' => 'mobile.dokter.inputId', 'uses' => 'MobileDokterController@inputId']);
    Route::get('/mobile/dokter/scan/{id}', ['as' => 'mobile.dokter.scan', 'uses' => 'MobileDokterController@scan']);
    Route::get('/mobile/dokter/login/{id}', ['as' => 'mobile.dokter.login', 'uses' => 'MobileDokterController@login']);
    Route::post('/mobile/dokter/login/{id}', ['as' => 'mobile.dokter.doLogin', 'uses' => 'MobileDokterController@doLogin'])->middleware('throttle:20,1');
    Route::get('/mobile/dokter/diagnosis/{id}', ['as' => 'mobile.dokter.diagnosis', 'uses' => 'MobileDokterController@diagnosis']);
    Route::post('/mobile/dokter/store-diagnosis/{id}', ['as' => 'mobile.dokter.storeDiagnosis', 'uses' => 'MobileDokterController@storeDiagnosis']);
    Route::get('/mobile/dokter/create-parameter/{id}', ['as' => 'mobile.dokter.create-parameter', 'uses' => 'MobileDokterController@createParameter']);
    Route::post('/mobile/dokter/store-parameter/{id}', ['as' => 'mobile.dokter.storeParameter', 'uses' => 'MobileDokterController@storeParameter']);
    Route::get('/mobile/dokter/validasi/{id}', ['as' => 'mobile.dokter.validasi', 'uses' => 'MobileDokterController@validasi']);
    Route::post('/mobile/dokter/store-validasi/{id}', ['as' => 'mobile.dokter.storeValidasi', 'uses' => 'MobileDokterController@storeValidasi']);
    Route::post('/mobile/dokter/logout', ['as' => 'mobile.dokter.logout', 'uses' => 'MobileDokterController@logout']);

    // Mobile Sampling Klinik Routes
    Route::get('/mobile/sampling-klinik', ['as' => 'mobile.sampling.klinik.home', 'uses' => 'MobileSamplingKlinikController@home']);
    Route::post('/mobile/sampling-klinik/input-id', ['as' => 'mobile.sampling.klinik.inputId', 'uses' => 'MobileSamplingKlinikController@inputId']);
    Route::get('/mobile/sampling-klinik/scan/{id}', ['as' => 'mobile.sampling.klinik.scan', 'uses' => 'MobileSamplingKlinikController@scan']);
    Route::get('/mobile/sampling-klinik/login', ['as' => 'mobile.sampling.klinik.login', 'uses' => 'MobileSamplingKlinikController@login']);
    Route::post('/mobile/sampling-klinik/login', ['as' => 'mobile.sampling.klinik.doLogin', 'uses' => 'MobileSamplingKlinikController@doLogin'])->middleware('throttle:20,1');
    Route::get('/mobile/sampling-klinik/form/{id}', ['as' => 'mobile.sampling.klinik.form', 'uses' => 'MobileSamplingKlinikController@form']);
    Route::get('/mobile/sampling-klinik/form/{id}/{count}', ['as' => 'mobile.sampling.klinik.form.withCount', 'uses' => 'MobileSamplingKlinikController@form']);
    Route::post('/mobile/sampling-klinik/store/{id}', ['as' => 'mobile.sampling.klinik.store', 'uses' => 'MobileSamplingKlinikController@store']);
    Route::post('/mobile/sampling-klinik/save-signature/{id}', ['as' => 'mobile.sampling.klinik.saveSignature', 'uses' => 'MobileSamplingKlinikController@saveSignature']);
    Route::get('/mobile/sampling-klinik/success/{id}', ['as' => 'mobile.sampling.klinik.success', 'uses' => 'MobileSamplingKlinikController@success']);
    Route::post('/mobile/sampling-klinik/mark-done/{id}', ['as' => 'mobile.sampling.klinik.markDone', 'uses' => 'MobileSamplingKlinikController@markAsDone']);
    Route::post('/mobile/sampling-klinik/logout', ['as' => 'mobile.sampling.klinik.logout', 'uses' => 'MobileSamplingKlinikController@logout']);

    // Mobile Signing (Unified: Kesmas & Klinik)
    Route::get('/mobile/signing', ['as' => 'mobile.signing.home', 'uses' => 'MobileSigningController@home']);
    Route::post('/mobile/signing/input-id', ['as' => 'mobile.signing.inputId', 'uses' => 'MobileSigningController@inputId']);
    Route::get('/mobile/signing/scan/{id}', ['as' => 'mobile.signing.scan', 'uses' => 'MobileSigningController@scan']);
    // Klinik
    Route::get('/mobile/signing/klinik/{id}', ['as' => 'mobile.signing.klinik.select', 'uses' => 'MobileSigningController@klinikSelect']);
    Route::get('/mobile/signing/klinik/nota/{id}', ['as' => 'mobile.signing.klinik.nota', 'uses' => 'MobileSigningController@klinikNota']);
    Route::post('/mobile/signing/klinik/nota/{id}/save', ['as' => 'mobile.signing.klinik.nota.save', 'uses' => 'MobileSigningController@saveKlinikNotaSignature']);
    Route::get('/mobile/signing/klinik/consent/{id}', ['as' => 'mobile.signing.klinik.consent', 'uses' => 'MobileSigningController@klinikConsent']);
    Route::post('/mobile/signing/klinik/consent/{id}/save', ['as' => 'mobile.signing.klinik.consent.save', 'uses' => 'MobileSigningController@saveKlinikConsentSignature']);
    // Kesmas
    Route::get('/mobile/signing/kesmas/nota/{id}', ['as' => 'mobile.signing.kesmas.nota', 'uses' => 'MobileSigningController@kesmasNota']);
    Route::post('/mobile/signing/kesmas/nota/{id}/save', ['as' => 'mobile.signing.kesmas.nota.save', 'uses' => 'MobileSigningController@saveKesmasNotaSignature']);


    Route::get('elits-release/print-kimia/{id}/{sample_type_id?}', ['as' => 'elits-release.print-kimia', 'uses' => 'LaboratoriumSampleManagement@printKimia']);
    Route::get('elits-release/print-kimia-2/{id}', ['as' => 'elits-release.print-kimia-2', 'uses' => 'LaboratoriumSampleManagement@printAllMakanMinum']);
    Route::get('elits-release/print-mikro/{id}/{sample_type_id?}/{packet_id?}', ['as' => 'elits-release.print-mikro', 'uses' => 'LaboratoriumSampleManagement@printMikro']);
    Route::get('elits-release/print-mikro-air-bersih-air-minum/{id}', ['as' => 'elits-release.print-mikro-gabungan', 'uses' => 'LaboratoriumSampleManagement@printMikroGabungan']);


    Route::get('elits-release/printLHU/{id}/{idlab}/{ischlor?}', ['as' => 'elits-release.printLHU', 'uses' => 'LaboratoriumSampleManagement@printLHU']);
    Route::get('elits-release/print-inform-concern/{id}/{idlab}/{ischlor?}', ['as' => 'elits-release.print-inform-concern', 'uses' => 'LaboratoriumSampleManagement@printInformConcern']);
    Route::get('elits-release/print-inform-concern-gabungan/{idPermohonanUji}', ['as' => 'elits-release.print-inform-concern-gabungan', 'uses' => 'LaboratoriumSampleManagement@printInformConcernGabungan']);
    Route::get('elits-release/print-inform-concern-gabungan/{idPermohonanUji}/{labType}', ['as' => 'elits-release.print-inform-concern-gabungan-lab', 'uses' => 'LaboratoriumSampleManagement@printInformConcernGabunganByLab']);
    // CETAK LAPORAN HARIAN, MINGGUAN, BULANAN


     // PRINT OUT PERMOHONAN UJI KLINIK (HASIL KLINIK)
     Route::get('print-permohonan-uji-klinik-hasil-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasil']);

     // PREVIEW PDF HASIL KLINIK
     Route::get('preview-pdf-hasil-klinik-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.preview-pdf-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@previewPdfHasil']);

     // Settings Nomor Lab & Spesimen Klinik
     Route::get('klinik-number-settings', ['as' => 'klinik-number-settings.index', 'uses' => 'KlinikNumberSettingsController@index']);
     Route::post('klinik-number-settings/update', ['as' => 'klinik-number-settings.update', 'uses' => 'KlinikNumberSettingsController@update']);

     // Settings nomor sampel & laboratorium Kesmas (form elits-samples/create)
     Route::get('kesmas-sample-number-settings', ['as' => 'kesmas-sample-number-settings.index', 'uses' => 'KesmasSampleNumberSettingsController@index']);
     Route::post('kesmas-sample-number-settings/update', ['as' => 'kesmas-sample-number-settings.update', 'uses' => 'KesmasSampleNumberSettingsController@update']);


     // PRINT OUT PERMOHONAN UJI KLINIK (HASIL RAPID ANTIBODY)
     Route::get('print-permohonan-uji-klinik-hasil-rapid-antibody-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil-rapid-antibody', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasilRapidAntibody']);

     // PRINT OUT PERMOHONAN UJI KLINIK (HASIL RAPID ANTIGEN)
     Route::get('print-permohonan-uji-klinik-hasil-rapid-antigen-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil-rapid-antigen', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasilRapidAntigen']);

     // PRINT OUT PERMOHONAN UJI KLINIK (HASIL PCR)
     Route::get('print-permohonan-uji-klinik-hasil-pcr-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil-pcr', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasilPcr']);

     // PRINT OUT PERMOHONAN UJI KLINIK (QRCODE)
     Route::get('print-permohonan-uji-klinik-qrcode-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-qrcode', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikQrcode']);

  });

  // Sampling Login Routes (no auth required)
  Route::get('/sampling/login', ['as' => 'sampling.login', 'uses' => 'SamplingAuthController@showLogin']);
  Route::post('/sampling/login', ['as' => 'sampling.login.submit', 'uses' => 'SamplingAuthController@login'])->middleware('throttle:20,1');
  Route::post('/sampling/logout', ['as' => 'sampling.logout', 'uses' => 'SamplingAuthController@logout']);

  Route::get('/scan/verification/{id}', ['as' => 'scan.verification', 'uses' => 'ScanController@verification']);

  // scan permohonan uji klinik
  Route::get('/qrcode/permohonan-uji-klinik/{text}/{size?}/{margin?}', [
    'uses' => 'ScanController@makeQrCodePermohonanUjiKlinik',
    'as'   => 'qrcode-permohonan-uji-klinik'
  ]);

  Route::get('/scan/permohonan-uji-klinik/{id}', ['as' => 'scan.permohonan-uji-klinik', 'uses' => 'ScanController@scanPermohonanUjiKlinik']);

  Route::get('/scan/print-permohonan-uji-klinik/{id}', ['as' => 'scan.print-permohonan-uji-klinik', 'uses' => 'ScanController@printPermohonanUjiKlinik']);

  Route::get('/scan/print/{id}/{idlab}', ['as' => 'scan.print', 'uses' => 'ScanController@printLHU']);
  Route::get('/scan/{id}', ['as' => 'scan.index', 'uses' => 'ScanController@index']);

  Route::get('/sm-master', 'AdmHomeController@index')->name('home');
  Route::get('/panel', 'AdmHomeController@index')->name('home');

  Route::get('/cronjob', 'AdmHomeController@index')->name('home');

  Route::get('/image/qrcode/{text}/{size?}/{margin?}', [
    'uses' => 'ImageController@makeQrCode',
    'as'   => 'qrcode'
  ]);




  // administrator route
  Route::group(['middleware' => ['web']], function () {
    Auth::routes(['verify' => true]);
    Route::get('/home', 'AdmHomeController@index')->name('home');
    Route::resource('biodata', 'AdmBiodataController');

    Route::get('adm-password', 'AdmPasswordController@edit')
      ->name('user.adm-password.edit');

    Route::put('adm-password', 'AdmPasswordController@update')
      ->name('user.adm-password.update');

    // Layout
    Route::get('adm-layout/type/{id}', 'AdmLayoutController@index');
    Route::post('adm-layout/store', 'AdmLayoutController@store');
    Route::get('adm-layout/getColumn', 'AdmLayoutController@columnData');
    Route::get('adm-layout/get_option_view', 'AdmLayoutController@getOption');

    // Users
    Route::resource('adm-users', 'UserController');
    Route::get('adm-users/reset/{param}', 'UserController@reset_password');
    // Privileges
    Route::resource('adm-privileges', 'AdmPrivilegesController');

    // Privileges
    Route::resource('privileges-elits', 'AdmPrivilegesElitsController');

    Route::post('privileges-features/store', 'AdmPrivilegesFeaturesController@store');
    Route::post('privileges-features/data', 'AdmPrivilegesFeaturesController@data');

    Route::post('privileges-features/update', ['as' => 'privileges-features.update', 'uses' => 'AdmPrivilegesFeaturesController@update']);
    Route::resource('privileges-features', 'AdmPrivilegesFeaturesController', ['except' => ['store', 'update']]);

    // Client
    Route::resource('adm-client', 'AdmClientController');
    Route::get('adm-client/publish/{id}', 'AdmClientController@publish');

    //Testimoni
    // Route::resource('adm-testimoni', 'AdmTestimoniController');
    // Route::get('adm-testimoni/publish/{id}', 'AdmTesimoniController@publish');

    //Category Portofolio
    Route::resource('adm-categoryportofolio', 'AdmCategoryPortofolioController');

    //Portofolio
    Route::resource('adm-portofolio', 'AdmPortofolioController');
    Route::get('adm-portofolio/publish/{id}', 'AdmPortofolioController@publish');



    Route::get('users/export/', 'UserController@export');
    Route::get('users/previlage/', 'UserController@previlage');



    //Route Admin Menu
    Route::resource('menuadm', 'AdmMenuController');
    Route::get('menuadm/index', 'AdmMenuController@index');
    Route::post('menuadm/sort', 'AdmMenuController@sort');
    Route::post('menuadm/store', 'AdmMenuController@store');
    Route::post('menuadm/data', 'AdmMenuController@data');
    Route::post('menuadm/update', 'AdmMenuController@update');
    Route::get('menuadm/change', 'AdmMenuController@change');
    Route::delete('menuadm/destroy/{id}', 'AdmMenuController@destroy');

    //Route Admin Diawan Menu
    Route::resource('menu-elits', 'AdmMenuElitsController');
    Route::get('menu-elits/index', 'AdmMenuElitsController@index');
    Route::post('menu-elits/sort', 'AdmMenuElitsController@sort');
    Route::post('menu-elits/store', 'AdmMenuElitsController@store');
    Route::post('menu-elits/data', 'AdmMenuElitsController@data');
    Route::post('menu-elits/update', 'AdmMenuElitsController@update');
    Route::get('menu-elits/change', 'AdmMenuElitsController@change');
    Route::delete('menu-elits/destroy/{id}', 'AdmMenuElitsController@destroy');


    //ROUTE MENU PUBLIC
    Route::resource('menu', 'AdmMenuPublicController');
    Route::get('menu/index', 'AdmMenuPublicController@index');
    Route::post('menu/sort', 'AdmMenuPublicController@sort');
    Route::post('menu/store', 'AdmMenuPublicController@store');
    Route::post('menu/data', 'AdmMenuPublicController@data');
    Route::post('menu/update', 'AdmMenuPublicController@update');
    Route::get('menu/change', 'AdmMenuPublicController@change');
    Route::delete('menu/destroy/{id}', 'AdmMenuPublicController@destroy');

    Route::resource('logo', 'AdmOptionsController');
    Route::get('favicon', 'AdmOptionsController@index_favicon');
    Route::put('favicon/{id}', 'AdmOptionsController@update_favicon');
    Route::get('metadata', 'AdmOptionsController@index_metadata');
    Route::put('metadata/{id}', 'AdmOptionsController@update_metadata');

    Route::get('adm-maps', 'AdmOptionsController@index_maps');
    Route::put('adm-maps/{id}', 'AdmOptionsController@update_maps');

    Route::resource('admsosmed', 'AdmSosmedController');

    // Admin
    Route::resource('admslideshow', 'AdmSlideshowController');
    Route::get('admslideshow/publish/{id}', 'AdmSlideshowController@publish');

    //ADMIN CONTENT
    Route::resource('admcontent', 'AdmContentController');

    //Admin Contact
    Route::resource('admcontact', 'AdmContactController');

    //Admin Contact
    Route::resource('admfeedback', 'AdmFeedbackController');

    //Admin Offer (Penawaran)
    Route::resource('admoffer', 'AdmOfferController');
    Route::get('admoffer/publish/{id}', 'AdmOfferController@publish');

    //Admin Contact
    Route::resource('adm-faq', 'AdmFaqController');
    Route::get('adm-faq/publish/{id}', 'AdmFaqController@publish');

    //Admin Layanan
    Route::resource('admlayanan', 'AdmLayananController');
    Route::resource('adm-categorylayanan', 'AdmCategoryLayananController');

    //generator
    // ubah
    Route::get('master/slideshow/{action?}/{id?}', 'MasterController@slideshow');
    Route::get('master/type/{action?}/{id?}', 'MasterController@type');
    //dilarang
    Route::post('master/SMStore', 'CrudController@store');
    Route::post('master/SMUpdate/{id}', 'CrudController@update');
    Route::get('master/SMDelete/{id}/{table}', 'CrudController@destroy');
    //end generator

    //Laboratorium





    Route::get('elits-release/getSamplePagination', ['as' => 'elits-release.getSamplePagination', 'uses' => 'LaboratoriumReleaseManagement@getSamplePagination']);

    Route::get('elits-release/nota/{id}', ['as' => 'elits-release.nota', 'uses' => 'LaboratoriumPermohonanUjiManagement@nota']);
    Route::get('elits-release/nota-gabungan/{idPermohonanUji}', ['as' => 'elits-release.nota-gabungan', 'uses' => 'LaboratoriumPermohonanUjiManagement@notaGabungan']);
    Route::get('elits-release/nota-gabungan-by-lab/{idPermohonanUji}/{labType}', ['as' => 'elits-release.nota-gabungan-by-lab', 'uses' => 'LaboratoriumPermohonanUjiManagement@notaGabunganByLab']);

    // PERSURATAN NOTA
    Route::get('elits-persuratan/nota/kesmas/{id}', ['as' => 'elits-persuratan.nota.kesmas', 'uses' => 'LaboratoriumNotaController@cetakNotaKesmas']);
    Route::get('elits-persuratan/nota/klinik/{id}', ['as' => 'elits-persuratan.nota.klinik', 'uses' => 'LaboratoriumNotaController@cetakNotaKlinik']);
    Route::get('elits-persuratan/invoice/kesmas/{id}', ['as' => 'elits-persuratan.invoice.kesmas', 'uses' => 'LaboratoriumNotaController@cetakInvoiceKesmas']);
    Route::get('elits-persuratan/invoice/klinik/{id}', ['as' => 'elits-persuratan.invoice.klinik', 'uses' => 'LaboratoriumNotaController@cetakInvoiceKlinik']);

    Route::get('elits-release/permintaan-pemeriksaan/{id}', ['as' => 'elits-release.permintaan-pemeriksaan', 'uses' => 'LaboratoriumPermohonanUjiManagement@permintaan_pemeriksaan']);
    Route::get('elits-release/formulir-pengambilan-sampel/{id}', ['as' => 'elits-release.formulir-pengambilan-sampel', 'uses' => 'LaboratoriumPermohonanUjiManagement@formulirPengambilanSampel']);
    Route::get('elits-release/print_verifikasi/{id}/{idlab}', ['as' => 'elits-release.print_verifikasi', 'uses' => 'LaboratoriumSampleManagement@print_verifikasi']);
    // Route::get('elits-release/sort-labnum/{idLab}/{plusCount}', ['as' => 'elits-release.print_verifikasi', 'uses' => 'LaboratoriumSampleManagement@sortingNumberBylabAndPlusCount']);

    // CETAK LAPORAN HARIAN, MINGGUAN, BULANAN
    Route::get('report-daily', [
      'as' => 'report-daily.index',
      'uses' => 'LaboratoriumReportManagement@report_daily'
    ]);

    Route::get('report-daily/data-report-daily', [
      'as' => 'report-daily.data-report-daily',
      'uses' => 'LaboratoriumReportManagement@data_report_daily'
    ]);

    Route::get('report-daily/print-report-daily', [
      'as' => 'report-daily.print-report-daily',
      'uses' => 'LaboratoriumReportManagement@printReportDaily'
    ]);

    Route::get('report/daily/{date_from?}/{date_to?}/', ['as' => 'report.daily', 'uses' => 'LaboratoriumReportManagement@daily']);

    Route::get('report-weekly', ['as' => 'report-weekly.index', 'uses' => 'LaboratoriumReportManagement@report_weekly']);
    Route::get('report-weekly/data-report-weekly', ['as' => 'report-weekly.data-report-weekly', 'uses' => 'LaboratoriumReportManagement@data_report_weekly']);
    Route::get('report-weekly/print-report-weekly', ['as' => 'report-weekly.print-report-weekly', 'uses' => 'LaboratoriumReportManagement@printReportWeekly']);
    Route::get('report/weekly/{date_from?}/{date_to?}/', ['as' => 'report.weekly', 'uses' => 'LaboratoriumReportManagement@weekly']);

    Route::get('report-monthly', ['as' => 'report-monthly.index', 'uses' => 'LaboratoriumReportManagement@report_monthly']);
    Route::get('report-monthly/data-report-monthly', ['as' => 'report-monthly.data-report-monthly', 'uses' => 'LaboratoriumReportManagement@data_report_monthly']);
    Route::get('report-monthly/print-report-monthly', ['as' => 'report-monthly.print-report-monthly', 'uses' => 'LaboratoriumReportManagement@printReportMonthly']);
    Route::get('report-monthly/print-report-monthly-excel', ['as' => 'report-monthly.print-report-monthly-excel', 'uses' => 'LaboratoriumReportManagement@printReportMonthly_to_excel']);
    Route::get('report-monthly/print-report-monthly-maatweb', ['as' => 'report-monthly.print-report-monthly-maatweb', 'uses' => 'LaboratoriumReportManagement@printReportMonthly_to_maatweb']);

    Route::get('report-monthly/get-total-harga-sample-monthly', [
      'as' => 'get-total-harga-sample-monthly',
      'uses' => 'LaboratoriumReportManagement@getTotalHargaSampleMonthly'
    ]);

    Route::get('report-date-verification-monthly', ['as' => 'report-date-verification-monthly.index', 'uses' => 'LaboratoriumReportManagement@report_date_verification_monthly']);
    Route::get('report-date-verification-monthly/data-date-verification-report-monthly', ['as' => 'report-date-verification-monthly.data-report-date-verification-monthly', 'uses' => 'LaboratoriumReportManagement@data_report_date_verification_monthly']);
    Route::get('report-date-verification-monthly/print-date-verification-report-monthly', ['as' => 'report-date-verification-monthly.print-report-date-verification-monthly', 'uses' => 'LaboratoriumReportManagement@printReportDateVerificationMonthly']);
    Route::get('report-date-verification-monthly/print-date-verification-report-monthly-excel', ['as' => 'report-date-verification-monthly.print-report-date-verification-monthly-excel', 'uses' => 'LaboratoriumReportManagement@printReportDateVerificationMonthly_to_excel']);
    Route::get('report-date-verification-monthly/print-date-verification-report-monthly-maatweb', ['as' => 'report-date-verification-monthly.print-report-date-verification-monthly-maatweb', 'uses' => 'LaboratoriumReportManagement@printReportDateVerificationMonthly_to_maatweb']);


    Route::get('report-daily/print-report-daily-maatweb', ['as' => 'report-daily.print-report-daily-maatweb', 'uses' => 'LaboratoriumReportManagement@printReportDaily_to_maatweb']);

    Route::get('report-daily/get-total-harga-sample-daily', [
      'as' => 'get-total-harga-sample-daily',
      'uses' => 'LaboratoriumReportManagement@getTotalHargaSampleDaily'
    ]);

    Route::get('report-register-pendaftaran/export', [
      'as' => 'report-register-pendaftaran.export',
      'uses' => 'LaboratoriumRegisterPendaftaranExportController@exportRegister',
    ]);

    Route::get('report-register-pendaftaran-nonklinik/export', [
      'as' => 'report-register-pendaftaran-nonklinik.export',
      'uses' => 'LaboratoriumRegisterPendaftaranExportController@exportExcel',
    ]);

    Route::get('report-register-pendaftaran-klinik/export', [
      'as' => 'report-register-pendaftaran-klinik.export',
      'uses' => 'LaboratoriumRegisterPendaftaranExportController@exportExcelKlinik',
    ]);

    Route::get('report-annual', ['as' => 'report-annual.index', 'uses' => 'LaboratoriumReportManagement@report_annual']);
    Route::get('report-annual/data-report-annual', ['as' => 'report-annual.data-report-annual', 'uses' => 'LaboratoriumReportManagement@data_report_annual']);
    Route::get('report-annual/print-report-annual', ['as' => 'report-annual.print-report-annual', 'uses' => 'LaboratoriumReportManagement@printReportAnnual']);
    Route::get('report-annual/print-report-annual-maatweb', ['as' => 'report-annual.print-report-annual-maatweb', 'uses' => 'LaboratoriumReportManagement@printReportAnually_to_maatweb']);

    Route::get('report-annual/get-total-harga-sample-annual', [
      'as' => 'get-total-harga-sample-annual',
      'uses' => 'LaboratoriumReportManagement@getTotalHargaSampleAnnual'
    ]);

    Route::get('report-annual-clinic', ['as' => 'report-annual-clinic.index', 'uses' => 'ReportManagementController@annualReportClinic']);
    Route::get('report-annual-clinic/export', ['as' => 'report-annual-clinic.export', 'uses' => 'ReportManagementController@exportAnnualClinic']);
    Route::get('report-annual-clinic/paket-settings', ['as' => 'report-annual-clinic.paket-settings', 'uses' => 'ReportManagementController@getPaketLaporanSettings']);
    Route::post('report-annual-clinic/paket-settings', ['as' => 'report-annual-clinic.paket-settings.save', 'uses' => 'ReportManagementController@savePaketLaporanSettings']);
    Route::get('report-annual-clinic-haji', ['as' => 'report-annual-clinic-haji.index', 'uses' => 'ReportManagementController@annualReportClinic']);
    Route::get('report-annual-clinic-haji/export', ['as' => 'report-annual-clinic-haji.export', 'uses' => 'ReportManagementController@exportAnnualClinic']);
    Route::get('report-annual-clinic-haji/paket-settings', ['as' => 'report-annual-clinic-haji.paket-settings', 'uses' => 'ReportManagementController@getPaketLaporanSettings']);
    Route::post('report-annual-clinic-haji/paket-settings', ['as' => 'report-annual-clinic-haji.paket-settings.save', 'uses' => 'ReportManagementController@savePaketLaporanSettings']);

    Route::get('register-result-clinic', ['as' => 'register-result-clinic.index', 'uses' => 'RegisterResultClinicController@index']);
    Route::get('register-result-clinic/export', ['as' => 'register-result-clinic.export', 'uses' => 'RegisterResultClinicController@export']);
    Route::get('register-result-clinic/kolom-settings', ['as' => 'register-result-clinic.kolom-settings', 'uses' => 'RegisterResultClinicController@getKolomSettings']);
    Route::post('register-result-clinic/kolom-settings', ['as' => 'register-result-clinic.kolom-settings.save', 'uses' => 'RegisterResultClinicController@saveKolomSettings']);

    Route::get('monitoring-sampling-penerima', ['as' => 'monitoring-sampling-penerima.index', 'uses' => 'MonitoringSamplingPenerimaController@index']);
    Route::get('monitoring-sampling-penerima/export', ['as' => 'monitoring-sampling-penerima.export', 'uses' => 'MonitoringSamplingPenerimaController@export']);

    Route::get('report-jumlah-jenis-sampel', ['as' => 'report-jumlah-jenis-sampel.index', 'uses' => 'ReportJumlahJenisSampelController@index']);
    Route::get('report-jumlah-jenis-sampel/export', ['as' => 'report-jumlah-jenis-sampel.export', 'uses' => 'ReportJumlahJenisSampelController@export']);

    Route::get('activity-log', ['as' => 'activity-log.index', 'uses' => 'ActivityLogController@index']);
    Route::get('activity-log/{id}', ['as' => 'activity-log.show', 'uses' => 'ActivityLogController@show']);

    Route::get('pengarsipan', ['as' => 'pengarsipan.index', 'uses' => 'PengarsipanController@index']);

    Route::get('pengarsipan-dokumen', ['as' => 'pengarsipan-dokumen.index', 'uses' => 'PengarsipanDokumenController@index']);
    Route::post('pengarsipan-dokumen', ['as' => 'pengarsipan-dokumen.store', 'uses' => 'PengarsipanDokumenController@store']);
    Route::put('pengarsipan-dokumen/{id}/nomor', ['as' => 'pengarsipan-dokumen.nomor', 'uses' => 'PengarsipanDokumenController@updateNomor']);
    Route::get('pengarsipan-dokumen/{id}/download', ['as' => 'pengarsipan-dokumen.download', 'uses' => 'PengarsipanDokumenController@download']);
    Route::delete('pengarsipan-dokumen/{id}', ['as' => 'pengarsipan-dokumen.destroy', 'uses' => 'PengarsipanDokumenController@destroy']);

    Route::get('report/yearly/{date_from?}/{date_to?}/', ['as' => 'report.yearly', 'uses' => 'LaboratoriumReportManagement@yearly']);

    // Route printLHU & print-inform-concern sudah terdaftar di group atas (baris ~131)
    // CETAK LAPORAN HARIAN, MINGGUAN, BULANAN

    // ROUTE MASTER
    Route::resource('elits-release', 'LaboratoriumReleaseManagement');
    Route::resource('elits-sample-officer', 'LaboratoriumSampleOfficerManagement');
    Route::resource('elits-containers', 'LaboratoriumContainerManagement');
    Route::resource('elits-packet', 'LaboratoriumPaketManagement');
    Route::get('elits-packet/{id}/data',           ['as' => 'elits-packet.get-data',    'uses' => 'LaboratoriumPaketManagement@getPacketData']);
    Route::post('elits-packet/ajax/store',          ['as' => 'elits-packet.ajax-store',  'uses' => 'LaboratoriumPaketManagement@storeAjax']);
    Route::post('elits-packet/{id}/ajax/update',    ['as' => 'elits-packet.ajax-update', 'uses' => 'LaboratoriumPaketManagement@updateAjax']);
    Route::resource('elits-jenis-makanan', 'LaboratoriumJenisMakananManagement');



    // ROUTE MASTER PARAMETER JENIS KLINIK
    // Reorder page first to avoid conflict with resource show
    Route::get('elits-parameter-jenis-klinik/reorder', ['as' => 'parameter-jenis-klinik.reorder-page', 'uses' => 'LaboratoriumParameterJenisKlinikManagement@reorderPage']);
    Route::post('elits-parameter-jenis-klinik/reorder', ['as' => 'parameter-jenis-klinik.reorder', 'uses' => 'LaboratoriumParameterJenisKlinikManagement@reorder']);
    Route::post('elits-parameter-jenis-klinik/delete-unused', ['as' => 'parameter-jenis-klinik.delete-unused', 'uses' => 'LaboratoriumParameterJenisKlinikManagement@deleteUnused']);
    Route::resource('elits-parameter-jenis-klinik', 'LaboratoriumParameterJenisKlinikManagement');
    Route::post('elits-parameter-jenis-klinik/getParameterJenisKlinik', 'LaboratoriumParameterJenisKlinikManagement@getParameterJenisKlinik')->name('getParameterJenisKlinik');
    Route::get('elits-parameter-jenis-klinik-destroy/{id}', 'LaboratoriumParameterJenisKlinikManagement@destroy');

    // ROUTE MASTER PARAMETER SATUAN KLINIK
    // Reorder page first to avoid conflict with resource show
    Route::get('elits-parameter-satuan-klinik/reorder', ['as' => 'parameter-satuan-klinik.reorder-page', 'uses' => 'LaboratoriumParameterSatuanKlinikManagement@reorderPage']);
    Route::post('elits-parameter-satuan-klinik/reorder', ['as' => 'parameter-satuan-klinik.reorder', 'uses' => 'LaboratoriumParameterSatuanKlinikManagement@reorder']);
    Route::resource('elits-parameter-satuan-klinik', 'LaboratoriumParameterSatuanKlinikManagement');
    Route::post('elits-parameter-satuan-klinik/getParameterSatuanKlinik', 'LaboratoriumParameterSatuanKlinikManagement@getParameterSatuanKlinik')->name('getParameterSatuanKlinik');
    Route::post('elits-parameter-satuan-klinik/getParameterSatuanKlinikDetail', 'LaboratoriumParameterSatuanKlinikManagement@getParameterSatuanKlinikDetail')->name('getParameterSatuanKlinikDetail');
    Route::get('elits-parameter-satuan-klinik-destroy/{id}', 'LaboratoriumParameterSatuanKlinikManagement@destroy');

    // ROUTE MASTER DEFAULT CATATAN HASIL KLINIK
    // Parameter dipendekkan: Symfony membatasi nama variabel route max 32 karakter
    Route::resource('elits-default-catatan-hasil-klinik', 'LaboratoriumDefaultCatatanHasilKlinikManagement')
        ->parameters(['elits-default-catatan-hasil-klinik' => 'id']);

    // ROUTE MASTER JENIS SAMPEL KLINIK
    Route::resource('elits-jenis-sampel-klinik', 'LaboratoriumJenisSampelKlinikManagement')
        ->parameters(['elits-jenis-sampel-klinik' => 'id']);

    // ROUTE MASTER PARAMETER PAKET KLINIK
    Route::resource('elits-parameter-paket-klinik', 'LaboratoriumParameterPaketKlinikManagement');
    Route::post('elits-parameter-paket-klinik/getParameterPaketKlinik', 'LaboratoriumParameterPaketKlinikManagement@getParameterPaketKlinik')->name('getParameterPaketKlinik');
    Route::post('elits-parameter-paket-klinik-update-sort', 'LaboratoriumParameterPaketKlinikManagement@updateSort');

    // ROUTE CATEGORY LAYOUT
    Route::get('elits-parameter-category-layout', 'LaboratoriumParameterPaketKlinikManagement@categoryLayout')->name('elits-parameter-paket-klinik.categoryLayout');
    Route::post('elits-parameter-category-layout-update', 'LaboratoriumParameterPaketKlinikManagement@updateCategoryLayout')->name('elits-parameter-paket-klinik.updateCategoryLayout');
    Route::post('elits-parameter-category-add', 'LaboratoriumParameterPaketKlinikManagement@addCategory')->name('elits-parameter-paket-klinik.addCategory');
    Route::post('elits-parameter-category-items-update', 'LaboratoriumParameterPaketKlinikManagement@updateCategoryItems')->name('elits-parameter-paket-klinik.updateCategoryItems');
    Route::post('elits-parameter-category-grid-config', 'LaboratoriumParameterPaketKlinikManagement@updateGridConfig')->name('elits-parameter-paket-klinik.updateGridConfig');
    Route::post('elits-parameter-category-grid-position', 'LaboratoriumParameterPaketKlinikManagement@updateItemGridPosition')->name('elits-parameter-paket-klinik.updateItemGridPosition');

    Route::get('elits-parameter-paket-klinik-destroy/{id}', 'LaboratoriumParameterPaketKlinikManagement@destroy');

    // ROUTE MASTER PARAMETER EXTRA PAKET KLINIK
    Route::resource('elits-parameter-paket-extra', 'LaboratoriumParameterPaketExtraKlinikManagement');
    Route::get('elits-parameter-paket-extra-destroy/{id}', 'LaboratoriumParameterPaketExtraKlinikManagement@destroy');

    // ROUTE MASTER PROGRAM
    Route::get('data-program', ['as' => 'elits-program.data-program', 'uses' => 'LaboratoriumProgramManagement@data_program']);
    Route::post('elits-program/get-program', 'LaboratoriumProgramManagement@getProgram')->name('getProgram');
    Route::post('elits-program/get-sampletype', 'LaboratoriumSampleTypeManagement@getSampleType')->name('getSampleType');
    Route::get('elits-program-destroy/{id}', 'LaboratoriumProgramManagement@destroy');
    Route::resource('elits-program', 'LaboratoriumProgramManagement');

    Route::get('elits-permohonan-uji/', ['as' => 'elits-permohonan-uji.index', 'uses' => 'LaboratoriumPermohonanUjiManagement@index']);
    Route::get('elits-permohonan-uji/pagination', ['as' => 'elits-permohonan-uji.pagination', 'uses' => 'LaboratoriumPermohonanUjiManagement@pagination']);

    Route::get('elits-permohonan-uji/analys/{id}/{id_method?}', ['as' => 'elits-permohonan-uji.analys', 'uses' => 'LaboratoriumPermohonanUjiManagement@analys']);

    Route::get('elits-permohonan-uji/getSamplePagination', ['as' => 'elits-permohonan-uji.getSamplePagination', 'uses' => 'LaboratoriumPermohonanUjiManagement@getSamplePagination']);
    // Route::post('elits-permohonan-uji/getSamplePagination', ['as' => 'elits-permohonan-uji.getSamplePagination', 'uses' => 'LaboratoriumPermohonanUjiManagement@getSamplePagination']);

    Route::get('elits-permohonan-uji/getIdSample/{id}', ['as' => 'elits-permohonan-uji.getIdSample', 'uses' => 'LaboratoriumPermohonanUjiManagement@getIdSample']);
    Route::get('elits-permohonan-uji/getPacketDetail/{id}', ['as' => 'elits-permohonan-uji.getPacketDetail', 'uses' => 'LaboratoriumPermohonanUjiManagement@getPacketDetail']);
    Route::post('elits-permohonan-uji/setPersiapanSample/{id}', ['as' => 'elits-permohonan-uji.setPersiapanSample', 'uses' => 'LaboratoriumPermohonanUjiManagement@setPersiapanSample']);
    Route::post('elits-permohonan-uji/setSampling/{id}', ['as' => 'elits-permohonan-uji.setSampling', 'uses' => 'LaboratoriumPermohonanUjiManagement@setSampling']);
    Route::get('elits-permohonan-uji/daftarPengujian/{id}', ['as' => 'elits-permohonan-uji.daftarPengujian', 'uses' => 'LaboratoriumPermohonanUjiManagement@daftarPengujian']);
    Route::get('elits-permohonan-uji/print/{id}', ['as' => 'elits-permohonan-uji.print', 'uses' => 'LaboratoriumPermohonanUjiManagement@print']);
    Route::get('elits-permohonan-uji/print-surat-perintah-sampling/{id}', ['as' => 'elits-permohonan-uji.print-surat-perintah-sampling', 'uses' => 'LaboratoriumPermohonanUjiManagement@printSuratPerintahSampling']);
    Route::post('elits-permohonan-uji/payment/{id}', ['as' => 'elits-permohonan-uji.payment', 'uses' => 'LaboratoriumPermohonanUjiManagement@payment']);
    Route::post('elits-permohonan-uji/edit_payment/{id}', ['as' => 'elits-permohonan-uji.edit_payment', 'uses' => 'LaboratoriumPermohonanUjiManagement@edit_payment']);
    Route::post('elits-permohonan-uji/edit-nota/{id}', ['as' => 'elits-permohonan-uji.edit-nota', 'uses' => 'LaboratoriumPermohonanUjiManagement@editNota']);

    // Input nomor lab Kesmas per (jenis sampel × laboratorium)
    Route::get('elits-permohonan-uji/{id}/nomer-lab', ['as' => 'elits-permohonan-uji.nomer-lab', 'uses' => 'LaboratoriumPermohonanUjiManagement@nomerLabForm']);
    Route::post('elits-permohonan-uji/{id}/nomer-lab', ['as' => 'elits-permohonan-uji.nomer-lab.store', 'uses' => 'LaboratoriumPermohonanUjiManagement@nomerLabStore']);

    // PRINT LABEL PERMOHONAN UJI
    Route::get('elits-label-permohonan-uji/select-samples/{id}', 'LaboratoriumPermohonanUjiManagement@selectSamplesForLabel')->name('elits-label-permohonan-uji.select-samples');
    Route::get('elits-label-permohonan-uji/print', 'LaboratoriumPermohonanUjiManagement@printLabel')->name('elits-label-permohonan-uji.print');



    Route::resource('elits-permohonan-uji', 'LaboratoriumPermohonanUjiManagement');

    Route::get('elits-permohonan-uji/elits-permohonan-uji-destroy/{id}', ['as' => 'elits-permohonan-uji.elits-permohonan-uji-destroy', 'uses' => 'LaboratoriumPermohonanUjiManagement@destroy']);

    //permohonan uji klinik parameter 2
    Route::post('elits-permohonan-uji-klinik-2/get-parameter-custom-permohonan-uji-klinik-parameter', ['as' => 'elits-permohonan-uji-klinik-2.get-parameter-custom-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement2@getDataParameterCustom']);
    Route::get('elits-permohonan-uji-klinik-2/create-permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.create-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement2@create']);
    Route::post('elits-permohonan-uji-klinik-2/store-permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.store-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement2@store']);
    Route::get('elits-permohonan-uji-klinik-2/permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement2@index']);
    Route::get('elits-permohonan-uji-klinik-2/test-bpjs', ['as' => 'elits-permohonan-uji-klinik-2.testBpjs', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@testBpjs']);
    // Sementara dinonaktifkan: pengurutan otomatis nomor sampel/lab
    // Route::get('elits-permohonan-uji-klinik-2/sorting-number-klinik', ['as' => 'elits-permohonan-uji-klinik-2.sortingNumberKlinikAll', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@sortingNumberKlinikAll']);

    // Routes untuk diagnosis dokter
    Route::get('elits-permohonan-uji-klinik-2/diagnosis/{id}', ['as' => 'elits-permohonan-uji-klinik-2.diagnosis', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@diagnosis']);
    Route::post('elits-permohonan-uji-klinik-2/store-diagnosis/{id}', ['as' => 'elits-permohonan-uji-klinik-2.store-diagnosis', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeDiagnosis']);

    // Dashboard analisis hasil klinik per wilayah (dokter / KLI)
    Route::get('klinik/analisis-hasil-wilayah', ['as' => 'klinik.analisis-hasil-wilayah', 'uses' => 'DokterDashboardController@index']);
    Route::get('klinik/analisis-hasil-wilayah/wilayah-options', ['as' => 'klinik.analisis-hasil-wilayah.wilayah-options', 'uses' => 'DokterDashboardController@apiGetWilayahOptions']);
    Route::redirect('dokter/dashboard', '/klinik/analisis-hasil-wilayah', 301);
    Route::redirect('dokter/api/wilayah-options', '/klinik/analisis-hasil-wilayah/wilayah-options', 301);

    Route::get('elits-permohonan-uji-klinik-destroy-2/{id}', 'LaboratoriumPermohonanUjiKlinikManagement2@destroy');
    Route::post('elits-permohonan-uji-klinik-destroy-massal', ['as' => 'elits-permohonan-uji-klinik.destroy-massal', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@destroyMassal']);



    // Route::get('test-bpjs', ['as' => 'elits-permohonan-uji-klinik-2.test-bpjs', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@testBpjs']);

    //verifikasi klinik
    Route::get('elits-permohonan-uji-klinik-2/verification/{id}', ['as' => 'elits-permohonan-uji-klinik-2.verification', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@verification']);
    Route::post('elits-permohonan-uji-klinik-2/verification/analytic/{id}', ['as' => 'elits-permohonan-uji-klinik-2.verification-analytic', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@verificationAnalytic']);
    Route::post('elits-permohonan-uji-klinik-2/verification/save-pengambil-sample-meta/{id}', ['as' => 'elits-permohonan-uji-klinik-2.save-pengambil-sample-meta', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@savePengambilSampleMeta']);
    Route::post('elits-permohonan-uji-klinik-2/verification/resend-hasil-whatsapp/{id}', ['as' => 'elits-permohonan-uji-klinik-2.resend-hasil-whatsapp', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@resendHasilWhatsApp']);
    Route::get('elits-permohonan-uji-klinik-2/print_verifikasi/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print_verifikasi', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@print_verifikasi']);

    //prolanis klinik
    Route::get('elits-permohonan-uji-klinik-2/prolanis', ['as' => 'elits-permohonan-uji-klinik-2.prolanis', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@index']);
    Route::get('elits-permohonan-uji-klinik-2/create-prolanis', ['as' => 'elits-permohonan-uji-klinik-2.create-prolanis', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@create']);
    Route::post('elits-permohonan-uji-klinik-2/store-prolanis', ['as' => 'elits-permohonan-uji-klinik-2.store-prolanis', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@store']);
    Route::get('elits-permohonan-uji-klinik-2/download-format-prolanis/gula/{id}', ['as' => 'elits-permohonan-uji-klinik-2.format-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@downloadFormatProlanisGula']);
    Route::get('elits-permohonan-uji-klinik-2/download-format-prolanis/urine/{id}', ['as' => 'elits-permohonan-uji-klinik-2.format-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@downloadFormatProlanisUrine']);
    Route::post('elits-permohonan-uji-klinik-2/import-prolanis/gula/{id}', ['as' => 'elits-permohonan-uji-klinik-2.importProlanisGula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@importProlanisGula']);
    Route::post('elits-permohonan-uji-klinik-2/import-prolanis/urine/{id}', ['as' => 'elits-permohonan-uji-klinik-2.importProlanisUrine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@importProlanisUrine']);
    Route::get('elits-permohonan-uji-klinik-2/get-prolanis-gula', ['as' => 'elits-permohonan-uji-klinik-2.get-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@getProlanisGula']);
    Route::get('elits-permohonan-uji-klinik-2/get-prolanis-gula', ['as' => 'elits-permohonan-uji-klinik-2.get-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@getProlanisGula']);
    Route::get('elits-permohonan-uji-klinik-2/get-prolanis-urine', ['as' => 'elits-permohonan-uji-klinik-2.get-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@getProlanisUrine']);
    Route::get('elits-permohonan-uji-klinik-2/get-prolanis', ['as' => 'elits-permohonan-uji-klinik-2.get-prolanis', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@getAllProlanis']);
    Route::get('elits-permohonan-uji-klinik-2/destroy-prolanis/{id}', ['as' => 'elits-permohonan-uji-klinik-2.destroy-prolanis', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@destroy']);


    //prolanis gula klinik
    Route::get('elits-permohonan-uji-klinik-2/prolanis-gula', ['as' => 'elits-permohonan-uji-klinik-2.prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisGulaManagement@index']);
    Route::get('elits-permohonan-uji-klinik-2/create-prolanis-gula', ['as' => 'elits-permohonan-uji-klinik-2.create-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisGulaManagement@create']);
    Route::post('elits-permohonan-uji-klinik-2/store-prolanis-gula', ['as' => 'elits-permohonan-uji-klinik-2.store-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisGulaManagement@store']);
    Route::get('elits-permohonan-uji-klinik-2/download-format-prolanis-gula/{id}', ['as' => 'elits-permohonan-uji-klinik-2.download-format-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisGulaManagement@downloadFormatProlanisGula']);
    Route::post('elits-permohonan-uji-klinik-2/import-prolanis-gula/{id}', ['as' => 'elits-permohonan-uji-klinik-2.import-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisGulaManagement@importProlanisGula']);
    // Route::get('elits-permohonan-uji-klinik-2/get-prolanis-gula', ['as' => 'elits-permohonan-uji-klinik-2.get-prolanis-gula', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisGulaManagement@getProlanisGula']);

    //prolanis urine klinik
    Route::get('elits-permohonan-uji-klinik-2/prolanis-urine', ['as' => 'elits-permohonan-uji-klinik-2.prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisUrineManagement@index']);
    Route::get('elits-permohonan-uji-klinik-2/create-prolanis-urine', ['as' => 'elits-permohonan-uji-klinik-2.create-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisUrineManagement@create']);
    Route::post('elits-permohonan-uji-klinik-2/store-prolanis-urine', ['as' => 'elits-permohonan-uji-klinik-2.store-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisUrineManagement@store']);
    Route::get('elits-permohonan-uji-klinik-2/download-format-prolanis-urine/{id}', ['as' => 'elits-permohonan-uji-klinik-2.download-format-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisUrineManagement@downloadFormatProlanisUrine']);
    Route::post('elits-permohonan-uji-klinik-2/import-prolanis-urine/{id}', ['as' => 'elits-permohonan-uji-klinik-2.import-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisUrineManagement@importProlanisUrine']);
    // Route::get('elits-permohonan-uji-klinik-2/get-prolanis-urine', ['as' => 'elits-permohonan-uji-klinik-2.get-prolanis-urine', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisUrineManagement@getProlanisUrine']);

    //haji klinik
    Route::get('elits-permohonan-uji-klinik-2/haji', ['as' => 'elits-permohonan-uji-klinik-2.haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@index']);
    Route::get('elits-permohonan-uji-klinik-2/create-haji', ['as' => 'elits-permohonan-uji-klinik-2.create-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@create']);
    Route::post('elits-permohonan-uji-klinik-2/store-haji', ['as' => 'elits-permohonan-uji-klinik-2.store-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@store']);

    //haji klinik - flow baru tanpa excel
    Route::get('elits-permohonan-uji-klinik-2/haji/create-new', ['as' => 'elits-permohonan-uji-klinik-2.haji.create-new', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@createNew']);
    Route::post('elits-permohonan-uji-klinik-2/haji/store-customer', ['as' => 'elits-permohonan-uji-klinik-2.haji.store-customer', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@storeCustomer']);
    Route::post('elits-permohonan-uji-klinik-2/haji/store-parameter', ['as' => 'elits-permohonan-uji-klinik-2.haji.store-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@storeParameter']);
    Route::post('elits-permohonan-uji-klinik-2/haji/store-pasien', ['as' => 'elits-permohonan-uji-klinik-2.haji.store-pasien', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@storePasien']);
    Route::post('elits-permohonan-uji-klinik-2/haji/get-customer-detail', ['as' => 'elits-permohonan-uji-klinik-2.haji.get-customer-detail', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@getCustomerDetail']);
    Route::post('elits-permohonan-uji-klinik-2/haji/get-pasien-detail', ['as' => 'elits-permohonan-uji-klinik-2.haji.get-pasien-detail', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@getPasienDetail']);
    Route::get('elits-permohonan-uji-klinik-2/download-format-haji/{id}', ['as' => 'elits-permohonan-uji-klinik-2.download-format-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@downloadFormatHaji']);
    Route::post('elits-permohonan-uji-klinik-2/import-haji/{id}', ['as' => 'elits-permohonan-uji-klinik-2.import-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@importHaji']);
    Route::get('elits-permohonan-uji-klinik-2/get-haji', ['as' => 'elits-permohonan-uji-klinik-2.get-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@getHaji']);
    Route::get('elits-permohonan-uji-klinik-2/haji/hasil-ids/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.hasil-ids', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@getHasilIdsByHaji']);
    Route::get('elits-permohonan-uji-klinik-2/haji/print-massal-hasil/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.print-massal-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@printMassalHasil']);
    Route::get('elits-permohonan-uji-klinik-2/haji/daftar-pasien/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.daftar-pasien', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@daftarPasien']);
    Route::get('elits-permohonan-uji-klinik-2/haji/riwayat-nomor/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.riwayat-nomor', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@riwayatNomor']);
    Route::post('elits-permohonan-uji-klinik-2/haji/create-penerima-sampel-massal/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.create-penerima-sampel-massal', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@createPenerimaSampelMassal']);
    Route::put('elits-permohonan-uji-klinik-2/haji/store-penerima-sampel-massal/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.store-penerima-sampel-massal', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@storePenerimaSampelMassal']);
    Route::post('elits-permohonan-uji-klinik-2/haji/create-pengolah-sampel-massal/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.create-pengolah-sampel-massal', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@createPengolahSampelMassal']);
    Route::put('elits-permohonan-uji-klinik-2/haji/store-pengolah-sampel-massal/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.store-pengolah-sampel-massal', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@storePengolahSampelMassal']);
    Route::get('elits-permohonan-uji-klinik-2/haji/tambah-pasien/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.tambah-pasien', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@tambahPasien']);
    Route::get('elits-permohonan-uji-klinik-2/haji/edit-pasien/{hajiId}/{permohonanId}', ['as' => 'elits-permohonan-uji-klinik-2.haji.edit-pasien', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@editPasien']);
    Route::put('elits-permohonan-uji-klinik-2/haji/update-pasien/{hajiId}/{permohonanId}', ['as' => 'elits-permohonan-uji-klinik-2.haji.update-pasien', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@updatePasien']);
    Route::get('elits-permohonan-uji-klinik-2/haji/edit-customer-dokter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.edit-customer-dokter', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@editCustomerDokterMassal']);
    Route::put('elits-permohonan-uji-klinik-2/haji/update-customer-dokter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.update-customer-dokter', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@updateCustomerDokterMassal']);
    Route::get('elits-permohonan-uji-klinik-2/haji/cetak-nota/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.cetak-nota', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@cetakNota']);
    Route::get('elits-permohonan-uji-klinik-2/haji/cetak-nota-per-pasien/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.cetak-nota-per-pasien', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@cetakNotaPerPasien']);
    Route::get('elits-permohonan-uji-klinik-2/haji/cetak-rekap/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.cetak-rekap', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@cetakRekap']);
    Route::get('elits-permohonan-uji-klinik-2/haji/export-rekap-haji/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.export-rekap-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@exportRekapHaji']);
    Route::get('elits-permohonan-uji-klinik-2/haji/export-pasien-haji/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.export-pasien-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@exportPasienHaji']);
    Route::get('elits-permohonan-uji-klinik-2/haji/export-rekap-haji-urin-rutin/{id}', ['as' => 'elits-permohonan-uji-klinik-2.haji.export-rekap-haji-urin-rutin', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@exportRekapHajiUrinRutin']);
    Route::get('elits-permohonan-uji-klinik-2/print-amplop/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-amplop', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@printAmplop']);
    Route::get('elits-permohonan-uji-klinik-2/print-amplop-prolanis/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-amplop-prolanis', 'uses' => 'LaboratoriumPermohonanUjiKlinikProlanisManagement@printAmplopProlanis']);
    Route::get('elits-permohonan-uji-klinik-2/destroy-haji/{id}', ['as' => 'elits-permohonan-uji-klinik-2.destroy-haji', 'uses' => 'LaboratoriumPermohonanUjiKlinikHajiManagement@destroy']);


    //permohonan uji klinik 2
    Route::get('elits-permohonan-uji-klinik-2/bukti-daftar-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.bukti-daftar-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@buktiDaftarPermohonanUjiParameter']);
    Route::get('data-permohonan-uji-klinik-2', ['as' => 'elits-permohonan-uji-klinik-2.data-permohonan-uji-klinik', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@data_permohonan_uji_klinik']);

    // Routes untuk registrasi (khusus untuk dokter melihat yang doctor_type = 'lab')
    Route::get('elits-permohonan-uji-klinik/registrasi', ['as' => 'elits-permohonan-uji-klinik.registrasi', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@indexRegistrasi']);
    Route::get('data-permohonan-uji-klinik-registrasi', ['as' => 'elits-permohonan-uji-klinik.data-permohonan-uji-klinik-registrasi', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@data_permohonan_uji_klinik_registrasi']);

    // Routes untuk verifikasi lists (done_register = true)
    Route::get('elits-permohonan-uji-klinik/verifikasi/lists', ['as' => 'elits-permohonan-uji-klinik.verifikasi-lists', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@indexVerifikasi']);
    Route::get('data-permohonan-uji-klinik-verifikasi', ['as' => 'elits-permohonan-uji-klinik.data-permohonan-uji-klinik-verifikasi', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@data_permohonan_uji_klinik_verifikasi']);
    Route::get('statistics-permohonan-uji-klinik-verifikasi', ['as' => 'elits-permohonan-uji-klinik.statistics-verifikasi', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getStatisticsVerifikasi']);
    Route::get('notifications/feed', ['as' => 'notifications.feed', 'uses' => 'NotificationController@index']);
    Route::get('notifications', ['as' => 'notifications.index', 'uses' => 'NotificationController@page']);
    Route::post('notifications/read-all', ['as' => 'notifications.read-all', 'uses' => 'NotificationController@markAllRead']);
    Route::post('notifications/{id}/read', ['as' => 'notifications.read', 'uses' => 'NotificationController@markRead']);
    Route::get('elits-permohonan-uji-klinik-2/bukti-daftar-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.bukti-daftar-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@buktiDaftarPermohonanUjiParameter']);
    Route::post('elits-permohonan-uji-klinik-2/storeDataPermohonanUjiKlinikPayment/{id}', ['as' => 'elits-permohonan-uji-klinik-2.storeDataPermohonanUjiKlinikPayment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeDataPermohonanUjiKlinikPayment']);
    Route::post('elits-permohonan-uji-klinik-2/updateDataPermohonanUjiKlinikPayment/{id}', ['as' => 'elits-permohonan-uji-klinik-2.updateDataPermohonanUjiKlinikPayment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@updateDataPermohonanUjiKlinikPayment']);
    Route::get('elits-permohonan-uji-klinik-2/add-parameter/', ['as' => 'elits-permohonan-uji-klinik-2.add-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@addParameter']);
    Route::post('elits-permohonan-uji-klinik-2/store-parameter', ['as' => 'elits-permohonan-uji-klinik-2.store-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeParameter']);
    Route::get('data-permohonan-uji-klinik-2', ['as' => 'elits-permohonan-uji-klinik-2.data-permohonan-uji-klinik', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@data_permohonan_uji_klinik']);
    // Override index route untuk menggunakan indexRegistrasi (sama seperti elits-permohonan-uji-klinik/registrasi)
    Route::get('elits-permohonan-uji-klinik-2', ['as' => 'elits-permohonan-uji-klinik-2.index', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@indexRegistrasi']);
    // Route data untuk elits-permohonan-uji-klinik-2 menggunakan method yang sama dengan registrasi
    Route::get('data-permohonan-uji-klinik-2-registrasi', ['as' => 'elits-permohonan-uji-klinik-2.data-permohonan-uji-klinik-registrasi', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@data_permohonan_uji_klinik_registrasi']);
    Route::resource('elits-permohonan-uji-klinik-2', 'LaboratoriumPermohonanUjiKlinikManagement2', ['except' => ['index']]);
    Route::get('list-pasien-satu-sehat', ['as' => 'get-list-pasien-satu-sehat', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getPatients']);
    Route::get('list-pasien-silaboy', ['as' => 'get-list-pasien-silaboy', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getPatientsSilaboy']);

    // UNTUK SAMPLEPERMOHONAN UJI KLINIK
    Route::get('elits-permohonan-uji-klinik-2/create-permohonan-uji-sample/{id_puk}/{count?}', ['as' => 'elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@createPermohonanUjiSample']);
    Route::put('elits-permohonan-uji-klinik-2/store-permohonan-uji-sample/{id_puk}/{count?}', ['as' => 'elits-permohonan-uji-klinik-2.store-permohonan-uji-sample', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storePermohonanUjiSample']);

    // UNTUK PENERIMA SAMPEL PERMOHONAN UJI KLINIK
    Route::get('elits-permohonan-uji-klinik-2/create-penerima-sampel/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.create-penerima-sampel', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@createPenerimaSampel']);
    Route::put('elits-permohonan-uji-klinik-2/store-penerima-sampel/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.store-penerima-sampel', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storePenerimaSampel']);

  // SIGNATURE Pengambil Sample
  Route::post('elits-permohonan-uji-klinik-2/pengambil-sample/signatures/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.save-signature-pengambil-sample', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@saveSignaturePengambilSample']);
  Route::get('elits-permohonan-uji-klinik-2/pengambil-sample/check-signature/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.check-signature-status', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@checkSignatureStatus']);

    // UNTUK ANALIS PERMOHONAN UJI KLINIK
    Route::get('elits-permohonan-uji-klinik-2/create-permohonan-uji-analis2/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.create-permohonan-uji-analis2', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@createPermohonanUjiAnalis']);
    Route::get('elits-permohonan-uji-klinik-2/fetch-tms-results/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.fetch-tms-results', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@fetchTmsResults']);
    Route::get('elits-permohonan-uji-klinik-2/tms-order-form/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.tms-order-form', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@tmsOrderForm']);
    Route::get('elits-permohonan-uji-klinik-2/list-tms-orders/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.list-tms-orders', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@listTmsOrders']);
    Route::post('elits-permohonan-uji-klinik-2/store-tms-order/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.store-tms-order', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeTmsOrder']);
    Route::get('elits-permohonan-uji-klinik-2/tms-mass-order-candidates/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.tms-mass-order-candidates', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@listTmsMassOrderCandidates']);
    Route::post('elits-permohonan-uji-klinik-2/store-tms-mass-order/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.store-tms-mass-order', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeTmsMassOrder']);
    Route::post('elits-permohonan-uji-klinik-2/update-tms-order/{id_order}', ['as' => 'elits-permohonan-uji-klinik-2.update-tms-order', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@updateTmsOrder']);
    Route::post('elits-permohonan-uji-klinik-2/delete-tms-order/{id_order}', ['as' => 'elits-permohonan-uji-klinik-2.delete-tms-order', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@deleteTmsOrder']);
    Route::post('elits-permohonan-uji-klinik-2/sync-tms-order/{id_order}', ['as' => 'elits-permohonan-uji-klinik-2.sync-tms-order', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@syncTmsOrder']);
    Route::get('elits-permohonan-uji-klinik-2/tms-mqtt-listen/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.tms-mqtt-listen', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@listenTmsMqtt']);
    Route::post('elits-permohonan-uji-klinik-2/republish-tms-mqtt/{id_order}', ['as' => 'elits-permohonan-uji-klinik-2.republish-tms-mqtt', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@republishTmsMqttOrder']);
    Route::post('elits-permohonan-uji-klinik-2/resend-tms-mqtt-result/{id_order}', ['as' => 'elits-permohonan-uji-klinik-2.resend-tms-mqtt-result', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@resendTmsMqttResult']);
    Route::get('elits-permohonan-uji-klinik-2/verification-permohonan-uji-paramater-klinik/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.verification-permohonan-uji-paramater-klinik', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@createVerificationPermohonanUjiParamaterKlinik']);
    Route::put('elits-permohonan-uji-klinik-2/store-verification-permohonan-uji-paramater-klinik/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.store-verification-permohonan-uji-paramater-klinik', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeVerificationPermohonanUjiParamaterKlinik']);
    Route::get('elits-permohonan-uji-klinik-2/disabled-permohonan-uji-analis2/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.disabled-permohonan-uji-analis2', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@disabledPermohonanUjiAnalis']);
    Route::put('elits-permohonan-uji-klinik-2/store-permohonan-uji-analis2/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.store-permohonan-uji-analis2', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storePermohonanUjiAnalis']);
    Route::post('elits-permohonan-uji-klinik-2/update-noregister/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.update-noregister', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@updateNoregister']);
    Route::post('elits-permohonan-uji-klinik-2/save-fontsize-hasil/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.save-fontsize-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@saveFontsizeHasil']);
    Route::put('elits-permohonan-uji-klinik-2/update-kesimpulan-hasil/{id_puk}', ['as' => 'elits-permohonan-uji-klinik-2.update-kesimpulan-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@updateKesimpulanHasil']);

    // Parameter History Routes
    Route::post('elits-permohonan-uji-klinik-2/save-parameter-history/{parameter_id}', ['as' => 'elits-permohonan-uji-klinik-2.save-parameter-history', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@saveParameterHistory']);
    Route::get('elits-permohonan-uji-klinik-2/get-parameter-history/{parameter_id}', ['as' => 'elits-permohonan-uji-klinik-2.get-parameter-history', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getParameterHistory']);
    Route::post('elits-permohonan-uji-klinik-2/select-parameter-history/{parameter_id}', ['as' => 'elits-permohonan-uji-klinik-2.select-parameter-history', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@selectParameterHistory']);


    // UNTUK DOKTER PERMOHONAN UJI KLINIK
    Route::get('elits-permohonan-uji-klinik-2/create-permohonan-uji-rekomendasi-dokter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.create-permohonan-uji-rekomendasi-dokter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@createPermohonanUjiRekomendasiDokter']);
    Route::post('elits-permohonan-uji-klinik-2/store-permohonan-uji-rekomendasi-dokter/{id}', ['as' => 'elits-permohonan-uji-klinik-2.store-permohonan-uji-rekomendasi-dokter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storePermohonanUjiRekomendasiDokter']);


    // PRINT KARTU MEDIS
    Route::get('print-permohonan-uji-klinik-kartu-medis-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-kartu-medis', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikKartuMedis']);

    // PRINT OUT PERMOHONAN UJI KLINIK (NOTA)
    Route::get('print-permohonan-uji-klinik-nota-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-nota', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikNota']);

    // PELUNASAN PEMBAYARAN PERMOHONAN UJI KLINIK
    Route::post('permohonan-uji-klinik-get-payment', ['as' => 'permohonan-uji-klinik-get-payment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getDataPermohonanUjiKlinikPayment']);
    Route::post('permohonan-uji-klinik-store-payment', ['as' => 'permohonan-uji-klinik-store-payment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeDataPermohonanUjiKlinikPayment']);

    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL KLINIK)
    Route::get('print-permohonan-uji-klinik-hasil-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasil']);

    // PREVIEW PDF HASIL KLINIK
    Route::get('preview-pdf-hasil-klinik-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.preview-pdf-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@previewPdfHasil']);


    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL RAPID ANTIBODY)
    Route::get('print-permohonan-uji-klinik-hasil-rapid-antibody-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil-rapid-antibody', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasilRapidAntibody']);

    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL RAPID ANTIGEN)
    Route::get('print-permohonan-uji-klinik-hasil-rapid-antigen-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil-rapid-antigen', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasilRapidAntigen']);

    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL PCR)
    Route::get('print-permohonan-uji-klinik-hasil-pcr-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil-pcr', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikHasilPcr']);

    // PRINT OUT PERMOHONAN UJI KLINIK (QRCODE)
    Route::get('print-permohonan-uji-klinik-qrcode-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-qrcode', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikQrcode']);

    // PRINT LABEL PERMOHONAN UJI KLINIK
    Route::get('elits-label-permohonan-uji-klinik-2', 'LaboratoriumPermohonanUjiKlinikManagement2@label');
    Route::get('elits-label-permohonan-uji-klinik-2/print', 'LaboratoriumPermohonanUjiKlinikManagement2@printLabel')->name('elits-permohonan-uji-klinik-2.print-label');


    Route::get('elits-permohonan-uji-klinik/', ['as' => 'elits-permohonan-uji-klinik.index', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@index']);

    Route::get('elits-permohonan-uji-klinik/analys/{id}/{id_method?}', ['as' => 'elits-permohonan-uji-klinik.analys', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@analys']);

    Route::get('elits-permohonan-uji-klinik/getSamplePagination', ['as' => 'elits-permohonan-uji-klinik.getSamplePagination', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getSamplePagination']);
    Route::post('elits-permohonan-uji-klinik/getSamplePagination', ['as' => 'elits-permohonan-uji-klinik.getSamplePagination', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getSamplePagination']);

    Route::get('elits-permohonan-uji-klinik/getIdSample/{id}', ['as' => 'elits-permohonan-uji-klinik.getIdSample', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getIdSample']);
    Route::get('elits-permohonan-uji-klinik/getPacketDetail/{id}', ['as' => 'elits-permohonan-uji-klinik.getPacketDetail', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getPacketDetail']);
    Route::post('elits-permohonan-uji-klinik/setPersiapanSample/{id}', ['as' => 'elits-permohonan-uji-klinik.setPersiapanSample', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@setPersiapanSample']);
    Route::post('elits-permohonan-uji-klinik/setSampling/{id}', ['as' => 'elits-permohonan-uji-klinik.setSampling', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@setSampling']);
    Route::get('elits-permohonan-uji-klinik/daftarPengujian/{id}', ['as' => 'elits-permohonan-uji-klinik.daftarPengujian', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@daftarPengujian']);
    Route::get('elits-permohonan-uji-klinik/print/{id}', ['as' => 'elits-permohonan-uji-klinik.print', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@print']);
    Route::post('elits-permohonan-uji-klinik/payment/{id}', ['as' => 'elits-permohonan-uji-klinik.payment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@payment']);

    // GET URL KETIKA USER TAMBAH/MENGUBAH PARAMETER DARI PERMOHONAN UJI KLINIK
    // #1 attempt
    /* Route::get('elits-permohonan-uji-klinik/create-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.create-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@createPermohonanUjiParameter']);

    Route::get('elits-permohonan-uji-klinik/bukti-daftar-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@buktiDaftarPermohonanUjiParameter']);

    Route::post('elits-permohonan-uji-klinik/get-parameter-dan-harga', ['as' => 'elits-permohonan-uji-klinik.get-parameter-dan-harga', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getParameterDanHarga']);
    Route::post('elits-permohonan-uji-klinik/count-parameter-dan-harga', ['as' => 'elits-permohonan-uji-klinik.count-parameter-dan-harga', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@countParameterDanHarga']);

    Route::post('elits-permohonan-uji-klinik/store-permohonan-uji-parameter', ['as' => 'elits-permohonan-uji-klinik.store-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@storePermohonanUjiParameter']);
    Route::put('elits-permohonan-uji-klinik/update-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.update-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@updatePermohonanUjiParameter']); */

    #2 attempt
    Route::get('elits-permohonan-uji-klinik/create-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.create-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@createPermohonanUjiParameter']);

    Route::get('elits-permohonan-uji-klinik/bukti-daftar-permohonan-uji-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@buktiDaftarPermohonanUjiParameter']);

    Route::post('elits-permohonan-uji-klinik/get-parameter-dan-harga', ['as' => 'elits-permohonan-uji-klinik.get-parameter-dan-harga', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getParameterDanHarga']);
    Route::post('elits-permohonan-uji-klinik/count-parameter-dan-harga', ['as' => 'elits-permohonan-uji-klinik.count-parameter-dan-harga', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@countParameterDanHarga']);

    Route::post('elits-permohonan-uji-klinik/store-permohonan-uji-parameter', ['as' => 'elits-permohonan-uji-klinik.store-permohonan-uji-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@storePermohonanUjiParameter']);

    Route::get('elits-permohonan-uji-klinik/permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@index']);

    Route::get('elits-permohonan-uji-klinik/create-permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.create-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@create']);
    Route::post('elits-permohonan-uji-klinik/store-permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-klinik.store-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@store']);

    Route::get('elits-permohonan-uji-klinik/show-permohonan-uji-klinik-parameter/{id}/{id_paket}', ['as' => 'elits-permohonan-uji-klinik.show-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@show']);

    Route::get('elits-permohonan-uji-klinik/edit-permohonan-uji-klinik-parameter/{id}/{id_paket}', ['as' => 'elits-permohonan-uji-klinik.edit-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@edit']);
    Route::post('elits-permohonan-uji-klinik/update-permohonan-uji-klinik-parameter/{id}/{id_paket}', ['as' => 'elits-permohonan-uji-klinik.update-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@update']);

    Route::get('elits-permohonan-uji/destroy-permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji.destroy-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@destroy']);

    Route::get('elits-permohonan-uji-2/destroy-permohonan-uji-klinik-parameter/{id}', ['as' => 'elits-permohonan-uji-2.destroy-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement2@destroy']);



    // get parameter by jenis parameter dan jenis paket
    Route::post('elits-permohonan-uji-klinik/get-parameter-custom-permohonan-uji-klinik-parameter', ['as' => 'elits-permohonan-uji-klinik.get-parameter-custom-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@getDataParameterCustom']);
    Route::post('elits-permohonan-uji-klinik/get-parameter-paket-permohonan-uji-klinik-parameter', ['as' => 'elits-permohonan-uji-klinik.get-parameter-paket-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@getDataParameterPaket']);
    Route::post('elits-permohonan-uji-klinik/get-harga-total-permohonan-uji-klinik-parameter', ['as' => 'elits-permohonan-uji-klinik.get-harga-total-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement@getCountHargaTotal']);
    Route::post('elits-permohonan-uji-klinik-2/get-harga-total-permohonan-uji-klinik-parameter', ['as' => 'elits-permohonan-uji-klinik-2.get-harga-total-permohonan-uji-klinik-parameter', 'uses' => 'LaboratoriumPermohonanUjiKlinikParameterManagement2@getCountHargaTotal']);

    // UNTUK ANALIS PERMOHONAN UJI KLINIK
    Route::get('elits-permohonan-uji-klinik/create-permohonan-uji-analis/{id_puk}', ['as' => 'elits-permohonan-uji-klinik.create-permohonan-uji-analis', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@createPermohonanUjiAnalis']);
    Route::put('elits-permohonan-uji-klinik/store-permohonan-uji-analis/{id_puk}', ['as' => 'elits-permohonan-uji-klinik.store-permohonan-uji-analis', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@storePermohonanUjiAnalis']);

    // UNTUK DOKTER PERMOHONAN UJI KLINIK
    Route::get('elits-permohonan-uji-klinik/create-permohonan-uji-rekomendasi-dokter/{id}', ['as' => 'elits-permohonan-uji-klinik.create-permohonan-uji-rekomendasi-dokter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@createPermohonanUjiRekomendasiDokter']);
    Route::post('elits-permohonan-uji-klinik/store-permohonan-uji-rekomendasi-dokter/{id}', ['as' => 'elits-permohonan-uji-klinik.store-permohonan-uji-rekomendasi-dokter', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@storePermohonanUjiRekomendasiDokter']);

    // URL PADA PERMOHONAN UJI KLINIK DARI MENGGUNAKAN RESOURCE SAMPAI CUSTOM ROUTES
    Route::get('data-permohonan-uji-klinik', ['as' => 'elits-permohonan-uji-klinik.data-permohonan-uji-klinik', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@data_permohonan_uji_klinik']);
    Route::get('elits-permohonan-uji-klinik-destroy/{id}', 'LaboratoriumPermohonanUjiKlinikManagement@destroy');


    Route::resource('elits-permohonan-uji-klinik', 'LaboratoriumPermohonanUjiKlinikManagement');




    // PRINT FORMULIR PENDAFTARAN
    Route::get('print-permohonan-uji-klinik-formulir/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-formulir', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikFormulir']);

    // PRINT FORMULIR PENDAFTARAN2
    Route::get('print-permohonan-uji-klinik-formulir-2/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-formulir', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printPermohonanUjiKlinikFormulir']);

    // PRINT LEMBAR PERSETUJUAN WA
    Route::get('print-lembar-persetujuan/{id}', ['as' => 'elits-permohonan-uji-klinik-2.print-lembar-persetujuan', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@printLembarPersetujuan']);

    // PRINT BLANGKO PENDAFTARAN (NON KLINIK)
    Route::get('print-permohonan-uji-blangko/{id}', ['as' => 'elits-permohonan-uji.print-permohonan-uji-blangko', 'uses' => 'LaboratoriumPermohonanUjiManagement@printPermohonanUjiBlangko']);

    // PRINT KARTU MEDIS
    Route::get('print-permohonan-uji-klinik-kartu-medis/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-kartu-medis', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikKartuMedis']);

    // PRINT OUT PERMOHONAN UJI KLINIK (NOTA)
    Route::get('print-permohonan-uji-klinik-nota/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-nota', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikNota']);

    // PELUNASAN PEMBAYARAN PERMOHONAN UJI KLINIK
    Route::post('permohonan-uji-klinik-get-payment', ['as' => 'permohonan-uji-klinik-get-payment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@getDataPermohonanUjiKlinikPayment']);
    Route::post('permohonan-uji-klinik-store-payment', ['as' => 'permohonan-uji-klinik-store-payment', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@storeDataPermohonanUjiKlinikPayment']);


    Route::post('permohonan-uji-klinik-get-payment2', ['as' => 'permohonan-uji-klinik-get-payment2', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getDataPermohonanUjiKlinikPayment']);
    Route::post('permohonan-uji-klinik-store-payment2', ['as' => 'permohonan-uji-klinik-store-payment2', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@storeDataPermohonanUjiKlinikPayment']);
    Route::post('permohonan-uji-klinik-payment-detail', ['as' => 'permohonan-uji-klinik-payment-detail', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement2@getPaymentDetail']);


    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL KLINIK)
    Route::get('print-permohonan-uji-klinik-hasil/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikHasil']);

    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL RAPID ANTIBODY)
    Route::get('print-permohonan-uji-klinik-hasil-rapid-antibody/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-rapid-antibody', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikHasilRapidAntibody']);

    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL RAPID ANTIGEN)
    Route::get('print-permohonan-uji-klinik-hasil-rapid-antigen/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-rapid-antigen', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikHasilRapidAntigen']);

    // PRINT OUT PERMOHONAN UJI KLINIK (HASIL PCR)
    Route::get('print-permohonan-uji-klinik-hasil-pcr/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-pcr', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikHasilPcr']);

    // PRINT OUT PERMOHONAN UJI KLINIK (QRCODE)
    Route::get('print-permohonan-uji-klinik-qrcode/{id}', ['as' => 'elits-permohonan-uji-klinik.print-permohonan-uji-klinik-qrcode', 'uses' => 'LaboratoriumPermohonanUjiKlinikManagement@printPermohonanUjiKlinikQrcode']);

    // PRINT LABEL PERMOHONAN UJI KLINIK
    Route::get('elits-label-permohonan-uji-klinik', 'LaboratoriumPermohonanUjiKlinikManagement@label');
    Route::get('elits-label-permohonan-uji-klinik/print', 'LaboratoriumPermohonanUjiKlinikManagement@printLabel')->name('elits-permohonan-uji-klinik.print-label');

    //Delegation Lab
    Route::get('elits-deligations/{id}', ['as' => 'elits-deligations', 'uses' => 'LaboratoriumDelegationManagement@index']);
    Route::post('elits-deligations/save/{id}', ['as' => 'elits-deligations.save', 'uses' => 'LaboratoriumDelegationManagement@save']);
    Route::post('elits-deligations/start/{id}', ['as' => 'elits-deligations.start', 'uses' => 'LaboratoriumDelegationManagement@start']);

    //Delegation Smapling
    Route::get('elits-deligations-sampling/{id}', ['as' => 'elits-deligations-sampling', 'uses' => 'LaboratoriumDelegationSamplingManagement@index']);
    Route::post('elits-deligations-sampling/save/{id}', ['as' => 'elits-deligations-sampling.save', 'uses' => 'LaboratoriumDelegationSamplingManagement@save']);
    Route::post('elits-deligations-sampling/start/{id}', ['as' => 'elits-deligations-sampling.start', 'uses' => 'LaboratoriumDelegationSamplingManagement@start']);

    //Pencahayaan Smapling
    Route::get('elits-pencahayaan/{id}', ['as' => 'elits-pencahayaan', 'uses' => 'LaboratoriumPencahayaanManagement@index']);
    Route::post('elits-pencahayaan/save/{id}', ['as' => 'elits-pencahayaan.save', 'uses' => 'LaboratoriumPencahayaanManagement@save']);
    Route::post('elits-pencahayaan/start/{id}', ['as' => 'elits-pencahayaan.start', 'uses' => 'LaboratoriumPencahayaanManagement@start']);

    Route::get('elits-kebisingan/{id}', ['as' => 'elits-kebisingan', 'uses' => 'LaboratoriumKebisinganManagement@index']);
    Route::post('elits-kebisingan/save/{id}', ['as' => 'elits-kebisingan.save', 'uses' => 'LaboratoriumKebisinganManagement@save']);
    Route::post('elits-kebisingan/start/{id}', ['as' => 'elits-kebisingan.start', 'uses' => 'LaboratoriumKebisinganManagement@start']);





    // Route::get('elits-samples/analys/{id}/{id_method?}', ['as' => 'elits-samples.analys', 'uses' => 'LaboratoriumSampleManagement@analys']);

    Route::get('elits-samples/getSamplePagination', ['as' => 'elits-samples.getSamplePagination', 'uses' => 'LaboratoriumSampleManagement@getSamplePagination']);
    Route::get('elits-samples/getIdSample/{id}', ['as' => 'elits-samples.getIdSample', 'uses' => 'LaboratoriumSampleManagement@getIdSample']);

    Route::get('elits-samples/getNewNumberSequence/{lab_key}/{permohonan_uji_id?}/{is_makmin?}', ['as' => 'elits-samples.getNewNumberSequence', 'uses' => 'LaboratoriumSampleManagement@getNewNumberSequence']);

    Route::get('elits-samples/create/{id}/{id_lab?}', ['as' => 'elits-samples.create', 'uses' => 'LaboratoriumSampleManagement@create']);
    /** Popup edit method — JSON: ambil data untuk form */
    Route::get('elits-samples/method/{id}/data', ['as' => 'elits-samples.method-data', 'uses' => 'LaboratoriumMethodManagement@getMethodData']);
    /** Popup edit method — JSON: simpan perubahan */
    Route::post('elits-samples/method/{id}/update', ['as' => 'elits-samples.method-update', 'uses' => 'LaboratoriumMethodManagement@updateAjax']);
    Route::get('elits-samples/get-current-sequence', ['as' => 'elits-samples.getCurrentSequence', 'uses' => 'LaboratoriumSampleManagement@getCurrentSequenceNumber']);
    Route::post('elits-samples/update/{id}', ['as' => 'elits-samples.update', 'uses' => 'LaboratoriumSampleManagement@update']);
    Route::delete('elits-samples/destroy/{id}', ['as' => 'elits-samples.destroy', 'uses' => 'LaboratoriumSampleManagement@destroy']);

    Route::get('elits-samples/list-samples-mikro/{idPermohonanUji}/{idSampleType}', ['as' => 'elits-samples.list-samples-mikro', 'uses' => 'LaboratoriumSampleManagement@getSamplesByPermohonanUjiAndSampleTypeMikro']);

    Route::get('elits-samples/list-samples-kimia/{idPermohonanUji}/{idSampleType}', ['as' => 'elits-samples.list-samples-kimia', 'uses' => 'LaboratoriumSampleManagement@getSamplesByPermohonanUjiAndSampleTypeKimia']);

    Route::get('elits-samples/list-samples-by-id-sample/{idSample}/{labId}', ['as' => 'elits-samples.list-samples-by-id-sample', 'uses' => 'LaboratoriumSampleManagement@getSamplesMikroBySampleId']);


    Route::get('elits-samples/update-titik/{id}', ['as' => 'elits-samples.update-titik', 'uses' => 'LaboratoriumSampleManagement@updateTitik']);


    Route::get('elits-samples-destroy/{id}', 'LaboratoriumSampleManagement@sample_destroy');

    Route::get('elits-samples/check-petugas/{namaPetugas}', ['as' => 'elits-samples.check-petugas', 'uses' => 'LaboratoriumSampleManagement@checkNikAndPassword']);
    Route::put('elits-samples/update-petugas/{namaPetugas}', ['as' => 'elits-samples.update-petugas', 'uses' => 'LaboratoriumSampleManagement@saveNikAndPassword']);


    Route::post('elits-samples/store/{id}', ['as' => 'elits-samples.store', 'uses' => 'LaboratoriumSampleManagement@store']);
    Route::get('elits-samples/edit/{id}', ['as' => 'elits-samples.edit', 'uses' => 'LaboratoriumSampleManagement@edit']);
    // Store duplicate
    Route::get('elits-samples/duplicate/{data}/{id_lab}', [\Smt\Masterweb\Http\Controllers\LaboratoriumSampleManagement::class, 'storeSampleDuplicate'])->name('elits-samples.store-duplicate');
    // Store multiple duplicate with titik pengambilan
    Route::post('elits-samples/duplicate-multiple', [\Smt\Masterweb\Http\Controllers\LaboratoriumSampleManagement::class, 'storeSampleDuplicateMultiple'])->name('elits-samples.store-duplicate-multiple');

    Route::get('elits-samples/getIdSample/{id}', ['as' => 'elits-samples.getIdSample', 'uses' => 'LaboratoriumSampleManagement@getIdSample']);
    Route::get('elits-samples/verification/{id}/{idlabs?}', ['as' => 'elits-samples.verification', 'uses' => 'LaboratoriumSampleManagement@verification']);

    //Verifikasi 2
    Route::get('elits-samples/verification-2/{id}/{idlabs?}', ['as' => 'elits-samples.verification-2', 'uses' => 'LaboratoriumSampleManagement@verification2']);
    Route::post('elits-samples/verification/analytic/{id_sample}', ['as' => 'elits-samples.verification-analytic-2', 'uses' => 'LaboratoriumSampleManagement@verificationAnalytic']);
    Route::put('elits-samples/verification-2/update-nama-pengambil/{id}', ['as' => 'elits-samples.update-nama-pengambil', 'uses' => 'LaboratoriumSampleManagement@updateNamaPengambil']);

    // Form Penerimaan Sampel Massal
    Route::get('elits-samples/penerimaan-sampel-form/{id_samples}/{id_permohonan_uji}/{idlabs}', ['as' => 'elits-samples.penerimaan-sampel-form', 'uses' => 'LaboratoriumSampleManagement@penerimaanSampelForm']);
    Route::post('elits-samples/penerimaan-sampel-store/{id_samples}/{id_permohonan_uji}/{idlabs}', ['as' => 'elits-samples.penerimaan-sampel-store', 'uses' => 'LaboratoriumSampleManagement@penerimaanSampelStore']);
    Route::get('elits-samples/verifikasi-pemeriksaan-analitik/{id_samples}/{id_permohonan_uji}/{idlabs}', ['as' => 'elits-samples.verifikasi-pemeriksaan-analitik', 'uses' => 'LaboratoriumSampleManagement@verifikasiPemeriksaanAnalitik']);
    Route::get('elits-samples/print-formulir-pengamanan/{id_permohonan_uji}/{idlabs}', ['as' => 'elits-samples.print-formulir-pengamanan', 'uses' => 'LaboratoriumSampleManagement@printFormulirPengamanan']);

    Route::get('elits-samples/print/{id}', ['as' => 'elits-samples.print', 'uses' => 'LaboratoriumSampleManagement@print']);
    // All Samples panel
    Route::get('elits-samples/all', ['as' => 'elits-samples.all', 'uses' => 'LaboratoriumSampleManagement@allSamples']);
    // Route print-kimia dan print-mikro sudah terdaftar di group atas (baris ~125) dengan parameter lengkap

    // Sementara dinonaktifkan: pengurutan otomatis nomor sampel Kesmas
    // Route::get('elits-samples/sorting-all', ['as' => 'elits-samples.sorting-all', 'uses' => 'LaboratoriumSampleManagement@sortingNumberAll']);
    // Route::get('elits-samples/sorting-number-kesmas-by-code', ['as' => 'elits-samples.sorting-number-kesmas-by-code', 'uses' => 'LaboratoriumSampleManagement@sortingNumberKesmasByCodeAll']);



    // Route::get('elits-release/print-mikro/{id}', ['as' => 'elits-release.print-mikro', 'uses' => 'LaboratoriumSampleManagement@printMikro']);


    Route::get('elits-penerimaan-sample/{id}/{idlabs}', ['as' => 'elits-penerimaan-sample.index', 'uses' => 'LaboratoriumPenerimaanSampleManagement@index']);
    Route::get('elits-penerimaan-sample/store/{id}/{idlabs}', ['as' => 'elits-penerimaan-sample.store', 'uses' => 'LaboratoriumPenerimaanSampleManagement@store']);

    Route::get('elits-penanganan-sample/{id}/{idlabs}', ['as' => 'elits-penanganan-sample.index', 'uses' => 'LaboratoriumPenangananSampleManagement@index']);
    Route::get('elits-penanganan-sample/store/{id}/{idlabs?}', ['as' => 'elits-penanganan-sample.store', 'uses' => 'LaboratoriumPenangananSampleManagement@store']);
    Route::get('elits-penanganan-sample/edit/{id}/{idlabs}', ['as' => 'elits-penanganan-sample.edit', 'uses' => 'LaboratoriumPenangananSampleManagement@edit']);


    Route::post('elits-baca-hasil/save/{id}/{idlabs}/{idprogress}', ['as' => 'elits-baca-hasil.save', 'uses' => 'LaboratoriumAnalitikSampleManagement@baca_hasil_save']);
    Route::post('elits-baca-hasil/save-fontsize-hasil/{id}/{idlabs}/{idprogress}', ['as' => 'elits-baca-hasil.save-fontsize-hasil', 'uses' => 'LaboratoriumAnalitikSampleManagement@saveReviewHasilSetting']);
    Route::post('elits-laboratorium/metode-parameter/{method_id}', ['as' => 'elits-laboratorium.update-metode-parameter', 'uses' => 'LaboratoriumAnalitikSampleManagement@updateMetodeParameter']);

    // Edit baku mutu dari halaman baca hasil — harus di ATAS route wildcard {id}/{idlabs}/{idprogress}
    Route::get('elits-baca-hasil/baku-mutu/{id_baku_mutu}/get-data',    ['as' => 'elits-baca-hasil.baku-mutu.get-data',         'uses' => 'LaboratoriumAnalitikSampleManagement@getBakuMutuDataForEdit']);
    Route::post('elits-baca-hasil/baku-mutu/{id_baku_mutu}/update-umum',['as' => 'elits-baca-hasil.baku-mutu.update-umum',      'uses' => 'LaboratoriumAnalitikSampleManagement@updateBakuMutuUmum']);
    Route::post('elits-baca-hasil/baku-mutu/override-sample',           ['as' => 'elits-baca-hasil.baku-mutu.override-sample',  'uses' => 'LaboratoriumAnalitikSampleManagement@upsertBakuMutuSampleOverride']);
    Route::get('elits-baca-hasil/baku-mutu-referensi',                  ['as' => 'elits-baca-hasil.baku-mutu.referensi',       'uses' => 'LaboratoriumAnalitikSampleManagement@getBakuMutuReferensiJenisMakanan']);

    Route::get('elits-baca-hasil/{id}/{idlabs}/{idprogress}', ['as' => 'elits-baca-hasil.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@baca_hasil']);
    Route::post('elits-baca-hasil/{id}/{idlabs}/{idprogress}', ['as' => 'elits-baca-hasil.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@baca_hasil_store']);


    Route::get('elits-inkubasi/{id}/{idlabs}/{idprogress}', ['as' => 'elits-inkubasi.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@inkubasi']);
    Route::post('elits-inkubasi/{id}/{idlabs}/{idprogress}', ['as' => 'elits-inkubasi.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@inkubasi_store']);

    Route::get('elits-pemeriksaan-alat/{id}/{idlabs}/{idprogress}', ['as' => 'elits-pemeriksaan-alat.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@pemeriksaan_alat']);
    Route::post('elits-pemeriksaan-alat/{id}/{idlabs}/{idprogress}', ['as' => 'elits-pemeriksaan-alat.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@pemeriksaan_alat_store']);

    Route::get('elits-persiapan-reagen/{id}/{idlabs}/{idprogress}', ['as' => 'elits-persiapan-reagen.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@persiapan_reagen']);
    Route::post('elits-persiapan-reagen/{id}/{idlabs}/{idprogress}', ['as' => 'elits-persiapan-reagen.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@persiapan_reagen_store']);


    Route::get('elits-preparasi/{id}/{idlabs}/{idprogress}', ['as' => 'elits-preparasi.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@preparasi']);
    Route::post('elits-preparasi/{id}/{idlabs}/{idprogress}', ['as' => 'elits-preparasi.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@preparasi_store']);

    Route::get('elits-pipetase/{id}/{idlabs}/{idprogress}', ['as' => 'elits-pipetase.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@pipetase']);
    Route::post('elits-pipetase/{id}/{idlabs}/{idprogress}', ['as' => 'elits-pipetase.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@pipetase_store']);



    Route::get('elits-pemeriksaan/{id}/{idlabs}/{idprogress}', ['as' => 'elits-pemeriksaan.index', 'uses' => 'LaboratoriumAnalitikSampleManagement@pemeriksaan']);
    Route::post('elits-pemeriksaan/{id}/{idlabs}/{idprogress}', ['as' => 'elits-pemeriksaan.store', 'uses' => 'LaboratoriumAnalitikSampleManagement@pemeriksaan_store']);


    Route::get('elits-samples/{id}', ['as' => 'elits-samples.index', 'uses' => 'LaboratoriumSampleManagement@index']);
    // Route::post('elits-samples/{id}', ['as' => 'elits-samples.index', 'uses' => 'LaboratoriumSampleManagement@index']);

    // Group operations
    Route::get('elits-samples/edit-group/{group_id}', ['as' => 'elits-samples.edit-group', 'uses' => 'LaboratoriumSampleManagement@editGroup']);
    Route::post('elits-samples/duplicate-group/{group_id}', ['as' => 'elits-samples.duplicate-group', 'uses' => 'LaboratoriumSampleManagement@duplicateGroup']);
    Route::get('elits-samples-destroy-group/{group_id}', ['as' => 'elits-samples.destroy-group', 'uses' => 'LaboratoriumSampleManagement@destroyGroup']);

    // Sample Draft Routes (for temporary sample input)
    Route::get('elits-sample-draft/create/{id}', ['as' => 'elits-sample-draft.create', 'uses' => 'LaboratoriumSampleDraftManagement@create']);
    Route::post('elits-sample-draft/store/{id}', ['as' => 'elits-sample-draft.store', 'uses' => 'LaboratoriumSampleDraftManagement@store']);
    Route::get('elits-sample-draft/edit/{id}', ['as' => 'elits-sample-draft.edit', 'uses' => 'LaboratoriumSampleDraftManagement@edit']);
    Route::put('elits-sample-draft/update/{id}', ['as' => 'elits-sample-draft.update', 'uses' => 'LaboratoriumSampleDraftManagement@update']);
    Route::post('elits-sample-draft/duplicate/{id}', ['as' => 'elits-sample-draft.duplicate', 'uses' => 'LaboratoriumSampleDraftManagement@duplicate']);
    Route::post('elits-sample-draft/duplicate-group/{draft_group_id}', ['as' => 'elits-sample-draft.duplicateGroup', 'uses' => 'LaboratoriumSampleDraftManagement@duplicateGroup']);
    Route::get('elits-sample-draft/print-nota/{id}', ['as' => 'elits-sample-draft.print-nota', 'uses' => 'LaboratoriumSampleDraftManagement@printNota']);
    Route::get('elits-sample-draft/{id}', ['as' => 'elits-sample-draft.index', 'uses' => 'LaboratoriumSampleDraftManagement@index']);
    Route::post('elits-sample-draft/confirm/{id}', ['as' => 'elits-sample-draft.confirm', 'uses' => 'LaboratoriumSampleDraftManagement@confirm']);
    Route::post('elits-sample-draft/confirm-all/{id}', ['as' => 'elits-sample-draft.confirmAll', 'uses' => 'LaboratoriumSampleDraftManagement@confirmAll']);
    Route::delete('elits-sample-draft/{id}', ['as' => 'elits-sample-draft.destroy', 'uses' => 'LaboratoriumSampleDraftManagement@destroy']);

    Route::get('elits-pelaporan-hasil/{id}/{idlabs}', ['as' => 'elits-pelaporan-hasil.index', 'uses' => 'LaboratoriumPelaporanHasilManagement@create']);
    Route::post('elits-pelaporan-hasil/{id}/{idlabs}/', ['as' => 'elits-pelaporan-hasil.store', 'uses' => 'LaboratoriumPelaporanHasilManagement@store']);

    Route::get('elits-pengetikan-hasil/{id}/{idlabs}', ['as' => 'elits-pengetikan-hasil.index', 'uses' => 'LaboratoriumPengetikanHasilManagement@create']);
    Route::post('elits-pengetikan-hasil/{id}/{idlabs}/', ['as' => 'elits-pengetikan-hasil.store', 'uses' => 'LaboratoriumPengetikanHasilManagement@store']);

    Route::get('elits-verifikasi-hasil/{id}/{idlabs}', ['as' => 'elits-verifikasi-hasil.index', 'uses' => 'LaboratoriumVerifikasiHasilManagement@create']);
    Route::post('elits-verifikasi-hasil/{id}/{idlabs}/', ['as' => 'elits-verifikasi-hasil.store', 'uses' => 'LaboratoriumVerifikasiHasilManagement@store']);

    Route::get('elits-pengesahan-hasil/{id}/{idlabs}', ['as' => 'elits-pengesahan-hasil.index', 'uses' => 'LaboratoriumPengesahanHasilManagement@create']);
    Route::post('elits-pengesahan-hasil/{id}/{idlabs}/', ['as' => 'elits-pengesahan-hasil.store', 'uses' => 'LaboratoriumPengesahanHasilManagement@store']);

    Route::get('elits-laporan-hasil/{id}/{idlabs}', ['as' => 'elits-laporan-hasil.tampil', 'uses' => 'LaboratoriumPengesahanHasilManagement@tampilLaporan']);
    Route::post('elits-laporan-hasil/save-fontsize-hasil/{id}/{idlabs}', ['as' => 'elits-laporan-hasil.save-fontsize-hasil', 'uses' => 'LaboratoriumPengesahanHasilManagement@saveLaporanHasilSetting']);


    // Route::resource('elits-samples','LaboratoriumSampleManagement');


    Route::resource('elits-rates', 'LaboratoriumTarifManagement');
    Route::resource('elits-input-laboratorium', 'LaboratoriumInputLaboratoriumManagement');

    Route::post('elits-input-laboratorium/get-laboratorium', 'LaboratoriumInputLaboratoriumManagement@getLaboratorium')
      ->name('get-laboratorium');
    Route::post('elits-input-laboratorium/get-laboratorium-non-klinik', 'LaboratoriumInputLaboratoriumManagement@getLaboratoriumNonKlinik')
      ->name('get-laboratorium-non-klinik');

    Route::resource('elits-majors', 'LaboratoriumMajorManagement');
    Route::get('elits-sampletypes', 'LaboratoriumSampleTypeManagement@index')->name('elits-sampletypes.index');
    Route::resource('elits-sampletypes', 'LaboratoriumSampleTypeManagement');


    Route::get('elits-sampletypes-destroy/{id}', 'LaboratoriumSampleTypeManagement@destroy')->name('elits-sampletypes-destroy');
    Route::get('elits-sampletypes/getdetail_sample_type/{id}', ['as' => 'elits-sampletypes.getdetail_sample_type', 'uses' => 'LaboratoriumSampleTypeManagement@getdetail_sample_type']);
    Route::get('elits-sampletypes/getbaku_mutu/{id}', ['as' => 'elits-sampletypes.getbaku_mutu', 'uses' => 'LaboratoriumSampleTypeManagement@getbaku_mutu']);



    // Route Data Matriks Jenis Sarana
    Route::resource('matriks-jenis-sarana', 'LaboratoriumMatriksJenisSaranaManagement');
    Route::post('matriks-jenis-sarana/store', ['as' => 'matriks-jenis-sarana-store', 'uses' => 'LaboratoriumMatriksJenisSaranaManagement@store']);
    Route::get('matriks-jenis-sarana/edit/{id}', ['as' => 'matriks-jenis-sarana-edit', 'uses' => 'LaboratoriumMatriksJenisSaranaManagement@edit']);
    Route::post('matriks-jenis-sarana/update', ['as' => 'matriks-jenis-sarana-update', 'uses' => 'LaboratoriumMatriksJenisSaranaManagement@update']);
    Route::post('matriks-jenis-sarana/get-matriks-jenis-sarana-by-select2', ['as' => 'matriks-jenis-sarana.get-matriks-jenis-sarana-by-select2', 'uses' => 'LaboratoriumMatriksJenisSaranaManagement@getMatriksJenisSaranaSelect2']);
    Route::get('matriks-jenis-sarana/delete/{id}', ['as' => 'matriks-jenis-sarana-delete', 'uses' => 'LaboratoriumMatriksJenisSaranaManagement@destroy']);


    Route::resource('elits-industries', 'LaboratoriumIndustryManagement');
    Route::get('elits-methods/load/{id}/{id_samples}', ['as' => 'elits-methods.load', 'uses' => 'LaboratoriumMethodManagement@load']);
    Route::get('elits-methods/reorder', ['as' => 'elits-methods.reorder-page', 'uses' => 'LaboratoriumMethodManagement@reorderPage']);
    Route::post('elits-methods/reorder', ['as' => 'elits-methods.reorder', 'uses' => 'LaboratoriumMethodManagement@reorder']);
    Route::post('elits-methods/reorder/sync-sample-type', ['as' => 'elits-methods.reorder-sync', 'uses' => 'LaboratoriumMethodManagement@syncOrderFromSampleType']);


    // Route::get('elits-baku-mutu-kimia/create', ['as' => 'elits-baku-mutu-kimia.create', 'uses' => 'LaboratoriumBakuMutuKimiaManagement@create']);
    // Route::post('elits-baku-mutu-kimia/{id}', ['as' => 'elits-baku-mutu-kimia.update', 'uses' => 'LaboratoriumBakuMutuKimiaManagement@update']);

    // ROUTE BAKU MUTU

    Route::get('elits-baku-mutu-kimia-destroy/{id}', ['as' => 'elits-baku-mutu-kimia-destroy', 'uses' => 'LaboratoriumBakuMutuKimiaManagement@destroy']);
    Route::resource('elits-baku-mutu-kimia', 'LaboratoriumBakuMutuKimiaManagement');
    // Route::get('elits-baku-mutu-mikro/create', ['as' => 'elits-baku-mutu-mikro.create', 'uses' => 'LaboratoriumBakuMutuMikroManagement@create']);
    // Route::delete('elits-baku-mutu-mikro/{id}', ['as' => 'elits-baku-mutu-mikro.destroy', 'uses' => 'LaboratoriumBakuMutuMikroManagement@destroy']);

    Route::get('elits-baku-mutu-mikro-destroy/{id}', ['as' => 'elits-baku-mutu-mikro-destroy', 'uses' => 'LaboratoriumBakuMutuMikroManagement@destroy']);
    Route::resource('elits-baku-mutu-mikro', 'LaboratoriumBakuMutuMikroManagement');

    // START ROUTE CUSTOM
    // Baca hasil: fetch baku mutu mikro by jenis makanan
    Route::post('elits-baca-hasil/get-baku-mutu-mikro', ['as' => 'elits-baca-hasil.get-baku-mutu-mikro', 'uses' => 'LaboratoriumAnalitikSampleManagement@getBakuMutuMikroByJenis']);
    // Route resync antara data suspend device di sql dan di firebase
    // route ini menjalankan perintah
    Route::get('resync-format-baku-mutu', [
      'as' => 'resync-format-baku-mutu',
      'uses' => 'LaboratoriumBakuMutuMikroManagement@resyncFormatBakuMutu',
    ]);
    // END ROUTE CUSTOM

    Route::get('elits-baku-mutu-klinik/data-baku-mutu-klinik', ['as' => 'elits-baku-mutu-klinik.data-baku-mutu-klinik', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@data_baku_mutu_klinik']);
    Route::post('elits-baku-mutu-klinik/update-group', ['as' => 'elits-baku-mutu-klinik.update-group', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@updateGroup']);
    Route::post('elits-baku-mutu-klinik/get-satuan', ['as' => 'elits-baku-mutu-klinik.get-satuan', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@getSatuan']);
    Route::post('elits-baku-mutu-klinik/update-satuan', ['as' => 'elits-baku-mutu-klinik.update-satuan', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@updateSatuan']);
    Route::get('elits-baku-mutu-klinik-destroy/{id}', 'LaboratoriumBakuMutuKlinikManagement@destroy');
    Route::post('elits-baku-mutu-klinik/getBakuMutuKlinik', 'LaboratoriumBakuMutuKlinikManagement@getBakuMutuKlinik')->name('getBakuMutuKlinik');
    Route::post('elits-baku-mutu-klinik/checkBakuMutuParameterKlinik', 'LaboratoriumBakuMutuKlinikManagement@checkBakuMutuParameterKlinik')->name('checkBakuMutuParameterKlinik');
    Route::post('elits-baku-mutu-klinik/checkBakuMutuSubParameterSatuan', 'LaboratoriumBakuMutuKlinikManagement@checkBakuMutuSubParameterSatuan')->name('checkBakuMutuSubParameterSatuan');
    Route::post('elits-baku-mutu-klinik/get-group', ['as' => 'elits-baku-mutu-klinik.get-group', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@getGroup']);
    Route::post('elits-baku-mutu-klinik/replace-group', ['as' => 'elits-baku-mutu-klinik.replace-group', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@replaceGroup']);
    Route::get('elits-baku-mutu-klinik/create-haji', ['as' => 'elits-baku-mutu-klinik.create-haji', 'uses' => 'LaboratoriumBakuMutuKlinikManagement@createHaji']);
    Route::resource('elits-baku-mutu-klinik', 'LaboratoriumBakuMutuKlinikManagement');
    //Module

    Route::post('elits-module-methods/formA/{id}/{id_samples}', ['as' => 'elits-module-methods.formA', 'uses' => 'LaboratoriumModuleMethodManagement@formA']);
    Route::post('elits-module-methods/formB/{id}/{id_samples}', ['as' => 'elits-module-methods.formB', 'uses' => 'LaboratoriumModuleMethodManagement@formB']);
    Route::post('elits-module-methods/forC/{id}/{id_samples}', ['as' => 'elits-module-methods.formC', 'uses' => 'LaboratoriumModuleMethodManagement@formC']);

    Route::post('elits-module-methods/formD/{id}/{id_samples}', ['as' => 'elits-module-methods.formD', 'uses' => 'LaboratoriumModuleMethodManagement@formD']);
    Route::post('elits-module-methods/formE/{id}/{id_samples}', ['as' => 'elits-module-methods.formE', 'uses' => 'LaboratoriumModuleMethodManagement@formE']);


    Route::resource('elits-methods', 'LaboratoriumMethodManagement');

    Route::get('elits-analys/klinik', ['as' => 'elits-analys.klinik', 'uses' => 'LaboratoriumAnalysManagement@index_klinik']);

    Route::get('elits-analys/analys/{id}/{id_method?}', ['as' => 'elits-analys.analys', 'uses' => 'LaboratoriumAnalysManagement@analys']);

    Route::get('elits-analys/analys/{id}/{id_method?}', ['as' => 'elits-analys.analys', 'uses' => 'LaboratoriumAnalysManagement@analys']);

    // Route::get('elits-analys/getSamplePagination', ['as' => 'elits-analys.getSamplePagination', 'uses' => 'LaboratoriumAnalysManagement@getSamplePagination']);
    Route::get('elits-analys/getSamplePagination', ['as' => 'elits-analys.getSamplePagination', 'uses' => 'LaboratoriumAnalysManagement@getSamplePagination']);
    Route::get('statistics-analys', ['as' => 'elits-analys.statistics', 'uses' => 'LaboratoriumAnalysManagement@getStatisticsAnalys']);
    Route::get('data-analys', ['as' => 'elits-analys.data-analys', 'uses' => 'LaboratoriumAnalysManagement@data_analys']);




    Route::post('elits-excel/formImports', ['as' => 'elits-excel.formImports', 'uses' => 'LaboratoriumExcelManagement@formImports']);

    Route::get('elits-excel/downloadFormImports/{id_method}', ['as' => 'elits-excel.downloadFormImports', 'uses' => 'LaboratoriumExcelManagement@downloadFormImports']);

    Route::resource('elits-excel', 'LaboratoriumExcelManagement');

    Route::resource('elits-analys', 'LaboratoriumAnalysManagement');

    Route::post('elits-libraries/getLibrary', 'LaboratoriumLibraryManagement@getLibrary')->name('getLibrary');
    Route::resource('elits-libraries', 'LaboratoriumLibraryManagement');

    Route::resource('elits-customers', 'LaboratoriumCustomerManagement');

    Route::post('elits-units/getDataUnitBySelect', 'LaboratoriumUnitManagement@getDataUnitBySelect')->name('getDataUnitBySelect');
    Route::resource('elits-units', 'LaboratoriumUnitManagement');

    Route::resource('elits-products', 'LaboratoriumProductManagement');
    Route::resource('elits-users', 'LaboratoriumUserManagement');
    Route::get('elits-users/reset/{param}', 'LaboratoriumUserManagement@reset_password');
    Route::post('elits-users/get-users-by-select', 'LaboratoriumUserManagement@getUsersBySelect2')->name('get-users-by-select');
    Route::post('elits-users/get-dokter-by-select', 'LaboratoriumUserManagement@getDokterBySelect2')->name('get-dokter-by-select');

    Route::resource('elits-inventories', 'LaboratoriumInventoryManagement');


    //stockopname
    Route::resource('stock-opname', 'StockOpnameController');

    // route adm pasien
    Route::resource('elits-pasien', 'AdmPasienController');
    Route::get('elits-pasien-datatables', ['as' => 'elits-pasien-datatables', 'uses' => 'AdmPasienController@dataPasienDatatables']);
    Route::get('elits-pasien/publish/{id}', 'AdmPasienController@publish');
    Route::get('elits-pasien-destroy/{id}', 'AdmPasienController@destroy');
    Route::post('elits-pasien/get-pasien-by-select', 'AdmPasienController@getPasienBySelect')->name('get-pasien-by-select');
    Route::post('elits-pasien/get-pasien-by-id', 'AdmPasienController@getPasienByID')->name('get-pasien-by-id');

    // route wilayah
    Route::get('get-provinsi', ['as' => 'get-provinsi', 'uses' => 'AdmPasienController@getProvinsi']);
    Route::post('get-kabupaten', ['as' => 'get-kabupaten', 'uses' => 'AdmPasienController@getKabupaten']);
    Route::post('get-kecamatan', ['as' => 'get-kecamatan', 'uses' => 'AdmPasienController@getKecamatan']);
    Route::post('get-desa', ['as' => 'get-desa', 'uses' => 'AdmPasienController@getDesa']);
    Route::get('search-wilayah', ['as' => 'search-wilayah', 'uses' => 'AdmPasienController@searchWilayah']);
    Route::get('get-wilayah-detail', ['as' => 'get-wilayah-detail', 'uses' => 'AdmPasienController@getWilayahDetail']);

    // route rekam medis klinik
    Route::get('elits-rekam-medis', ['as' => 'elits-rekam-medis', 'uses' => 'LaboratoriumRekamMedisKlinikController@index']);
    Route::get('elits-rekam-medis-show/{id}', ['as' => 'elits-rekam-medis-show', 'uses' => 'LaboratoriumRekamMedisKlinikController@show']);
    Route::get('elits-rekam-medis-destroy/{id}', 'LaboratoriumRekamMedisKlinikController@destroy')->name('elits-rekam-medis-destroy');

    // route rekam medis klinik detail
    Route::get('elits-rekam-medis-detail-hasil/{id}', ['as' => 'elits-rekam-medis-detail-hasil', 'uses' => 'LaboratoriumRekamMedisKlinikController@show_detail_hasil']);
    /* Route::get('elits-rekam-medis-detail-show/{id}', ['as' => 'elits-rekam-medis-show', 'uses' => 'LaboratoriumRekamMedisKlinikController@show']); */
    Route::get('elits-rekam-medis-detail-destroy/{id}', 'LaboratoriumRekamMedisKlinikController@destroy_detail')->name('elits-rekam-medis-detail-destroy');

    // route laporan pendapatan
    Route::get('elits-pendapatan-nonklinik', ['as' => 'elits-pendapatan-nonklinik', 'uses' => 'LaboratoriumPendapatanNonklinikController@index']);
    Route::post('elits-pendapatan-nonklinik-count', ['as' => 'elits-pendapatan-nonklinik-count', 'uses' => 'LaboratoriumPendapatanNonklinikController@getCountTotalPendapatan']);
    Route::get('elits-pendapatan-nonklinik-set-print', ['as' => 'elits-pendapatan-nonklinik-set-print', 'uses' => 'LaboratoriumPendapatanNonklinikController@setPrintDataPeriodikNonklinik']);

    Route::get('elits-rekam-medis-detail-destroy/{id}', 'LaboratoriumRekamMedisKlinikController@destroy_detail')->name('elits-rekam-medis-detail-destroy');

    Route::get('elits-pendapatan-klinik', ['as' => 'elits-pendapatan-klinik', 'uses' => 'LaboratoriumPendapatanKlinikController@index']);
    Route::post('elits-pendapatan-klinik-count', ['as' => 'elits-pendapatan-klinik-count', 'uses' => 'LaboratoriumPendapatanKlinikController@getCountTotalPendapatan']);
    Route::get('elits-pendapatan-klinik-set-print', ['as' => 'elits-pendapatan-klinik-set-print', 'uses' => 'LaboratoriumPendapatanKlinikController@setPrintDataPeriodikKlinik']);
    Route::get('bendahara/pembayaran-pemeriksaan', ['as' => 'bendahara.pembayaran-pemeriksaan', 'uses' => 'BendaharaPemeriksaanController@index']);

    /* Proses awal login akan mengarahkan ke default auth controller sebelum dialihkan ke login controller */
    // Route::get('/', 'Auth\AuthController@showLoginForm');
    Route::get('/', 'Auth\LoginController@index');
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login-form');
    Route::post('/', 'Auth\LoginController@login')->name('login')->middleware('throttle:30,1');
    // CAPTCHA image
    Route::get('/captcha', 'CaptchaController@generate')->name('captcha.generate')->middleware('throttle:60,1');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('elits-signature/progress/{id}/{isClinic}', ['as' => 'signature-progress', 'uses' => 'SignatureController@signatureProgress']);
    Route::get('elits-signature-dummy/progress/{id}/{isClinic}', ['as' => 'signature-progress', 'uses' => 'SignatureController@signatureProgressDummy']);

    Route::get('elits-signature/verify', ['as' => 'signature-verify', 'uses' => 'SignatureController@verifySignatureView']);
    Route::post('elits-signature/verify', ['as' => 'signature-verify-post', 'uses' => 'SignatureController@verifySignature']);

    Route::get('elits-petugas/sync-all-names', ['as' => 'adm-petugas-sync-all-names', 'uses' => 'AdmPetugasController@syncAllPractitionerNames']);
    Route::get('elits-petugas/get-roles-by-lab', ['as' => 'adm-petugas-get-roles-by-lab', 'uses' => 'AdmPetugasController@getRolesByLab']);
    Route::get('elits-petugas/add', ['as' => 'adm-petugas-add', 'uses' => 'AdmPetugasController@create']);
    Route::post('elits-petugas', ['as' => 'adm-petugas-store', 'uses' => 'AdmPetugasController@store']);
    Route::get('elits-petugas', ['as' => 'adm-petugas', 'uses' => 'AdmPetugasController@index']);
    Route::get('elits-petugas/{id}', ['as' => 'adm-petugas-edit', 'uses' => 'AdmPetugasController@edit']);
    Route::put('elits-petugas/{id}', ['as' => 'adm-petugas-update', 'uses' => 'AdmPetugasController@update']);
    Route::delete('elits-petugas/{id}', ['as' => 'adm-petugas-delete', 'uses' => 'AdmPetugasController@destroy']);

    // Satu Sehat Location Routes
    Route::get('elits-satusehat-location/add', ['as' => 'adm-satusehat-location-add', 'uses' => 'AdmSatuSehatLocationController@create']);
    Route::post('elits-satusehat-location', ['as' => 'adm-satusehat-location-store', 'uses' => 'AdmSatuSehatLocationController@store']);
    Route::get('elits-satusehat-location', ['as' => 'adm-satusehat-location', 'uses' => 'AdmSatuSehatLocationController@index']);
    Route::get('elits-satusehat-location/{id}', ['as' => 'adm-satusehat-location-edit', 'uses' => 'AdmSatuSehatLocationController@edit']);
    Route::put('elits-satusehat-location/{id}', ['as' => 'adm-satusehat-location-update', 'uses' => 'AdmSatuSehatLocationController@update']);
    Route::delete('elits-satusehat-location/{id}', ['as' => 'adm-satusehat-location-delete', 'uses' => 'AdmSatuSehatLocationController@destroy']);
  });



  //proses contact us
  Route::post('create_contact', 'ProcessController@contact');

  //proses penawaran
  Route::post('create_offer', 'ProcessController@offer');

  //proses penawaran
  Route::post('register_process', 'ProcessController@register');

  //proses penawaran
  Route::get('cronjob/email', 'CronController@email');

  //dinamic
  // Home page

  Route::get('export/{id}/{mount}/{year}', ['as' => 'export.report', 'uses' => 'ExportController@report']);

  // Route::get('/', [
  //     'as'      => 'home',
  //     'uses'    => 'PageController@index'
  // ]);

  // Route::get('/', function () {
  //   return redirect('/login');
  // });

  // Route::get('/register', function () {
  //   return view('masterweb::register');
  // });

  // Catch all page controller (place at the very bottom)
  Route::get('{slug}', [
    'uses' => 'PageController@getPage'
  ])->where('slug', '([A-Za-z0-9\-\/]+)');

  Route::get('{slug}/view/{link}', [
    'uses' => 'PageController@getPage'
  ])->where('slug', '([A-Za-z0-9\-\/]+)');

  //if have category in page
  Route::get('{slug}/cat/{link}', [
    'uses' => 'PageController@getPage'
  ])->where('slug', '([A-Za-z0-9\-\/]+)');
});