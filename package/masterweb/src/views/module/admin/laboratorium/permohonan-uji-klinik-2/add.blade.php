@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection


@section('content')
    @php
        $isEdit = $isEdit ?? false;
        $isHajiEdit = $isEdit && (int) (($item->is_haji ?? 0)) === 1;
    @endphp
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <input type="hidden" id="csrf-token-global" value="{{ csrf_token() }}">
    <script src="{{ asset('assets/admin/cdn-local/js/gijgo.min.js') }}" type="text/javascript"></script>
    <link href="{{ asset('assets/admin/cdn-local/css/gijgo.min.css') }}" rel="stylesheet" type="text/css" />


    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <!-- TinyMCE is already loaded in scripts.blade.php, no need to load again -->

    <!-- Flatpickr CSS and JS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>



    <script src="{{ asset('assets/admin/js/bootstrap-birthday.js') }}"></script>
    <style>
        .modal-dialog.custom-width {
            max-width: 80% !important;
            width: 80% !important;
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .single-date-field {
            width: 120px;
        }

        .form-check-input {
            position: relative;
            width: 30px;
            height: 15px;
            -webkit-appearance: none;
            appearance: none;
            background-color: #ccc;
            outline: none;
            cursor: pointer;
            border-radius: 15px;
            transition: background-color 0.3s;
        }

        .form-check-input:checked {
            background-color: #0d8f7f;
        }

        .form-check-input:before {
            content: "";
            position: absolute;
            width: 13px;
            height: 13px;
            background-color: white;
            border-radius: 50%;
            top: 1px;
            left: 1px;
            transition: transform 0.3s;
        }

        .form-check-input:checked:before {
            transform: translateX(15px);
        }

        .form-check-label {
            margin-left: 2px;
            font-size: 16px;
        }

        /* Enhanced Professional UI Styles */
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding: 0 20px;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 35px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: linear-gradient(to right, #e0e0e0 0%, #e0e0e0 100%);
            z-index: 0;
            border-radius: 2px;
        }

        .wizard-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .wizard-step-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: white;
            border: 4px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: 700;
            color: #999;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .wizard-step.active .wizard-step-circle {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border-color: #0b3a5c;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(11, 58, 92, 0.4);
        }

        .wizard-step.completed .wizard-step-circle {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-color: #11998e;
            color: white;
        }

        .wizard-step.completed .wizard-step-circle::after {
            content: '✓';
            position: absolute;
            font-size: 28px;
        }

        .wizard-step-title {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }

        .wizard-step.active .wizard-step-title {
            color: #0b3a5c;
            font-size: 15px;
        }

        .wizard-step.completed .wizard-step-title {
            color: #11998e;
        }

        /* Doctor Type Selector - Enhanced */
        .doctor-type-selector {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 40px 0;
        }

        .doctor-type-card {
            flex: 1;
            max-width: 350px;
            padding: 40px 30px;
            border: 3px solid #e8e8e8;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
        }

        .doctor-type-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #0b3a5c 0%, #0d8f7f 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .doctor-type-card:hover {
            border-color: #0b3a5c;
            box-shadow: 0 10px 30px rgba(11, 58, 92, 0.25);
            transform: translateY(-8px);
        }

        .doctor-type-card:hover::before {
            transform: scaleX(1);
        }

        .doctor-type-card.selected {
            border-color: #0b3a5c;
            background: linear-gradient(135deg, #e7f4f2 0%, #dcefeb 100%);
            box-shadow: 0 10px 35px rgba(11, 58, 92, 0.3);
            transform: translateY(-5px) scale(1.02);
        }

        .doctor-type-card.selected::before {
            transform: scaleX(1);
        }

        .doctor-type-card.selected::after {
            content: '✓';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(17, 153, 142, 0.3);
        }

        .doctor-type-icon {
            font-size: 70px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }

        .doctor-type-card:hover .doctor-type-icon {
            transform: scale(1.1) rotateY(10deg);
        }

        .doctor-type-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #2d3748;
        }

        .doctor-type-description {
            font-size: 14px;
            color: #718096;
            line-height: 1.6;
        }

        .form-section {
            background: white;
            padding: 28px 32px 32px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e2ebe9;
            box-shadow: 0 2px 10px rgba(11, 58, 92, 0.06);
        }

        .form-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, #0b3a5c 0%, #0d8f7f 100%);
            border-image-slice: 1;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 12px;
            font-size: 24px;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-step {
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-step:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-step.btn-primary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border: none;
        }

        .btn-step.btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
        }

        .btn-step.btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
        }

        .btn-step.btn-secondary:hover {
            background: #cbd5e0;
            color: #2d3748;
        }

        .btn-step:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .step-content {
            display: none;
            content-visibility: hidden;
            contain-intrinsic-size: 1px 600px;
        }

        .step-content.active {
            display: block;
            content-visibility: visible;
            contain-intrinsic-size: auto;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .patient-search-buttons {
            display: flex;
            gap: 20px;
            margin: 4px 0 28px;
            flex-wrap: wrap;
        }

        #patient-search-container,
        #patient-detail-display {
            margin-top: 8px;
            padding: 0 2px;
        }

        #patient-search-container .card,
        #patient-detail-display .card {
            margin-left: 0;
            margin-right: 0;
        }

        #patient-search-container .card-body,
        #patient-detail-display .card-body {
            padding: 20px 22px !important;
        }

        @media (max-width: 767px) {
            .form-section {
                padding: 20px 16px 22px;
            }
        }

        .btn-patient-search {
            flex: 1;
            min-width: 220px;
            padding: 20px 25px;
            border: 3px solid #e8e8e8;
            border-radius: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
            position: relative;
            overflow: hidden;
        }

        .btn-patient-search::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #0b3a5c 0%, #0d8f7f 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .btn-patient-search:hover {
            border-color: #0b3a5c;
            background: linear-gradient(135deg, #e7f4f2 0%, #dcefeb 100%);
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(11, 58, 92, 0.2);
        }

        .btn-patient-search:hover::before {
            transform: scaleX(1);
        }

        .btn-patient-search i {
            font-size: 24px;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-patient-search.active {
            border-color: #0b3a5c;
            background: linear-gradient(135deg, #e7f4f2 0%, #dcefeb 100%);
            box-shadow: 0 8px 20px rgba(11, 58, 92, 0.25);
        }

        .btn-patient-search.active::before {
            transform: scaleX(1);
        }

        .info-badge {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, #e7f3ff 0%, #d4e9ff 100%);
            color: #0b3a5c;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 10px;
            border: 2px solid #0b3a5c;
            box-shadow: 0 2px 8px rgba(11, 58, 92, 0.15);
        }

        /* Enhanced Form Controls */
        .form-control,
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        /* Patient Detail Display Enhancement */
        #patient-detail-display {
            animation: slideIn 0.4s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #patient-detail-display .card {
            border: 3px solid #11998e;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.2);
            overflow: hidden;
        }

        #patient-detail-display .card-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            font-weight: 700;
            padding: 15px 20px;
            border: none;
        }

        #patient-detail-display .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 700;
            color: #2d3748;
            padding: 15px;
            border: none;
        }

        #patient-detail-display .table td {
            padding: 15px;
            color: #4a5568;
            border-color: #e2e8f0;
        }

        #patient-detail-display .table {
            margin-bottom: 0;
        }

        /* Modal edit pasien — harus di atas select2/wizard agar input bisa diketik */
        #modalEditPasienTerpilih {
            z-index: 20050 !important;
        }

        #modalEditPasienTerpilih .modal-dialog {
            max-height: calc(100vh - 2rem);
            margin: 1rem auto;
        }

        #modalEditPasienTerpilih .modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #modalEditPasienTerpilih .modal-header,
        #modalEditPasienTerpilih .modal-footer {
            flex-shrink: 0;
        }

        #modalEditPasienTerpilih .modal-body {
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(100vh - 11rem);
            -webkit-overflow-scrolling: touch;
        }

        #modalEditPasienTerpilih .modal-content,
        #modalEditPasienTerpilih .modal-body input,
        #modalEditPasienTerpilih .modal-body textarea {
            pointer-events: auto !important;
        }

        body.modal-edit-pasien-open .modal-backdrop.show {
            z-index: 20040 !important;
        }

        /* Alert Styling */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            font-weight: 500;
        }

        .alert-info {
            background: linear-gradient(135deg, #e7f3ff 0%, #d4e9ff 100%);
            color: #0b3a5c;
            border-left: 4px solid #0b3a5c;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4f4dd 0%, #b8f2c6 100%);
            color: #11998e;
            border-left: 4px solid #11998e;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #f57c00;
            border-left: 4px solid #ff9800;
        }

        /* Enhanced Select2 Styling */
        .select2-container--classic .select2-selection--single {
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            height: 48px !important;
            padding: 8px 15px !important;
            background: white !important;
            transition: all 0.3s ease !important;
        }

        .select2-container--classic .select2-selection--single:hover {
            border-color: #0b3a5c !important;
        }

        .select2-container--classic.select2-container--focus .select2-selection--single,
        .select2-container--classic.select2-container--open .select2-selection--single {
            border-color: #0b3a5c !important;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1) !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            color: #2d3748 !important;
            font-size: 15px !important;
            padding-left: 0 !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__placeholder {
            color: #a0aec0 !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 10px !important;
        }

        .select2-container--classic .select2-dropdown {
            border: 2px solid #0b3a5c !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
            margin-top: 5px !important;
        }

        .select2-container--classic .select2-results__option {
            padding: 12px 15px !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--classic .select2-results__option--highlighted {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%) !important;
            color: white !important;
        }

        .select2-container--classic .select2-search--dropdown .select2-search__field {
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 10px 15px !important;
            font-size: 14px !important;
        }

        .select2-container--classic .select2-search--dropdown .select2-search__field:focus {
            border-color: #0b3a5c !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1) !important;
        }

        /* Select2 di dalam input-group (Petugas Pengambil Sampel) */
        .input-group-petugas-pengambil {
            flex-wrap: nowrap;
            align-items: stretch;
        }

        .input-group-petugas-pengambil .input-group-prepend .input-group-text {
            height: 48px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border: 2px solid #e2e8f0;
            border-right: 0;
            background: #f8fafc;
        }

        .input-group-petugas-pengambil .petugas-pengambil-select-wrap {
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
            position: relative;
        }

        .input-group-petugas-pengambil .petugas-pengambil-select-wrap .select2-container {
            width: 100% !important;
            display: block;
        }

        .input-group-petugas-pengambil .select2-container--classic .select2-selection--single {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-left: 0 !important;
            min-height: 48px !important;
            height: 48px !important;
        }

        .input-group-petugas-pengambil .select2-container--classic.select2-container--focus .select2-selection--single,
        .input-group-petugas-pengambil .select2-container--classic.select2-container--open .select2-selection--single {
            border-left: 0 !important;
        }

        .input-group-petugas-pengambil .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-right: 28px !important;
        }

        #petugas_pengambil_container .form-text {
            margin-top: 0.5rem;
            line-height: 1.45;
        }

        /* Search Pasien Modal Enhancement */
        .modal-dialog.custom-width {
            max-width: 90% !important;
            width: 90% !important;
        }

        .modal-content {
            border-radius: 15px !important;
            border: none !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
        }

        .modal-header {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%) !important;
            color: white !important;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 30px !important;
            border: none !important;
        }

        .modal-header .modal-title {
            font-weight: 700 !important;
            font-size: 20px !important;
        }

        .modal-header .close {
            color: white !important;
            opacity: 0.9 !important;
            text-shadow: none !important;
            font-size: 28px !important;
        }

        .modal-header .close:hover {
            opacity: 1 !important;
        }

        .modal-body {
            padding: 30px !important;
        }

        .modal-footer {
            border-top: 2px solid #f0f0f0 !important;
            padding: 20px 30px !important;
            border-radius: 0 0 15px 15px !important;
        }

        /* DataTable Enhancement */
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 8px 15px !important;
            margin-left: 10px !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #0b3a5c !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1) !important;
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, #e7f4f2 0%, #dcefeb 100%) !important;
        }

        /* Close Button Enhancement */
        .btn-close-modal {
            background: linear-gradient(135deg, #f56565 0%, #c53030 100%) !important;
            color: white !important;
            border: none !important;
            padding: 10px 25px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .btn-close-modal:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 15px rgba(245, 101, 101, 0.3) !important;
        }

        /* Table Body Enhancement */
        #patientsTableBody td {
            padding: 12px 15px !important;
            color: #4a5568 !important;
            vertical-align: middle !important;
        }

        #patientsTableBody tr {
            transition: all 0.2s ease !important;
        }

        #patientsTableBody .btn {
            padding: 8px 16px !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            transition: all 0.3s ease !important;
        }

        #patientsTableBody .btn-primary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%) !important;
            border: none !important;
        }

        #patientsTableBody .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(11, 58, 92, 0.3) !important;
        }

        /* Wilayah Dropdown Styling */
        .select-wilayah {
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 10px 15px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            background-color: white !important;
        }

        .select-wilayah:focus {
            border-color: #0b3a5c !important;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1) !important;
            outline: none !important;
        }

        .select-wilayah:disabled {
            background-color: #f3f4f6 !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
        }

        .select-wilayah option {
            padding: 10px !important;
        }

        /* Loading state for select */
        .select-wilayah.loading {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24'%3E%3Cpath fill='%230b3a5c' d='M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z' opacity='.25'/%3E%3Cpath fill='%230d8f7f' d='M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z'%3E%3CanimateTransform attributeName='transform' type='rotate' dur='0.75s' values='0 12 12;360 12 12' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat !important;
            background-position: calc(100% - 12px) center !important;
            background-size: 20px 20px !important;
        }

        /* Card styling for wilayah section */
        .card.border-0.shadow-sm {
            border-radius: 12px !important;
            overflow: hidden !important;
        }

        /* Search Wilayah Autocomplete Styling */
        #search_wilayah_input {
            transition: all 0.3s ease !important;
        }

        #search_wilayah_input:focus {
            border-color: #0b3a5c !important;
            box-shadow: 0 0 0 4px rgba(11, 58, 92, 0.2) !important;
        }

        #search_wilayah_results {
            max-height: 400px;
            overflow-y: auto;
            z-index: 9999 !important;
            position: absolute !important;
            width: 100%;
            margin-top: 2px;
        }

        #search_wilayah_results .card {
            margin-bottom: 0 !important;
        }

        #search_wilayah_results .list-group-item {
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 12px 16px;
        }

        #search_wilayah_results .list-group-item:hover {
            background: linear-gradient(135deg, #e7f4f2 0%, #dcefeb 100%);
            transform: translateX(5px);
        }

        #search_wilayah_results .list-group-item:active {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white !important;
        }

        #search_wilayah_results .wilayah-name {
            font-weight: 600;
            font-size: 15px;
            color: #2d3748;
        }

        #search_wilayah_results .wilayah-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        #search_wilayah_results .wilayah-type.desa {
            background: #d4edda;
            color: #155724;
        }

        #search_wilayah_results .wilayah-type.kec {
            background: #d1ecf1;
            color: #0c5460;
        }

        #search_wilayah_results .wilayah-type.kab {
            background: #fff3cd;
            color: #856404;
        }

        #search_wilayah_results .wilayah-path {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }

        #search_wilayah_results .no-results {
            padding: 20px;
            text-align: center;
            color: #a0aec0;
        }

        /* Loading animation for search */
        #search_wilayah_input.searching {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24'%3E%3Cpath fill='%231976d2' d='M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z' opacity='.25'/%3E%3Cpath fill='%231976d2' d='M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z'%3E%3CanimateTransform attributeName='transform' type='rotate' dur='0.75s' values='0 12 12;360 12 12' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat !important;
            background-position: calc(100% - 12px) center !important;
            background-size: 20px 20px !important;
        }

        /* Selected wilayah badge */
        .selected-wilayah-badge {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #d4f4dd 0%, #b8f2c6 100%);
            color: #11998e;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
            border: 2px solid #11998e;
        }

        .selected-wilayah-badge i {
            margin-right: 6px;
        }

        /* Fix parent container positioning for autocomplete */
        #search_wilayah_input.form-control {
            position: relative !important;
        }

        /* Ensure search container has proper stacking context */
        .flex-grow-1.position-relative {
            z-index: 1000;
            position: relative !important;
        }

        /* Additional fix for card overflow */
        .card-body {
            overflow: visible !important;
        }

        /* Make sure results appear above everything */
        #patient-search-container {
            position: relative;
            z-index: 1;
        }

        #patient-search-container #search_wilayah_results {
            position: absolute !important;
            z-index: 10000 !important;
            left: 0;
            right: 0;
        }

        /* Birth Date Toggle Button Styles */
        #btn_mode_dropdown,
        #btn_mode_manual {
            cursor: pointer !important;
            pointer-events: auto !important;
            transition: all 0.3s ease;
            font-size: 15px;
            border-width: 2px !important;
            position: relative !important;
            z-index: 101 !important;
        }

        #btn_mode_dropdown:hover,
        #btn_mode_manual:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            z-index: 102 !important;
        }

        #btn_mode_dropdown:active,
        #btn_mode_manual:active {
            transform: translateY(0);
            z-index: 102 !important;
        }

        /* Ensure buttons are clickable */
        .btn-group {
            z-index: 100 !important;
            position: relative !important;
        }

        .btn-group button {
            user-select: none;
            -webkit-user-select: none;
        }

        /* Ensure containers don't overlay buttons */
        #birth_dropdown_container,
        #birth_manual_container {
            position: relative;
            z-index: 10 !important;
        }

        /* Age display animation */
        #age_display_container {
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>


    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="template-demo">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                        class="fa fa-home menu-icon mr-1"></i>
                                    Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan
                                    Uji
                                    Klinik
                                    Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <span>{{ $isEdit ? 'Edit' : 'Create' }}</span>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Page Header -->
    <div class="page-header-card"
        style="background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%); border-radius: 12px; padding: 28px 30px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(11, 58, 92, 0.22); color: white;">
        <h2 style="margin: 0; font-size: 26px; font-weight: 800; display: flex; align-items: center; letter-spacing: -0.02em;">
            <i class="fa fa-{{ $isEdit ? 'edit' : 'plus-circle' }}"
                style="margin-right: 15px; font-size: 28px; background: rgba(255, 255, 255, 0.18); padding: 12px; border-radius: 12px;"></i>
            {{ $isEdit ? 'Edit Permohonan Uji Klinik' : 'Permohonan Uji Klinik Baru' }}
        </h2>
        <div style="margin-top: 10px; opacity: 0.9; font-size: 14px;">
            @if ($isEdit)
                Ubah data permohonan uji klinik melalui langkah-langkah berikut
                @if (!empty($item->noregister_permohonan_uji_klinik))
                    — No. Sample: <strong>{{ $item->noregister_permohonan_uji_klinik }}</strong>
                @endif
            @else
                Lengkapi formulir permohonan uji klinik dengan mengikuti langkah-langkah berikut
            @endif
        </div>
    </div>

    <div class="card" style="border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none;">
        <div class="card-body" style="padding: 40px;">
            <!-- Wizard Steps Indicator -->
            <div class="wizard-steps">
                <div class="wizard-step {{ $isHajiEdit ? 'completed' : 'active' }}" data-step="1">
                    <div class="wizard-step-circle">1</div>
                    <div class="wizard-step-title">Tipe Dokter</div>
                </div>
                <div class="wizard-step {{ $isHajiEdit ? 'completed' : '' }}" data-step="2">
                    <div class="wizard-step-circle">2</div>
                    <div class="wizard-step-title">Data Pasien</div>
                </div>
                <div class="wizard-step {{ $isHajiEdit ? 'active' : '' }}" data-step="3">
                    <div class="wizard-step-circle">3</div>
                    <div class="wizard-step-title">Informasi Permohonan</div>
                </div>
            </div>

            <!-- Step 1: Pilih Tipe Dokter -->
            <div class="step-content {{ $isHajiEdit ? '' : 'active' }}" id="step-1">
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-user-md"></i>
                        Pilih Tipe Dokter
                    </div>
                    <p class="text-muted mb-4">
                        @if (!empty($isEdit) && (int) ($item->is_haji ?? 0) === 1)
                            Pasien haji memakai <strong>Dokter Rujukan</strong>.
                        @else
                            Silakan pilih tipe dokter untuk permohonan uji klinik ini
                        @endif
                    </p>

                    <div class="doctor-type-selector">
                        <div class="doctor-type-card" data-type="lab" @if (!empty($isEdit) && (int) ($item->is_haji ?? 0) === 1) style="display: none;" @endif>
                            <div class="doctor-type-icon">
                                <i class="fa fa-flask"></i>
                            </div>
                            <div class="doctor-type-title">Dokter Lab</div>
                            <div class="doctor-type-description">
                                Untuk pemeriksaan laboratorium internal tanpa rujukan dari dokter luar
                            </div>
                        </div>

                        <div class="doctor-type-card @if (!empty($isEdit) && (int) ($item->is_haji ?? 0) === 1) selected @endif" data-type="rujukan">
                            <div class="doctor-type-icon">
                                <i class="fa fa-hospital"></i>
                            </div>
                            <div class="doctor-type-title">Dokter Rujukan</div>
                            <div class="doctor-type-description">
                                Untuk pemeriksaan berdasarkan rujukan dari dokter pengirim dengan diagnosa
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary btn-step"
                            onclick="window.location='{{ url('/elits-permohonan-uji-klinik-2') }}'">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary btn-step" id="btn-next-step-1" disabled>
                            Lanjut <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Data Pasien -->
            <div class="step-content" id="step-2">
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-users"></i>
                        Data Pasien
                    </div>

                    <div class="patient-search-buttons">
                        <button type="button" class="btn-patient-search" id="btn-search-silaboy">
                            <i class="fa fa-database"></i>
                            <span>Cari Pasien</span>
                        </button>
                        <button type="button" class="btn-patient-search" id="btn-add-new-patient">
                            <i class="fa fa-user-plus"></i>
                            <span>Tambah Pasien Baru</span>
                        </button>
                    </div>

                    <!-- Search Forms Container -->
                    <div id="patient-search-container" style="margin-top: 20px;"></div>

                    <!-- Patient Detail Display -->
                    <div id="patient-detail-display" style="margin-top: 20px;"></div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary btn-step" id="btn-prev-step-2">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary btn-step" id="btn-next-step-2" disabled>
                            Lanjut <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Informasi Permohonan -->
            <div class="step-content {{ $isHajiEdit ? 'active' : '' }}" id="step-3">
                <form action="{{ $isEdit ? route('elits-permohonan-uji-klinik-2.update', $item->id_permohonan_uji_klinik) : route('elits-permohonan-uji-klinik-2.store') }}" method="POST"
                    enctype="multipart/form-data" id="form">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                    <input type="hidden" name="doctor_type" id="doctor_type" value="{{ (!empty($isEdit) && (int) ($item->is_haji ?? 0) === 1) ? 'rujukan' : '' }}">
                    @if (!empty($isEdit) && (int) ($item->is_haji ?? 0) === 1)
                        <input type="hidden" name="is_haji" value="1">
                    @endif
                    <input type="hidden" class="form-control" name="nourut_permohonan_uji_klinik"
                        id="nourut_permohonan_uji_klinik" value="{{ $set_count }}" readonly>
                    <input type="hidden" class="form-control" readonly name="pasien_permohonan_uji_klinik"
                        id="seccond_pasien_permohonan_uji_klinik">
                    <input type="hidden" class="form-control" readonly name="nopasien_permohonan_uji_klinik"
                        id="nopasien_permohonan_uji_klinik">
                    <input type="hidden" class="form-control date_birth_last"
                        name="tgllahir_pasien_permohonan_uji_klinik" id="tgllahir_pasien_permohonan_uji_klinik"
                        placeholder="dd/mm/yyyy" readonly>

                    <!-- Informasi Dasar -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa fa-file-text"></i>
                            Informasi Dasar Permohonan
                        </div>

                        <div class="form-group">
                            <label for="nomor_spesimen_display">No. Spesimen <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                                </div>
                                <input type="text" class="form-control" readonly
                                    id="nomor_spesimen_display"
                                    placeholder="03/{nomor}/{{ date('Y') }}"
                                    value="{{ $nomor_spesimen_preview ?? '' }}">
                            </div>
                            {{-- Nilai spesimen untuk register/JS (otomatis atau manual) --}}
                            <input type="hidden" id="nomor_spesimen_auto" value="{{ $nomor_spesimen_manual_default ?? '' }}">
                            @if(!empty($isEdit) || (isset($numberSettings) && $numberSettings->is_nomor_spesimen_manual))
                            <div class="mt-2">
                                <div id="nomor_spesimen_manual_container" style="margin-top: 10px;">
                                    <label class="font-weight-bold">{{ !empty($isEdit) ? 'Ubah Nomor Spesimen' : 'Input Manual Nomor Spesimen' }}</label>
                                    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); position: relative;">
                                        <div class="card-body" style="display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 8px 12px;">
                                            <span style="color: #0b3a5c;">03/</span>
                                            <input type="text" class="form-control"
                                                name="nomor_spesimen_manual" id="nomor_spesimen_manual"
                                                placeholder="no_urut"
                                                value="{{ $nomor_spesimen_manual_default ?? '' }}"
                                                inputmode="numeric"
                                                style="text-align: center; flex: 0 1 auto; max-width: 120px; font-weight: 600; color: #0b3a5c; height: 32px;">
                                            <span style="color: #0b3a5c; white-space: nowrap;">/{{ (int) ($seqYear ?? date('Y')) }}</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        <i class="fa fa-info-circle mr-1"></i>
                                        @if(!empty($isEdit))
                                        Kosongkan field jika tidak ingin mengubah — nomor lama tetap dipakai. Isi angka baru hanya jika ingin mengganti. Format: 03/{number sampel}/{{ (int) ($seqYear ?? date('Y')) }}
                                        @else
                                        Format: 03/{number sampel}/{{ date('Y') }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @else
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Otomatis dari nomor global. Format: 03/{number sampel}/{{ date('Y') }}
                            </small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="nomor_lab_display">No. Laboratorium</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-flask"></i></span>
                                </div>
                                <input type="text" class="form-control" readonly
                                    name="nomor_lab_display" id="nomor_lab_display"
                                    placeholder="449.5/03/{nomor}/{{ date('Y') }}"
                                    value="{{ $nomor_lab_preview ?? '' }}">
                            </div>
                            @if(!empty($isEdit) || (isset($numberSettings) && $numberSettings->is_nomor_lab_manual))
                            <div class="mt-2">
                                <div id="nomor_lab_manual_container" style="margin-top: 10px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                        <label class="font-weight-bold mb-0">{{ !empty($isEdit) ? 'Ubah Nomor Laboratorium' : 'Input Manual Nomor Laboratorium' }}</label>
                                        <button type="button" class="btn btn-sm btn-primary" id="btn-set-default-nomor-lab"
                                            title="Isi dengan nomor lab terakhir + 1 ({{ (int) ($lastLabNumber ?? 0) > 0 ? ((int) $lastLabNumber + 1) : 1 }})">
                                            <i class="fa fa-magic mr-1"></i>Set Default
                                        </button>
                                    </div>
                                    <div class="card border-0 shadow-sm mb-3 mt-2" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); position: relative;">
                                        <div class="card-body" style="display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 8px 12px;">
                                            <span style="color: #0b3a5c;">449.5/03/</span>
                                            <input type="text" class="form-control"
                                                name="nomor_lab_manual" id="nomor_lab_manual"
                                                placeholder="no_urut"
                                                value="{{ $nomor_lab_manual_default ?? '' }}"
                                                inputmode="numeric"
                                                style="text-align: center; flex: 0 1 auto; max-width: 120px; font-weight: 600; color: #0b3a5c; height: 32px;">
                                            <span style="color: #0b3a5c; white-space: nowrap;">/{{ (int) ($seqYear ?? date('Y')) }}</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        <i class="fa fa-info-circle mr-1"></i>
                                        @if(!empty($isEdit))
                                        Kosongkan field jika tidak ingin mengubah — nomor lama tetap dipakai. Isi angka baru hanya jika ingin mengganti. Format: 449.5/03/{number lab}/{{ (int) ($seqYear ?? date('Y')) }}
                                        @else
                                        Format: 449.5/03/{number lab}/{{ date('Y') }}
                                        @endif
                                        Nomor lab terakhir: <strong>{{ (int) ($lastLabNumber ?? 0) > 0 ? (int) $lastLabNumber : 'belum ada' }}</strong>
                                        → Set Default: <strong>{{ (int) ($lastLabNumber ?? 0) + 1 }}</strong>
                                    </small>
                                </div>
                            </div>
                            @else
                            <input type="hidden" id="nomor_lab_auto" value="{{ $nomor_lab_manual_default ?? '' }}">
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Otomatis dari nomor lab terakhir gabungan kesmas + klinik. Format: 449.5/03/{number lab}/{{ date('Y') }}
                            </small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="noregister_permohonan_uji_klinik">No. Register <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                                </div>
                                <input type="text" class="form-control" readonly
                                    name="noregister_permohonan_uji_klinik" id="noregister_permohonan_uji_klinik"
                                    placeholder="{number sampel} / {number lab}"
                                    value="{{ $nomor_register_preview ?? $code }}">
                            </div>
                            <small class="form-text text-muted">
                                Format: {number sampel} / {number lab}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="tglregister_permohonan_uji_klinik">TGL. REGISTER <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="text" class="form-control" autocomplete="on"
                                    id="tglregister_permohonan_uji_klinik_display" placeholder="Pilih tanggal dan waktu"
                                    readonly value="{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}">
                                <input type="hidden" id="tglregister_permohonan_uji_klinik"
                                    name="tglregister_permohonan_uji_klinik"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}">
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="petugas_penerima">Petugas Registrasi</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                </div>
                                <select class="form-control" name="petugas_penerima" id="petugas_penerima">
                                    <option value="">Pilih Petugas Registrasi</option>
                                    @foreach (($petugasPenerima ?? []) as $petugas)
                                        <option value="{{ $petugas }}">{{ $petugas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="petugas_pengambil_container">
                            <label for="petugas_pengambil_sampel">Petugas Pengambil Sampel <span class="text-danger petugas-pengambil-required">*</span></label>
                            <div class="input-group input-group-petugas-pengambil">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user-md"></i></span>
                                </div>
                                <div class="petugas-pengambil-select-wrap">
                                    <select class="form-control" name="petugas_pengambil_sampel" id="petugas_pengambil_sampel">
                                        <option value="">Pilih Petugas Pengambil Sampel</option>
                                        <option value="...................">......</option>
                                        <option value="__urine_only__">Urin saja (tidak perlu petugas)</option>
                                        <option value="__diisi_pelanggan__">Diisi pelanggan</option>
                                        @foreach (($petugasPengambilSampel ?? []) as $petugas)
                                            <option value="{{ $petugas }}">{{ $petugas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Pilih dari daftar, ketik nama manual, pilih <strong>......</strong> jika belum ditentukan, <strong>Diisi pelanggan</strong> jika sampel dibawa/diisi pelanggan, atau <strong>Urin saja</strong> untuk pemeriksaan urine.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="mode_pengambilan_sampel">MODE PENGAMBILAN SAMPEL</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-vial"></i></span>
                                </div>
                                <select class="form-control" name="mode_pengambilan_sampel" id="mode_pengambilan_sampel">
                                    <option value="">Pilih mode pengambilan sampel</option>
                                    <option value="diambil_lab"
                                        {{ old('mode_pengambilan_sampel', 'diambil_lab') == 'diambil_lab' ? 'selected' : '' }}>
                                        Diambil di Lab</option>
                                    <option value="dibawa_pelanggan"
                                        {{ old('mode_pengambilan_sampel') == 'dibawa_pelanggan' ? 'selected' : '' }}>Dibawa
                                        Pelanggan Sendiri</option>
                                    <option value="diambil_lokasi_rumah"
                                        {{ old('mode_pengambilan_sampel') == 'diambil_lokasi_rumah' ? 'selected' : '' }}>Diambil
                                        Di Lokasi/Rumah</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="biaya_pengambilan_container" style="display: none;">
                            <label for="biaya_pengambilan_sampel">BIAYA PENGAMBILAN SAMPEL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-money-bill-wave"></i></span>
                                </div>
                                <input type="text" class="form-control" name="biaya_pengambilan_sampel"
                                    id="biaya_pengambilan_sampel" placeholder="Masukkan biaya pengambilan sampel"
                                    value="20000" inputmode="numeric" autocomplete="off"
                                    pattern="[0-9]*">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Biaya default: Rp 20.000
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="umur_pasien">UMUR PASIEN</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            name="umurtahun_pasien_permohonan_uji_klinik"
                                            id="umurtahun_pasien_permohonan_uji_klinik" placeholder="0" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Tahun</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            name="umurbulan_pasien_permohonan_uji_klinik"
                                            id="umurbulan_pasien_permohonan_uji_klinik" placeholder="0" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Bulan</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            name="umurhari_pasien_permohonan_uji_klinik"
                                            id="umurhari_pasien_permohonan_uji_klinik" placeholder="0" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Metode Pembayaran</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                                </div>
                                <select class="form-control" name="metode_pembayaran">
                                    <option value="0">Cash</option>
                                    <option value="1">Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="request_pasien_permohonan_uji_klinik">REQUEST PASIEN / KELUHAN <span class="text-muted">(Sebelum pemilihan layanan pemeriksaan)</span></label>
                            <textarea class="form-control" name="request_pasien_permohonan_uji_klinik" id="request_pasien_permohonan_uji_klinik"
                                placeholder="Masukkan request pasien atau keluhan sebelum memilih layanan pemeriksaan yang dipilihkan dokter" rows="4">{{ old('request_pasien_permohonan_uji_klinik') }}</textarea>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Field ini digunakan untuk mencatat request pasien atau keluhan sebelum dokter memilih layanan pemeriksaan.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="kirim_hasil_whatsapp">Kirim Hasil Otomatis Melalui WhatsApp</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="">
                                        <i class="fab fa-whatsapp"></i>
                                    </span>
                                </div>
                                <div class="form-control" style="background-color: #f8f9fa; border-left: none; padding: 12px 20px; display: flex; align-items: center; gap: 30px;">
                                    <div class="form-check" style="margin: 0;">
                                        <input class="form-check-input" type="radio" name="kirim_hasil_whatsapp" id="kirim_hasil_whatsapp_ya" value="1" {{ old('kirim_hasil_whatsapp', '0') == '1' ? 'checked' : '' }} style="cursor: pointer;">
                                        <label class="form-check-label" for="kirim_hasil_whatsapp_ya" style="cursor: pointer; margin-left: 8px; font-weight: 500; color: #495057;">
                                            Ya
                                        </label>
                                    </div>
                                    <div class="form-check" style="margin: 0;">
                                        <input class="form-check-input" type="radio" name="kirim_hasil_whatsapp" id="kirim_hasil_whatsapp_tidak" value="0" {{ old('kirim_hasil_whatsapp', '0') == '0' ? 'checked' : '' }} style="cursor: pointer;">
                                        <label class="form-check-label" for="kirim_hasil_whatsapp_tidak" style="cursor: pointer; margin-left: 8px; font-weight: 500; color: #495057;">
                                            Tidak
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted" style="margin-top: 5px;">
                                <i class="fa fa-info-circle mr-1"></i>Pilih apakah hasil pemeriksaan akan dikirim otomatis melalui WhatsApp setelah selesai.
                            </small>
                        </div>
                    </div>

                    <!-- Informasi Dokter Rujukan (Only for Dokter Rujukan) -->
                    <div class="form-section" id="rujukan-fields" style="{{ $isHajiEdit ? '' : 'display: none;' }}">
                        <div class="form-section-title">
                            <i class="fa fa-hospital"></i>
                            Informasi Dokter Pengirim
                        </div>

                        <div class="form-group">
                            <label for="nama_dokter_pengirim_permohonan_uji_klinik">NAMA DOKTER PENGIRIM</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user-md"></i></span>
                                </div>
                                <input type="text" class="form-control"
                                    name="nama_dokter_pengirim_permohonan_uji_klinik"
                                    id="nama_dokter_pengirim_permohonan_uji_klinik"
                                    placeholder="Masukkan nama dokter pengirim">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="hp_dokter_pengirim_permohonan_uji_klinik">No. HP DOKTER PENGIRIM</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control"
                                    name="hp_dokter_pengirim_permohonan_uji_klinik"
                                    id="hp_dokter_pengirim_permohonan_uji_klinik"
                                    placeholder="Masukkan no. hp dokter pengirim">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="diagnosa_permohonan_uji_klinik">DIAGNOSA</label>
                            <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik"
                                placeholder="Masukkan diagnosa" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- Informasi Perwakilan -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa fa-users"></i>
                            Wali (Opsional)
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                name="isPerwakilan">
                            <label class="form-check-label" for="flexSwitchCheckDefault" style="margin-left: 12px;">
                                Menggunakan Wali
                            </label>
                        </div>

                        <div id="form_perwakilan" style="display: none;">
                            <div class="form-group">
                                <label for="nama_perwakian_permohonan_uji_klinik">NAMA WALI</label>
                                <input type="text" class="form-control" name="nama_perwakian_permohonan_uji_klinik"
                                    id="nama_perwakian_permohonan_uji_klinik" placeholder="Masukkan nama perwakilan">
                            </div>

                            <div class="form-group">
                                <label for="gender_perwakilan_permohonan_uji_klinik">JENIS KELAMIN</label>
                                <select class="form-control" id="gender_perwakilan_permohonan_uji_klinik"
                                    name="gender_perwakilan_permohonan_uji_klinik">
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_lahir_perwakilan">TANGGAL LAHIR WALI</label>
                                <input type="text" name="tanggal_lahir_perwakilan" id="basic2"
                                    class="form-control" />
                            </div>

                            <div class="form-group">
                                <label for="alamat_perwakilan">ALAMAT WALI</label>
                                <textarea class="form-control" name="alamat_perwakilan" id="alamat_perwakilan" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="status_hubungan_perwakilan_permohonan_uji_klinik">STATUS HUBUNGAN DENGAN PASIEN</label>
                                <select class="form-control" id="status_hubungan_perwakilan_permohonan_uji_klinik"
                                    name="status_hubungan_perwakilan_permohonan_uji_klinik">
                                    <option value="">-- Pilih Status Hubungan --</option>
                                    <option value="Orang Tua">Orang Tua</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Istri">Istri</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Wali">Wali</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="form-group" id="status_hubungan_lainnya_group" style="display: none;">
                                <label for="status_hubungan_lainnya_permohonan_uji_klinik">KETERANGAN LAINNYA</label>
                                <input type="text" class="form-control" name="status_hubungan_lainnya_permohonan_uji_klinik"
                                    id="status_hubungan_lainnya_permohonan_uji_klinik" placeholder="Masukkan status hubungan lainnya">
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Perwakilan Dokter-->
                    <div class="form-section" id="perwakilan_dokter_form_group" style="display: none;">
                        <div class="form-section-title">
                            <i class="fa fa-users"></i>
                            Perwakilan Dokter
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefaultDokter"
                                name="isPerwakilanDokter">
                            <label class="form-check-label" for="flexSwitchCheckDefaultDokter" style="margin-left: 12px;">
                                Menggunakan Perwakilan
                            </label>
                        </div>

                        <div id="form_perwakilan_dokter" style="display: none;">
                            <div class="form-group">
                                <label for="diagnosa_permohonan_uji_klinik">DIAGNOSA</label>
                                <textarea class="form-control" name="diagnosa_permohonan_uji_klinik_perwakilan"
                                    id="diagnosa_permohonan_uji_klinik_perwakilan" placeholder="Masukkan diagnosa" rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary btn-step" id="btn-prev-step-3">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-success btn-step btn-simpan">
                            <i class="fa fa-save"></i> {{ $isEdit ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            window.klinikNumberSettings = {
                isNomorLabManual: {{ (isset($numberSettings) && $numberSettings->is_nomor_lab_manual) ? 'true' : 'false' }},
                isNomorSpesimenManual: {{ (isset($numberSettings) && $numberSettings->is_nomor_spesimen_manual) ? 'true' : 'false' }},
                defaultAutoCode: @json($nomor_register_preview ?? $code),
                year: {{ (int) ($seqYear ?? date('Y')) }},
                lastLabNumber: {{ (int) ($lastLabNumber ?? 0) }},
                defaultLabNumber: {{ max(1, (int) ($lastLabNumber ?? 0) + 1) }}
            };

            function cleanNomorUrut(val) {
                var digits = String(val || '').replace(/\D/g, '');
                if (!digits || parseInt(digits, 10) < 1) {
                    return '';
                }
                return String(parseInt(digits, 10));
            }

            function updateKlinikNumberDisplays() {
                var settings = window.klinikNumberSettings || {};
                var year = settings.year || new Date().getFullYear();

                var labUrut = cleanNomorUrut($('#nomor_lab_manual').val());
                if (!labUrut) {
                    labUrut = cleanNomorUrut($('#nomor_lab_auto').val());
                }

                var spesimenUrut = cleanNomorUrut($('#nomor_spesimen_manual').val());
                if (!spesimenUrut) {
                    spesimenUrut = cleanNomorUrut($('#nomor_spesimen_auto').val());
                }

                // No. Spesimen : 03/{number sampel}/YYYY
                if (spesimenUrut) {
                    $('#nomor_spesimen_display').val('03/' + spesimenUrut + '/' + year);
                }

                // No. Laboratorium : 449.5/03/{number lab}/YYYY
                if (labUrut) {
                    $('#nomor_lab_display').val('449.5/03/' + labUrut + '/' + year);
                }

                // No. Register : {number sampel} / {number lab}
                if (spesimenUrut && labUrut) {
                    $('#noregister_permohonan_uji_klinik').val(spesimenUrut + ' / ' + labUrut);
                } else if (spesimenUrut) {
                    $('#noregister_permohonan_uji_klinik').val(spesimenUrut);
                } else {
                    $('#noregister_permohonan_uji_klinik').val(settings.defaultAutoCode || '');
                }
            }

            // Alias lama (dipakai beberapa handler)
            function updateNoregisterFromManualNumbers() {
                updateKlinikNumberDisplays();
            }

            // Toggle biaya pengambilan sampel field based on mode_pengambilan_sampel selection
            function toggleModePengambilanFields() {
                const mode = $('#mode_pengambilan_sampel').val();
                if (mode === 'diambil_lokasi_rumah') {
                    $('#biaya_pengambilan_container').slideDown();
                } else {
                    $('#biaya_pengambilan_container').slideUp();
                }

                if (mode === 'dibawa_pelanggan') {
                    $('#petugas_pengambil_container').slideUp();
                    var $petugasPengambil = $('#petugas_pengambil_sampel');
                    if ($petugasPengambil.hasClass('select2-hidden-accessible')) {
                        $petugasPengambil.val('__diisi_pelanggan__').trigger('change');
                    } else {
                        $petugasPengambil.val('__diisi_pelanggan__');
                    }
                    $('.petugas-pengambil-required').hide();
                } else {
                    $('#petugas_pengambil_container').slideDown();
                    $('.petugas-pengambil-required').show();
                }
            }

            $(document).ready(function() {
                $('#mode_pengambilan_sampel').on('change', toggleModePengambilanFields);

                $('#biaya_pengambilan_sampel').on('input', function() {
                    this.value = String(this.value || '').replace(/\D/g, '');
                });

                // Trigger on page load in case of old values
                toggleModePengambilanFields();

                // Auto-format nomor spesimen: 03/no_urut/tahun
                $('#nomor_spesimen_manual').on('input', function() {
                    let cleanValue = $(this).val().replace(/\D/g, '');
                    $(this).val(cleanValue);
                    updateKlinikNumberDisplays();
                });

                $('#nomor_spesimen_manual').on('paste', function() {
                    let self = this;
                    setTimeout(function() {
                        $(self).trigger('input');
                    }, 10);
                });

                // Auto-format nomor laboratorium: 449.5/03/no_urut/tahun
                $('#nomor_lab_manual').on('input', function() {
                    let cleanValue = $(this).val().replace(/\D/g, '');
                    $(this).val(cleanValue);
                    updateKlinikNumberDisplays();
                });

                $('#nomor_lab_manual').on('paste', function() {
                    let self = this;
                    setTimeout(function() {
                        $(self).trigger('input');
                    }, 10);
                });

                $('#btn-set-default-nomor-lab').on('click', function() {
                    var settings = window.klinikNumberSettings || {};
                    var nextLab = parseInt(settings.defaultLabNumber, 10);
                    if (!nextLab || nextLab < 1) {
                        nextLab = (parseInt(settings.lastLabNumber, 10) || 0) + 1;
                    }
                    if (nextLab < 1) {
                        nextLab = 1;
                    }
                    $('#nomor_lab_manual').val(nextLab).trigger('input');
                });

                // Prefill → sync semua tampilan format resmi
                updateKlinikNumberDisplays();
            });
        </script>

        <div class="mx-4 mb-4 mt-4" id="search_satu_sehat" style="display: none;">
            <h5>Search Data Satu Sehat</h5>
            <form id="searchForm">
                <div class="form-group">
                    <label for="identifier">NIK</label>
                    <input type="text" class="form-control" id="identifier" name="identifier"
                        placeholder="Masukan NIK pasien">
                </div>
                <div class="form-group">
                    <label for="name">Nama Pasien</label>
                    <input type="text" class="form-control" id="name" name="name"
                        placeholder="Masukan nama pasien">
                </div>
                <div class="form-group">
                    <label for="birthdate">Tanggal Lahir</label>
                    <input type="text" name="birthday" value="" id="basic" />


                </div>

                <script type="text/javascript">
                    $('#basic').bootstrapBirthday({
                        dateFormat: "littleEndian"
                    });
                </script>
                <div class="form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select class="form-control" id="gender" name="gender">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="male">Laki Laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary" id="fetchPatientsBtn">Cari Pasien</button>
                <button type="button" class="btn btn-danger" id="close_button_search_satu_sehat">Close</button>
            </form>
        </div>

        {{-- Search Silaboy Template - Hidden --}}
        <div class="d-none" id="search_silaboy_template">
            <div class="mb-4"
                style="display: flex; align-items: center; justify-content: space-between; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px; border-left: 4px solid #0b3a5c;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa fa-search" style="font-size: 24px; color: #0b3a5c;"></i>
                    <P style="font-size: 18px; margin: 0; font-weight: 700; color: #2d3748;">Cari Pasien</P>
                </div>
                <button type="button" class="btn btn-close-modal btn-close-search"
                    style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa fa-times"></i> Tutup
                </button>
            </div>
            <div class="form-group" style="margin-top: 20px;">
                <label
                    style="font-weight: 600; color: #2d3748; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-user" style="color: #0b3a5c;"></i>
                    Pilih Pasien
                </label>
                <select class="form-control patient-select-silaboy" name="pasien_permohonan_uji_klinik"
                    style="width: 100%">
                    <option value=""></option>
                </select>
            </div>
        </div>

        {{-- Keep for compatibility --}}
        <div id="search_silaboy" style="display: none !important;"></div>

        <!-- Modal -->
        <div class="modal fade modal-xl" id="patientsModal" tabindex="-1" role="dialog"
            aria-labelledby="patientsModalLabel" aria-hidden="true">
            <div class="modal-dialog custom-width" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="patientsModalLabel"
                            style="display: flex; align-items: center; gap: 12px;">
                            <i class="fa fa-users"></i> Data Pasien
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label
                                style="font-weight: 600; color: #2d3748; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-search" style="color: #0b3a5c;"></i>
                                Cari Berdasarkan Alamat
                            </label>
                            <input type="text" id="searchAddress" class="form-control"
                                placeholder="Ketik alamat pasien..."
                                style="border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px 15px; font-size: 15px;">
                        </div>
                        <div style="overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                                <thead style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);">
                                    <tr>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">NIK</th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">Gender
                                        </th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">ID</th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">Alamat
                                        </th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">Tanggal
                                            Lahir</th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">Nama</th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">Telepon
                                        </th>
                                        <th style="color: white; font-weight: 600; padding: 15px; border: none;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="patientsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-close-modal" data-dismiss="modal"
                            style="display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa fa-times"></i> Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Old form elements kept hidden for compatibility with existing JavaScript -->
        <div style="display: none !important;">
            <div id="button_action_add"></div>
            <form id="old-form-duplicate">
                <div class="form-group">
                    <label for="code_register">No. SAMPLE <span style="color: red">*</span></label>
                    <div class="input-group date">
                        <input type="text" class="form-control" readonly name="noregister_permohonan_uji_klinik"
                            id="noregister_permohonan_uji_klinik" placeholder="No. REGISTER"
                            value="{{ $code }}">
                    </div>
                </div>

                <input type="hidden" class="form-control" readonly name="pasien_permohonan_uji_klinik"
                    id="seccond_pasien_permohonan_uji_klinik">

                <input type="hidden" class="form-control" readonly name="nopasien_permohonan_uji_klinik"
                    id="nopasien_permohonan_uji_klinik">

                {{-- view untuk menampilkan data pasien yang sudah ada --}}
                <div class="card" id="display-detail-pasien" style="display: none; margin-bottom: 20px">
                    <div class="card-body">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th style="width: 20%">ID Satu Sehat</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="id_satu_sehat_1" name="id_satu_sehat"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">NIK Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="nik_pasien_1" name="nik_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">No Rekam Medis</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="no_rekam_medis_pasien_1"
                                                        name="no_rekammedis_pasien" class="form-control"
                                                        value="{{ str_pad((int) $count_pasien, 4, '0', STR_PAD_LEFT) }}"
                                                        required readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Nama Lengkap (Sesuai KTP)</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="nama_pasien_1" name="nama_pasien"
                                                        class="form-control" required
                                                        style="text-transform: uppercase;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Jenis Kelamin Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="gender_pasien_1" name="gender_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Tanggal Lahir Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="tgllahir_pasien_1" name="tgllahir_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Nomor Telepon</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="phone_pasien_1" name="phone_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Alamat Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="alamat_pasien_1" name="alamat_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- view untuk menampilkan data pasien yang sudah ada silaboy --}}
                <div class="card" id="display-detail-pasien-silaboy" style="display: none; margin-bottom: 20px">
                    <div class="card-body">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th style="width: 20%">ID Satu Sehat</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="id_satu_sehat_3" name="id_satu_sehat"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">NIK Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="nik_pasien_3" name="nik_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">No Rekam Medis</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="no_rekam_medis_pasien_3"
                                                        name="no_rekammedis_pasien" class="form-control"
                                                        value="{{ str_pad((int) $count_pasien, 4, '0', STR_PAD_LEFT) }}"
                                                        required readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Nama Lengkap (Sesuai KTP)</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="nama_pasien_3" name="nama_pasien"
                                                        class="form-control" required
                                                        style="text-transform: uppercase;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Jenis Kelamin Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="gender_pasien_3" name="gender_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Tanggal Lahir Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="tgllahir_pasien_3" name="tgllahir_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Nomor Telepon</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="phone_pasien_3" name="phone_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%">Alamat Pasien</th>
                                                <th style="width: 2%">:</th>
                                                <td style="width: 78%">
                                                    <input type="text" id="alamat_pasien_3" name="alamat_pasien"
                                                        class="form-control" required>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--                    view untuk menampilkan data pasien baru --}}
                <div class="card" id="display-new-pasien" style="display: none; margin-bottom: 20px">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="button" class="close cancel-new-pasien" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>

                        <!-- Beautiful Patient Information Section -->
                        <div class="card border-0 shadow-sm mb-4"
                            style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); overflow: visible !important;">
                            <div class="card-body" style="overflow: visible !important;">
                                <h5 class="font-weight-bold mb-4"
                                    style="color: #0b3a5c; border-bottom: 3px solid #0b3a5c; padding-bottom: 10px;">
                                    <i class="fa fa-user-circle mr-2"></i>INFORMASI PASIEN
                                </h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nik_pasien-2" class="font-weight-bold" style="color: #495057;">
                                                <i class="fa fa-id-card mr-2" style="color: #0b3a5c;"></i>NIK PASIEN
                                            </label>
                                            <div class="input-group" style="position: relative;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"
                                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                        <i class="fa fa-id-card"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" name="nik_pasien"
                                                    id="nik_pasien_2" placeholder="Masukkan NIK 16 Digit"
                                                    value="{{ old('nikpasien_pasien') }}" maxlength="16"
                                                    style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle mr-1"></i>Sesuai Kartu Tanda Penduduk (16
                                                digit)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_pasien-2" class="font-weight-bold" style="color: #495057;">
                                                <i class="fa fa-user mr-2" style="color: #0b3a5c;"></i>NAMA LENGKAP
                                                <span style="color: red">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"
                                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                        <i class="fa fa-user"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" name="nama_pasien"
                                                    id="nama_pasien_2" placeholder="Nama Sesuai KTP"
                                                    style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; text-transform: uppercase;">
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle mr-1"></i>Sesuai Kartu Tanda Penduduk
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="no_rekammedis_pasien" class="font-weight-bold" style="color: #495057;">
                                        <i class="fa fa-file-text mr-2" style="color: #0b3a5c;"></i>NOMOR REKAM MEDIS
                                        <span style="color: red">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                <i class="fa fa-file-text"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control no_rekammedis_pasien"
                                            name="no_rekammedis_pasien" id="no_rekammedis_pasien_2"
                                            placeholder="Nomor rekam medis"
                                            value="{{ str_pad((int) $count_pasien, 4, '0', STR_PAD_LEFT) }}" readonly
                                            style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; background-color: #f8f9fa; font-weight: bold; color: #0b3a5c;">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-lock mr-1"></i>Nomor otomatis tergenerate
                                    </small>
                                </div>

                                <div class="form-group" hidden>
                                    <label for="divisi_instansi_pasien-2">DIVISI/INSTANSI</label>
                                    <input type="text" class="form-control divisi_instansi_pasien"
                                        name="divisi_instansi_pasien" id="divisi_instansi_pasien-2"
                                        placeholder="Divisi/instansi" value="{{ old('divisi_instansi_pasien') }}">
                                </div>

                                <div class="form-group">
                                    <label for="jenis_kelamin-2" class="font-weight-bold mb-3" style="color: #495057;">
                                        <i class="fa fa-venus-mars mr-2" style="color: #0b3a5c;"></i>JENIS KELAMIN
                                        <span style="color: red">*</span>
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm mb-2"
                                                style="cursor: pointer; transition: all 0.3s;"
                                                onclick="$('#gender_pasien_male').prop('checked', true).trigger('change');">
                                                <div class="card-body p-3"
                                                    style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input type="radio" class="form-check-input"
                                                            name="gender_pasien" id="gender_pasien_male" value="L"
                                                            checked style="cursor: pointer; width: 20px; height: 20px;">
                                                        <label class="form-check-label ml-3 mb-0" for="gender_pasien_male"
                                                            style="cursor: pointer; font-size: 16px; font-weight: 600; color: #1976d2;">
                                                            <i class="fa fa-mars mr-2"
                                                                style="font-size: 20px;"></i>Laki-laki
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm mb-2"
                                                style="cursor: pointer; transition: all 0.3s;"
                                                onclick="$('#gender_pasien_female').prop('checked', true).trigger('change');">
                                                <div class="card-body p-3"
                                                    style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input type="radio" class="form-check-input"
                                                            name="gender_pasien" id="gender_pasien_female" value="P"
                                                            style="cursor: pointer; width: 20px; height: 20px;">
                                                        <label class="form-check-label ml-3 mb-0"
                                                            for="gender_pasien_female"
                                                            style="cursor: pointer; font-size: 16px; font-weight: 600; color: #c2185b;">
                                                            <i class="fa fa-venus mr-2"
                                                                style="font-size: 20px;"></i>Perempuan
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="tmpt_lahir_2" class="font-weight-bold" style="color: #495057;">
                                        <i class="fa fa-map-pin mr-2" style="color: #0b3a5c;"></i>TEMPAT LAHIR
                                    </label>
                                    <div class="mb-2" style="position: relative; z-index: 200;">
                                        <label class="small font-weight-bold text-muted mb-1" for="search_tmpt_lahir_input">
                                            <i class="fa fa-search mr-1"></i> Cari kota/kabupaten atau kecamatan
                                        </label>
                                        <div style="position: relative;">
                                            <input type="text" class="form-control" id="search_tmpt_lahir_input"
                                                placeholder="Ketik nama kabupaten/kota atau kecamatan..."
                                                autocomplete="off"
                                                style="border: 2px solid #e2e8f0; border-radius: 8px; padding-left: 38px; font-size: 14px; height: 42px;">
                                            <i class="fa fa-search position-absolute"
                                                style="left: 14px; top: 12px; color: #0b3a5c; pointer-events: none;"></i>
                                            <div id="search_tmpt_lahir_results"
                                                style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                                                <div class="card border-0 shadow-lg">
                                                    <div class="list-group list-group-flush" id="search_tmpt_lahir_results_list"
                                                        style="max-height: 220px; overflow-y: auto;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                <i class="fa fa-map-pin"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="tmpt_lahir"
                                            id="tmpt_lahir_2" placeholder="Contoh: Jakarta atau Bandung"
                                            style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Opsional — pilih dari master wilayah atau ketik manual
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="datelab_samples" class="font-weight-bold mb-3" style="color: #495057;">
                                        <i class="fa fa-calendar mr-2" style="color: #0b3a5c;"></i>TANGGAL LAHIR
                                        <span style="color: red">*</span>
                                    </label>

                                    <!-- Hidden input for storing the final date value -->
                                    <input type="hidden" class="form-control js-date datepicker" name="tgllahir_pasien"
                                        id="tgllahir_pasien_2" placeholder="dd/mm/yyyy">

                                    <!-- Toggle Mode Buttons -->
                                    <div class="mb-3 d-flex justify-content-center"
                                        style="position: relative; z-index: 100;">
                                        <div class="btn-group" role="group" style="position: relative; z-index: 100;">
                                            <button type="button" class="btn btn-primary" id="btn_mode_dropdown"
                                                style="border-radius: 8px 0 0 8px; padding: 10px 20px; font-weight: 600; position: relative; z-index: 101;">
                                                <i class="fa fa-list mr-1"></i> Pilih Dropdown
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" id="btn_mode_manual"
                                                style="border-radius: 0 8px 8px 0; padding: 10px 20px; font-weight: 600; position: relative; z-index: 101;">
                                                <i class="fa fa-keyboard mr-1"></i> Input Manual
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-center mb-3" style="position: relative; z-index: 100;">
                                        <small class="text-muted">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            <span id="mode_info">Pilih mode input yang Anda inginkan (Dropdown atau
                                                Manual)</span>
                                        </small>
                                    </div>

                                    <!-- Dropdown Mode -->
                                    <div id="birth_dropdown_container" class="card border-0 shadow-sm"
                                        style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); position: relative; z-index: 10;">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="small font-weight-bold text-muted mb-2">
                                                        <i class="fa fa-calendar-day mr-1"></i>Tanggal
                                                    </label>
                                                    <select class="form-control" id="birth_day"
                                                        onchange="updateBirthDate()"
                                                        style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px; font-weight: 600;">
                                                        <option value="">Pilih</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small font-weight-bold text-muted mb-2">
                                                        <i class="fa fa-calendar-alt mr-1"></i>Bulan
                                                    </label>
                                                    <select class="form-control" id="birth_month"
                                                        onchange="updateBirthDate()"
                                                        style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px; font-weight: 600;">
                                                        <option value="">Pilih</option>
                                                        <option value="01">Januari</option>
                                                        <option value="02">Februari</option>
                                                        <option value="03">Maret</option>
                                                        <option value="04">April</option>
                                                        <option value="05">Mei</option>
                                                        <option value="06">Juni</option>
                                                        <option value="07">Juli</option>
                                                        <option value="08">Agustus</option>
                                                        <option value="09">September</option>
                                                        <option value="10">Oktober</option>
                                                        <option value="11">November</option>
                                                        <option value="12">Desember</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small font-weight-bold text-muted mb-2">
                                                        <i class="fa fa-calendar mr-1"></i>Tahun
                                                    </label>
                                                    <select class="form-control" id="birth_year"
                                                        onchange="updateBirthDate()"
                                                        style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px; font-weight: 600;">
                                                        <option value="">Pilih</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Display Selected Date -->
                                            <div class="mt-3 p-2 text-center"
                                                style="background: rgba(255, 255, 255, 0.7); border-radius: 8px;">
                                                <small class="text-muted">Tanggal Lahir:</small>
                                                <div id="selected_birth_date"
                                                    style="font-size: 18px; font-weight: bold; color: #e65100;">
                                                    -- Belum dipilih --
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Manual Input Mode -->
                                    <div id="birth_manual_container" class="card border-0 shadow-sm"
                                        style="display: none; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); position: relative; z-index: 10;">
                                        <div class="card-body p-3">
                                            <label class="small font-weight-bold mb-2" style="color: #2e7d32;">
                                                <i class="fa fa-keyboard mr-1"></i>Ketik Angka Tanggal Lahir Langsung
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"
                                                        style="background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); border: none; color: white;">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" id="birth_manual_input"
                                                    placeholder="Ketik: 23021990" maxlength="10" inputmode="numeric"
                                                    oninput="formatBirthDate(this)"
                                                    style="border: 2px solid #4caf50; border-left: none; font-size: 18px; height: 50px; font-weight: 600; letter-spacing: 2px;">
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-success" style="font-weight: 600;">
                                                    <i class="fa fa-magic mr-1"></i><strong>Tips:</strong> Ketik angka
                                                    saja,
                                                    slash otomatis muncul!
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    Ketik <code
                                                        style="background: #fff; padding: 2px 6px; border-radius: 4px; color: #4caf50; font-weight: bold;">23</code>
                                                    → <strong>23/</strong> ✓
                                                    lalu <code
                                                        style="background: #fff; padding: 2px 6px; border-radius: 4px; color: #4caf50; font-weight: bold;">02</code>
                                                    → <strong>23/02/</strong> ✓
                                                    lalu <code
                                                        style="background: #fff; padding: 2px 6px; border-radius: 4px; color: #4caf50; font-weight: bold;">1990</code>
                                                    → <strong>23/02/1990</strong> ✓
                                                </small>
                                            </div>

                                            <!-- Real-time format preview -->
                                            <div class="mt-2 p-2" id="typing_preview"
                                                style="background: rgba(255, 255, 255, 0.5); border-radius: 6px; min-height: 35px; border: 1px dashed #4caf50; display: none;">
                                                <small class="text-muted">Sedang mengetik:</small>
                                                <div id="typing_preview_text"
                                                    style="font-size: 16px; font-weight: bold; color: #2e7d32; letter-spacing: 1px;">
                                                </div>
                                            </div>

                                            <!-- Display Parsed Date -->
                                            <div class="mt-3 p-3 text-center" id="manual_date_preview"
                                                style="background: rgba(255, 255, 255, 0.9); border-radius: 8px; display: none; border: 2px solid #4caf50;">
                                                <small class="text-muted">✓ Tanggal Lahir Valid:</small>
                                                <div id="manual_birth_date_display"
                                                    style="font-size: 20px; font-weight: bold; color: #2e7d32; margin-top: 5px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Age Display -->
                                    <div class="card border-0 shadow-sm mt-3" id="age_display_container"
                                        style="display: none; background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);">
                                        <div class="card-body p-3">
                                            <div class="text-center mb-2">
                                                <i class="fa fa-birthday-cake mr-2"
                                                    style="color: #0277bd; font-size: 20px;"></i>
                                                <strong style="color: #0277bd; font-size: 16px;">UMUR PASIEN</strong>
                                            </div>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="p-2" style="background: white; border-radius: 8px;">
                                                        <div style="font-size: 24px; font-weight: bold; color: #0277bd;"
                                                            id="age_years">0</div>
                                                        <small class="text-muted">Tahun</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2" style="background: white; border-radius: 8px;">
                                                        <div style="font-size: 24px; font-weight: bold; color: #0277bd;"
                                                            id="age_months">0</div>
                                                        <small class="text-muted">Bulan</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2" style="background: white; border-radius: 8px;">
                                                        <div style="font-size: 24px; font-weight: bold; color: #0277bd;"
                                                            id="age_days">0</div>
                                                        <small class="text-muted">Hari</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    // Global function to calculate age from birth date
                                    // Attach to window to ensure it's truly global and overrides any local function
                                    window.calculateAge = function(birthDate) {
                                        console.log('🔢 calculateAge (GLOBAL) called with:', birthDate);

                                        const today = new Date();
                                        const birth = new Date(birthDate);

                                        console.log('Today:', today);
                                        console.log('Birth:', birth);

                                        let years = today.getFullYear() - birth.getFullYear();
                                        let months = today.getMonth() - birth.getMonth();
                                        let days = today.getDate() - birth.getDate();

                                        console.log('Before adjustment:', {
                                            years,
                                            months,
                                            days
                                        });

                                        if (days < 0) {
                                            months--;
                                            const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                                            days += lastMonth.getDate();
                                            console.log('After days adjustment:', {
                                                years,
                                                months,
                                                days
                                            });
                                        }

                                        if (months < 0) {
                                            years--;
                                            months += 12;
                                            console.log('After months adjustment:', {
                                                years,
                                                months,
                                                days
                                            });
                                        }

                                        const result = {
                                            years: years,
                                            months: months,
                                            days: days
                                        };

                                        console.log('Returning result:', result);
                                        return result;
                                    };

                                    // Global function to update age display
                                    // Attach to window to ensure it's truly global
                                    window.updateAgeDisplay = function(birthDateStr) {
                                        console.log('🎂 updateAgeDisplay called with:', birthDateStr);
                                        console.log('Age container exists:', $('#age_display_container').length);
                                        console.log('Age container visible before:', $('#age_display_container').is(':visible'));

                                        if (!birthDateStr) {
                                            $('#age_display_container').hide();
                                            $('#umurtahun_pasien_permohonan_uji_klinik').val('');
                                            $('#umurbulan_pasien_permohonan_uji_klinik').val('');
                                            $('#umurhari_pasien_permohonan_uji_klinik').val('');
                                            console.log('⚠ No birth date, hiding age display');
                                            return;
                                        }

                                        // Parse dd/MM/yyyy format
                                        const parts = birthDateStr.split('/');
                                        console.log('Date parts:', parts);

                                        if (parts.length === 3) {
                                            const day = parseInt(parts[0]);
                                            const month = parseInt(parts[1]) - 1; // JS months are 0-indexed
                                            const year = parseInt(parts[2]);

                                            console.log('Parsed values:', {
                                                day,
                                                month: month + 1,
                                                year
                                            });

                                            const birthDate = new Date(year, month, day);
                                            console.log('Birth date object:', birthDate);
                                            console.log('Is valid date:', !isNaN(birthDate.getTime()));

                                            if (!isNaN(birthDate.getTime())) {
                                                const age = calculateAge(birthDate);
                                                console.log('✓ Age calculated:', age);

                                                // Update text
                                                console.log('Updating #age_years to:', age.years);
                                                $('#age_years').text(age.years);
                                                console.log('#age_years after update:', $('#age_years').text());

                                                console.log('Updating #age_months to:', age.months);
                                                $('#age_months').text(age.months);
                                                console.log('#age_months after update:', $('#age_months').text());

                                                console.log('Updating #age_days to:', age.days);
                                                $('#age_days').text(age.days);
                                                console.log('#age_days after update:', $('#age_days').text());

                                                // Show container
                                                console.log('Showing age container...');
                                                $('#age_display_container').css('display', 'block');
                                                console.log('Age container visible after:', $('#age_display_container').is(':visible'));

                                                // Update hidden fields for form submission
                                                $('#umurtahun_pasien_permohonan_uji_klinik').val(age.years);
                                                $('#umurbulan_pasien_permohonan_uji_klinik').val(age.months);
                                                $('#umurhari_pasien_permohonan_uji_klinik').val(age.days);
                                                $('#tgllahir_pasien_permohonan_uji_klinik').val(birthDateStr);

                                                console.log('✓ Age display updated and shown');
                                            } else {
                                                console.log('✗ Invalid birth date');
                                            }
                                        } else {
                                            console.log('✗ Invalid date format, parts.length:', parts.length);
                                        }
                                    };

                                    // Global function for inline onchange (more reliable!)
                                    // Attach to window to ensure it's truly global
                                    window.updateBirthDate = function() {
                                        console.log('📅 updateBirthDate called!');
                                        const day = $('#birth_day').val();
                                        const month = $('#birth_month').val();
                                        const year = $('#birth_year').val();

                                        console.log('Selected values:', {
                                            day,
                                            month,
                                            year
                                        });

                                        if (day && month && year) {
                                            // Format: dd/MM/yyyy
                                            const formattedDate = `${day}/${month}/${year}`;
                                            $('#tgllahir_pasien_2').val(formattedDate);
                                            console.log('✓ Date formatted:', formattedDate);

                                            // Get month name
                                            const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                            ];
                                            const monthName = monthNames[parseInt(month)];

                                            // Display formatted date
                                            $('#selected_birth_date').html(
                                                `<i class="fa fa-check-circle mr-2" style="color: #4caf50;"></i>${day} ${monthName} ${year}`
                                            );

                                            // Add visual feedback
                                            $('#selected_birth_date').parent().css('background', 'rgba(76, 175, 80, 0.1)');
                                            console.log('✓ Date display updated');

                                            // Calculate and display age
                                            updateAgeDisplay(formattedDate);
                                        } else {
                                            $('#tgllahir_pasien_2').val('');
                                            $('#selected_birth_date').html('-- Belum dipilih --');
                                            $('#selected_birth_date').parent().css('background', 'rgba(255, 255, 255, 0.7)');
                                            updateAgeDisplay('');
                                            console.log('⚠ Not all values selected yet');
                                        }
                                    };

                                    // Global function for inline oninput (more reliable!)
                                    // Attach to window to ensure it's truly global
                                    window.formatBirthDate = function(input) {
                                        console.log('🔥 formatBirthDate called!');
                                        console.log('Raw value:', input.value);

                                        // Remove all non-digits
                                        let value = input.value.replace(/[^\d]/g, '');
                                        console.log('Cleaned:', value, '| Length:', value.length);

                                        let formatted = '';

                                        // Build formatted string
                                        if (value.length > 0) {
                                            formatted = value.substring(0, 2); // dd
                                        }
                                        if (value.length >= 2) {
                                            formatted += '/'; // First slash after 2 digits
                                            console.log('✓ First slash added:', formatted);
                                        }
                                        if (value.length >= 3) {
                                            formatted += value.substring(2, 4); // MM
                                        }
                                        if (value.length >= 4) {
                                            formatted += '/'; // Second slash after 4 digits
                                            console.log('✓ Second slash added:', formatted);
                                        }
                                        if (value.length >= 5) {
                                            formatted += value.substring(4, 8); // yyyy
                                        }

                                        console.log('→ Final:', formatted);
                                        input.value = formatted;

                                        // Update preview and age
                                        updateBirthDatePreview(value, formatted);
                                    };

                                    // Function to update preview and calculate age
                                    // Attach to window to ensure it's truly global
                                    window.updateBirthDatePreview = function(value, formatted) {
                                        if (value.length > 0 && value.length < 8) {
                                            $('#typing_preview').show();
                                            let msg = '';
                                            if (value.length === 2) msg = formatted + ' ✓ slash otomatis!';
                                            else if (value.length === 4) msg = formatted + ' ✓ slash otomatis!';
                                            else msg = formatted + ' (ketik ' + (8 - value.length) + ' digit lagi)';
                                            $('#typing_preview_text').text(msg);
                                        } else if (value.length === 0) {
                                            $('#typing_preview').hide();
                                        }

                                        // If complete, validate and show
                                        if (formatted.length === 10) {
                                            $('#typing_preview').hide();
                                            const parts = formatted.split('/');
                                            const day = parseInt(parts[0]);
                                            const month = parseInt(parts[1]);
                                            const year = parseInt(parts[2]);
                                            const currentYear = new Date().getFullYear();

                                            if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= currentYear) {
                                                const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                                ];
                                                $('#manual_birth_date_display').html(
                                                    `<i class="fa fa-check-circle mr-2" style="color: #4caf50; font-size: 22px;"></i>${day} ${monthNames[month]} ${year}`
                                                );
                                                $('#manual_date_preview').slideDown(200);
                                                $('#tgllahir_pasien_2').val(formatted);

                                                // Calculate age
                                                const birthDate = new Date(year, month - 1, day);
                                                const today = new Date();
                                                let years = today.getFullYear() - birthDate.getFullYear();
                                                let months = today.getMonth() - birthDate.getMonth();
                                                let days = today.getDate() - birthDate.getDate();

                                                if (days < 0) {
                                                    months--;
                                                    const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                                                    days += lastMonth.getDate();
                                                }
                                                if (months < 0) {
                                                    years--;
                                                    months += 12;
                                                }

                                                $('#age_years').text(years);
                                                $('#age_months').text(months);
                                                $('#age_days').text(days);
                                                $('#age_display_container').fadeIn();
                                                $('#umurtahun_pasien_permohonan_uji_klinik').val(years);
                                                $('#umurbulan_pasien_permohonan_uji_klinik').val(months);
                                                $('#umurhari_pasien_permohonan_uji_klinik').val(days);

                                                console.log('✓ Valid date! Age:', years, 'years', months, 'months', days, 'days');
                                            } else {
                                                $('#typing_preview').show();
                                                $('#typing_preview_text').html('<span style="color: #f44336;">❌ Tanggal tidak valid!</span>');
                                            }
                                        }
                                    };

                                    $(document).ready(function() {
                                        console.log('🚀 Document ready - initializing birth date inputs');

                                        // FORCE RE-ATTACH global functions to ensure they override any conflicting functions
                                        // This is needed because there might be duplicate function definitions elsewhere
                                        setTimeout(function() {
                                            console.log('🔧 Force re-attaching global functions...');

                                            // Store reference to ensure our functions are used
                                            const ourCalculateAge = window.calculateAge;
                                            const ourUpdateAgeDisplay = window.updateAgeDisplay;

                                            console.log('Our calculateAge type:', typeof ourCalculateAge);
                                            console.log('Our updateAgeDisplay type:', typeof ourUpdateAgeDisplay);

                                            // Test our function
                                            const testResult = ourCalculateAge(new Date(2024, 1, 2));
                                            console.log('Test calculateAge result:', testResult);

                                            if (testResult && testResult.years !== undefined) {
                                                console.log('✓ Functions are working correctly!');
                                            } else {
                                                console.error('✗ Functions NOT working! Result:', testResult);
                                            }
                                        }, 100);

                                        // Populate days (1-31)
                                        for (let i = 1; i <= 31; i++) {
                                            const day = i.toString().padStart(2, '0');
                                            $('#birth_day').append(`<option value="${day}">${day}</option>`);
                                        }

                                        // Populate years (current year - 100 to current year)
                                        const currentYear = new Date().getFullYear();
                                        for (let i = currentYear; i >= currentYear - 100; i--) {
                                            $('#birth_year').append(`<option value="${i}">${i}</option>`);
                                        }

                                        // Note: calculateAge() and updateAgeDisplay() are now global functions (defined above)

                                        // Note: updateBirthDate() is now a global function (defined above)
                                        // It's called via inline onchange="updateBirthDate()" on dropdowns

                                        // Attach event listeners as backup (jQuery handlers)
                                        $('#birth_day, #birth_month, #birth_year').on('change', function() {
                                            console.log('🔔 Dropdown changed:', this.id, '=', $(this).val());
                                            updateBirthDate();
                                        });

                                        // Delegated event as backup
                                        $(document).on('change', '#birth_day, #birth_month, #birth_year', function() {
                                            console.log('🔔 (Delegated) Dropdown changed:', this.id, '=', $(this).val());
                                            updateBirthDate();
                                        });

                                        // Check if dropdowns exist
                                        console.log('Birth dropdowns exist:', {
                                            day: $('#birth_day').length,
                                            month: $('#birth_month').length,
                                            year: $('#birth_year').length
                                        });

                                        // Debug: Check if buttons exist
                                        console.log('Dropdown button exists:', $('#btn_mode_dropdown').length);
                                        console.log('Manual button exists:', $('#btn_mode_manual').length);

                                        // Toggle between dropdown and manual mode
                                        function switchToDropdownMode() {
                                            console.log('=== SWITCHING TO DROPDOWN MODE ===');

                                            // Update button styles
                                            $('#btn_mode_dropdown').removeClass('btn-outline-primary').addClass('btn-primary');
                                            $('#btn_mode_manual').removeClass('btn-primary').addClass('btn-outline-primary');

                                            console.log('Button classes updated');

                                            // Show dropdown, hide manual - using show/hide for reliability
                                            $('#birth_dropdown_container').show();
                                            $('#birth_manual_container').hide();

                                            console.log('Containers toggled');

                                            // Clear manual input
                                            $('#birth_manual_input').val('');
                                            $('#manual_date_preview').hide();

                                            // Update info text
                                            $('#mode_info').text('Mode Dropdown aktif - Pilih tanggal menggunakan dropdown');

                                            console.log('Dropdown mode activated successfully');
                                        }

                                        function switchToManualMode() {
                                            console.log('=== SWITCHING TO MANUAL MODE ===');

                                            // Update button styles
                                            $('#btn_mode_manual').removeClass('btn-outline-primary').addClass('btn-primary');
                                            $('#btn_mode_dropdown').removeClass('btn-primary').addClass('btn-outline-primary');

                                            console.log('Button classes updated');

                                            // Show manual, hide dropdown - using show/hide for reliability
                                            $('#birth_dropdown_container').hide();
                                            $('#birth_manual_container').show();

                                            console.log('Containers toggled');

                                            // Clear dropdowns
                                            $('#birth_day, #birth_month, #birth_year').val('');
                                            $('#selected_birth_date').html('-- Belum dipilih --');

                                            // Update info text
                                            $('#mode_info').text('Mode Manual aktif - Ketik tanggal dalam format dd/MM/yyyy');

                                            // Focus on manual input
                                            setTimeout(function() {
                                                $('#birth_manual_input').focus();
                                                console.log('Focus set to manual input');
                                            }, 100);

                                            console.log('Manual mode activated successfully');
                                        }

                                        $('#btn_mode_dropdown').on('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            console.log('>>> Dropdown button clicked <<<');
                                            switchToDropdownMode();
                                        });

                                        $('#btn_mode_manual').on('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            console.log('>>> Manual button clicked <<<');
                                            switchToManualMode();
                                        });

                                        // Alternative event binding (fallback) - also triggers the switch
                                        $(document).on('click', '#btn_mode_dropdown', function(e) {
                                            console.log('=== Dropdown clicked (delegated) ===');
                                            if (!$(this).hasClass('btn-primary')) {
                                                switchToDropdownMode();
                                            }
                                        });

                                        $(document).on('click', '#btn_mode_manual', function(e) {
                                            console.log('=== Manual clicked (delegated) ===');
                                            if (!$(this).hasClass('btn-primary')) {
                                                switchToManualMode();
                                            }
                                        });

                                        // Test on document ready
                                        setTimeout(function() {
                                            console.log('=== Testing button clickability after 1 second ===');
                                            console.log('Dropdown button visible:', $('#btn_mode_dropdown').is(':visible'));
                                            console.log('Manual button visible:', $('#btn_mode_manual').is(':visible'));
                                            console.log('Dropdown button z-index:', $('#btn_mode_dropdown').css('z-index'));
                                            console.log('Manual button z-index:', $('#btn_mode_manual').css('z-index'));
                                            console.log('Parent container (#display-new-pasien) visible:', $('#display-new-pasien').is(
                                                ':visible'));
                                            console.log('Birth dropdown container visible:', $('#birth_dropdown_container').is(
                                                ':visible'));
                                            console.log('Birth manual container visible:', $('#birth_manual_container').is(':visible'));
                                        }, 1000);

                                        // Check if manual input exists
                                        console.log('Manual input exists:', $('#birth_manual_input').length);

                                        // Manual input with auto-formatting and real-time preview
                                        $('#birth_manual_input').on('input keyup paste', function(e) {
                                            console.log('=== Input event triggered ===');
                                            console.log('Raw value:', this.value);

                                            let value = this.value.replace(/[^\d]/g, ''); // Remove non-digits only
                                            console.log('Cleaned value (digits only):', value);
                                            console.log('Digit count:', value.length);

                                            let formatted = '';

                                            // Auto-add slashes IMMEDIATELY after reaching length
                                            if (value.length > 0) {
                                                formatted = value.substring(0, 2); // dd
                                                console.log('After dd:', formatted);
                                            }
                                            if (value.length >= 2) {
                                                formatted += '/'; // Slash langsung setelah 2 digit!
                                                console.log('After first slash:', formatted);
                                            }
                                            if (value.length >= 3) {
                                                formatted += value.substring(2, 4); // MM
                                                console.log('After MM:', formatted);
                                            }
                                            if (value.length >= 4) {
                                                formatted += '/'; // Slash langsung setelah bulan!
                                                console.log('After second slash:', formatted);
                                            }
                                            if (value.length >= 5) {
                                                formatted += value.substring(4, 8); // yyyy
                                                console.log('After yyyy:', formatted);
                                            }

                                            console.log('Final formatted:', formatted);
                                            this.value = formatted;
                                            console.log('=== End input event ===');

                                            // Show typing preview while typing (incomplete)
                                            if (value.length > 0 && value.length < 8) {
                                                $('#typing_preview').show();
                                                let previewText = '';

                                                if (value.length === 1) {
                                                    previewText = formatted + ' (ketik 1 digit lagi)';
                                                } else if (value.length === 2) {
                                                    previewText = formatted + ' ✓ slash otomatis!';
                                                } else if (value.length === 3) {
                                                    previewText = formatted + ' (ketik 1 digit lagi)';
                                                } else if (value.length === 4) {
                                                    previewText = formatted + ' ✓ slash otomatis!';
                                                } else {
                                                    previewText = formatted + ' (ketik ' + (8 - value.length) + ' digit lagi)';
                                                }

                                                $('#typing_preview_text').text(previewText);
                                                $('#manual_date_preview').hide();
                                            } else if (value.length === 0) {
                                                $('#typing_preview').hide();
                                                $('#manual_date_preview').hide();
                                            }

                                            // Validate and display if complete (dd/MM/yyyy = 10 chars)
                                            if (formatted.length === 10) {
                                                $('#typing_preview').hide();

                                                const parts = formatted.split('/');
                                                const day = parseInt(parts[0]);
                                                const month = parseInt(parts[1]);
                                                const year = parseInt(parts[2]);

                                                // Basic validation
                                                if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <=
                                                    currentYear) {
                                                    const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                                    ];
                                                    const monthName = monthNames[month];

                                                    $('#manual_birth_date_display').html(
                                                        `<i class="fa fa-check-circle mr-2" style="color: #4caf50; font-size: 22px;"></i>${parts[0]} ${monthName} ${year}`
                                                    );
                                                    $('#manual_date_preview').slideDown(200);

                                                    // Update hidden field
                                                    $('#tgllahir_pasien_2').val(formatted);

                                                    // Calculate and display age
                                                    updateAgeDisplay(formatted);

                                                    console.log('✓ Tanggal lahir valid:', formatted);
                                                } else {
                                                    $('#manual_date_preview').hide();
                                                    $('#tgllahir_pasien_2').val('');
                                                    updateAgeDisplay('');
                                                    $('#typing_preview').show();
                                                    $('#typing_preview_text').html(
                                                        '<span style="color: #f44336;">❌ Tanggal tidak valid!</span>');
                                                    console.log('✗ Tanggal tidak valid:', formatted);
                                                }
                                            } else {
                                                if (value.length === 8) {
                                                    // Full 8 digits but not valid
                                                    $('#typing_preview').show();
                                                    $('#typing_preview_text').html(
                                                        '<span style="color: #ff9800;">⚠ Memeriksa...</span>');
                                                }
                                                $('#tgllahir_pasien_2').val('');
                                                updateAgeDisplay('');
                                            }
                                        });

                                        // Delegated event as backup (if input added dynamically)
                                        $(document).on('input keyup paste', '#birth_manual_input', function(e) {
                                            console.log('=== DELEGATED Input event triggered ===');
                                            // Same logic would be here, but for now just log
                                        });

                                        // Add hover effects for gender cards
                                        $('.form-check').closest('.card').hover(
                                            function() {
                                                $(this).css('transform', 'scale(1.02)');
                                                $(this).css('box-shadow', '0 4px 12px rgba(0,0,0,0.15)');
                                            },
                                            function() {
                                                $(this).css('transform', 'scale(1)');
                                                $(this).css('box-shadow', '0 1px 3px rgba(0,0,0,0.12)');
                                            }
                                        );

                                        // Add visual feedback when radio is checked
                                        $('input[name="gender_pasien"]').on('change', function() {
                                            $('.form-check').closest('.card').removeClass('border-primary').css('border-width', '0');
                                            $(this).closest('.card').addClass('border-primary').css('border-width', '3px');
                                        });

                                        // Set initial border for checked radio
                                        $('input[name="gender_pasien"]:checked').closest('.card').addClass('border-primary').css('border-width',
                                            '3px');

                                        // NIK input validation (only numbers)
                                        $('#nik_pasien_2').on('input', function() {
                                            this.value = this.value.replace(/[^\d]/g, '');
                                        });

                                        // Name input - auto capitalize
                                        $('#nama_pasien_2').on('input', function() {
                                            this.value = this.value.toUpperCase();
                                        });
                                    });
                                </script>

                                <div class="form-group">
                                    <label for="pekerjaan_2" class="font-weight-bold" style="color: #495057;">
                                        <i class="fa fa-briefcase mr-2" style="color: #0b3a5c;"></i>PEKERJAAN
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                <i class="fa fa-briefcase"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="pekerjaan"
                                            id="pekerjaan_2" placeholder="Contoh: Pegawai Swasta"
                                            style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Opsional
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="phone_pasien_2" class="font-weight-bold" style="color: #495057;">
                                        <i class="fa fa-phone mr-2" style="color: #0b3a5c;"></i>NO. TELP/HP
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                <i class="fa fa-phone"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="phone_pasien"
                                            id="phone_pasien_2" placeholder="Contoh: 081234567890"
                                            style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Nomor telepon/HP yang dapat dihubungi
                                    </small>
                                </div>

                                <script>
                                    $(document).ready(function() {
                                        // Phone number validation (only numbers)
                                        $('#phone_pasien_2').on('input', function() {
                                            this.value = this.value.replace(/[^\d]/g, '');
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                        <!-- End Beautiful Patient Information Section -->

                        <!-- Wilayah Section with Beautiful Design -->
                        <div class="form-group">
                            <label class="font-weight-bold mb-3" style="color: #0b3a5c; font-size: 16px;">
                                <i class="fa fa-map-marker mr-2"></i>WILAYAH DOMISILI
                            </label>

                            <!-- Search Wilayah Box -->
                            <div class="mb-3" style="position: relative; z-index: 100;">
                                <div class="card border-0 shadow-sm"
                                    style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #0b3a5c !important; overflow: visible !important;">
                                    <div class="card-body py-3" style="overflow: visible !important;">
                                        <div class="wilayah-quick-search-row">
                                            <div class="flex-grow-1 position-relative" style="z-index: 1000;">
                                                <label class="small font-weight-bold mb-2" style="color: #1976d2;">
                                                    Pencarian Cepat Wilayah
                                                </label>
                                                <div class="wilayah-quick-search">
                                                    <i class="fa fa-search wilayah-quick-search__icon" aria-hidden="true"></i>
                                                    <input type="text" class="form-control form-control-lg wilayah-quick-search__input"
                                                        id="search_wilayah_input"
                                                        placeholder="Ketik nama desa, kecamatan, atau kabupaten... (min 2 karakter)"
                                                        autocomplete="off"
                                                        style="border: 2px solid #1976d2; border-radius: 10px; font-size: 15px;">

                                                    <!-- Autocomplete Results -->
                                                    <div id="search_wilayah_results"
                                                        style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                                                        <div class="card border-0 shadow-lg"
                                                            style="margin-bottom: 0 !important;">
                                                            <div class="list-group list-group-flush"
                                                                id="search_wilayah_results_list"
                                                                style="max-height: 400px; overflow-y: auto;">
                                                                <!-- Results will be populated here -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ml-3 text-center">
                                                <button type="button" class="btn btn-sm" id="btn_toggle_manual_select"
                                                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                                                    <i class="fa fa-list mr-1"></i> Pilih Manual
                                                </button>
                                                <div class="small text-muted mt-1">atau pilih bertahap</div>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-2">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            <strong>Tips:</strong> Ketik minimal 2 karakter untuk melihat rekomendasi.
                                            Contoh: "Jakarta", "Bandung", "Surabaya"
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cascade Dropdown (Can be collapsed) -->
                            <div class="card border-0 shadow-sm mb-3" id="manual_wilayah_selector"
                                style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: none; position: relative; z-index: 10;">
                                <div class="card-body" style="overflow: visible !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 font-weight-bold" style="color: #0b3a5c;">
                                            <i class="fa fa-list-ul mr-2"></i>Pilih Wilayah Secara Bertahap
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="btn_hide_manual_select">
                                            <i class="fa fa-times"></i> Tutup
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="provinsi_pasien_2"
                                                class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-globe mr-1"></i> Provinsi <span
                                                    class="text-danger">*</span>
                                            </label>
                                            <select class="form-control select-wilayah" id="provinsi_pasien_2"
                                                name="provinsi_pasien">
                                                <option value="">-- Pilih Provinsi --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="kabupaten_pasien_2"
                                                class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-building mr-1"></i> Kabupaten/Kota <span
                                                    class="text-danger">*</span>
                                            </label>
                                            <select class="form-control select-wilayah" id="kabupaten_pasien_2"
                                                name="kabupaten_pasien" disabled>
                                                <option value="">-- Pilih Kabupaten/Kota --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="kecamatan_pasien_2"
                                                class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-map-signs mr-1"></i> Kecamatan
                                            </label>
                                            <select class="form-control select-wilayah" id="kecamatan_pasien_2"
                                                name="kecamatan_pasien" disabled>
                                                <option value="">-- Pilih Kecamatan --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="desa_pasien_2" class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-home mr-1"></i> Desa/Kelurahan
                                            </label>
                                            <select class="form-control select-wilayah" id="desa_pasien_2"
                                                name="desa_pasien" disabled>
                                                <option value="">-- Pilih Desa/Kelurahan --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alamat_pasien_2" class="font-weight-bold" style="color: #0b3a5c;">
                                <i class="fa fa-map-marker mr-2"></i>ALAMAT <span style="color: red">*</span>
                            </label>
                            <textarea class="form-control" name="alamat_pasien" id="alamat_pasien_2" rows="3"
                                placeholder="Alamat terisi otomatis sesuai wilayah yang dipilih (desa/kecamatan/kabupaten)"
                                style="border: 2px solid #e2e8f0; border-radius: 8px; resize: vertical;"></textarea>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Alamat mengikuti wilayah domisili yang dipilih, hingga tingkat desa/kelurahan.
                            </small>
                        </div>
                    </div>
                </div>

                <input type="hidden" class="form-control date_birth_last"
                    name="tgllahir_pasien_permohonan_uji_klinik" id="tgllahir_pasien_permohonan_uji_klinik"
                    placeholder="dd/mm/yyyy" readonly>

                <div class="form-group">
                    <label for="datelab_samples">UMUR PASIEN</label>
                    <div class="row">
                        <div class="col-sm">
                            <div class="input-group">
                                <input type="text" class="form-control"
                                    name="umurtahun_pasien_permohonan_uji_klinik"
                                    id="umurtahun_pasien_permohonan_uji_klinik" placeholder="Umur" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        tahun
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="input-group">
                                <input type="text" class="form-control"
                                    name="umurbulan_pasien_permohonan_uji_klinik"
                                    id="umurbulan_pasien_permohonan_uji_klinik" placeholder="Umur" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        Bulan
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="input-group">
                                <input type="text" class="form-control"
                                    name="umurhari_pasien_permohonan_uji_klinik"
                                    id="umurhari_pasien_permohonan_uji_klinik" placeholder="Umur" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        Hari
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" hidden>
                    <div class="col-md-12">
                        <div class="form-group table-sample">
                            <label for="jenis_spesimen">JENIS SPESIMEN<span style="color: red">*</span></label>
                            <div class="input-group date">
                                <select class="form-control" name="jenis_spesimen" id="jenis_spesimen" required>
                                    <option value="Darah Beku">Darah Beku</option>
                                    <option value="NaF">NaF</option>
                                    <option value="EDTA">EDTA</option>
                                    <option value="Urine">Urine</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group table-sample">
                            <label for="#">TGL. REGISTER <span style="color: red">*</span></label>
                            <div class="input-group date">
                                <input type="datetime-local" class="form-control" autocomplete="on"
                                    name="tglregister_permohonan_uji_klinik" id="tglregister_permohonan_uji_klinik"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label for="nama_dokter_pengirim_permohonan_uji_klinik">NAMA DOKTER PENGIRIM</label>
                    <input type="text" class="form-control" name="nama_dokter_pengirim_permohonan_uji_klinik"
                        id="nama_dokter_pengirim_permohonan_uji_klinik" placeholder="Masukkan nama dokter pengirim">
                </div>

                <div class="form-group">
                    <label for="hp_dokter_pengirim_permohonan_uji_klinik">No. HP DOKTER PENGIRIM</label>
                    <input type="text" class="form-control" name="hp_dokter_pengirim_permohonan_uji_klinik"
                        id="hp_dokter_pengirim_permohonan_uji_klinik" placeholder="Masukkan no. hp dokter pengirim">
                    <script>
                        $(document).ready(function() {
                            $('#hp_dokter_pengirim_permohonan_uji_klinik').on('input', function() {
                                this.value = this.value.replace(/[^\d]+/g, '');
                            });
                        });
                    </script>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" name="isPerwakilan">
                    <label class="form-check-label" for="flexSwitchCheckDefault"
                        style="font-size: 16px; margin-left: 12px;">Perwakilan</label>
                </div>

                <div class="form-group" id="form_perwakilan" style="display: none;">
                    <label for="nama_perwakian_permohonan_uji_klinik">NAMA PERWAKILAN</label>
                    <input type="text" class="form-control" name="nama_perwakian_permohonan_uji_klinik"
                        id="nama_perwakian_permohonan_uji_klinik" placeholder="Masukkan perwakilan">
                    <label for="gender" class="mt-3">JENIS KELAMIN</label>
                    <select class="form-control" id="gender_perwakilan_permohonan_uji_klinik"
                        name="gender_perwakilan_permohonan_uji_klinik">
                        <option value="L">Laki Laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <label for="tanggal_lahir_perwakilan" class="mt-3">TANGGAL LAHIR PERWAKILAN</label>
                    <input type="text" name="tanggal_lahir_perwakilan" value="" id="basic2" />
                    <script type="text/javascript">
                        $('#basic2').bootstrapBirthday({
                            dateFormat: "littleEndian"
                        });
                    </script>
                    <label for="alamat_perwakilan" class="mt-3">ALAMAT PERWAKILAN</label>
                    <textarea class="form-control" name="alamat_perwakilan" id="alamat_perwakilan" rows="3"></textarea>
                    <label for="status_hubungan_perwakilan" class="mt-3">STATUS HUBUNGAN DENGAN PASIEN</label>
                    <select class="form-control" id="status_hubungan_perwakilan_permohonan_uji_klinik_2"
                        name="status_hubungan_perwakilan_permohonan_uji_klinik">
                        <option value="">-- Pilih Status Hubungan --</option>
                        <option value="Orang Tua">Orang Tua</option>
                        <option value="Suami">Suami</option>
                        <option value="Istri">Istri</option>
                        <option value="Anak">Anak</option>
                        <option value="Wali">Wali</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div id="status_hubungan_lainnya_group_2" style="display: none; margin-top: 10px;">
                        <label for="status_hubungan_lainnya_permohonan_uji_klinik_2">KETERANGAN LAINNYA</label>
                        <input type="text" class="form-control" name="status_hubungan_lainnya_permohonan_uji_klinik"
                            id="status_hubungan_lainnya_permohonan_uji_klinik_2" placeholder="Masukkan status hubungan lainnya">
                    </div>
                </div>


                <div class="form-group">
                    <label for="petugas_penerima">Petugas Registrasi</label>
                    <div class="input-group date">
                        <select class="form-control" name="petugas_penerima" id="petugas_penerima">
                            <option value="">Pilih Petugas Registrasi</option>
                            @foreach ($petugasPenerima as $petugas)
                                <option value="{{ $petugas }}">{{ $petugas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="diagnosa_permohonan_uji_klinik">DIAGNOSA</label>
                    <input type="text" class="form-control" name="diagnosa_permohonan_uji_klinik"
                        id="diagnosa_permohonan_uji_klinik" placeholder="Masukkan diagnosa">
                </div>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select class="form-control" name="metode_pembayaran">
                        <option disabled>Metode Pembayaran</option>
                        <option value="0">Cash</option>
                        <option value="1">Transfer</option>
                    </select>
                </div>
            </form>
            </form>
        </div>
        <!-- End of old form elements -->
    </div>

    <!-- Modal Edit Data Pasien Terpilih (di luar wizard agar input tidak terblokir) -->
    <div class="modal fade" id="modalEditPasienTerpilih" tabindex="-1" role="dialog"
        aria-labelledby="modalEditPasienTerpilihLabel" aria-hidden="true" data-backdrop="static" data-keyboard="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white;">
                    <h5 class="modal-title" id="modalEditPasienTerpilihLabel">
                        <i class="fa fa-edit mr-2"></i>Edit Data Pasien
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background: #f8f9fa;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">NIK Pasien</label>
                                <input type="text" class="form-control" id="edit_pasien_nik" readonly tabindex="-1"
                                    style="background: #e9ecef;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">No Rekam Medis</label>
                                <input type="text" class="form-control" id="edit_pasien_rm" readonly tabindex="-1"
                                    style="background: #e9ecef;">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-muted small">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit_pasien_nama" readonly tabindex="-1"
                            style="background: #e9ecef; text-transform: uppercase;">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Jenis Kelamin</label>
                                <input type="text" class="form-control" id="edit_pasien_gender" readonly tabindex="-1"
                                    style="background: #e9ecef;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">ID Satu Sehat</label>
                                <input type="text" class="form-control" id="edit_pasien_satu_sehat" readonly tabindex="-1"
                                    style="background: #e9ecef;">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted small mb-3">
                        <i class="fa fa-info-circle mr-1"></i>Field di bawah dapat diubah sebelum melanjutkan permohonan.
                    </p>

                    <div class="form-group">
                        <label for="edit_pasien_tmpt_lahir" class="font-weight-bold">Tempat Lahir</label>
                        <div class="mb-2" style="position: relative;">
                            <input type="text" class="form-control" id="search_tmpt_lahir_modal_input"
                                placeholder="Cari kabupaten/kota atau kecamatan..." autocomplete="off"
                                style="padding-left: 36px;">
                            <i class="fa fa-search position-absolute"
                                style="left: 12px; top: 11px; color: #0b3a5c; pointer-events: none;"></i>
                            <div id="search_tmpt_lahir_modal_results"
                                style="position: absolute; width: 100%; z-index: 20060; display: none; top: 100%; left: 0; margin-top: 4px;">
                                <div class="card border-0 shadow-lg mb-0">
                                    <div class="list-group list-group-flush" id="search_tmpt_lahir_modal_results_list"
                                        style="max-height: 200px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>
                        <input type="text" class="form-control" id="edit_pasien_tmpt_lahir"
                            placeholder="Contoh: Jakarta atau Bandung">
                        <small class="form-text text-muted">Opsional — pilih dari master wilayah atau ketik manual</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_pasien_tgllahir" class="font-weight-bold">Tanggal Lahir</label>
                        <input type="text" class="form-control" id="edit_pasien_tgllahir"
                            placeholder="dd/mm/yyyy" maxlength="10" autocomplete="off">
                        <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_pasien_pekerjaan" class="font-weight-bold">Pekerjaan</label>
                        <input type="text" class="form-control" id="edit_pasien_pekerjaan"
                            placeholder="Contoh: Pegawai Swasta" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="edit_pasien_phone" class="font-weight-bold">Nomor Telepon</label>
                        <input type="text" class="form-control" id="edit_pasien_phone"
                            placeholder="Contoh: 081234567890" autocomplete="off" inputmode="numeric">
                    </div>

                    <div class="form-group mb-0">
                        <label for="edit_pasien_alamat" class="font-weight-bold">Alamat</label>
                        <textarea class="form-control" id="edit_pasien_alamat" rows="2"
                            placeholder="Alamat lengkap pasien"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: #fff;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-save-edit-pasien-terpilih"
                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none;">
                        <i class="fa fa-check mr-1"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/vendors/Inputmask-5.x/dist/inputmask.js') }}"></script>
    <script>
        function deferCreatePageInit(callback) {
            if (window.requestIdleCallback) {
                requestIdleCallback(callback, { timeout: 2500 });
            } else {
                setTimeout(callback, 400);
            }
        }

        function getCsrfToken() {
            var token = $('#csrf-token-global').val()
                || $('#csrf-token').val()
                || $('input[name="_token"]').first().val()
                || $('meta[name="csrf-token"]').attr('content')
                || '';
            return token;
        }

        function buildPatientSelect2AjaxOptions(dropdownParent) {
            return {
                url: "{{ route('get-pasien-by-select') }}",
                type: 'post',
                dataType: 'json',
                delay: 400,
                data: function(params) {
                    return {
                        _token: getCsrfToken(),
                        search: params.term || ''
                    };
                },
                transport: function(params, success, failure) {
                    var request = $.ajax(params);
                    request.then(success);
                    request.fail(function(jqXHR, textStatus) {
                        if (textStatus === 'abort') {
                            return;
                        }
                        console.error('Gagal memuat data pasien:', jqXHR.status, textStatus, jqXHR.responseText);
                        failure();
                    });
                    return request;
                },
                processResults: function(response) {
                    if (!Array.isArray(response)) {
                        console.error('Format response pasien tidak valid:', response);
                        return { results: [] };
                    }
                    return {
                        results: $.map(response, function(obj) {
                            return {
                                id: obj.id,
                                text: obj.text
                            };
                        })
                    };
                },
                cache: true
            };
        }

        function initPatientSelect2($select, dropdownParent) {
            if (!$select.length || typeof $.fn.select2 === 'undefined') {
                console.warn('Select2 belum siap atau elemen pasien tidak ditemukan');
                return;
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                ajax: buildPatientSelect2AjaxOptions(dropdownParent),
                placeholder: 'Pilih pasien...',
                allowClear: true,
                theme: 'classic',
                width: '100%',
                minimumInputLength: 2,
                dropdownParent: dropdownParent && dropdownParent.length ? dropdownParent : $(document.body),
                language: {
                    inputTooShort: function() {
                        return 'Ketik minimal 2 karakter untuk mencari pasien';
                    },
                    searching: function() {
                        return 'Mencari...';
                    },
                    noResults: function() {
                        return 'Pasien tidak ditemukan';
                    },
                    errorLoading: function() {
                        return 'Gagal memuat data pasien';
                    }
                },
                templateResult: function(data) {
                    if (!data.id) {
                        return data.text;
                    }
                    return $('<span>' + (data.text || '').toUpperCase() + '</span>');
                },
                templateSelection: function(data) {
                    if (!data.id) {
                        return data.text;
                    }
                    return (data.text || '').toUpperCase();
                }
            });
        }

        var step3ControlsInitialized = false;

        function registerMomentPreciseDiff() {
            if (typeof moment === 'undefined' || typeof moment.preciseDiff !== 'undefined') {
                return;
            }
            (function(e) {
                var t = {
                    nodiff: "",
                    year: "year",
                    years: "years",
                    month: "month",
                    months: "months",
                    day: "day",
                    days: "days",
                    hour: "hour",
                    hours: "hours",
                    minute: "minute",
                    minutes: "minutes",
                    second: "second",
                    seconds: "seconds",
                    delimiter: " "
                };
                e.fn.preciseDiff = function(t) {
                    return e.preciseDiff(this, t)
                };
                e.preciseDiff = function(n, r) {
                    function d(e, n) {
                        return e + " " + t[n + (e === 1 ? "" : "s")]
                    }
                    var i = e(n),
                        s = e(r);
                    if (i.isSame(s)) {
                        return t.nodiff
                    }
                    if (i.isAfter(s)) {
                        var o = i;
                        i = s;
                        s = o
                    }
                    var u = s.year() - i.year();
                    var a = s.month() - i.month();
                    var f = s.date() - i.date();
                    var l = s.hour() - i.hour();
                    var c = s.minute() - i.minute();
                    var h = s.second() - i.second();
                    if (h < 0) {
                        h = 60 + h;
                        c--
                    }
                    if (c < 0) {
                        c = 60 + c;
                        l--
                    }
                    if (l < 0) {
                        l = 24 + l;
                        f--
                    }
                    if (f < 0) {
                        var p = e(s.year() + "-" + (s.month() + 1), "YYYY-MM").subtract("months", 1)
                            .daysInMonth();
                        if (p < i.date()) {
                            f = p + f + (i.date() - p)
                        } else {
                            f = p + f
                        }
                        a--
                    }
                    if (a < 0) {
                        a = 12 + a;
                        u--
                    }
                    var v = [];
                    v.push(u || 0);
                    v.push(a || 0);
                    v.push(f || 0);
                    return v
                }
            })(moment);
        }

        function initPetugasPengambilSelect2() {
            var $select = $('#petugas_pengambil_sampel');
            if (!$select.length || typeof $.fn.select2 === 'undefined') {
                return;
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                theme: 'classic',
                width: '100%',
                tags: true,
                placeholder: 'Pilih atau ketik nama petugas...',
                allowClear: true,
                dropdownParent: $('#petugas_pengambil_container'),
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term
                    };
                }
            });
        }

        function setPetugasPengambilValue(value) {
            var $select = $('#petugas_pengambil_sampel');
            if (!$select.length) {
                return;
            }
            value = (value || '').trim();
            if (!value) {
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.val(null).trigger('change');
                } else {
                    $select.val('');
                }
                return;
            }
            if ($select.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length === 0) {
                $select.append(new Option(value, value, true, true));
            }
            $select.val(value).trigger('change');
        }

        function initializeStep3Controls() {
            if (step3ControlsInitialized) {
                return;
            }
            step3ControlsInitialized = true;

            registerMomentPreciseDiff();

            if (typeof flatpickr !== 'undefined' && $('#tglregister_permohonan_uji_klinik_display').length) {
                flatpickr('#tglregister_permohonan_uji_klinik_display', {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'd/m/Y H:i',
                    defaultDate: new Date(),
                    locale: 'id',
                    onChange: function(selectedDates) {
                        if (selectedDates.length > 0) {
                            const date = selectedDates[0];
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            const hours = String(date.getHours()).padStart(2, '0');
                            const minutes = String(date.getMinutes()).padStart(2, '0');
                            const seconds = String(date.getSeconds()).padStart(2, '0');
                            $('#tglregister_permohonan_uji_klinik').val(
                                `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
                            );
                        }
                    }
                });
            }

            if ($('.datepicker').length && typeof $.fn.datepicker !== 'undefined') {
                $('.datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                });
            }

            if ($('.date_birth').length && typeof $.fn.datepicker !== 'undefined') {
                $(".date_birth").datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                });
            }

            initPetugasPengambilSelect2();
            updateNoregisterFromManualNumbers();
        }

        $(document).ready(function() {
            var CSRF_TOKEN = getCsrfToken();

            $("#umurtahun_pasien_permohonan_uji_klinik").val('');
            $("#umurbulan_pasien_permohonan_uji_klinik").val('');
            $("#umurhari_pasien_permohonan_uji_klinik").val('');

            deferCreatePageInit(function() {
                registerMomentPreciseDiff();

                $.fn.select2.defaults.set("theme", "classic");

                var $legacyPasienSelect = $("#pasien_permohonan_uji_klinik");
                if ($legacyPasienSelect.length) {
                    initPatientSelect2($legacyPasienSelect, $legacyPasienSelect.parent());
                }
            });

            $(".date_birth").change(function() {
                var datevalue = $(this).val();
                getAge(datevalue);
            });

            if ($("#pasien_permohonan_uji_klinik").length) {
            $("#pasien_permohonan_uji_klinik").change(function() {
                $('#display-new-pasien').css('display', 'none');
                $('#display-detail-pasien-silaboy').css('display', 'block');
                $("#umurtahun_pasien_permohonan_uji_klinik").val('')
                $("#umurbulan_pasien_permohonan_uji_klinik").val('')
                $("#umurhari_pasien_permohonan_uji_klinik").val('')
                if ($("#pasien_permohonan_uji_klinik").val()) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('get-pasien-by-id') }}",
                        data: {
                            _token: CSRF_TOKEN,
                            pasien_id: $("#pasien_permohonan_uji_klinik").val()
                        },
                        dataType: "JSON",
                        success: function(response) {
                            console.log(response)

                            $('#id_satu_sehat_3').val(response.id_pasien_satu_sehat);
                            if ($('#id_satu_sehat_3').val() == "" || $('#id_satu_sehat_3')
                                .val() === '-') {
                                $('#id_satu_sehat_3').prop('readonly', false);
                            } else {
                                $('#id_satu_sehat_3').prop('readonly', true);
                            }

                            $('#nik_pasien_3').val(response.nik_pasien);
                            if ($('#nik_pasien_3').val() === "" || $('#nik_pasien_3').val() ===
                                '-') {
                                $('#nik_pasien_3').prop('readonly', false);
                            } else {
                                $('#nik_pasien_3').prop('readonly', true);
                            }

                            $('#no_rekammedis_detail_pasien').val(response
                                .no_rekammedis_pasien);
                            $('#nama_pasien_3').val((response.nama_pasien || '').toUpperCase());
                            if ($('#nama_pasien_3').val() === "" || $('#nama_pasien_3')
                                .val() === '-') {
                                $('#nama_pasien_3').prop('readonly', false);
                            } else {
                                $('#nama_pasien_3').prop('readonly', true);
                            }

                            $("#gender_pasien_3").val(response.gender_pasien);
                            if ($('#gender_pasien_3').val() === "" || $('#gender_pasien_3')
                                .val() === '-') {
                                $('#gender_pasien_3').prop('readonly', false);
                            } else {
                                $('#gender_pasien_3').prop('readonly', true);
                            }

                            $('#tgllahir_pasien_3').val(response.tgllahir_pasien_normal);
                            if ($('#tgllahir_pasien_3').val() === "" || $('#tgllahir_pasien_3')
                                .val() === '-') {
                                $('#tgllahir_pasien_3').prop('readonly', false);
                            } else {
                                $('#tgllahir_pasien_3').prop('readonly', true);
                            }

                            $('#phone_pasien_3').val(response.phone_pasien);
                            if ($('#phone_pasien_3').val() === "" || $('#phone_pasien_3')
                                .val() === '-') {
                                $('#phone_pasien_3').prop('readonly', false);
                            } else {
                                $('#phone_pasien_3').prop('readonly', true);
                            }

                            $('#alamat_pasien_3').val(response.alamat_pasien);
                            if ($('#alamat_pasien_3').val() === "" || $('#alamat_pasien_3')
                                .val() === '-') {
                                $('#alamat_pasien_3').prop('readonly', false);
                            } else {
                                $('#alamat_pasien_3').prop('readonly', true);
                            }

                            $('#nopasien_permohonan_uji_klinik').val(response.nik_pasien);

                            $('#seccond_pasien_permohonan_uji_klinik').val($(
                                "#pasien_permohonan_uji_klinik").val());


                            // if ($('#nopasien_permohonan_uji_klinik').val() === "" || $('#nopasien_permohonan_uji_klinik').val() === '-'){
                            //   $('#nopasien_permohonan_uji_klinik').prop('readonly', false);
                            // }else {
                            //   $('#nopasien_permohonan_uji_klinik').prop('readonly', true);
                            // }
                            //
                            $('#tgllahir_pasien_permohonan_uji_klinik').val(response
                                .tgllahir_pasien_normal);
                            getAge(response.tgllahir_pasien_normal);

                            $("#display-detail-pasien input, #display-detail-pasien textarea")
                                .prop("disabled", true);
                            $('#display-new-pasien input, #display-new-pasien textarea').prop(
                                'disabled', true);
                        },
                        error: function() {
                            swal("Error!", "System gagal mendapatkan data pasien!", "error");
                        }
                    });
                } else {
                    $('#display-detail-pasien').css('display', 'none');
                }
            });
            }

            // jika element tersebut kosong maka form umur kosong
            if ($('#tgllahir_pasien_permohonan_uji_klinik').val().length === 0) {
                $("#umurtahun_pasien_permohonan_uji_klinik").val('')
                $("#umurbulan_pasien_permohonan_uji_klinik").val('')
                $("#umurhari_pasien_permohonan_uji_klinik").val('')
            }

            // jika user mengisi data pasien baru
            // jika user mengisikan data pasien maka isian select2 kosong dan tmapilan detail pasien hilang
            $('.btn-new-pasien').click(function() {
                $('#display-new-pasien').css('display', 'block');
                $('#display-detail-pasien').css('display', 'none');


                $('.cancel-new-pasien').click(function() {
                    // reset form
                    $('#nik_pasien_2').val('');
                    $('#nama_pasien_2').val('');
                    $('#gender_pasien_2').prop('checked', true);
                    $('#tgllahir_pasien_2').val('');
                    $('#phone_pasien_2').val('');
                    $('#alamat_pasien_2').val('');

                    $("#umurtahun_pasien_permohonan_uji_klinik").val('');
                    $("#umurbulan_pasien_permohonan_uji_klinik").val('');
                    $("#umurhari_pasien_permohonan_uji_klinik").val('');

                    $('#nopasien_permohonan_uji_klinik').val('');

                    $("#pasien_permohonan_uji_klinik").prop("disabled", false);
                    $('#display-new-pasien').css('display', 'none');

                    location.reload();
                })

                $("#display-detail-pasien input, #display-detail-pasien textarea").prop("disabled", true);
                $('#display-detail-pasien-silaboy input, #display-detail-pasien-silaboy textarea').prop(
                    'disabled', true);
            });

            // mengisikan input nik
            $('#nik_pasien').keyup(function(e) {
                $('#nopasien_permohonan_uji_klinik').val($(this).val());
            });

            function getAge(dateString) {
                registerMomentPreciseDiff();
                var today = moment().toDate();
                var birthDate = moment(dateString, "DD/MM/YYYY").toDate();

                var diff = moment.preciseDiff(today, birthDate, true);

                $("#umurtahun_pasien_permohonan_uji_klinik").val(diff[0])
                $("#umurbulan_pasien_permohonan_uji_klinik").val(diff[1])
                $("#umurhari_pasien_permohonan_uji_klinik").val(diff[2])
            }

        });


        $('.btn-simpan').on('click', function() {
            var $button = $(this); // Simpan referensi tombol simpan

            // Sync TinyMCE content to textarea before form submission
            if (typeof tinymce !== 'undefined') {
                var editor = tinymce.get('request_pasien_permohonan_uji_klinik');
                if (editor) {
                    editor.save(); // This syncs the content to the textarea
                }
            }

            // Make sure critical hidden fields are not disabled/readonly (disabled fields don't submit)
            $('#seccond_pasien_permohonan_uji_klinik').prop('disabled', false).prop('readonly', false);
            $('#nopasien_permohonan_uji_klinik').prop('disabled', false).prop('readonly', false);
            $('#tgllahir_pasien_permohonan_uji_klinik').prop('disabled', false).prop('readonly', false);

            // CRITICAL: Check if data is missing and restore from global variable
            const pasienIdValue = $('input[name="pasien_permohonan_uji_klinik"]').val();
            const nikValue = $('input[name="nopasien_permohonan_uji_klinik"]').val();
            const tgllahirValue = $('input[name="tgllahir_pasien_permohonan_uji_klinik"]').val();
            const namaValue = $('input[name="nama_pasien"]').val();

            // RESTORE DATA FROM GLOBAL VARIABLE IF MISSING
            if (selectedPatientFullData) {
                // For existing patient
                if (selectedPatientFullData.id_pasien) {
                    $('#seccond_pasien_permohonan_uji_klinik').val(selectedPatientFullData.id_pasien);
                }

                // Always restore these for both new and existing patients
                if (selectedPatientFullData.nik_pasien) {
                    $('#nopasien_permohonan_uji_klinik').val(selectedPatientFullData.nik_pasien);
                }

                if (selectedPatientFullData.tgllahir_pasien) {
                    $('#tgllahir_pasien_permohonan_uji_klinik').val(selectedPatientFullData.tgllahir_pasien);
                }

                // For NEW patient - populate nama_pasien and tgllahir_pasien fields
                if (!selectedPatientFullData.id_pasien || selectedPatientFullData.id_pasien === '') {
                    // Normalize gender to single letter format for backend
                    let genderValue = selectedPatientFullData.gender_pasien;
                    if (genderValue === 'Laki-Laki' || genderValue === 'L' || genderValue === 'male') {
                        genderValue = 'L';
                    } else if (genderValue === 'Perempuan' || genderValue === 'P' || genderValue === 'female') {
                        genderValue = 'P';
                    }

                    // Populate ALL possible nama_pasien fields to ensure at least one is captured
                    $('input[name="nama_pasien"]').val(selectedPatientFullData.nama_pasien);
                    $('input[name="tgllahir_pasien"]').val(selectedPatientFullData.tgllahir_pasien);
                    $('input[name="nik_pasien"]').val(selectedPatientFullData.nik_pasien);
                    $('input[name="gender_pasien"]').val(genderValue);
                    $('input[name="phone_pasien"]').val(selectedPatientFullData.phone_pasien || '');
                    $('input[name="alamat_pasien"]').val(selectedPatientFullData.alamat_pasien || '');
                    $('input[name="tmpt_lahir"]').val(selectedPatientFullData.tmpt_lahir || '');
                    $('input[name="pekerjaan"]').val(selectedPatientFullData.pekerjaan || '');
                }
            }

            // Get form data and check for missing fields
            var formData = $('#form').serializeArray();

            const modePengambilan = $('#mode_pengambilan_sampel').val();
            const petugasPengambil = ($('#petugas_pengambil_sampel').val() || '').trim();
            if (modePengambilan !== 'dibawa_pelanggan' && !petugasPengambil) {
                swal({
                    title: 'Error!',
                    text: 'Petugas pengambil sampel wajib dipilih. Pilih nama petugas, ketik manual, pilih "......", atau "Urin saja" jika hanya pemeriksaan urine.',
                    icon: 'warning'
                });
                return;
            }

            const hasNama = formData.some(f => f.name === 'nama_pasien' && f.value);
            const hasTglLahir = formData.some(f => f.name === 'tgllahir_pasien' && f.value);

            // CRITICAL FIX: If fields are missing from serialize, add them manually from global data
            if (selectedPatientFullData && (!selectedPatientFullData.id_pasien || selectedPatientFullData
                    .id_pasien === '')) {
                // Add missing fields to formData array
                if (!hasNama && selectedPatientFullData.nama_pasien) {
                    formData.push({
                        name: 'nama_pasien',
                        value: selectedPatientFullData.nama_pasien
                    });
                }

                if (!hasTglLahir && selectedPatientFullData.tgllahir_pasien) {
                    formData.push({
                        name: 'tgllahir_pasien',
                        value: selectedPatientFullData.tgllahir_pasien
                    });
                }

                if (!formData.some(f => f.name === 'nik_pasien' && f.value) && selectedPatientFullData.nik_pasien) {
                    formData.push({
                        name: 'nik_pasien',
                        value: selectedPatientFullData.nik_pasien
                    });
                }

                // Normalize gender
                let genderValue = selectedPatientFullData.gender_pasien;
                if (genderValue === 'Laki-Laki' || genderValue === 'male') genderValue = 'L';
                else if (genderValue === 'Perempuan' || genderValue === 'female') genderValue = 'P';

                if (!formData.some(f => f.name === 'gender_pasien' && f.value) && genderValue) {
                    formData.push({
                        name: 'gender_pasien',
                        value: genderValue
                    });
                }

                if (!formData.some(f => f.name === 'phone_pasien' && f.value) && selectedPatientFullData
                    .phone_pasien) {
                    formData.push({
                        name: 'phone_pasien',
                        value: selectedPatientFullData.phone_pasien
                    });
                }

                if (!formData.some(f => f.name === 'alamat_pasien' && f.value) && selectedPatientFullData
                    .alamat_pasien) {
                    formData.push({
                        name: 'alamat_pasien',
                        value: selectedPatientFullData.alamat_pasien
                    });
                }

                if (!formData.some(f => f.name === 'tmpt_lahir' && f.value) && selectedPatientFullData.tmpt_lahir) {
                    formData.push({
                        name: 'tmpt_lahir',
                        value: selectedPatientFullData.tmpt_lahir
                    });
                }

                if (!formData.some(f => f.name === 'pekerjaan' && f.value) && selectedPatientFullData.pekerjaan) {
                    formData.push({
                        name: 'pekerjaan',
                        value: selectedPatientFullData.pekerjaan
                    });
                }

                // Add wilayah IDs if available
                if (selectedPatientFullData.provinsi_id) {
                    formData.push({
                        name: 'provinsi_pasien',
                        value: selectedPatientFullData.provinsi_id
                    });
                }
                if (selectedPatientFullData.kabupaten_id) {
                    formData.push({
                        name: 'kabupaten_pasien',
                        value: selectedPatientFullData.kabupaten_id
                    });
                }
                if (selectedPatientFullData.kecamatan_id) {
                    formData.push({
                        name: 'kecamatan_pasien',
                        value: selectedPatientFullData.kecamatan_id
                    });
                }
                if (selectedPatientFullData.desa_id) {
                    formData.push({
                        name: 'desa_pasien',
                        value: selectedPatientFullData.desa_id
                    });
                }
            } else if (selectedPatientFullData && selectedPatientFullData.id_pasien) {
                const existingPatientFields = [{
                        name: 'tmpt_lahir',
                        value: selectedPatientFullData.tmpt_lahir || ''
                    },
                    {
                        name: 'pekerjaan',
                        value: selectedPatientFullData.pekerjaan || ''
                    },
                    {
                        name: 'phone_pasien',
                        value: selectedPatientFullData.phone_pasien || ''
                    },
                    {
                        name: 'alamat_pasien',
                        value: selectedPatientFullData.alamat_pasien || ''
                    }
                ];

                existingPatientFields.forEach(function(field) {
                    formData = formData.filter(function(item) {
                        return item.name !== field.name;
                    });
                    formData.push(field);
                });
            }

            $button.prop('disabled', true); // Disable tombol simpan
            $button.html('Loading...'); // Ganti teks tombol dengan "Loading..."

            // CKEditor sync removed - using plain textarea now
            // if (CKEDITOR.instances.diagnosa_permohonan_uji_klinik) {
            //     var editorData = CKEDITOR.instances.diagnosa_permohonan_uji_klinik.getData();
            //     $('#diagnosa_permohonan_uji_klinik').val(editorData);
            // }

            // Use native jQuery AJAX instead of ajaxSubmit plugin
            $.ajax({
                url: $('#form').attr('action'),
                type: 'POST',
                data: $.param(formData), // Use modified formData with manual additions
                dataType: 'json',
                success: function(response) {
                    console.log(response);

                    if (response.status == true) {
                        swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            })
                            .then(function() {
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else if (window.KLINIK_IS_EDIT) {
                                    if (window.KLINIK_EDIT_DATA && window.KLINIK_EDIT_DATA.is_haji && window.KLINIK_EDIT_DATA.id_haji) {
                                        window.location.href = '/elits-permohonan-uji-klinik-2/haji/daftar-pasien/' + window.KLINIK_EDIT_DATA.id_haji;
                                    } else {
                                        window.location.href = '/elits-permohonan-uji-klinik-2';
                                    }
                                } else {
                                    window.location.href = response.redirect_url;
                                }
                            });
                    } else {
                        // Pastikan tombol diaktifkan kembali pada error
                        $button.prop('disabled', false); // Aktifkan kembali tombol simpan
                        $button.html(window.KLINIK_IS_EDIT ? 'Update' : 'Simpan');

                        var pesan = "";
                        var data_pesan = response.pesan;
                        const wrapper = document.createElement('div');

                        if (typeof(data_pesan) == 'object') {
                            jQuery.each(data_pesan, function(key, value) {
                                pesan += value + '. <br>';
                                wrapper.innerHTML = pesan;
                            });

                            swal({
                                title: "Error!",
                                content: wrapper,
                                icon: "warning"
                            });
                        } else {
                            var detail = response.error || response.message || '';
                            var textPesan = response.pesan || 'System gagal melakukan penyimpanan!';
                            if (detail && String(detail).trim() !== '' && String(detail) !== String(textPesan)) {
                                textPesan = textPesan + '\n\nDetail: ' + String(detail).substring(0, 400);
                            }
                            swal({
                                title: "Error!",
                                text: textPesan,
                                icon: "warning"
                            });
                        }
                    }
                },
                error: function(xhr, status) {
                    // Pastikan tombol diaktifkan kembali pada error
                    $button.prop('disabled', false); // Aktifkan kembali tombol simpan
                    $button.html(window.KLINIK_IS_EDIT ? 'Update' : 'Simpan');

                    var msg = 'System gagal menyimpan!';
                    if (status === 'timeout') {
                        msg = 'Koneksi timeout. Periksa jaringan lalu coba lagi.';
                    } else if (status === 'error' && (!xhr || xhr.status === 0)) {
                        msg = 'Tidak terhubung ke server. Periksa koneksi internet/Wi‑Fi komputer, lalu refresh halaman dan coba simpan lagi.';
                    } else if (xhr && xhr.status >= 500) {
                        msg = 'Server bermasalah (HTTP ' + xhr.status + '). Coba beberapa saat lagi.';
                    } else if (xhr && xhr.responseJSON && (xhr.responseJSON.pesan || xhr.responseJSON.message)) {
                        msg = xhr.responseJSON.pesan || xhr.responseJSON.message;
                    }
                    swal("Error!", msg, "error");
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // OLD Function - RENAMED to avoid conflict with global function
            // This function uses moment.js and DD-MM-YYYY format (different from our global function)
            function calculateAge_OLD_MomentJS(birthDate) {

                // console.log(birthDate);
                birthDate = moment(birthDate, 'DD-MM-YYYY', true).format("YYYY-MM-DD");
                const today = new Date();
                const birth = new Date(birthDate);
                let years = today.getFullYear() - birth.getFullYear();
                let months = today.getMonth() - birth.getMonth();
                let days = today.getDate() - birth.getDate();

                console.log(birth);
                console.log(today);
                console.log(days);

                if (days < 0) {
                    months--;
                    days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
                }
                if (months < 0) {
                    years--;
                    months += 12;
                }

                return {
                    years,
                    months,
                    days
                };
            }

            // Use global calculateAge function instead
            // (This ensures consistency across all birth date inputs)
            const calculateAge = window.calculateAge;

            // Fetch patient data and populate form
            $('#fetchPatientsBtn').on('click', function() {
                let formData = $('#searchForm').serialize();

                $.ajax({
                    url: "{{ route('get-list-pasien-satu-sehat') }}",
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        let tableBody = $('#patientsTableBody');
                        tableBody.empty();

                        response.forEach(function(patient) {
                            let row = `<tr>
                      <td>${patient.nik || 'N/A'}</td>
                      <td>${patient.gender=="L"?"Laki - Laki":"Perempuan" || 'N/A'}</td>
                      <td>${patient.id || 'N/A'}</td>
                      <td class="address">${patient.address || 'N/A'}</td>
                      <td>${patient.birthDate || 'N/A'}</td>
                      <td>${patient.name || 'N/A'}</td>
                      <td>${patient.telepon || 'N/A'}</td>
                      <td><button type="button" class="btn btn-success pilih-pasien" data-patient='${JSON.stringify(patient)}'>Pilih</button></td>
                    </tr>`;
                            tableBody.append(row);
                        });

                        $('#patientsModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching patient data:', error);
                    }
                });
            });

            // Untuk memilih pasien
            $(document).on('click', '.pilih-pasien', function() {
                let patient = $(this).data('patient');
                let age = calculateAge(patient.birthDate);

                $('#id_satu_sehat_1').val(patient.id || 'N/A');
                if ($('#id_satu_sehat_1').val() === 'N/A') {
                    $('#id_satu_sehat_1').prop('readonly', false);
                } else {
                    $('#id_satu_sehat_1').prop('readonly', true);
                }

                $('#nik_pasien_1').val(patient.nik || 'N/A');
                if ($('#nik_pasien_1').val() === 'N/A') {
                    $('#nik_pasien_1').prop('readonly', false);
                } else {
                    $('#nik_pasien_1').prop('readonly', true);
                }

                $('#nama_pasien_1').val((patient.name || 'N/A').toUpperCase());
                if ($('#nama_pasien_1').val() === 'N/A') {
                    $('#nama_pasien_1').prop('readonly', false);
                } else {
                    $('#nama_pasien_1').prop('readonly', true);
                }

                let gender = patient.gender === 'L' ? 'Laki-Laki' : 'Perempuan'

                $('#gender_pasien_1').val(gender || 'N/A');
                if ($('#gender_pasien_1').val() === 'N/A') {
                    $('#gender_pasien_1').prop('readonly', false);
                } else {
                    $('#gender_pasien_1').prop('readonly', true);
                }

                $('#tgllahir_pasien_1').val(patient.birthDate || 'N/A');
                if ($('#tgllahir_pasien_1').val() === 'N/A') {
                    $('#tgllahir_pasien_1').prop('readonly', false);
                } else {
                    $('#tgllahir_pasien_1').prop('readonly', true);
                }


                $('#phone_pasien_1').val(patient.telepon || 'N/A');
                if ($('#phone_pasien_1').val() === 'N/A') {
                    $('#phone_pasien_1').prop('readonly', false);
                } else {
                    $('#phone_pasien_1').prop('readonly', true);
                }

                $('#alamat_pasien_1').val(patient.address || 'N/A');
                if ($('#alamat_pasien_1').val() === 'N/A') {
                    $('#alamat_pasien_1').prop('readonly', false);
                } else {
                    $('#alamat_pasien_1').prop('readonly', true);
                }

                // Update age fields
                $('#umurtahun_pasien_permohonan_uji_klinik').val(age.years);
                $('#umurbulan_pasien_permohonan_uji_klinik').val(age.months);
                $('#umurhari_pasien_permohonan_uji_klinik').val(age.days);

                // Show the patient detail form
                $('#display-detail-pasien').show();

                $('#display-new-pasien input, #display-new-pasien textarea').prop('disabled', true);
                $('#display-detail-pasien-silaboy input, #display-detail-pasien-silaboy textarea').prop(
                    'disabled', true);

                // Close the modal
                $('#patientsModal').modal('hide');
            });

            // Filter addresses based on search input
            $('#searchAddress').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $('#patientsTableBody tr').filter(function() {
                    $(this).toggle($(this).find('.address').text().toLowerCase().indexOf(value) > -
                        1);
                });
            });
        });

        // Form Perwakilan
        document.getElementById('flexSwitchCheckDefault').addEventListener('change', function() {
            var formPerwakilan = document.getElementById('form_perwakilan');
            if (this.checked) {
                $('#form_perwakilan input, #form_perwakilan textarea').prop('disabled', false)
                formPerwakilan.style.display = 'block';
            } else {
                $('#form_perwakilan input, #form_perwakilan textarea').prop('disabled', true)
                formPerwakilan.style.display = 'none';
            }
        });

        // Form Perwakilan Dokter
        document.getElementById('flexSwitchCheckDefaultDokter').addEventListener('change', function() {
            var formPerwakilanDokter = document.getElementById('form_perwakilan_dokter');
            if (this.checked) {
                $('#form_perwakilan_dokter input, #form_perwakilan_dokter textarea').prop('disabled', false)
                formPerwakilanDokter.style.display = 'block';
            } else {
                $('#form_perwakilan_dokter input, #form_perwakilan_dokter textarea').prop('disabled', true)
                formPerwakilanDokter.style.display = 'none';
            }
        });

        // Toggle input "Lainnya" untuk status hubungan perwakilan
        function toggleStatusHubunganLainnya(selectElement, inputGroupId, inputId) {
            if (selectElement.value === 'Lainnya') {
                document.getElementById(inputGroupId).style.display = 'block';
                document.getElementById(inputId).required = true;
            } else {
                document.getElementById(inputGroupId).style.display = 'none';
                document.getElementById(inputId).required = false;
                document.getElementById(inputId).value = '';
            }
        }

        // Event listener untuk status hubungan perwakilan (form pertama)
        document.getElementById('status_hubungan_perwakilan_permohonan_uji_klinik').addEventListener('change', function() {
            toggleStatusHubunganLainnya(this, 'status_hubungan_lainnya_group', 'status_hubungan_lainnya_permohonan_uji_klinik');
        });

        // Event listener untuk status hubungan perwakilan (form kedua)
        document.getElementById('status_hubungan_perwakilan_permohonan_uji_klinik_2').addEventListener('change', function() {
            toggleStatusHubunganLainnya(this, 'status_hubungan_lainnya_group_2', 'status_hubungan_lainnya_permohonan_uji_klinik_2');
        });

        // Form Search Satu Sehat
        $('#button_search_satu_sehat').on('click', function() {
            $('#search_satu_sehat').css('display', 'block');
            $('#button_action_add').css('display', 'none');

        })

        $('#close_button_search_satu_sehat').on('click', function() {
            $('#search_satu_sehat').css('display', 'none');
            $('#button_action_add').css('display', 'block');
            $('#display-detail-pasien').css('display', 'none');

            location.reload();
        })

        // Form Search Silaboy
        $('#button_search_silaboy').on('click', function() {
            $('#search_silaboy').css('display', 'block');
            $('#button_action_add').css('display', 'none');
        })

        $('#close_button_silaboy').on('click', function() {
            $('#search_silaboy').css('display', 'none');
            $('#button_action_add').css('display', 'block');
            $('#display-detail-pasien-silaboy').css('display', 'none');

            location.reload();
        })

        // Fungsi untuk menyembunyikan/menampilkan field berdasarkan doctor_type
        function toggleFieldsByDoctorType(doctorType) {
            const fieldsToToggle = [
                '.form-group:has(#nama_dokter_pengirim_permohonan_uji_klinik)',
                '.form-group:has(#hp_dokter_pengirim_permohonan_uji_klinik)',
                '.form-group:has(#tipe_pemeriksaan_prolanis)',
                '.form-group:has(#diagnosa_permohonan_uji_klinik)'
            ];

            if (doctorType === 'lab') {
                if (window.KLINIK_EDIT_DATA && window.KLINIK_EDIT_DATA.is_haji) {
                    return;
                }
                // Sembunyikan field untuk dokter lab
                fieldsToToggle.forEach(function(selector) {
                    $(selector).hide();
                });

                // Kosongkan nilai field yang disembunyikan
                $('#nama_dokter_pengirim_permohonan_uji_klinik').val('');
                $('#hp_dokter_pengirim_permohonan_uji_klinik').val('');
                $('#tipe_pemeriksaan_prolanis').val('');

                // CKEditor removed - using plain textarea now
                // if (CKEDITOR.instances.diagnosa_permohonan_uji_klinik) {
                //     CKEDITOR.instances.diagnosa_permohonan_uji_klinik.setData('');
                // }
                $('#diagnosa_permohonan_uji_klinik').val('');
            } else {
                // Tampilkan field untuk dokter rujukan atau tidak dipilih
                fieldsToToggle.forEach(function(selector) {
                    $(selector).show();
                });
            }
        }

        // Event listener untuk perubahan doctor_type
        $('#doctor_type').on('change', function() {
            const selectedType = $(this).val();
            toggleFieldsByDoctorType(selectedType);
        });

        // Inisialisasi saat halaman dimuat
        $(document).ready(function() {
            const initialType = $('#doctor_type').val();
            toggleFieldsByDoctorType(initialType);
        });

        // ============================================
        // WIZARD NAVIGATION SCRIPT
        // ============================================
        var currentStep = {{ $isHajiEdit ? 3 : 1 }};
        var selectedDoctorType = {!! $isHajiEdit ? "'rujukan'" : "''" !!};
        var selectedPatientData = null;

        // Step 1: Doctor Type Selection
        $('.doctor-type-card').on('click', function() {
            if (window.KLINIK_EDIT_DATA && window.KLINIK_EDIT_DATA.is_haji) {
                return;
            }
            $('.doctor-type-card').removeClass('selected');
            $(this).addClass('selected');
            selectedDoctorType = $(this).data('type');
            $('#doctor_type').val(selectedDoctorType);
            $('#btn-next-step-1').prop('disabled', false);
        });

        // Step Navigation Buttons
        $('#btn-next-step-1').on('click', function() {
            if (selectedDoctorType) {
                goToStep(2);
            }
        });

        $('#btn-prev-step-2').on('click', function() {
            goToStep(1);
        });

        $('#btn-next-step-2').on('click', function() {
            if (selectedPatientData) {
                goToStep(3);
            }
        });

        $('#btn-prev-step-3').on('click', function() {
            goToStep(2);
        });

        // Function to initialize TinyMCE for request pasien/keluhan
        function initializeTinyMCE() {
            // Check if TinyMCE is loaded
            if (typeof tinymce === 'undefined') {
                console.warn('TinyMCE is not loaded yet. Retrying in 500ms...');
                setTimeout(function() {
                    initializeTinyMCE();
                }, 500);
                return;
            }

            // Check if element exists
            var $textarea = $('#request_pasien_permohonan_uji_klinik');
            if ($textarea.length === 0) {
                console.warn('Textarea #request_pasien_permohonan_uji_klinik not found. Retrying in 300ms...');
                setTimeout(function() {
                    initializeTinyMCE();
                }, 300);
                return;
            }

            // Remove existing TinyMCE instance if any
            var existingEditor = tinymce.get('request_pasien_permohonan_uji_klinik');
            if (existingEditor) {
                tinymce.remove('#request_pasien_permohonan_uji_klinik');
            }

            // Initialize TinyMCE using local version
            try {
                tinymce.init({
                    selector: '#request_pasien_permohonan_uji_klinik',
                    height: 300,
                    menubar: false,
                    theme: 'modern',
                    plugins: [
                        'advlist autolink lists link charmap',
                        'searchreplace code',
                        'insertdatetime table paste help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic underline | ' +
                        'alignleft aligncenter alignright | ' +
                        'bullist numlist | removeformat | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; padding: 10px; }',
                    branding: false,
                    setup: function(editor) {
                        editor.on('init', function() {
                            console.log('✅ TinyMCE initialized for request_pasien_permohonan_uji_klinik');
                        });
                    }
                });
            } catch (error) {
                console.error('Error initializing TinyMCE:', error);
            }
        }

        // Function to change steps
        function goToStep(step) {
            // Hide all steps
            $('.step-content').removeClass('active');
            $('.wizard-step').removeClass('active completed');

            // Show current step
            $('#step-' + step).addClass('active');

            // Update wizard indicators
            for (let i = 1; i <= step; i++) {
                if (i < step) {
                    $('.wizard-step[data-step="' + i + '"]').addClass('completed');
                } else if (i === step) {
                    $('.wizard-step[data-step="' + i + '"]').addClass('active');
                }
            }

            currentStep = step;

            if (step === 3) {
                initializeStep3Controls();
            }

            // Show/hide doctor rujukan fields in step 3
            if (step === 3) {
                var typeDokter = selectedDoctorType || $('#doctor_type').val();
                if (typeDokter === 'rujukan') {
                    $('#rujukan-fields').show();
                    $('#perwakilan_dokter_form_group').hide();
                } else {
                    $('#rujukan-fields').hide();
                    $('#perwakilan_dokter_form_group').show();

                }

                // Initialize TinyMCE when step 3 is shown
                setTimeout(function() {
                    initializeTinyMCE();
                }, 500);

                // Get current value and check if it's truly empty
                const pasienIdValue = $('#seccond_pasien_permohonan_uji_klinik').val();

                // Copy patient data from step 2 to actual submit fields
                const isNewPatient = !pasienIdValue || pasienIdValue.trim() === '';

                if (isNewPatient && currentSearchType === 'new') {
                    // Only copy if data exists in container
                    const hasContainerData = $('#patient-search-container #nik_pasien_2').length > 0;

                    if (hasContainerData) {
                        // Copy data from patient-search-container form to hidden form fields
                        $('#nik_pasien_2').val($('#patient-search-container #nik_pasien_2').val());
                        $('#nama_pasien_2').val($('#patient-search-container #nama_pasien_2').val());
                        $('#tgllahir_pasien_2').val($('#patient-search-container #tgllahir_pasien_2').val());
                        $('#phone_pasien_2').val($('#patient-search-container #phone_pasien_2').val());
                        $('#alamat_pasien_2').val($('#patient-search-container #alamat_pasien_2').val());

                        // Copy gender
                        const selectedGender = $('#patient-search-container input[name="gender_pasien"]:checked').val();
                        $('input[name="gender_pasien"][value="' + selectedGender + '"]').prop('checked', true);
                    }
                } else if (!isNewPatient) {
                    // For existing patient, make sure the ID is properly set
                    // Remove readonly to ensure it can be submitted
                    $('#seccond_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                    $('#nopasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                    $('#tgllahir_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                } else if (currentSearchType === 'existing') {
                    if (selectedPatientFullData && selectedPatientFullData.id_pasien) {
                        // Remove readonly/disabled first
                        $('#seccond_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                        $('#nopasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                        $('#tgllahir_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);

                        // Restore values
                        $('#seccond_pasien_permohonan_uji_klinik').val(selectedPatientFullData.id_pasien);
                        $('#nopasien_permohonan_uji_klinik').val(selectedPatientFullData.nik_pasien);
                        $('#tgllahir_pasien_permohonan_uji_klinik').val(selectedPatientFullData.tgllahir_pasien);
                    }
                } else {
                    // Last attempt to restore from global variable
                    if (selectedPatientFullData) {
                        $('#seccond_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false).val(
                            selectedPatientFullData.id_pasien || '');
                        $('#nopasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false).val(
                            selectedPatientFullData.nik_pasien || '');
                        $('#tgllahir_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false).val(
                            selectedPatientFullData.tgllahir_pasien || '');
                    }
                }
            }

            // Scroll to top
            $('html, body').animate({
                scrollTop: $('.card').offset().top - 20
            }, 500);
        }

        // ============================================
        // PATIENT SEARCH INTEGRATION
        // ============================================

        let isSearchActive = false;
        let currentSearchType = null;
        let selectedPatientFullData = null; // Store full patient data here
        let selectedWilayahData = null; // Store wilayah data from search

        // Button: Search Silaboy
        $('#btn-search-silaboy').on('click', function() {
            // Reset if clicking same button
            if (isSearchActive && currentSearchType === 'silaboy') {
                resetPatientSearch();
                return;
            }

            // Reset previous search
            resetPatientSearch();

            isSearchActive = true;
            currentSearchType = 'silaboy';
            $('.btn-patient-search').removeClass('active');
            $(this).addClass('active');
            $('#btn-next-step-2').prop('disabled', true);

            // Clone template and show
            const silaboySearch = $('#search_silaboy_template').clone();
            silaboySearch.removeClass('d-none').show();
            $('#patient-search-container').html(silaboySearch);

            // Initialize select2 for the cloned element
            setTimeout(function() {
                const $select = $("#patient-search-container .patient-select-silaboy");
                const $dropdownParent = $('#patient-search-container');
                console.log('Initializing select2, element found:', $select.length);

                initPatientSelect2($select, $dropdownParent);

                // Add direct change handler on select2
                $select.on('select2:select', function(e) {
                    console.log('Select2 select event triggered');
                    const data = e.params.data;
                    if (data && data.id) {
                        fetchPatientData(data.id);
                    }
                });

                // Add close button handler
                $('#patient-search-container .btn-close-search').on('click', function() {
                    resetPatientSearch();
                });
            }, 100);
        });

        // Button: Add New Patient
        $('#btn-add-new-patient').on('click', function() {
            // Reset if clicking same button
            if (isSearchActive && currentSearchType === 'new') {
                resetPatientSearch();
                return;
            }

            // Reset previous search
            resetPatientSearch();

            isSearchActive = true;
            currentSearchType = 'new';
            $('.btn-patient-search').removeClass('active');
            $(this).addClass('active');
            $('#btn-next-step-2').prop('disabled', true);

            // Clone and show new patient form
            const newPatientForm = $('#display-new-pasien').clone();
            newPatientForm.show().css('display', 'block');
            $('#patient-search-container').html(newPatientForm);

            // Re-initialize datepicker for cloned element
            setTimeout(function() {
                $('#patient-search-container .datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                });

                // Re-initialize date input mask
                var input = $('#patient-search-container .js-date')[0];
                if (input) {
                    var dateInputMask = function dateInputMask(elm) {
                        elm.addEventListener('keypress', function(e) {
                            if (e.keyCode < 47 || e.keyCode > 57) {
                                e.preventDefault();
                            }
                            var len = elm.value.length;
                            if (len !== 1 || len !== 3) {
                                if (e.keyCode == 47) {
                                    e.preventDefault();
                                }
                            }
                            if (len === 2) {
                                elm.value += '/';
                            }
                            if (len === 5) {
                                elm.value += '/';
                            }
                        });
                    };
                    dateInputMask(input);
                }

                // Add close button handler
                $('#patient-search-container .cancel-new-pasien').on('click', function() {
                    resetPatientSearch();
                });

                // Monitor form fields
                monitorNewPatientForm();
            }, 100);
        });

        // Reset patient search function
        function resetPatientSearch() {
            // Destroy select2 if exists
            const $select = $("#patient-search-container .patient-select-silaboy");
            if ($select.length && $select.hasClass("select2-hidden-accessible")) {
                $select.select2('destroy');
            }

            $('#patient-search-container').empty();
            $('#patient-detail-display').empty();
            $('.btn-patient-search').removeClass('active');
            $('#btn-next-step-2').prop('disabled', true);
            isSearchActive = false;
            currentSearchType = null;
            selectedPatientData = null;
            selectedPatientFullData = null; // Clear global stored data

            // Clear hidden fields
            $('#seccond_pasien_permohonan_uji_klinik').val('');
            $('#nopasien_permohonan_uji_klinik').val('');
            $('#tgllahir_pasien_permohonan_uji_klinik').val('');
        }

        // Function to calculate age from birthdate (Global scope for accessibility)
        window.calculateAge = function(dateString) {
            try {
                if (!dateString) return;

                var today = moment();
                var birthDate = moment(dateString, "DD/MM/YYYY");

                if (!birthDate.isValid()) return;

                var diff = moment.preciseDiff(today, birthDate, true);

                $("#umurtahun_pasien_permohonan_uji_klinik").val(diff[0] || 0);
                $("#umurbulan_pasien_permohonan_uji_klinik").val(diff[1] || 0);
                $("#umurhari_pasien_permohonan_uji_klinik").val(diff[2] || 0);
            } catch (e) {
                console.error('Error calculating age:', e);
            }
        };

        // Alias for backward compatibility
        window.getAge = window.calculateAge;

        // Function to fetch patient data from server
        function fetchPatientData(patientId) {
            $.ajax({
                type: "POST",
                url: "{{ route('get-pasien-by-id') }}",
                data: {
                    _token: getCsrfToken(),
                    pasien_id: patientId
                },
                dataType: "JSON",
                success: function(response) {
                    // IMPORTANT: Mark as existing patient (not new)
                    currentSearchType = 'existing';

                    // IMPORTANT: Store to global variable for persistence
                    selectedPatientFullData = {
                        id_pasien: response.id_pasien || response.patient_id || response.pasien_id ||
                            patientId,
                        nik_pasien: response.nik_pasien,
                        nama_pasien: response.nama_pasien,
                        tgllahir_pasien: response.tgllahir_pasien_normal,
                        gender_pasien: response.gender_pasien,
                        phone_pasien: response.phone_pasien,
                        alamat_pasien: response.alamat_pasien,
                        alamat_lengkap: response.alamat_lengkap || response.alamat_pasien,
                        wilayah_id: response.wilayah_id || null,
                        tmpt_lahir: response.tmpt_lahir || '',
                        pekerjaan: response.pekerjaan || '',
                        id_pasien_satu_sehat: response.id_pasien_satu_sehat,
                        no_rekammedis_pasien: response.no_rekammedis_pasien
                    };

                    // IMPORTANT: Remove readonly first, then set values
                    $('#seccond_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                    $('#nopasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);
                    $('#tgllahir_pasien_permohonan_uji_klinik').prop('readonly', false).prop('disabled', false);

                    // Set hidden fields for existing patient (use the globally stored data)
                    $('#seccond_pasien_permohonan_uji_klinik').val(selectedPatientFullData.id_pasien);
                    $('#nopasien_permohonan_uji_klinik').val(selectedPatientFullData.nik_pasien);
                    $('#tgllahir_pasien_permohonan_uji_klinik').val(selectedPatientFullData.tgllahir_pasien);

                    // Calculate age
                    calculateAge(response.tgllahir_pasien_normal);

                    // Display patient details
                    displayPatientDetail(buildPatientDetailViewData(selectedPatientFullData));
                },
                error: function(xhr, status, error) {
                    console.error('Error getting patient data:', error);
                    swal("Error!", "Gagal mendapatkan data pasien!", "error");
                }
            });
        }

        function escapePatientDisplayValue(value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function normalizeGenderLabel(gender) {
            if (!gender) return '-';
            const g = String(gender).toLowerCase();
            if (g === 'l' || g === 'male' || g === 'laki-laki' || g === 'laki laki') return 'Laki-laki';
            if (g === 'p' || g === 'female' || g === 'perempuan') return 'Perempuan';
            return gender;
        }

        function buildPatientDetailViewData(source) {
            return {
                id_satu_sehat: source.id_pasien_satu_sehat || source.id_satu_sehat || '-',
                nik_pasien: source.nik_pasien || '-',
                no_rekammedis_pasien: source.no_rekammedis_pasien || '-',
                nama_pasien: source.nama_pasien || '-',
                gender_pasien: normalizeGenderLabel(source.gender_pasien),
                tgllahir_pasien: normalizeBirthDateToUi(source.tgllahir_pasien) || source.tgllahir_pasien || '-',
                tmpt_lahir: source.tmpt_lahir || '-',
                pekerjaan: source.pekerjaan || '-',
                phone_pasien: source.phone_pasien || '-',
                // Tampilkan alamat lengkap (desa/kec/kabupaten-kota) dari wilayah_id jika ada
                alamat_pasien: source.alamat_lengkap || source.alamat_pasien || '-'
            };
        }

        function normalizeBirthDateToUi(value) {
            if (!value) {
                return '';
            }
            const raw = String(value).trim();
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) {
                return raw;
            }
            const iso = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (iso) {
                return iso[3] + '/' + iso[2] + '/' + iso[1];
            }
            return raw;
        }

        function isValidBirthDateFormat(value) {
            const normalized = normalizeBirthDateToUi(value);
            if (!/^\d{2}\/\d{2}\/\d{4}$/.test(normalized)) {
                return false;
            }
            const parts = normalized.split('/');
            const day = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10);
            const year = parseInt(parts[2], 10);
            if (month < 1 || month > 12 || day < 1 || day > 31 || year < 1900) {
                return false;
            }
            const date = new Date(year, month - 1, day);
            return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
        }

        function openEditPatientDetailModal() {
            if (!selectedPatientFullData) {
                swal('Info', 'Pilih pasien terlebih dahulu.', 'info');
                return;
            }

            const data = selectedPatientFullData;
            const tgllahirUi = normalizeBirthDateToUi(data.tgllahir_pasien || '');
            $('#edit_pasien_nik').val(data.nik_pasien || '');
            $('#edit_pasien_rm').val(data.no_rekammedis_pasien || '');
            $('#edit_pasien_nama').val((data.nama_pasien || '').toUpperCase());
            $('#edit_pasien_gender').val(normalizeGenderLabel(data.gender_pasien));
            $('#edit_pasien_satu_sehat').val(data.id_pasien_satu_sehat || data.id_satu_sehat || '-');
            $('#edit_pasien_tmpt_lahir').val(data.tmpt_lahir || '');
            $('#edit_pasien_tgllahir').val(tgllahirUi);
            $('#edit_pasien_pekerjaan').val(data.pekerjaan || '');
            $('#edit_pasien_phone').val(data.phone_pasien || '');
            $('#edit_pasien_alamat').val(data.alamat_pasien || '');
            $('#search_tmpt_lahir_modal_input').val('');
            $('#search_tmpt_lahir_modal_results').hide();

            const $modal = $('#modalEditPasienTerpilih');
            const $patientSelect = $('#patient-search-container .patient-select-silaboy');
            if ($patientSelect.length && $patientSelect.hasClass('select2-hidden-accessible')) {
                $patientSelect.select2('close');
            }

            $('#edit_pasien_tmpt_lahir, #search_tmpt_lahir_modal_input, #edit_pasien_tgllahir, #edit_pasien_pekerjaan, #edit_pasien_phone, #edit_pasien_alamat')
                .prop('readonly', false)
                .prop('disabled', false);

            if ($modal.parent()[0] !== document.body) {
                $modal.appendTo('body');
            }

            $modal.modal('show');
        }

        $('#modalEditPasienTerpilih').on('show.bs.modal', function() {
            $('body').addClass('modal-edit-pasien-open');
        });

        $('#modalEditPasienTerpilih').on('shown.bs.modal', function() {
            // Bootstrap focus trap bentrok dengan Select2 — nonaktifkan agar input bisa diketik
            $(document).off('focusin.bs.modal');
            setTimeout(function() {
                $('#edit_pasien_tmpt_lahir').trigger('focus');
            }, 150);
        });

        $('#modalEditPasienTerpilih').on('hidden.bs.modal', function() {
            $('body').removeClass('modal-edit-pasien-open');
            $('#search_tmpt_lahir_modal_results').hide();
        });

        function saveEditPatientDetailFromModal() {
            if (!selectedPatientFullData) {
                return;
            }

            const tmptLahir = ($('#edit_pasien_tmpt_lahir').val() || '').trim();
            const tgllahirRaw = ($('#edit_pasien_tgllahir').val() || '').trim();
            const tgllahir = normalizeBirthDateToUi(tgllahirRaw);
            const pekerjaan = ($('#edit_pasien_pekerjaan').val() || '').trim();
            const phone = ($('#edit_pasien_phone').val() || '').replace(/[^\d]/g, '');
            const alamat = ($('#edit_pasien_alamat').val() || '').trim();

            if (tgllahirRaw && !isValidBirthDateFormat(tgllahirRaw)) {
                swal('Perhatian', 'Format tanggal lahir tidak valid. Gunakan dd/mm/yyyy.', 'warning');
                return;
            }

            selectedPatientFullData.tmpt_lahir = tmptLahir;
            selectedPatientFullData.pekerjaan = pekerjaan;
            selectedPatientFullData.phone_pasien = phone;
            selectedPatientFullData.alamat_pasien = alamat;
            selectedPatientFullData.alamat_lengkap = alamat;

            if (tgllahir) {
                selectedPatientFullData.tgllahir_pasien = tgllahir;
                $('#edit_pasien_tgllahir').val(tgllahir);
                $('#tgllahir_pasien_permohonan_uji_klinik').val(tgllahir);
                calculateAge(tgllahir);
            }

            if (selectedPatientFullData.nik_pasien) {
                $('#nopasien_permohonan_uji_klinik').val(selectedPatientFullData.nik_pasien);
            }

            displayPatientDetail(buildPatientDetailViewData(selectedPatientFullData));
            $('#modalEditPasienTerpilih').modal('hide');
        }

        // Function to display patient details
        function displayPatientDetail(patientData) {
            const detailHtml = `
                <div class="card border-success" style="margin-top: 20px;">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-check-circle"></i> Data Pasien Terpilih</h5>
                        <button type="button" class="btn btn-light btn-sm" id="btn-edit-pasien-terpilih">
                            <i class="fa fa-edit mr-1"></i> Edit Data
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 25%">ID Satu Sehat</th>
                                    <td>${escapePatientDisplayValue(patientData.id_satu_sehat)}</td>
                                </tr>
                                <tr>
                                    <th>NIK Pasien</th>
                                    <td><strong>${escapePatientDisplayValue(patientData.nik_pasien)}</strong></td>
                                </tr>
                                <tr>
                                    <th>No Rekam Medis</th>
                                    <td><strong>${escapePatientDisplayValue(patientData.no_rekammedis_pasien)}</strong></td>
                                </tr>
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <td><strong style="font-size: 16px;">${escapePatientDisplayValue((patientData.nama_pasien || '').toUpperCase())}</strong></td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td>${escapePatientDisplayValue(patientData.gender_pasien)}</td>
                                </tr>
                                <tr>
                                    <th>Tempat Lahir</th>
                                    <td>${escapePatientDisplayValue(patientData.tmpt_lahir)}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Lahir</th>
                                    <td>${escapePatientDisplayValue(patientData.tgllahir_pasien)}</td>
                                </tr>
                                <tr>
                                    <th>Pekerjaan</th>
                                    <td>${escapePatientDisplayValue(patientData.pekerjaan)}</td>
                                </tr>
                                <tr>
                                    <th>Nomor Telepon</th>
                                    <td>${escapePatientDisplayValue(patientData.phone_pasien)}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>${escapePatientDisplayValue(patientData.alamat_pasien)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            $('#patient-detail-display').html(detailHtml);
            selectedPatientData = true;
            $('#btn-next-step-2').prop('disabled', false);
        }

        $(document).on('click', '#btn-edit-pasien-terpilih', function() {
            openEditPatientDetailModal();
        });

        $('#btn-save-edit-pasien-terpilih').on('click', function() {
            saveEditPatientDetailFromModal();
        });

        let searchTmptLahirModalTimer;
        $('#search_tmpt_lahir_modal_input').on('input', function() {
            const keyword = $(this).val().trim();
            clearTimeout(searchTmptLahirModalTimer);

            if (keyword.length < 2) {
                $('#search_tmpt_lahir_modal_results').hide();
                return;
            }

            searchTmptLahirModalTimer = setTimeout(function() {
                $.ajax({
                    url: "{{ route('search-wilayah') }}",
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        limit: 15,
                        types: 'KAB,KEC'
                    },
                    success: function(response) {
                        const $resultsList = $('#search_tmpt_lahir_modal_results_list');
                        $resultsList.empty();

                        if (!response.length) {
                            $resultsList.html('<div class="p-3 text-center text-muted">Wilayah tidak ditemukan</div>');
                        } else {
                            response.forEach(function(item) {
                                const tipeLabel = item.tipe === 'KAB' ? 'Kabupaten/Kota' : (item.tipe === 'KEC' ? 'Kecamatan' : item.tipe);
                                $resultsList.append(`
                                    <a href="javascript:void(0)" class="list-group-item list-group-item-action tmpt-lahir-modal-result-item"
                                       data-nama="${item.nama}">
                                        <strong>${item.nama}</strong> <span class="text-muted">(${tipeLabel})</span>
                                        <br><small class="text-muted">${item.full_path || '-'}</small>
                                    </a>
                                `);
                            });
                        }
                        $('#search_tmpt_lahir_modal_results').show();
                    }
                });
            }, 400);
        });

        $(document).on('click', '.tmpt-lahir-modal-result-item', function() {
            $('#edit_pasien_tmpt_lahir').val($(this).data('nama'));
            $('#search_tmpt_lahir_modal_input').val('');
            $('#search_tmpt_lahir_modal_results').hide();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#search_tmpt_lahir_modal_input, #search_tmpt_lahir_modal_results').length) {
                $('#search_tmpt_lahir_modal_results').hide();
            }
        });

        $('#modalEditPasienTerpilih').on('mousedown click', function(e) {
            e.stopPropagation();
        });

        $('#edit_pasien_phone').on('input', function() {
            this.value = this.value.replace(/[^\d]/g, '');
        });

        $('#edit_pasien_tgllahir').on('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            let formatted = '';
            if (value.length > 0) formatted = value.substring(0, 2);
            if (value.length >= 2) formatted += '/';
            if (value.length >= 3) formatted += value.substring(2, 4);
            if (value.length >= 4) formatted += '/';
            if (value.length >= 5) formatted += value.substring(4, 8);
            this.value = formatted;
        });

        // Backup handler - in case select2:select doesn't fire
        $(document).on('change', "#patient-search-container .patient-select-silaboy", function() {
            const selectedId = $(this).val();
            if (!selectedId) {
                $('#patient-detail-display').empty();
                $('#btn-next-step-2').prop('disabled', true);
                return;
            }

            fetchPatientData(selectedId);
        });

        // Monitor new patient form
        function monitorNewPatientForm() {
            // Use debounce for better performance
            let debounceTimer;

            // Function to update preview
            function updateNewPatientPreview() {
                const nik = ($('#patient-search-container #nik_pasien_2').val() || '').trim();
                const nama = ($('#patient-search-container #nama_pasien_2').val() || '').trim().toUpperCase();
                const tgllahir = ($('#patient-search-container #tgllahir_pasien_2').val() || '').trim();
                const gender = $('#patient-search-container input[name="gender_pasien"]:checked').val() || 'L';
                const phone = ($('#patient-search-container #phone_pasien_2').val() || '').trim();
                const alamatDetail = ($('#patient-search-container #alamat_pasien_2').val() || '').trim();
                const tmptLahir = ($('#patient-search-container #tmpt_lahir_2').val() || '').trim();
                const pekerjaan = ($('#patient-search-container #pekerjaan_2').val() || '').trim();

                const fullAlamat = alamatDetail;

                // Get wilayah values from dropdown OR from selectedWilayahData (if using search)
                let provinsiId = $('#patient-search-container #provinsi_pasien_2').val();
                let kabupatenId = $('#patient-search-container #kabupaten_pasien_2').val();
                let kecamatanId = $('#patient-search-container #kecamatan_pasien_2').val();
                let desaId = $('#patient-search-container #desa_pasien_2').val();

                // IMPORTANT: If user used search, get IDs from selectedWilayahData
                if (typeof selectedWilayahData !== 'undefined' && selectedWilayahData) {
                    provinsiId = selectedWilayahData.provinsi_id || provinsiId;
                    kabupatenId = selectedWilayahData.kabupaten_id || kabupatenId;
                    kecamatanId = selectedWilayahData.kecamatan_id || kecamatanId;
                    desaId = selectedWilayahData.desa_id || desaId;
                }

                // More lenient validation - need Nama, Tanggal Lahir, and at least Provinsi (NIK is optional)
                const isValid = nama.length >= 3 && tgllahir.length >= 8 && provinsiId;

                if (isValid) {
                    // Build full wilayah string
                    let fullWilayah = '';
                    if (desaId) fullWilayah = $('#patient-search-container #desa_pasien_2 option:selected').text() + ', ';
                    if (kecamatanId) fullWilayah += $('#patient-search-container #kecamatan_pasien_2 option:selected')
                        .text() + ', ';
                    if (kabupatenId) fullWilayah += $('#patient-search-container #kabupaten_pasien_2 option:selected')
                        .text() + ', ';
                    if (provinsiId) fullWilayah += $('#patient-search-container #provinsi_pasien_2 option:selected').text();

                    // If using search, use full_address from selectedWilayahData
                    if (selectedWilayahData && selectedWilayahData.full_address) {
                        fullWilayah = selectedWilayahData.full_address;
                    }

                    const fullAlamat = alamatDetail;

                    // IMPORTANT: Store new patient data to global variable for persistence
                    selectedPatientFullData = {
                        id_pasien: '', // Empty for new patient
                        nik_pasien: nik,
                        nama_pasien: nama,
                        tgllahir_pasien: tgllahir,
                        gender_pasien: gender === 'L' ? 'Laki-Laki' : 'Perempuan',
                        phone_pasien: phone,
                        alamat_pasien: fullAlamat, // Use combined full address
                        alamat_detail: alamatDetail, // Store detail separately
                        tmpt_lahir: tmptLahir,
                        pekerjaan: pekerjaan,
                        provinsi_id: provinsiId,
                        kabupaten_id: kabupatenId,
                        kecamatan_id: kecamatanId,
                        desa_id: desaId,
                        id_pasien_satu_sehat: '-',
                        no_rekammedis_pasien: $('#patient-search-container #no_rekammedis_pasien_2').val() || '-'
                    };

                    // Set hidden fields (pasien_permohonan_uji_klinik tetap kosong untuk pasien baru)
                    $('#seccond_pasien_permohonan_uji_klinik').val(''); // Keep empty for new patient
                    $('#nopasien_permohonan_uji_klinik').val(nik);
                    $('#tgllahir_pasien_permohonan_uji_klinik').val(tgllahir);

                    // Calculate age if date is valid (dd/mm/yyyy = 10 chars)
                    if (tgllahir.length === 10 && tgllahir.includes('/')) {
                        calculateAge(tgllahir);
                    }

                    // Display new patient details
                    displayPatientDetail(buildPatientDetailViewData({
                        id_pasien_satu_sehat: 'Pasien Baru',
                        nik_pasien: nik || '-',
                        no_rekammedis_pasien: $('#patient-search-container #no_rekammedis_pasien_2').val() || 'Auto Generate',
                        nama_pasien: nama,
                        gender_pasien: gender === 'L' ? 'Laki-Laki' : 'Perempuan',
                        tgllahir_pasien: tgllahir,
                        tmpt_lahir: tmptLahir,
                        pekerjaan: pekerjaan,
                        phone_pasien: phone || '-',
                        alamat_pasien: fullAlamat || '-'
                    }));
                } else {
                    // Show helpful message
                    if (nama || tgllahir || provinsiId) {
                        const missing = [];
                        if (!nama || nama.length < 3) missing.push('Nama (min 3 karakter)');
                        if (!tgllahir || tgllahir.length < 8) missing.push('Tanggal Lahir (format: dd/mm/yyyy)');
                        if (!provinsiId) missing.push('Provinsi');

                        $('#patient-detail-display').html(
                            '<div class="alert alert-info">' +
                            '<i class="fa fa-info-circle"></i> <strong>Lengkapi data berikut:</strong><br>' +
                            '• ' + missing.join('<br>• ') +
                            '</div>'
                        );
                    } else {
                        // If all fields are empty, show empty state
                        $('#patient-detail-display').html(
                            '<div class="alert alert-info">' +
                            '<i class="fa fa-info-circle"></i> <strong>Lengkapi data pasien:</strong><br>' +
                            '• Nama (min 3 karakter)<br>' +
                            '• Tanggal Lahir (format: dd/mm/yyyy)<br>' +
                            '• Provinsi' +
                            '</div>'
                        );
                    }
                    $('#btn-next-step-2').prop('disabled', true);
                    selectedPatientData = null;
                }
            }

            // Monitor ALL input events on these fields including wilayah dropdowns
            $(document).on('input change blur keyup paste',
                '#patient-search-container #nik_pasien_2, ' +
                '#patient-search-container #nama_pasien_2, ' +
                '#patient-search-container #tgllahir_pasien_2, ' +
                '#patient-search-container #phone_pasien_2, ' +
                '#patient-search-container #alamat_pasien_2, ' +
                '#patient-search-container #tmpt_lahir_2, ' +
                '#patient-search-container #pekerjaan_2, ' +
                '#patient-search-container #provinsi_pasien_2, ' +
                '#patient-search-container #kabupaten_pasien_2, ' +
                '#patient-search-container #kecamatan_pasien_2, ' +
                '#patient-search-container #desa_pasien_2',
                function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(updateNewPatientPreview, 300); // Reduced debounce for faster response
                }
            );

            // Also monitor when NIK is cleared (backspace/delete)
            $(document).on('keydown', '#patient-search-container #nik_pasien_2', function(e) {
                // If backspace or delete is pressed and field will be empty
                if ((e.key === 'Backspace' || e.key === 'Delete') && $(this).val().length <= 1) {
                    setTimeout(function() {
                        updateNewPatientPreview();
                    }, 100);
                }
            });

            // Monitor radio buttons for gender
            $(document).on('change', '#patient-search-container input[name="gender_pasien"]', function() {
                updateNewPatientPreview();
            });

            // Trigger initial check after short delay
            setTimeout(function() {
                updateNewPatientPreview();
            }, 1000);
        }

        // Initialize birthday picker for perwakilan when wizard loads
        $(document).ready(function() {
            if (typeof $.fn.bootstrapBirthday !== 'undefined') {
                setTimeout(function() {
                    $('#basic2').bootstrapBirthday({
                        dateFormat: "littleEndian"
                    });
                }, 500);
            }
        });

        // Re-initialize phone number filter for hp dokter pengirim
        $(document).on('input', '#hp_dokter_pengirim_permohonan_uji_klinik', function() {
            this.value = this.value.replace(/[^\d]+/g, '');
        });

        // ============================================
        // WILAYAH CASCADE DROPDOWN SCRIPT
        // ============================================

        // Flag to prevent auto-reset during programmatic fill
        let isAutoFilling = false;
        let buildAddressTimeout = null;

        // Load Provinsi on page load
        function loadProvinsi() {
            $.ajax({
                url: "{{ route('get-provinsi') }}",
                type: 'GET',
                beforeSend: function() {
                    $('#provinsi_pasien_2').addClass('loading');
                },
                success: function(response) {
                    $('#provinsi_pasien_2').removeClass('loading');
                    $('#provinsi_pasien_2').html('<option value="">-- Pilih Provinsi --</option>');

                    $.each(response, function(index, item) {
                        $('#provinsi_pasien_2').append(
                            '<option value="' + item.id_wilayah + '" data-kode="' + item
                            .wilayah_kode + '">' +
                            item.wilayah +
                            '</option>'
                        );
                    });
                },
                error: function(xhr, status, error) {
                    $('#provinsi_pasien_2').removeClass('loading');
                    console.error('Error loading provinsi:', error);
                    swal("Error!", "Gagal memuat data provinsi", "error");
                }
            });
        }

        // Load Kabupaten when Provinsi changes
        $(document).on('change', '#provinsi_pasien_2', function() {
            const provinsiKode = $(this).find(':selected').data('kode');
            const provinsiId = $(this).val();

            // During auto-fill, only reset if not already loading/loaded
            // This allows the dropdown to populate during auto-fill sequence
            if (!isAutoFilling) {
                // Reset child dropdowns only when not auto-filling
                $('#kabupaten_pasien_2').html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop(
                    'disabled',
                    true);
                $('#kecamatan_pasien_2').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled',
                    true);
                $('#desa_pasien_2').html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled',
                    true);
            } else {
                // During auto-fill, only reset if kabupaten is not already loading
                if (!$('#kabupaten_pasien_2').hasClass('loading') && $('#kabupaten_pasien_2 option').length <= 1) {
                    $('#kabupaten_pasien_2').html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop(
                        'disabled',
                        true);
                }
            }

            if (provinsiId) {
                // Check if already loading to prevent duplicate requests
                if ($('#kabupaten_pasien_2').hasClass('loading')) {
                    console.log('⏭️ Kabupaten already loading, skipping duplicate request');
                    return;
                }

                $.ajax({
                    url: "{{ route('get-kabupaten') }}",
                    type: 'POST',
                    data: {
                        _token: getCsrfToken(),
                        provinsi_kode: provinsiKode
                    },
                    beforeSend: function() {
                        $('#kabupaten_pasien_2').addClass('loading');
                    },
                    success: function(response) {
                        $('#kabupaten_pasien_2').removeClass('loading').prop('disabled', false);

                        // Clear existing options except the first one
                        $('#kabupaten_pasien_2 option:not(:first)').remove();

                        $.each(response, function(index, item) {
                            $('#kabupaten_pasien_2').append(
                                '<option value="' + item.id_wilayah + '" data-kode="' + item
                                .wilayah_kode + '">' +
                                item.wilayah +
                                '</option>'
                            );
                        });

                        // Update wilayah data after kabupaten list loaded
                        if (!isAutoFilling) {
                            buildFullAddress();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#kabupaten_pasien_2').removeClass('loading');
                        console.error('Error loading kabupaten:', error);
                        if (!isAutoFilling) {
                            swal("Error!", "Gagal memuat data kabupaten", "error");
                        }
                    }
                });
            } else {
                // Still update even if provinsi is deselected
                if (!isAutoFilling) {
                    buildFullAddress();
                }
            }
        });

        // Load Kecamatan when Kabupaten changes
        $(document).on('change', '#kabupaten_pasien_2', function() {
            syncAlamatFromWilayah();

            const kabupatenKode = $(this).find(':selected').data('kode');
            const kabupatenId = $(this).val();

            // During auto-fill, only reset if not already loading/loaded
            if (!isAutoFilling) {
                // Reset child dropdowns only when not auto-filling
                $('#kecamatan_pasien_2').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled',
                    true);
                $('#desa_pasien_2').html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled',
                    true);
            } else {
                // During auto-fill, only reset if kecamatan is not already loading
                if (!$('#kecamatan_pasien_2').hasClass('loading') && $('#kecamatan_pasien_2 option').length <= 1) {
                    $('#kecamatan_pasien_2').html('<option value="">-- Pilih Kecamatan --</option>').prop(
                        'disabled', true);
                }
            }

            if (kabupatenId) {
                // Check if already loading to prevent duplicate requests
                if ($('#kecamatan_pasien_2').hasClass('loading')) {
                    console.log('⏭️ Kecamatan already loading, skipping duplicate request');
                    return;
                }

                $.ajax({
                    url: "{{ route('get-kecamatan') }}",
                    type: 'POST',
                    data: {
                        _token: getCsrfToken(),
                        kabupaten_kode: kabupatenKode
                    },
                    beforeSend: function() {
                        $('#kecamatan_pasien_2').addClass('loading');
                    },
                    success: function(response) {
                        $('#kecamatan_pasien_2').removeClass('loading').prop('disabled', false);

                        // Clear existing options except the first one
                        $('#kecamatan_pasien_2 option:not(:first)').remove();

                        $.each(response, function(index, item) {
                            $('#kecamatan_pasien_2').append(
                                '<option value="' + item.id_wilayah + '" data-kode="' + item
                                .wilayah_kode + '">' +
                                item.wilayah +
                                '</option>'
                            );
                        });

                        // Update wilayah data after kecamatan list loaded
                        if (!isAutoFilling) {
                            buildFullAddress();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#kecamatan_pasien_2').removeClass('loading');
                        console.error('Error loading kecamatan:', error);
                        if (!isAutoFilling) {
                            swal("Error!", "Gagal memuat data kecamatan", "error");
                        }
                    }
                });
            } else {
                // Still update even if kabupaten is deselected
                if (!isAutoFilling) {
                    buildFullAddress();
                }
            }
        });

        // Load Desa when Kecamatan changes
        $(document).on('change', '#kecamatan_pasien_2', function() {
            const kecamatanKode = $(this).find(':selected').data('kode');
            const kecamatanId = $(this).val();

            // During auto-fill, only reset if not already loading/loaded
            if (!isAutoFilling) {
                // Reset child dropdown only when not auto-filling
                $('#desa_pasien_2').html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled',
                    true);
            } else {
                // During auto-fill, only reset if desa is not already loading
                if (!$('#desa_pasien_2').hasClass('loading') && $('#desa_pasien_2 option').length <= 1) {
                    $('#desa_pasien_2').html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop(
                        'disabled', true);
                }
            }

            if (kecamatanId) {
                // Check if already loading to prevent duplicate requests
                if ($('#desa_pasien_2').hasClass('loading')) {
                    console.log('⏭️ Desa already loading, skipping duplicate request');
                    return;
                }

                $.ajax({
                    url: "{{ route('get-desa') }}",
                    type: 'POST',
                    data: {
                        _token: getCsrfToken(),
                        kecamatan_kode: kecamatanKode
                    },
                    beforeSend: function() {
                        $('#desa_pasien_2').addClass('loading');
                    },
                    success: function(response) {
                        $('#desa_pasien_2').removeClass('loading').prop('disabled', false);

                        // Clear existing options except the first one
                        $('#desa_pasien_2 option:not(:first)').remove();

                        $.each(response, function(index, item) {
                            $('#desa_pasien_2').append(
                                '<option value="' + item.id_wilayah + '" data-kode="' + item
                                .wilayah_kode + '">' +
                                item.wilayah +
                                '</option>'
                            );
                        });

                        // Update wilayah data after desa list loaded
                        if (!isAutoFilling) {
                            buildFullAddress();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#desa_pasien_2').removeClass('loading');
                        console.error('Error loading desa:', error);
                        if (!isAutoFilling) {
                            swal("Error!", "Gagal memuat data desa", "error");
                        }
                    }
                });
            } else {
                // Still update even if kecamatan is deselected
                if (!isAutoFilling) {
                    buildFullAddress();
                }
            }
        });

        // Auto-build full address when desa is selected (Optional - for preview)
        $(document).on('change', '#desa_pasien_2', function() {
            // Skip if auto-filling to prevent double trigger
            if (isAutoFilling) {
                console.log('⏭️ Skipping desa change handler during auto-fill');
                return;
            }
            buildFullAddress();
        });

        function buildFullAddress() {
            // Skip if auto-filling to prevent race condition
            if (isAutoFilling) {
                console.log('⏭️ Skipping buildFullAddress during auto-fill');
                return;
            }

            // Clear any pending timeout to debounce
            clearTimeout(buildAddressTimeout);

            // Debounce: wait 300ms before actually building address
            buildAddressTimeout = setTimeout(function() {
                buildFullAddressNow();
            }, 300);
        }

        function getWilayahPartsFromDropdown() {
            const desaText = $('#desa_pasien_2 option:selected').text();
            const kecamatanText = $('#kecamatan_pasien_2 option:selected').text();
            const kabupatenText = $('#kabupaten_pasien_2 option:selected').text();
            const provinsiText = $('#provinsi_pasien_2 option:selected').text();
            const wilayahParts = [];

            if (desaText && desaText !== '-- Pilih Desa/Kelurahan --') {
                wilayahParts.push(desaText);
            }
            if (kecamatanText && kecamatanText !== '-- Pilih Kecamatan --') {
                wilayahParts.push(kecamatanText);
            }
            if (kabupatenText && kabupatenText !== '-- Pilih Kabupaten/Kota --') {
                wilayahParts.push(kabupatenText);
            }
            if (provinsiText && provinsiText !== '-- Pilih Provinsi --') {
                wilayahParts.push(provinsiText);
            }

            return wilayahParts;
        }

        function syncAlamatFromWilayah() {
            const $alamat = $('#alamat_pasien_2');
            if (!$alamat.length) {
                return;
            }

            const fullWilayah = getWilayahPartsFromDropdown().join(', ');
            if (fullWilayah) {
                $alamat.val(fullWilayah);
            }
        }

        function buildFullAddressNow() {
            console.log('📍 Building full address from dropdown values...');

            const desaId = $('#desa_pasien_2').val();
            const kecamatanId = $('#kecamatan_pasien_2').val();
            const kabupatenId = $('#kabupaten_pasien_2').val();
            const provinsiId = $('#provinsi_pasien_2').val();

            const wilayahParts = getWilayahPartsFromDropdown();
            const fullWilayah = wilayahParts.join(', ');

            syncAlamatFromWilayah();

            // Update selectedWilayahData if we have at least one wilayah selected
            if (fullWilayah) {
                selectedWilayahData = {
                    provinsi_id: provinsiId || null,
                    kabupaten_id: kabupatenId || null,
                    kecamatan_id: kecamatanId || null,
                    desa_id: desaId || null,
                    full_address: fullWilayah
                };

                console.log('📍 Updated selectedWilayahData from dropdown:', selectedWilayahData);

                // Show hint badge
                const hint =
                    '<div class="alert alert-success mt-2 wilayah-hint"><i class="fa fa-check-circle mr-2"></i>' +
                    '<strong>Wilayah terpilih:</strong> ' + fullWilayah + '</div>';

                $('.wilayah-hint').remove();
                $('#alamat_pasien_2').after(hint);

                // IMPORTANT: Trigger update patient preview to show wilayah in display
                setTimeout(function() {
                    $('#patient-search-container #nik_pasien_2').trigger('input');
                    console.log('🔄 Triggered patient preview update after dropdown selection');
                }, 300);
            }
        }

        // Initialize: Load provinsi when "Tambah Pasien Baru" is clicked
        $(document).on('click', '#btn-add-new-patient', function() {
            setTimeout(function() {
                loadProvinsi();
            }, 200);
        });

        // Also load provinsi if new patient form is already shown on page load
        $(document).ready(function() {
            if ($('#display-new-pasien').is(':visible') || $('#patient-search-container #provinsi_pasien_2')
                .length > 0) {
                loadProvinsi();
            }
        });

        // IMPORTANT: Update monitorNewPatientForm to also check wilayah fields
        function monitorNewPatientFormWithWilayah() {
            // Use debounce for better performance
            let debounceTimer;

            // Function to update preview
            function updateNewPatientPreview() {
                const nik = ($('#patient-search-container #nik_pasien_2').val() || '').trim();
                const nama = ($('#patient-search-container #nama_pasien_2').val() || '').trim();
                const tgllahir = ($('#patient-search-container #tgllahir_pasien_2').val() || '').trim();
                const gender = $('#patient-search-container input[name="gender_pasien"]:checked').val() || 'L';
                const phone = ($('#patient-search-container #phone_pasien_2').val() || '').trim();
                const tmptLahir = ($('#patient-search-container #tmpt_lahir_2').val() || '').trim();
                const pekerjaan = ($('#patient-search-container #pekerjaan_2').val() || '').trim();

                // Get wilayah values from dropdown OR from selectedWilayahData (if using search)
                let provinsiId = $('#patient-search-container #provinsi_pasien_2').val();
                let kabupatenId = $('#patient-search-container #kabupaten_pasien_2').val();
                let kecamatanId = $('#patient-search-container #kecamatan_pasien_2').val();
                let desaId = $('#patient-search-container #desa_pasien_2').val();

                // IMPORTANT: If user used search, get IDs from selectedWilayahData
                if (typeof selectedWilayahData !== 'undefined' && selectedWilayahData) {
                    console.log('🔍 Using wilayah data from search:', selectedWilayahData);
                    provinsiId = selectedWilayahData.provinsi_id || provinsiId;
                    kabupatenId = selectedWilayahData.kabupaten_id || kabupatenId;
                    kecamatanId = selectedWilayahData.kecamatan_id || kecamatanId;
                    desaId = selectedWilayahData.desa_id || desaId;
                } else {
                    console.log('📝 Using wilayah data from dropdown:', {
                        provinsiId,
                        kabupatenId,
                        kecamatanId,
                        desaId
                    });
                }

                const alamat = ($('#patient-search-container #alamat_pasien_2').val() || '').trim();

                // More lenient validation - need Nama, Tanggal Lahir, and at least Provinsi (NIK is optional)
                const isValid = nama.length >= 3 && tgllahir.length >= 8 && provinsiId;

                if (isValid) {
                    // Build full wilayah string
                    let fullWilayah = '';
                    if (desaId) fullWilayah = $('#patient-search-container #desa_pasien_2 option:selected').text() + ', ';
                    if (kecamatanId) fullWilayah += $('#patient-search-container #kecamatan_pasien_2 option:selected')
                        .text() + ', ';
                    if (kabupatenId) fullWilayah += $('#patient-search-container #kabupaten_pasien_2 option:selected')
                        .text() + ', ';
                    if (provinsiId) fullWilayah += $('#patient-search-container #provinsi_pasien_2 option:selected').text();

                    const fullAlamat = alamat;

                    // IMPORTANT: Store new patient data to global variable for persistence
                    selectedPatientFullData = {
                        id_pasien: '', // Empty for new patient
                        nik_pasien: nik,
                        nama_pasien: nama,
                        tgllahir_pasien: tgllahir,
                        gender_pasien: gender === 'L' ? 'Laki-Laki' : 'Perempuan',
                        phone_pasien: phone,
                        alamat_pasien: fullAlamat,
                        tmpt_lahir: tmptLahir,
                        pekerjaan: pekerjaan,
                        provinsi_id: provinsiId,
                        kabupaten_id: kabupatenId,
                        kecamatan_id: kecamatanId,
                        desa_id: desaId,
                        id_pasien_satu_sehat: '-',
                        no_rekammedis_pasien: $('#patient-search-container #no_rekammedis_pasien_2').val() || '-'
                    };

                    console.log('✅ selectedPatientFullData with wilayah:', selectedPatientFullData);

                    // Set hidden fields
                    $('#seccond_pasien_permohonan_uji_klinik').val('');
                    $('#nopasien_permohonan_uji_klinik').val(nik);
                    $('#tgllahir_pasien_permohonan_uji_klinik').val(tgllahir);

                    // Calculate age if date is valid
                    if (tgllahir.length === 10 && tgllahir.includes('/')) {
                        calculateAge(tgllahir);
                    }

                    // Display new patient details
                    displayPatientDetail(buildPatientDetailViewData({
                        id_pasien_satu_sehat: 'Pasien Baru',
                        nik_pasien: nik || '-',
                        no_rekammedis_pasien: $('#patient-search-container #no_rekammedis_pasien_2').val() || 'Auto Generate',
                        nama_pasien: nama,
                        gender_pasien: gender === 'L' ? 'Laki-Laki' : 'Perempuan',
                        tgllahir_pasien: tgllahir,
                        tmpt_lahir: tmptLahir,
                        pekerjaan: pekerjaan,
                        phone_pasien: phone || '-',
                        alamat_pasien: fullAlamat || '-'
                    }));
                } else {
                    // Show helpful message
                    if (nama || tgllahir || provinsiId) {
                        const missing = [];
                        if (!nama || nama.length < 3) missing.push('Nama (min 3 karakter)');
                        if (!tgllahir || tgllahir.length < 8) missing.push('Tanggal Lahir (format: dd/mm/yyyy)');
                        if (!provinsiId) missing.push('Provinsi');

                        $('#patient-detail-display').html(
                            '<div class="alert alert-info">' +
                            '<i class="fa fa-info-circle"></i> <strong>Lengkapi data berikut:</strong><br>' +
                            '• ' + missing.join('<br>• ') +
                            '</div>'
                        );
                    } else {
                        // If all fields are empty, show empty state
                        $('#patient-detail-display').html(
                            '<div class="alert alert-info">' +
                            '<i class="fa fa-info-circle"></i> <strong>Lengkapi data pasien:</strong><br>' +
                            '• Nama (min 3 karakter)<br>' +
                            '• Tanggal Lahir (format: dd/mm/yyyy)<br>' +
                            '• Provinsi' +
                            '</div>'
                        );
                    }
                    $('#btn-next-step-2').prop('disabled', true);
                    selectedPatientData = null;
                }
            }

            // Monitor ALL input events
            $(document).on('input change blur keyup paste',
                '#patient-search-container #nik_pasien_2, ' +
                '#patient-search-container #nama_pasien_2, ' +
                '#patient-search-container #tgllahir_pasien_2, ' +
                '#patient-search-container #phone_pasien_2, ' +
                '#patient-search-container #alamat_pasien_2, ' +
                '#patient-search-container #tmpt_lahir_2, ' +
                '#patient-search-container #pekerjaan_2, ' +
                '#patient-search-container #provinsi_pasien_2, ' +
                '#patient-search-container #kabupaten_pasien_2, ' +
                '#patient-search-container #kecamatan_pasien_2, ' +
                '#patient-search-container #desa_pasien_2',
                function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(updateNewPatientPreview, 300); // Reduced debounce for faster response
                }
            );

            // Also monitor when NIK is cleared (backspace/delete)
            $(document).on('keydown', '#patient-search-container #nik_pasien_2', function(e) {
                // If backspace or delete is pressed and field will be empty
                if ((e.key === 'Backspace' || e.key === 'Delete') && $(this).val().length <= 1) {
                    setTimeout(function() {
                        updateNewPatientPreview();
                    }, 100);
                }
            });

            // Monitor radio buttons for gender
            $(document).on('change', '#patient-search-container input[name="gender_pasien"]', function() {
                updateNewPatientPreview();
            });

            // Trigger initial check
            setTimeout(function() {
                updateNewPatientPreview();
            }, 1000);
        }

        // Replace old monitorNewPatientForm with new one that includes wilayah
        // This should be called after new patient form is loaded
        $(document).on('click', '#btn-add-new-patient', function() {
            setTimeout(function() {
                monitorNewPatientFormWithWilayah();
            }, 500);
        });

        // ============================================
        // SEARCH TEMPAT LAHIR (KAB/KOTA & KECAMATAN)
        // ============================================

        let searchTmptLahirTimer;
        const tmptLahirTypeLabel = { KAB: 'Kabupaten/Kota', KEC: 'Kecamatan' };

        $(document).on('input', '#patient-search-container #search_tmpt_lahir_input', function() {
            const keyword = $(this).val().trim();
            clearTimeout(searchTmptLahirTimer);

            if (keyword.length < 2) {
                $('#patient-search-container #search_tmpt_lahir_results').hide();
                return;
            }

            searchTmptLahirTimer = setTimeout(function() {
                $.ajax({
                    url: "{{ route('search-wilayah') }}",
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        limit: 15,
                        types: 'KAB,KEC'
                    },
                    success: function(response) {
                        const $resultsList = $('#patient-search-container #search_tmpt_lahir_results_list');
                        $resultsList.empty();

                        if (!response.length) {
                            $resultsList.html(
                                '<div class="p-3 text-center text-muted">Wilayah tidak ditemukan</div>'
                            );
                        } else {
                            response.forEach(function(item) {
                                const tipeLabel = tmptLahirTypeLabel[item.tipe] || item.tipe;
                                $resultsList.append(`
                                    <a href="javascript:void(0)" class="list-group-item list-group-item-action tmpt-lahir-result-item"
                                       data-nama="${item.nama}">
                                        <strong>${item.nama}</strong> <span class="text-muted">(${tipeLabel})</span>
                                        <br><small class="text-muted">${item.full_path || '-'}</small>
                                    </a>
                                `);
                            });
                        }
                        $('#patient-search-container #search_tmpt_lahir_results').show();
                    }
                });
            }, 400);
        });

        $(document).on('click', '#patient-search-container .tmpt-lahir-result-item', function() {
            const nama = $(this).data('nama');
            $('#patient-search-container #tmpt_lahir_2').val(nama).trigger('input');
            $('#patient-search-container #search_tmpt_lahir_input').val('');
            $('#patient-search-container #search_tmpt_lahir_results').hide();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#patient-search-container #search_tmpt_lahir_input, #patient-search-container #search_tmpt_lahir_results').length) {
                $('#patient-search-container #search_tmpt_lahir_results').hide();
            }
        });

        // ============================================
        // SEARCH WILAYAH AUTOCOMPLETE SCRIPT
        // ============================================

        let searchWilayahTimer;
        // selectedWilayahData already declared above at line 3804

        // Toggle manual selector
        $(document).on('click', '#btn_toggle_manual_select', function() {
            $('#manual_wilayah_selector').slideToggle(300);
            const icon = $(this).find('i');
            if (icon.hasClass('fa-list')) {
                icon.removeClass('fa-list').addClass('fa-times');
                $(this).html('<i class="fa fa-times mr-1"></i> Sembunyikan');
            } else {
                icon.removeClass('fa-times').addClass('fa-list');
                $(this).html('<i class="fa fa-list mr-1"></i> Pilih Manual');
            }
        });

        $(document).on('click', '#btn_hide_manual_select', function() {
            $('#manual_wilayah_selector').slideUp(300);
            $('#btn_toggle_manual_select').html('<i class="fa fa-list mr-1"></i> Pilih Manual');
        });

        // Search wilayah with debounce
        $(document).on('input', '#patient-search-container #search_wilayah_input', function() {
            const keyword = $(this).val().trim();

            clearTimeout(searchWilayahTimer);

            if (keyword.length < 2) {
                $('#patient-search-container #search_wilayah_results').hide();
                return;
            }

            // Add loading class
            $(this).addClass('searching');

            searchWilayahTimer = setTimeout(function() {
                $.ajax({
                    url: "{{ route('search-wilayah') }}",
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        limit: 10
                    },
                    success: function(response) {
                        $('#patient-search-container #search_wilayah_input').removeClass(
                            'searching');
                        displaySearchResults(response);
                    },
                    error: function(xhr, status, error) {
                        $('#patient-search-container #search_wilayah_input').removeClass(
                            'searching');
                        console.error('Error searching wilayah:', error);
                    }
                });
            }, 500); // 500ms debounce
        });

        // Display search results
        function displaySearchResults(results) {
            const $resultsList = $('#patient-search-container #search_wilayah_results_list');
            const $resultsContainer = $('#patient-search-container #search_wilayah_results');

            $resultsList.empty();

            if (results.length === 0) {
                $resultsList.html(`
                    <div class="no-results">
                        <i class="fa fa-search" style="font-size: 32px; margin-bottom: 10px;"></i>
                        <div>Wilayah tidak ditemukan</div>
                        <small>Coba kata kunci lain</small>
                    </div>
                `);
                $resultsContainer.show();
                return;
            }

            results.forEach(function(item) {
                const typeClass = item.tipe.toLowerCase();
                const typeLabel = getTipeLabel(item.tipe);

                const resultItem = $(`
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action wilayah-result-item"
                        data-id="${item.id}"
                        data-kode="${item.kode}"
                        data-nama="${item.nama}"
                        data-tipe="${item.tipe}"
                        data-full-path="${item.full_path}">
                        <div class="wilayah-name">
                            ${item.nama}
                            <span class="wilayah-type ${typeClass}">${typeLabel}</span>
                        </div>
                        <div class="wilayah-path">
                            <i class="fa fa-map-marker mr-1"></i>${item.full_path || '-'}
                        </div>
                    </a>
                `);

                $resultsList.append(resultItem);
            });

            $resultsContainer.show();
        }

        // Select wilayah from search results
        $(document).on('click', '.wilayah-result-item', function() {
            const wilayahId = $(this).data('id');
            const wilayahKode = $(this).data('kode');
            const wilayahNama = $(this).data('nama');
            const wilayahTipe = $(this).data('tipe');
            const wilayahFullPath = $(this).data('full-path');

            // Hide search results
            $('#patient-search-container #search_wilayah_results').hide();

            // Clear search input
            $('#patient-search-container #search_wilayah_input').val('');

            // Show loading
            swal({
                title: "Memuat data wilayah...",
                text: "Mohon tunggu sebentar",
                icon: "info",
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false
            });

            // Fetch full wilayah detail with parent IDs
            $.ajax({
                url: "{{ route('get-wilayah-detail') }}",
                type: 'GET',
                data: {
                    wilayah_id: wilayahId
                },
                success: function(response) {
                    swal.close();

                    const parents = response.parents;

                    // Set flag to prevent auto-reset
                    isAutoFilling = true;

                    console.log('🔄 Starting auto-fill sequence...');
                    console.log('   Target: wilayahTipe =', wilayahTipe, ', wilayahId =', wilayahId);
                    console.log('   Parents:', parents);

                    // Set the dropdown values based on selected wilayah
                    // Using chained callbacks to ensure proper sequence
                    if (parents.provinsi_id) {
                        $('#patient-search-container #provinsi_pasien_2').val(parents.provinsi_id)
                            .trigger('change');
                        console.log('   ✓ Set provinsi:', parents.provinsi_id);

                        // Wait for kabupaten to load
                        setTimeout(function() {
                            if (parents.kabupaten_id) {
                                // Wait for kabupaten dropdown to be populated
                                let kabupatenAttempts = 0;
                                const checkKabupaten = setInterval(function() {
                                    kabupatenAttempts++;
                                    const $kabupatenDropdown = $('#kabupaten_pasien_2');

                                    if (kabupatenAttempts >
                                        60) { // 6 seconds timeout (100ms * 60)
                                        clearInterval(checkKabupaten);
                                        console.error(
                                            '⏱ Timeout waiting for kabupaten dropdown after',
                                            kabupatenAttempts * 100, 'ms'
                                        );
                                        console.error('   Dropdown loading state:',
                                            $kabupatenDropdown.hasClass('loading'));
                                        console.error('   Dropdown options count:',
                                            $kabupatenDropdown.find('option').length
                                        );
                                        console.error('   Expected kabupaten_id:',
                                            parents.kabupaten_id);
                                        isAutoFilling = false;
                                        swal.close();
                                        swal("Error!",
                                            "Gagal memuat data kabupaten. Silakan coba lagi atau pilih manual.",
                                            "error");
                                        return;
                                    }

                                    // Check if dropdown is ready (not loading and has options)
                                    const isNotLoading = !$kabupatenDropdown.hasClass(
                                        'loading');
                                    const hasOptions = $kabupatenDropdown.find('option')
                                        .length > 1;
                                    const hasTargetOption = $kabupatenDropdown.find(
                                        'option[value="' + parents.kabupaten_id +
                                        '"]').length > 0;

                                    if (isNotLoading && hasOptions && hasTargetOption) {
                                        clearInterval(checkKabupaten);
                                        console.log(
                                            '   ✓ Kabupaten dropdown ready, setting value:',
                                            parents.kabupaten_id);

                                        $kabupatenDropdown
                                            .val(parents.kabupaten_id).trigger(
                                                'change');

                                        // Wait for kecamatan to load
                                        setTimeout(function() {
                                                if (parents.kecamatan_id) {
                                                    // Wait for kecamatan dropdown to be populated
                                                    let kecamatanAttempts = 0;
                                                    const checkKecamatan =
                                                        setInterval(function() {
                                                                kecamatanAttempts++;
                                                                if (kecamatanAttempts >
                                                                    30
                                                                ) { // 3 seconds timeout (reduced from 5)
                                                                    clearInterval(
                                                                        checkKecamatan
                                                                    );
                                                                    console.error(
                                                                        '⏱ Timeout waiting for kecamatan dropdown'
                                                                    );
                                                                    isAutoFilling =
                                                                        false;
                                                                    return;
                                                                }

                                                                if (!$(
                                                                        '#patient-search-container #kecamatan_pasien_2'
                                                                    )
                                                                    .hasClass(
                                                                        'loading'
                                                                    ) &&
                                                                    $('#patient-search-container #kecamatan_pasien_2 option[value="' +
                                                                        parents
                                                                        .kecamatan_id +
                                                                        '"]')
                                                                    .length > 0) {
                                                                    clearInterval(
                                                                        checkKecamatan
                                                                    );
                                                                    console.log(
                                                                        '   ✓ Kecamatan dropdown ready, setting value:',
                                                                        parents
                                                                        .kecamatan_id
                                                                    );

                                                                    $('#patient-search-container #kecamatan_pasien_2')
                                                                        .val(parents
                                                                            .kecamatan_id
                                                                        )
                                                                        .trigger(
                                                                            'change'
                                                                        );

                                                                    // Wait for desa to load
                                                                    setTimeout(
                                                                        function() {
                                                                            if (parents
                                                                                .desa_id
                                                                            ) {
                                                                                // Wait for desa dropdown to be populated
                                                                                let desaAttempts =
                                                                                    0;
                                                                                const
                                                                                    checkDesa =
                                                                                    setInterval(
                                                                                        function() {
                                                                                            desaAttempts++;
                                                                                            if (desaAttempts >
                                                                                                30
                                                                                            ) { // 3 seconds timeout (reduced from 5)
                                                                                                clearInterval
                                                                                                    (
                                                                                                        checkDesa
                                                                                                    );
                                                                                                console
                                                                                                    .error(
                                                                                                        '⏱ Timeout waiting for desa dropdown'
                                                                                                    );
                                                                                                isAutoFilling
                                                                                                    =
                                                                                                    false;
                                                                                                return;
                                                                                            }

                                                                                            if (!
                                                                                                $(
                                                                                                    '#patient-search-container #desa_pasien_2'
                                                                                                )
                                                                                                .hasClass(
                                                                                                    'loading'
                                                                                                ) &&
                                                                                                $('#patient-search-container #desa_pasien_2 option[value="' +
                                                                                                    parents
                                                                                                    .desa_id +
                                                                                                    '"]'
                                                                                                )
                                                                                                .length >
                                                                                                0
                                                                                            ) {
                                                                                                clearInterval
                                                                                                    (
                                                                                                        checkDesa
                                                                                                    );
                                                                                                console
                                                                                                    .log(
                                                                                                        '   ✓ Desa dropdown ready, setting value:',
                                                                                                        parents
                                                                                                        .desa_id
                                                                                                    );

                                                                                                $('#patient-search-container #desa_pasien_2')
                                                                                                    .val(
                                                                                                        parents
                                                                                                        .desa_id
                                                                                                    );
                                                                                                console
                                                                                                    .log(
                                                                                                        '✅ AUTO-FILL COMPLETE! Final desa value:',
                                                                                                        $(
                                                                                                            '#patient-search-container #desa_pasien_2'
                                                                                                        )
                                                                                                        .val()
                                                                                                    );

                                                                                                // Finish auto-filling
                                                                                                isAutoFilling
                                                                                                    =
                                                                                                    false;

                                                                                                // Now manually call buildFullAddress after auto-fill is complete
                                                                                                setTimeout
                                                                                                    (function() {
                                                                                                            buildFullAddress
                                                                                                                ();
                                                                                                        },
                                                                                                        800
                                                                                                    );
                                                                                            }
                                                                                        },
                                                                                        100
                                                                                    );
                                                                            } else if (
                                                                                wilayahTipe ===
                                                                                'DESA'
                                                                            ) {
                                                                                // Wait for desa dropdown to be populated
                                                                                let desaAttempts =
                                                                                    0;
                                                                                const
                                                                                    checkDesa =
                                                                                    setInterval(
                                                                                        function() {
                                                                                            desaAttempts++;
                                                                                            if (desaAttempts >
                                                                                                30
                                                                                            ) { // 3 seconds timeout (reduced from 5)
                                                                                                clearInterval
                                                                                                    (
                                                                                                        checkDesa
                                                                                                    );
                                                                                                console
                                                                                                    .error(
                                                                                                        '⏱ Timeout waiting for desa dropdown (DESA type)'
                                                                                                    );
                                                                                                isAutoFilling
                                                                                                    =
                                                                                                    false;
                                                                                                return;
                                                                                            }

                                                                                            if (!
                                                                                                $(
                                                                                                    '#patient-search-container #desa_pasien_2'
                                                                                                )
                                                                                                .hasClass(
                                                                                                    'loading'
                                                                                                ) &&
                                                                                                $('#patient-search-container #desa_pasien_2 option[value="' +
                                                                                                    wilayahId +
                                                                                                    '"]'
                                                                                                )
                                                                                                .length >
                                                                                                0
                                                                                            ) {
                                                                                                clearInterval
                                                                                                    (
                                                                                                        checkDesa
                                                                                                    );
                                                                                                console
                                                                                                    .log(
                                                                                                        '   ✓ Desa dropdown ready (DESA type), setting value:',
                                                                                                        wilayahId
                                                                                                    );

                                                                                                $('#patient-search-container #desa_pasien_2')
                                                                                                    .val(
                                                                                                        wilayahId
                                                                                                    );
                                                                                                console
                                                                                                    .log(
                                                                                                        '✅ AUTO-FILL COMPLETE! Final desa value (DESA type):',
                                                                                                        $(
                                                                                                            '#patient-search-container #desa_pasien_2'
                                                                                                        )
                                                                                                        .val()
                                                                                                    );

                                                                                                // Finish auto-filling
                                                                                                isAutoFilling
                                                                                                    =
                                                                                                    false;

                                                                                                // Now manually call buildFullAddress after auto-fill is complete
                                                                                                setTimeout
                                                                                                    (function() {
                                                                                                            buildFullAddress
                                                                                                                ();
                                                                                                        },
                                                                                                        800
                                                                                                    );
                                                                                            }
                                                                                        },
                                                                                        100
                                                                                    );
                                                                            } else if (
                                                                                wilayahTipe ===
                                                                                'KEC'
                                                                            ) {
                                                                                $('#patient-search-container #kecamatan_pasien_2')
                                                                                    .val(
                                                                                        wilayahId
                                                                                    );
                                                                                console
                                                                                    .log(
                                                                                        '✓ Set kecamatan from selected item (KEC type):',
                                                                                        wilayahId
                                                                                    );

                                                                                // Finish auto-filling
                                                                                isAutoFilling
                                                                                    =
                                                                                    false;

                                                                                // Now manually call buildFullAddress after auto-fill is complete
                                                                                setTimeout
                                                                                    (function() {
                                                                                            buildFullAddress
                                                                                                ();
                                                                                        },
                                                                                        200
                                                                                    );
                                                                            } else {
                                                                                // Finish auto-filling
                                                                                isAutoFilling
                                                                                    =
                                                                                    false;

                                                                                // Call buildFullAddress just in case
                                                                                setTimeout
                                                                                    (function() {
                                                                                            buildFullAddress
                                                                                                ();
                                                                                        },
                                                                                        800
                                                                                    );
                                                                            }
                                                                        }, 50
                                                                    ); // Reduced from 100ms to 50ms for faster checking
                                                                }
                                                            },
                                                            50
                                                        ); // Reduced from 100ms to 50ms for faster checking
                                                } else if (wilayahTipe === 'KAB') {
                                                    $('#patient-search-container #kabupaten_pasien_2')
                                                        .val(wilayahId);
                                                    console.log(
                                                        '✓ Set kabupaten from selected item (KAB type):',
                                                        wilayahId);

                                                    // Finish auto-filling
                                                    isAutoFilling = false;

                                                    // Now manually call buildFullAddress after auto-fill is complete
                                                    setTimeout(function() {
                                                        buildFullAddress();
                                                    }, 800);
                                                } else {
                                                    // Finish auto-filling
                                                    isAutoFilling = false;
                                                }
                                            },
                                            50
                                        ); // Reduced from 100ms to 50ms for faster checking
                                    }
                                }, 50); // Reduced from 100ms to 50ms for faster checking
                            } else if (wilayahTipe === 'PROV') {
                                console.log('✓ Set provinsi only (PROV type):', wilayahId);

                                // Finish auto-filling
                                isAutoFilling = false;

                                // Now manually call buildFullAddress after auto-fill is complete
                                setTimeout(function() {
                                    buildFullAddress();
                                }, 800);
                            } else {
                                // Finish auto-filling
                                isAutoFilling = false;

                                // Call buildFullAddress just in case
                                setTimeout(function() {
                                    buildFullAddress();
                                }, 200);
                            }
                        }, 50); // Reduced from 100ms to 50ms for faster checking
                    } else {
                        // Finish auto-filling
                        isAutoFilling = false;

                        // Call buildFullAddress just in case
                        setTimeout(function() {
                            buildFullAddress();
                        }, 800);
                    }

                    // Build full address for display
                    const fullAddress = wilayahFullPath ? wilayahNama + ', ' + wilayahFullPath :
                        wilayahNama;

                    // Store selected wilayah
                    selectedWilayahData = {
                        provinsi_id: parents.provinsi_id,
                        kabupaten_id: parents.kabupaten_id || (wilayahTipe === 'KAB' ? wilayahId :
                            null),
                        kecamatan_id: parents.kecamatan_id || (wilayahTipe === 'KEC' ? wilayahId :
                            null),
                        desa_id: parents.desa_id || (wilayahTipe === 'DESA' ? wilayahId : null),
                        full_address: fullAddress
                    };

                    console.log('📍 SELECTED WILAYAH DATA from search:');
                    console.log('   wilayahId:', wilayahId);
                    console.log('   wilayahTipe:', wilayahTipe);
                    console.log('   parents:', parents);
                    console.log('   ➡️ Final selectedWilayahData:', selectedWilayahData);

                    if (fullAddress) {
                        $('#alamat_pasien_2').val(fullAddress);
                    }

                    // Show success badge
                    $('.wilayah-hint').remove();
                    const successBadge = `
                        <div class="selected-wilayah-badge wilayah-hint">
                            <i class="fa fa-check-circle"></i>
                            Wilayah terpilih: <strong>${fullAddress}</strong>
                        </div>
                    `;
                    $('#patient-search-container #search_wilayah_input').parent().after(successBadge);

                    // IMPORTANT: Trigger update patient preview to show wilayah in display
                    // Wait a bit longer to ensure auto-fill is completely done
                    setTimeout(function() {
                        // Manually trigger input event on NIK to force preview update
                        $('#patient-search-container #nik_pasien_2').trigger('input');
                        console.log(
                            '🔄 Triggered patient preview update after wilayah selection');
                    }, 2000);

                    // Show success notification
                    swal({
                        title: "Berhasil!",
                        text: "Wilayah " + wilayahNama + " telah dipilih",
                        icon: "success",
                        timer: 2000,
                        buttons: false
                    });
                },
                error: function(xhr, status, error) {
                    swal.close();
                    console.error('Error getting wilayah detail:', error);
                    swal("Error!", "Gagal memuat detail wilayah", "error");
                }
            });
        });

        // Helper function for tipe label
        function getTipeLabel(tipe) {
            const labels = {
                'DESA': 'Desa/Kel',
                'KEC': 'Kecamatan',
                'KAB': 'Kab/Kota',
                'PROV': 'Provinsi'
            };
            return labels[tipe] || tipe;
        }

        // Close search results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#search_wilayah_input, #search_wilayah_results').length) {
                $('#patient-search-container #search_wilayah_results').hide();
            }
        });

        // Show search results when focus on input
        $(document).on('focus', '#patient-search-container #search_wilayah_input', function() {
            if ($('#patient-search-container #search_wilayah_results_list').children().length > 0) {
                $('#patient-search-container #search_wilayah_results').show();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            window.calculateAge = function(birthDate) {
                try {
                    const today = new Date();
                    let birth;

                    if (birthDate instanceof Date) {
                        birth = birthDate;
                    } else if (typeof birthDate === 'string') {
                        const s = birthDate.trim();
                        if (/^\d{2}\/\d{2}\/\d{4}$/.test(s)) {
                            const [dd, mm, yyyy] = s.split('/').map(v => parseInt(v, 10));
                            birth = new Date(yyyy, mm - 1, dd);
                        } else if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
                            birth = new Date(s);
                        } else {
                            birth = new Date(s);
                        }
                    } else {
                        birth = new Date(birthDate);
                    }

                    if (!(birth instanceof Date) || isNaN(birth.getTime())) {
                        $('#umurtahun_pasien_permohonan_uji_klinik').val('');
                        $('#umurbulan_pasien_permohonan_uji_klinik').val('');
                        $('#umurhari_pasien_permohonan_uji_klinik').val('');
                        return { years: 0, months: 0, days: 0 };
                    }

                    let years = today.getFullYear() - birth.getFullYear();
                    let months = today.getMonth() - birth.getMonth();
                    let days = today.getDate() - birth.getDate();

                    if (days < 0) {
                        months--;
                        days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
                    }
                    if (months < 0) {
                        years--;
                        months += 12;
                    }

                    $('#umurtahun_pasien_permohonan_uji_klinik').val(years);
                    $('#umurbulan_pasien_permohonan_uji_klinik').val(months);
                    $('#umurhari_pasien_permohonan_uji_klinik').val(days);

                    return { years: years, months: months, days: days };
                } catch (error) {
                    $('#umurtahun_pasien_permohonan_uji_klinik').val('');
                    $('#umurbulan_pasien_permohonan_uji_klinik').val('');
                    $('#umurhari_pasien_permohonan_uji_klinik').val('');
                    return { years: 0, months: 0, days: 0 };
                }
            };

            if ($('#step-3').hasClass('active')) {
                initializeStep3Controls();
                deferCreatePageInit(function() {
                    initializeTinyMCE();
                });
            }

            @if(isset($numberSettings) && $numberSettings->is_nomor_spesimen_manual)
            $('#noregister_permohonan_uji_klinik').prop('readonly', false);
            @endif
        });
    </script>
    @if (!empty($isEdit) && !empty($klinikEditData))
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.klinik-edit-init')
    @endif
@endsection
