@extends('masterweb::template.admin.layout')

@section('title')
  Pembayaran Pemeriksaan Bendahara
@endsection

@section('css')
<style>
  /* Payment Modal Styles - Exactly matching Registrasi Popup */
  #modal-payment .modal-dialog {
    max-width: 600px;
    max-height: calc(100vh - 2rem);
    margin: 1rem auto;
    display: flex;
    flex-direction: column;
  }

  #modal-payment .modal-dialog.modal-dialog-centered {
    align-items: stretch;
    min-height: 0;
  }

  #modal-payment .modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  #modal-payment .modal-content > form {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    max-height: 100%;
    overflow: hidden;
    margin-bottom: 0;
  }

  #modal-payment .modal-header {
    background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 25px 30px;
    border: none;
    flex-shrink: 0;
  }

  #modal-payment .modal-header .modal-title {
    font-size: 22px;
    font-weight: 600;
    display: flex;
    align-items: center;
    color: white;
    margin: 0;
  }

  #modal-payment .modal-header .modal-title i {
    margin-right: 12px;
    font-size: 26px;
  }

  #modal-payment .modal-header .close {
    color: white;
    opacity: 1;
    text-shadow: none;
    font-size: 32px;
    font-weight: 300;
    padding: 0;
    margin: -10px -10px 0 0;
  }

  #modal-payment .modal-body {
    padding: 25px 30px;
    background-color: #f8f9fa;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    flex: 1 1 auto;
    min-height: 0;
  }

  .payment-info-card {
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .payment-info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  }

  .payment-field-group {
    margin-bottom: 0;
  }

  .payment-field-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
  }

  .payment-field-label i {
    margin-right: 8px;
    color: #0b3a5c;
    font-size: 16px;
  }

  .payment-field-value {
    font-size: 16px;
    color: #2c3e50;
    font-weight: 500;
    padding: 12px 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    min-height: 48px;
    display: flex;
    align-items: center;
  }

  .payment-field-value.readonly {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    cursor: default;
  }

  .payment-total-card {
    background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 18px;
    color: white;
    text-align: center;
    box-shadow: 0 4px 15px rgba(11, 58, 92, 0.3);
  }

  .payment-total-label {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
    margin-bottom: 8px;
  }

  .payment-total-amount {
    font-size: 34px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .payment-divider {
    height: 1px;
    background: linear-gradient(to right, transparent, #dee2e6, transparent);
    margin: 18px 0;
  }

  /* Payment Input Field */
  .payment-input-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 2px solid #0b3a5c;
  }

  .payment-input-field {
    position: relative;
  }

  .payment-input-field input {
    width: 100%;
    padding: 12px 20px 12px 55px;
    font-size: 24px;
    font-weight: 600;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s;
    text-align: right;
  }

  .payment-input-field input:focus {
    border-color: #0b3a5c;
    outline: none;
    box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
  }

  .payment-input-prefix {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    font-weight: 600;
    color: #6c757d;
  }

  .payment-change-card {
    background: linear-gradient(135deg, #48c774 0%, #3abb7c 100%);
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 18px;
    color: white;
    text-align: center;
  }

  .payment-change-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
    margin-bottom: 6px;
  }

  .payment-change-amount {
    font-size: 28px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .payment-error-message {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 10px 14px;
    margin-top: 10px;
    color: #856404;
    font-size: 13px;
    display: none;
  }

  .payment-error-message.show {
    display: block;
  }

  .payment-error-message i {
    margin-right: 8px;
  }

  /* Quick Amount Buttons */
  .quick-amount-buttons {
    display: flex;
    gap: 10px;
    margin-top: 14px;
    flex-wrap: wrap;
  }

  .quick-amount-btn {
    flex: 1;
    min-width: 80px;
    padding: 10px 14px;
    background: white;
    border: 2px solid #0b3a5c;
    border-radius: 8px;
    color: #0b3a5c;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .quick-amount-btn:hover {
    background: #0b3a5c;
    color: white;
    transform: translateY(-2px);
  }

  .quick-amount-btn.exact {
    background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
    color: white;
    border: none;
  }

  #modal-payment .modal-footer {
    flex-shrink: 0;
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 18px 30px;
    border-radius: 0 0 15px 15px;
  }

  #modal-payment .modal-footer .btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.2s;
  }

  #modal-payment .modal-footer .btn-secondary {
    background-color: #6c757d;
    border: none;
    color: white;
  }

  #modal-payment .modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  #modal-payment .modal-footer .btn-primary {
    background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
    border: none;
    color: white;
  }

  #modal-payment .modal-footer .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(11, 58, 92, 0.4);
  }

  #modal-payment .modal-footer .btn-primary:disabled {
    background: #cccccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }

  /* Modal Detail Styles */
  #modal-payment-detail .modal-dialog,
  #modalEditNotaKesmas .modal-dialog {
    max-width: 800px;
  }

  #modal-payment-detail .modal-content,
  #modalEditNotaKesmas .modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    overflow: hidden;
  }

  #modal-payment-detail .modal-header,
  #modalEditNotaKesmas .modal-header {
    padding: 20px 30px;
    border-bottom: none;
  }

  #modal-payment-detail .modal-header .modal-title,
  #modalEditNotaKesmas .modal-header .modal-title {
    font-size: 20px;
    font-weight: 600;
    color: white;
    display: flex;
    align-items: center;
    margin: 0;
  }

  #modal-payment-detail .modal-header .close,
  #modalEditNotaKesmas .modal-header .close {
    color: white;
    opacity: 0.9;
    text-shadow: none;
    font-size: 28px;
    padding: 0;
    margin: -10px -10px 0 0;
  }

  #modal-payment-detail .modal-body,
  #modalEditNotaKesmas .modal-body {
    padding: 25px 30px;
    background-color: #f8f9fa;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
  }

  #modal-payment-detail .modal-footer,
  #modalEditNotaKesmas .modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 15px 30px;
    border-radius: 0 0 15px 15px;
  }

  @media (max-width: 576px) {
    #modal-payment .modal-dialog {
      margin: 10px;
    }

    .payment-total-amount {
      font-size: 28px;
    }
  }
</style>
@endsection

@section('content')
  {{-- Inline style backup to guarantee render regardless of @yield placement --}}
  <style>
    /* Payment Modal Styles - Exactly matching Registrasi Popup */
    #modal-payment .modal-dialog {
      max-width: 600px;
      max-height: calc(100vh - 2rem);
      margin: 1rem auto;
      display: flex;
      flex-direction: column;
    }

    #modal-payment .modal-dialog.modal-dialog-centered {
      align-items: stretch;
      min-height: 0;
    }

    #modal-payment .modal-content {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      max-height: calc(100vh - 2rem);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    #modal-payment .modal-content > form {
      display: flex;
      flex-direction: column;
      flex: 1 1 auto;
      min-height: 0;
      max-height: 100%;
      overflow: hidden;
      margin-bottom: 0;
    }

    #modal-payment .modal-header {
      background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
      color: white;
      border-radius: 15px 15px 0 0;
      padding: 25px 30px;
      border: none;
      flex-shrink: 0;
    }

    #modal-payment .modal-header .modal-title {
      font-size: 22px;
      font-weight: 600;
      display: flex;
      align-items: center;
      color: white;
      margin: 0;
    }

    #modal-payment .modal-header .modal-title i {
      margin-right: 12px;
      font-size: 26px;
    }

    #modal-payment .modal-header .close {
      color: white;
      opacity: 1;
      text-shadow: none;
      font-size: 32px;
      font-weight: 300;
      padding: 0;
      margin: -10px -10px 0 0;
    }

    #modal-payment .modal-body {
      padding: 25px 30px;
      background-color: #f8f9fa;
      overflow-y: auto;
      overflow-x: hidden;
      -webkit-overflow-scrolling: touch;
      flex: 1 1 auto;
      min-height: 0;
    }

    .payment-info-card {
      background: white;
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .payment-info-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .payment-field-group {
      margin-bottom: 0;
    }

    .payment-field-label {
      font-size: 12px;
      font-weight: 600;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
    }

    .payment-field-label i {
      margin-right: 8px;
      color: #0b3a5c;
      font-size: 16px;
    }

    .payment-field-value {
      font-size: 16px;
      color: #2c3e50;
      font-weight: 500;
      padding: 12px 15px;
      background-color: #f8f9fa;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      min-height: 48px;
      display: flex;
      align-items: center;
    }

    .payment-field-value.readonly {
      background-color: #f8f9fa;
      border-color: #e9ecef;
      cursor: default;
    }

    .payment-total-card {
      background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
      border-radius: 12px;
      padding: 22px;
      margin-bottom: 18px;
      color: white;
      text-align: center;
      box-shadow: 0 4px 15px rgba(11, 58, 92, 0.3);
    }

    .payment-total-label {
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      opacity: 0.9;
      margin-bottom: 8px;
    }

    .payment-total-amount {
      font-size: 34px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payment-divider {
      height: 1px;
      background: linear-gradient(to right, transparent, #dee2e6, transparent);
      margin: 18px 0;
    }

    /* Payment Input Field */
    .payment-input-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 18px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 2px solid #0b3a5c;
    }

    .payment-input-field {
      position: relative;
    }

    .payment-input-field input {
      width: 100%;
      padding: 12px 20px 12px 55px;
      font-size: 24px;
      font-weight: 600;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      transition: all 0.3s;
      text-align: right;
    }

    .payment-input-field input:focus {
      border-color: #0b3a5c;
      outline: none;
      box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
    }

    .payment-input-prefix {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 20px;
      font-weight: 600;
      color: #6c757d;
    }

    .payment-change-card {
      background: linear-gradient(135deg, #48c774 0%, #3abb7c 100%);
      border-radius: 12px;
      padding: 18px;
      margin-bottom: 18px;
      color: white;
      text-align: center;
    }

    .payment-change-label {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      opacity: 0.9;
      margin-bottom: 6px;
    }

    .payment-change-amount {
      font-size: 28px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payment-error-message {
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 8px;
      padding: 10px 14px;
      margin-top: 10px;
      color: #856404;
      font-size: 13px;
      display: none;
    }

    .payment-error-message.show {
      display: block;
    }

    .payment-error-message i {
      margin-right: 8px;
    }

    /* Quick Amount Buttons */
    .quick-amount-buttons {
      display: flex;
      gap: 10px;
      margin-top: 14px;
      flex-wrap: wrap;
    }

    .quick-amount-btn {
      flex: 1;
      min-width: 80px;
      padding: 10px 14px;
      background: white;
      border: 2px solid #0b3a5c;
      border-radius: 8px;
      color: #0b3a5c;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .quick-amount-btn:hover {
      background: #0b3a5c;
      color: white;
      transform: translateY(-2px);
    }

    .quick-amount-btn.exact {
      background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
      color: white;
      border: none;
    }

    #modal-payment .modal-footer {
      flex-shrink: 0;
      background-color: #f8f9fa;
      border-top: 1px solid #e9ecef;
      padding: 18px 30px;
      border-radius: 0 0 15px 15px;
    }

    #modal-payment .modal-footer .btn {
      padding: 10px 24px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.2s;
    }

    #modal-payment .modal-footer .btn-secondary {
      background-color: #6c757d;
      border: none;
      color: white;
    }

    #modal-payment .modal-footer .btn-secondary:hover {
      background-color: #5a6268;
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    #modal-payment .modal-footer .btn-primary {
      background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
      border: none;
      color: white;
    }

    #modal-payment .modal-footer .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(11, 58, 92, 0.4);
    }

    #modal-payment .modal-footer .btn-primary:disabled {
      background: #cccccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
  </style>

  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home mr-1"></i> Beranda</a></li>
              <li class="breadcrumb-item active" aria-current="page">Pembayaran Pemeriksaan</li>
            </ol>
          </nav>

          <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

          <div class="row mb-3">
            <div class="col-md-3 mb-2">
              <label for="filter-source">Sumber</label>
              <select id="filter-source" class="form-control">
                <option value="all">Semua</option>
                <option value="klinik">Klinik</option>
                <option value="kesmas">Kesmas</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label for="filter-payment-status">Status Pembayaran</label>
              <select id="filter-payment-status" class="form-control">
                <option value="all">Semua</option>
                <option value="belum_lunas">Belum Lunas</option>
                <option value="lunas">Lunas</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label for="filter-date-start">Tanggal Mulai</label>
              <input type="date" id="filter-date-start" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
              <label for="filter-date-end">Tanggal Akhir</label>
              <input type="date" id="filter-date-end" class="form-control">
            </div>
          </div>

          <div class="table-responsive">
            <table id="bendahara-pemeriksaan-table" class="table table-striped w-100">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Sumber</th>
                  <th>No Pemeriksaan</th>
                  <th>Nama</th>
                  <th>Tanggal</th>
                  <th>Nominal</th>
                  <th>Status Pembayaran</th>
                  <th>Cetak Dokumen</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL PAYMENT KLINIK --}}
  <div class="modal fade text-left" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modalPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPaymentLabel">
            <i class="fa fa-cash-register mr-2"></i>
            <span>Konfirmasi Pembayaran Klinik</span>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="form-payment" method="POST">
          @csrf
          <input type="hidden" id="id_permohonan_uji_klinik" name="id_permohonan_uji_klinik">
          <input type="hidden" name="nota_petugas_permohonan_uji_payment_klinik" id="nota_petugas_permohonan_uji_payment_klinik">
          <input type="hidden" name="nota_namapetugas_permohonan_uji_payment_klinik" id="nota_namapetugas_permohonan_uji_payment_klinik">
          <input type="hidden" name="nama_pasien" id="nama_pasien">
          <input type="hidden" name="alamat_pasien" id="alamat_pasien">
          <input type="hidden" name="total_harga" id="total_harga">
          <input type="hidden" name="total_harga_permohonan_uji_payment_klinik" id="total_harga_permohonan_uji_payment_klinik">
          <input type="hidden" name="total_harga_custom" id="total_harga_custom">

          <div class="modal-body">
            <!-- Total Amount Card -->
            <div class="payment-total-card">
              <div class="payment-total-label">
                <i class="fa fa-money-bill-wave mr-1"></i> Total Pembayaran
              </div>
              <div class="payment-total-amount" id="display_total_harga">
                Rp. 0
              </div>
              <div id="partial-payment-section" style="display: none; margin-top: 15px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.3);">
                <div class="row">
                  <div class="col-6 text-left">
                    <small style="opacity: 0.9;"><i class="fa fa-check mr-1"></i>Sudah Dibayar:</small>
                    <div id="display_sudah_dibayar" class="font-weight-bold" style="opacity: 0.95;">Rp. 0</div>
                  </div>
                  <div class="col-6 text-right">
                    <small style="opacity: 0.9;"><i class="fa fa-exclamation-circle mr-1"></i>Sisa Tagihan:</small>
                    <div id="display_sisa_tagihan" class="font-weight-bold" style="opacity: 0.95;">Rp. 0</div>
                  </div>
                </div>
              </div>
              <!-- Biaya Pengambilan Sampel (if any) -->
              <div id="biaya_pengambilan_section" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.3);">
                <div class="row">
                  <div class="col-6 text-left">
                    <small style="opacity: 0.9;"><i class="fa fa-vial mr-1"></i>Biaya Parameter:</small>
                    <div id="display_biaya_parameter" class="font-weight-bold" style="opacity: 0.95;">Rp. 0</div>
                  </div>
                  <div class="col-6 text-right">
                    <small style="opacity: 0.9;"><i class="fa fa-home mr-1"></i>Biaya Pengambilan Sampel:</small>
                    <div id="display_biaya_pengambilan" class="font-weight-bold" style="opacity: 0.95;">Rp. 0</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Patient Information Card -->
            <div class="payment-info-card">
              <div class="payment-field-group">
                <div class="payment-field-label">
                  <i class="fa fa-user"></i> Nama Pasien
                </div>
                <div class="payment-field-value readonly font-weight-bold" id="display_nama_pasien">
                  -
                </div>
              </div>
            </div>

            <!-- Address Card -->
            <div class="payment-info-card">
              <div class="payment-field-group">
                <div class="payment-field-label">
                  <i class="fa fa-map-marker-alt"></i> Alamat Pasien
                </div>
                <div class="payment-field-value readonly" id="display_alamat_pasien" style="min-height: 60px; align-items: flex-start;">
                  -
                </div>
              </div>
            </div>

            <!-- Officer Card -->
            <div class="payment-info-card">
              <div class="payment-field-group">
                <div class="payment-field-label">
                  <i class="fa fa-user-shield"></i> Petugas
                </div>
                <div class="payment-field-value readonly" id="display_petugas">
                  -
                </div>
              </div>
            </div>

            <!-- Detail Pemeriksaan Card -->
            <div class="payment-info-card" id="detail-pemeriksaan-card">
              <div class="payment-field-label mb-2">
                <i class="fa fa-list-alt"></i> Detail Rincian Pemeriksaan
              </div>
              <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th style="width: 120px;">Tipe</th>
                      <th>Nama Pemeriksaan</th>
                      <th style="width: 160px;" class="text-right">Nominal</th>
                    </tr>
                  </thead>
                  <tbody id="payment-items-body">
                    <tr>
                      <td colspan="3" class="text-center text-muted py-3">
                        <i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Payment Input Card -->
            <div class="payment-input-card">
              <div class="payment-field-label mb-2">
                <i class="fa fa-wallet"></i> Nominal Pembayaran
              </div>
              <div class="payment-input-field">
                <span class="payment-input-prefix">Rp</span>
                <input type="text" class="form-control" id="terbayar_permohonan_uji_payment_klinik"
                  name="terbayar_permohonan_uji_payment_klinik" placeholder="0" autocomplete="off">
              </div>
              <div class="payment-error-message" id="payment-error">
                <i class="fa fa-exclamation-triangle"></i>
                <span id="payment-error-text"></span>
              </div>

              <!-- Quick Amount Buttons -->
              <div class="quick-amount-buttons">
                <button type="button" class="quick-amount-btn exact" data-action="exact">
                  <i class="fa fa-check mr-1"></i> Pas
                </button>
                <button type="button" class="quick-amount-btn" data-amount="50000">
                  + 50rb
                </button>
                <button type="button" class="quick-amount-btn" data-amount="100000">
                  + 100rb
                </button>
              </div>
            </div>

            <!-- Change Card -->
            <div class="payment-change-card" id="change-card" style="display: none;">
              <div class="payment-change-label">
                <i class="fa fa-hand-holding-usd mr-2"></i> Kembalian
              </div>
              <div class="payment-change-amount" id="display_kembalian">
                Rp. 0
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <i class="fa fa-times mr-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-primary ml-2" id="btnSavePayment">
              <i class="fa fa-check-circle mr-1"></i> Simpan Pembayaran
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- MODAL PAYMENT DETAIL --}}
  <div class="modal fade" id="modal-payment-detail" tabindex="-1" role="dialog" aria-labelledby="paymentDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #48c774 0%, #3abb7c 100%); color: white;">
          <h5 class="modal-title" id="paymentDetailLabel">
            <i class="fa fa-file-invoice-dollar mr-2"></i> Detail Pembayaran Klinik
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="background-color: #f8f9fa; padding: 25px 30px;">
          <!-- Patient Info Card -->
          <div class="card mb-3" style="border-left: 4px solid #48c774; border-radius: 10px;">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-6">
                  <p class="mb-1 text-muted small font-weight-bold"><i class="fa fa-user mr-1 text-primary"></i> Nama Pasien:</p>
                  <h6 id="detail_nama_pasien" class="font-weight-bold ml-3 mb-0">-</h6>
                </div>
                <div class="col-md-6">
                  <p class="mb-1 text-muted small font-weight-bold"><i class="fa fa-hashtag mr-1 text-primary"></i> No. Register:</p>
                  <h6 id="detail_no_register" class="font-weight-bold ml-3 mb-0">-</h6>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Summary Card -->
          <div class="card mb-3" style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; border-radius: 10px;">
            <div class="card-body p-3">
              <div class="row text-center">
                <div class="col-md-6 border-right border-light">
                  <h6 class="mb-1 text-white-50 small text-uppercase font-weight-bold"><i class="fa fa-file-invoice mr-1"></i> Total Tagihan</h6>
                  <h4 id="detail_total_tagihan" class="mb-0 font-weight-bold">Rp. 0</h4>
                </div>
                <div class="col-md-6">
                  <h6 class="mb-1 text-white-50 small text-uppercase font-weight-bold"><i class="fa fa-money-bill-wave mr-1"></i> Total Terbayar</h6>
                  <h4 id="detail_total_terbayar" class="mb-0 font-weight-bold">Rp. 0</h4>
                </div>
              </div>
            </div>
          </div>

          <!-- Packages/Parameters Card -->
          <div class="card mb-3" style="border-radius: 10px;">
            <div class="card-header bg-white py-2">
              <h6 class="mb-0 font-weight-bold"><i class="fa fa-list-alt mr-2 text-info"></i> Paket & Parameter Pemeriksaan</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th style="width: 120px;">Tipe</th>
                      <th>Nama Pemeriksaan</th>
                      <th style="width: 150px;" class="text-right">Harga</th>
                    </tr>
                  </thead>
                  <tbody id="items-detail-body">
                    <tr><td colspan="3" class="text-center text-muted py-3">Memuat data...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Payment History Card -->
          <div class="card" style="border-radius: 10px;">
            <div class="card-header bg-white py-2">
              <h6 class="mb-0 font-weight-bold"><i class="fa fa-history mr-2 text-success"></i> Riwayat Pembayaran (Nota)</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>No. Nota</th>
                      <th>Tanggal</th>
                      <th class="text-right">Total Tagihan</th>
                      <th class="text-right">Nominal Terbayar</th>
                      <th class="text-right">Kembalian</th>
                      <th>Petugas</th>
                    </tr>
                  </thead>
                  <tbody id="payment-history-body">
                    <tr><td colspan="6" class="text-center text-muted py-3">Memuat data...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fa fa-times mr-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEditNotaKesmas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white;">
          <h5 class="modal-title">
            <i class="fa fa-edit mr-2"></i> Edit Nota Kesmas
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formEditNotaKesmas" method="POST" action="#">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="edit-nota-diterima-dari" class="font-weight-bold text-muted small text-uppercase">Telah Diterima Dari</label>
              <input type="text" class="form-control" name="nota_diterima_dari" id="edit-nota-diterima-dari">
            </div>
            <div class="form-group mb-0">
              <label for="edit-nota-alamat" class="font-weight-bold text-muted small text-uppercase">Alamat</label>
              <textarea class="form-control" name="nota_address_from" id="edit-nota-alamat" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    $(function() {
      var CSRF_TOKEN = $('#csrf-token').val();
      var editNotaUrlBase = @json(url('elits-permohonan-uji/edit-nota'));

      function formatRupiah(angka, prefix) {
        prefix = (prefix !== undefined) ? prefix : 'Rp. ';
        var raw = (angka == null ? '0' : String(angka));
        var number_string = raw.replace(/[^,\d]/g, ''),
          split = number_string.split(','),
          sisa = split[0].length % 3,
          rupiah = split[0].substr(0, sisa),
          ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
          var separator = sisa ? '.' : '';
          rupiah += separator + ribuan.join('.');
        }

        if (rupiah === '') {
          rupiah = '0';
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix + rupiah;
      }

      function formatNumber(input) {
        return String(input == null ? '' : input).replace(/[^0-9]/g, '');
      }

      function parseRupiahToInt(text) {
        var digits = formatNumber(text || '');
        return digits === '' ? 0 : (parseInt(digits, 10) || 0);
      }

      function getEffectivePaymentTotal() {
        var fromHidden = parseInt($('#total_harga').val(), 10) || 0;
        var fromItems = 0;
        $('#payment-items-body tr').each(function() {
          var $priceCell = $(this).find('td').last();
          if ($priceCell.length) {
            fromItems += parseRupiahToInt($priceCell.text());
          }
        });
        var fromDisplay = parseRupiahToInt($('#display_total_harga').text());
        return Math.max(fromHidden, fromItems, fromDisplay);
      }

      function setPaymentTotal(total) {
        total = parseInt(total, 10) || 0;
        $('#total_harga').val(String(total));
        $('[name="total_harga"]').val(String(total));
        $('[name="total_harga_permohonan_uji_payment_klinik"]').val(String(total));
        $('#display_total_harga').text(formatRupiah(total));
      }

      function refreshPaymentButtonState() {
        var totalHarga = getEffectivePaymentTotal();
        if ((parseInt($('#total_harga').val(), 10) || 0) !== totalHarga) {
          setPaymentTotal(totalHarga);
        }

        var terbayarRaw = formatNumber($('#terbayar_permohonan_uji_payment_klinik').val() || '');
        var terbayar = terbayarRaw === '' ? null : (parseInt(terbayarRaw, 10) || 0);
        var kembalian = (terbayar == null ? 0 : terbayar) - totalHarga;

        $('#payment-error').removeClass('show');
        $('#change-card').hide();

        if (totalHarga <= 0) {
          if (terbayar === null) {
            $('#terbayar_permohonan_uji_payment_klinik').val('0');
            terbayar = 0;
          }
          $('#btnSavePayment').prop('disabled', false);
          return;
        }

        if (terbayar === null) {
          $('#btnSavePayment').prop('disabled', true);
          return;
        }

        if (terbayar < totalHarga) {
          $('#payment-error-text').text('Nominal kurang dari total tagihan. Status akan menjadi "Belum Lunas".');
          $('#payment-error').addClass('show');
          $('#btnSavePayment').prop('disabled', false);
        } else if (kembalian > 0) {
          $('#display_kembalian').text(formatRupiah(kembalian));
          $('#change-card').show();
          $('#btnSavePayment').prop('disabled', false);
        } else {
          $('#btnSavePayment').prop('disabled', false);
        }
      }

      $('.quick-amount-btn').on('click', function() {
        var action = $(this).data('action');
        var amount = $(this).data('amount');
        var totalHarga = getEffectivePaymentTotal();
        setPaymentTotal(totalHarga);

        if (action === 'exact') {
          $('#terbayar_permohonan_uji_payment_klinik').val(String(totalHarga));
          refreshPaymentButtonState();
        } else if (amount) {
          var currentVal = parseInt(formatNumber($('#terbayar_permohonan_uji_payment_klinik').val() || '0'), 10) || 0;
          $('#terbayar_permohonan_uji_payment_klinik').val(String(currentVal + amount));
          refreshPaymentButtonState();
        }
      });

      $('#terbayar_permohonan_uji_payment_klinik').on('keyup input', function() {
        var number = formatNumber($(this).val());
        $(this).val(number);
        refreshPaymentButtonState();
      });

      var table = $('#bendahara-pemeriksaan-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: '{{ url()->current() }}',
          type: 'GET',
          data: function(d) {
            d.source_type = $('#filter-source').val();
            d.payment_status = $('#filter-payment-status').val();
            d.date_start = $('#filter-date-start').val();
            d.date_end = $('#filter-date-end').val();
          }
        },
        columns: [
          { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
          { data: 'jenis', name: 'jenis', orderable: false, searchable: false },
          { data: 'nomor', name: 'nomor' },
          { data: 'nama', name: 'nama' },
          { data: 'tanggal', name: 'tanggal' },
          { data: 'nominal', name: 'nominal' },
          { data: 'status_html', name: 'status_html', orderable: false, searchable: false },
          { data: 'dokumen_html', name: 'dokumen_html', orderable: false, searchable: false }
        ]
      });

      $('#filter-source, #filter-payment-status, #filter-date-start, #filter-date-end').on('change', function() {
        table.ajax.reload();
      });

      $('#bendahara-pemeriksaan-table').on('click', '.btn-edit-nota-kesmas', function(e) {
        e.preventDefault();
        var btn = $(this);
        $('#formEditNotaKesmas').attr('action', editNotaUrlBase + '/' + btn.data('id'));
        $('#edit-nota-diterima-dari').val(btn.data('diterima-dari') || '');
        $('#edit-nota-alamat').val(btn.data('alamat') || '');
        $('#modalEditNotaKesmas').modal('show');
      });

      $('#bendahara-pemeriksaan-table').on('click', '.btn-payment', function(e) {
        e.preventDefault();
        var permohonanId = $(this).data('id');

        $('#form-payment')[0].reset();
        $('#payment-error').removeClass('show');
        $('#change-card').hide();
        $('#partial-payment-section').hide();
        $('#biaya_pengambilan_section').hide();
        $('#payment-items-body').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>');
        $('#id_permohonan_uji_klinik').val(permohonanId);

        $.post('{{ route('permohonan-uji-klinik-get-payment2') }}', {
          _token: CSRF_TOKEN,
          permohonan_uji_klinik_id: permohonanId
        }, function(data) {
          $('[name="nota_petugas_permohonan_uji_payment_klinik"]').val(data.nota_petugas || '');
          $('[name="nota_namapetugas_permohonan_uji_payment_klinik"]').val(data.nota_namapetugas || '');
          $('[name="nama_pasien"]').val((data.nama_pasien || '').toUpperCase());
          $('[name="alamat_pasien"]').val(data.alamat_pasien || '');

          $('#display_nama_pasien').text((data.nama_pasien || '-').toUpperCase());
          $('#display_alamat_pasien').text(data.alamat_pasien || '-');
          $('#display_petugas').text(data.nota_namapetugas || '-');

          if (data.biaya_pengambilan_sampel && data.biaya_pengambilan_sampel > 0) {
            $('#display_biaya_parameter').text(formatRupiah(data.total_harga_parameter || 0));
            $('#display_biaya_pengambilan').text(formatRupiah(data.biaya_pengambilan_sampel));
            $('#biaya_pengambilan_section').show();
          } else {
            $('#biaya_pengambilan_section').hide();
          }

          if (data.sudah_dibayar && data.sudah_dibayar > 0) {
            $('#display_sudah_dibayar').text(formatRupiah(data.sudah_dibayar || 0));
            $('#display_sisa_tagihan').text(formatRupiah(data.sisa_tagihan || 0));
            $('#partial-payment-section').show();
          } else {
            $('#partial-payment-section').hide();
          }

          var itemsHtml = '';
          var sumItems = 0;
          if (data.items && data.items.length > 0) {
            $.each(data.items, function(index, item) {
              var typeBadge = '';
              if (item.type === 'Paket Extra') {
                typeBadge = '<span class="badge badge-warning badge-pill"><i class="fa fa-star mr-1"></i>Paket Extra</span>';
              } else if (item.type === 'Paket') {
                typeBadge = '<span class="badge badge-primary badge-pill"><i class="fa fa-box mr-1"></i>Paket</span>';
              } else {
                typeBadge = '<span class="badge badge-info badge-pill"><i class="fa fa-flask mr-1"></i>Parameter</span>';
              }

              var itemHarga = parseInt(item.harga, 10) || 0;
              sumItems += itemHarga;

              itemsHtml += '<tr>';
              itemsHtml += '<td>' + typeBadge + '</td>';
              itemsHtml += '<td>' + (item.name || '-') + '</td>';
              itemsHtml += '<td class="text-right font-weight-bold">' + formatRupiah(itemHarga) + '</td>';
              itemsHtml += '</tr>';
            });
          } else {
            itemsHtml = '<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-info-circle mr-2"></i>Tidak ada rincian pemeriksaan</td></tr>';
          }
          $('#payment-items-body').html(itemsHtml);

          var biayaAmbil = parseInt(data.biaya_pengambilan_sampel, 10) || 0;
          var totalFromApi = parseInt(data.total_harga, 10) || 0;
          var totalHargaNum = Math.max(totalFromApi, sumItems + biayaAmbil);
          setPaymentTotal(totalHargaNum);
          $('[name="total_harga_custom"]').val(formatRupiah(totalHargaNum));

          if (totalHargaNum <= 0) {
            $('#terbayar_permohonan_uji_payment_klinik').val('0');
          } else {
            $('#terbayar_permohonan_uji_payment_klinik').val('');
          }
          refreshPaymentButtonState();

          $('#modal-payment').modal('show');
          setTimeout(function() {
            $('#terbayar_permohonan_uji_payment_klinik').focus();
          }, 300);
        }, 'json').fail(function() {
          if (typeof swal === 'function') {
            swal("Error", "Gagal mengambil data pembayaran dari server!", "error");
          } else {
            alert("Gagal mengambil data pembayaran dari server!");
          }
        });
      });

      $('#form-payment').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSavePayment');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.post('{{ route('permohonan-uji-klinik-store-payment2') }}', $(this).serialize(), function(resp) {
          $btn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Simpan Pembayaran');
          $('#modal-payment').modal('hide');
          if (typeof swal === 'function') {
            swal("Berhasil!", resp.pesan || 'Pembayaran berhasil disimpan.', "success");
          } else {
            alert(resp.pesan || 'Pembayaran berhasil disimpan.');
          }
          table.ajax.reload(null, false);
        }, 'json').fail(function(xhr) {
          $btn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Simpan Pembayaran');
          var msg = (xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal menyimpan pembayaran.';
          if (typeof swal === 'function') {
            swal("Error!", msg, "error");
          } else {
            alert(msg);
          }
        });
      });

      $('#bendahara-pemeriksaan-table').on('click', '.btn-payment-detail', function(e) {
        e.preventDefault();
        var permohonanId = $(this).data('id');

        $('#detail_nama_pasien').text('-');
        $('#detail_no_register').text('-');
        $('#detail_total_tagihan').text('Rp. 0');
        $('#detail_total_terbayar').text('Rp. 0');
        $('#items-detail-body').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>');
        $('#payment-history-body').html('<tr><td colspan="6" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>');

        $('#modal-payment-detail').modal('show');

        $.post('{{ route('permohonan-uji-klinik-payment-detail') }}', {
          _token: CSRF_TOKEN,
          permohonan_uji_klinik_id: permohonanId
        }, function(resp) {
          if (!resp.status || !resp.data) {
            $('#items-detail-body').html('<tr><td colspan="3" class="text-danger text-center">Detail pemeriksaan tidak tersedia.</td></tr>');
            $('#payment-history-body').html('<tr><td colspan="6" class="text-danger text-center">Detail pembayaran tidak tersedia.</td></tr>');
            return;
          }

          var data = resp.data;
          $('#detail_nama_pasien').text((data.nama_pasien || '-').toUpperCase());
          $('#detail_no_register').text(data.no_register || '-');
          $('#detail_total_tagihan').text(data.total_tagihan_formatted || 'Rp. 0');
          $('#detail_total_terbayar').text(data.total_terbayar_formatted || 'Rp. 0');

          var itemsHtml = '';
          if (data.items && data.items.length > 0) {
            $.each(data.items, function(index, item) {
              var typeBadge = '';
              if (item.type === 'Paket Extra') {
                typeBadge = '<span class="badge badge-warning badge-pill"><i class="fa fa-star mr-1"></i>Paket Extra</span>';
              } else if (item.type === 'Paket') {
                typeBadge = '<span class="badge badge-primary badge-pill"><i class="fa fa-box mr-1"></i>Paket</span>';
              } else {
                typeBadge = '<span class="badge badge-info badge-pill"><i class="fa fa-flask mr-1"></i>Parameter</span>';
              }

              itemsHtml += '<tr>';
              itemsHtml += '<td>' + typeBadge + '</td>';
              itemsHtml += '<td>' + (item.name || '-') + '</td>';
              itemsHtml += '<td class="text-right font-weight-bold">' + formatRupiah(item.harga || 0) + '</td>';
              itemsHtml += '</tr>';
            });
          } else {
            itemsHtml = '<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-info-circle mr-2"></i>Belum ada paket/parameter</td></tr>';
          }
          $('#items-detail-body').html(itemsHtml);

          var historyHtml = '';
          if (data.payments && data.payments.length > 0) {
            $.each(data.payments, function(index, payment) {
              var kembalianText = payment.kembalian > 0
                ? '<span class="text-success font-weight-bold">' + formatRupiah(payment.kembalian) + '</span>'
                : '<span class="text-muted">-</span>';

              historyHtml += '<tr>';
              historyHtml += '<td class="font-weight-bold">#' + (payment.no_nota || '-') + '</td>';
              historyHtml += '<td>' + (payment.created_at || '-') + '</td>';
              historyHtml += '<td class="text-right">' + formatRupiah(payment.total_harga || 0) + '</td>';
              historyHtml += '<td class="text-right font-weight-bold text-primary">' + formatRupiah(payment.terbayar || 0) + '</td>';
              historyHtml += '<td class="text-right">' + kembalianText + '</td>';
              historyHtml += '<td>' + (payment.petugas || '-') + '</td>';
              historyHtml += '</tr>';
            });
          } else {
            historyHtml = '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat pembayaran.</td></tr>';
          }
          $('#payment-history-body').html(historyHtml);
        }, 'json').fail(function() {
          $('#items-detail-body').html('<tr><td colspan="3" class="text-danger text-center">Gagal memuat detail pemeriksaan.</td></tr>');
          $('#payment-history-body').html('<tr><td colspan="6" class="text-danger text-center">Gagal memuat riwayat pembayaran.</td></tr>');
        });
      });
    });
  </script>
@endsection
