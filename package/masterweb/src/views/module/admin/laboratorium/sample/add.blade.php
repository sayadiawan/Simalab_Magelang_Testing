@extends('masterweb::template.admin.layout')
@section('title')
    Input Data Sampel
@endsection

@section('css')
    <style>
        * {
            margin: 0;
            padding: 0
        }

        html {
            height: 100%
        }

        /* Modern Page Styling */
        .page-header-card-sample {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
            color: white;
        }

        .page-header-card-sample h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .page-header-card-sample h2 i {
            margin-right: 15px;
            font-size: 32px;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
        }

        .page-header-card-sample .subtitle {
            margin-top: 10px;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-section-card-sample {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: none;
            contain: layout style;
        }

        .section-title-sample {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #11998e;
            display: flex;
            align-items: center;
        }

        .section-title-sample i {
            margin-right: 12px;
            color: #11998e;
            font-size: 24px;
        }

        .info-alert-custom {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            border-left: 5px solid #00acc1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-alert-custom strong {
            color: #006064;
        }

        .guide-alert-custom {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left: 5px solid #ff9800;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .guide-alert-custom strong {
            color: #e65100;
            font-size: 16px;
        }

        .guide-alert-custom ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
            margin-top: 10px;
        }

        .guide-alert-custom li {
            margin-bottom: 8px;
            color: #424242;
        }

        .action-buttons-sample {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .gap-3 {
            gap: 15px;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .btn-primary-sample {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
            transition: all 0.3s;
            color: white;
        }

        .btn-primary-sample:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .btn-secondary-sample {
            background: #e2e8f0;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            color: #4a5568;
            transition: all 0.3s;
        }

        .btn-secondary-sample:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
        }

        .breadcrumb {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .breadcrumb-item a {
            color: #11998e;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #4a5568;
        }

        /* Parameter Search & Pagination Styles */
        .card-header h5 {
            margin-bottom: 0 !important;
        }

        #search-parameter {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        #search-parameter:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
        }

        #items-per-page {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        /* Radio Button Styling */
        .form-check {
            padding: 12px 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .form-check:hover {
            background: #e9ecef;
            border-color: #11998e;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 0.15em;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #11998e;
            border-color: #11998e;
        }

        .form-check-label {
            margin-left: 10px;
            font-size: 15px;
            font-weight: 500;
            color: #4a5568;
            cursor: pointer;
        }

        .list-group-item {
            border: none;
            padding: 0;
        }

        /* Jenis Sampel Button Styling */
        .btn-pick-jenis {
            border: 2px solid #11998e !important;
            color: #11998e !important;
            background: white !important;
            padding: 10px 20px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            transition: all 0.3s !important;
            font-size: 14px !important;
        }

        .btn-pick-jenis:hover {
            background: #11998e !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3) !important;
        }

        .btn-pick-jenis.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
            color: white !important;
            border-color: #11998e !important;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4) !important;
        }

        .parameter-group {
            transition: all 0.2s ease;
        }

        .parameter-group table {
            width: 100%;
        }

        .method-row {
            transition: background-color 0.15s ease;
        }

        .method-row:hover {
            background-color: #f8f9fa;
        }

        #pagination-controls {
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
        }

        .pagination .page-link {
            color: #007bff;
            border-color: #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            cursor: not-allowed;
        }

        #showing-info {
            font-size: 0.875rem;
        }

        /* Make search input and dropdown responsive */
        @media (max-width: 768px) {
            .card-header .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            #search-parameter,
            #items-per-page {
                width: 100% !important;
                margin-bottom: 10px;
            }
        }

        /* Collapse & Auto-Sort Styles */
        .parameter-group-header {
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 15px !important;
            margin-bottom: 15px !important;
            font-weight: 600;
            color: #2d3748;
        }

        .parameter-group-header:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            border-color: #667eea !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .parameter-group-header:hover .collapse-icon {
            color: white !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
            font-size: 14px;
            margin-right: 8px;
            color: #667eea;
        }

        /* Edit parameter (pensil) — hanya saat mode edit grup aktif */
        .method-row-tab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .method-row-tab>label {
            flex: 1;
            margin-bottom: 0;
            min-width: 0;
        }

        .btn-pencil-edit-method {
            display: none !important;
            flex-shrink: 0;
            padding: 2px 8px;
            line-height: 1.2;
            text-decoration: none;
        }

        .parameter-group-item.parameter-edit-mode .btn-pencil-edit-method {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .btn-toggle-edit-parameter.active {
            background-color: #495057;
            color: #fff;
            border-color: #495057;
        }

        /* Baris harga aktif/dipilih di popup edit method */
        #mepm-stp-table .mepm-current-st-row {
            background-color: #e8f5e9 !important;
        }

        #mepm-stp-table .mepm-current-st-row td:first-child {
            border-left: 3px solid #11998e;
        }

        #mepm-stp-filter-bar {
            display: flex;
        }

        /* Checkbox Styling for Parameters */
        .method-row {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .method-row:hover {
            background: #f8f9fa;
        }

        .method-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #11998e;
        }

        .method-row label {
            cursor: pointer;
            margin-bottom: 0;
            font-size: 14px;
            color: #4a5568;
        }

        /* Bottom Action Buttons Enhancement */
        .bottom-action-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-top: 3px solid #11998e;
            padding: 20px 30px;
            box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .bottom-action-container.hidden {
            transform: translateY(100%);
        }

        .btn-simpan:disabled {
            background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%) !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        .btn-simpan:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 768px) {
            .bottom-action-container {
                flex-direction: column;
                gap: 15px;
            }

            .bottom-action-container .d-flex {
                width: 100%;
                justify-content: center;
            }

            .bottom-action-container button {
                width: 100%;
            }
        }

        .param-count {
            font-size: 13px;
            transition: all 0.2s ease;
        }

        /* Multi-Step Form Wizard */
        .form-wizard-container {
            position: relative;
            overflow: hidden;
        }

        /* Calendars mount on body (Flatpickr appendTo); keep above sticky wizard/footer UI */
        .flatpickr-calendar {
            z-index: 10050 !important;
        }

        .form-step {
            display: none;
            content-visibility: hidden;
            contain-intrinsic-size: 1px 600px;
        }

        .form-step.active {
            display: block;
            content-visibility: visible;
            contain-intrinsic-size: auto;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 15px 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 30px;
            right: -50%;
            width: 100%;
            height: 3px;
            background: #e2e8f0;
            z-index: -1;
        }

        .step-item.completed::after {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #a0aec0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .step-item.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transform: scale(1.1);
        }

        .step-item.completed .step-number {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .step-item.completed .step-number::before {
            content: '✓';
        }

        .step-title {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-top: 8px;
        }

        .step-item.active .step-title {
            color: #667eea;
        }

        .step-item.completed .step-title {
            color: #11998e;
        }

        /* Navigation Buttons */
        .step-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .btn-step {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-prev {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-prev:hover {
            background: #cbd5e0;
            transform: translateX(-3px);
        }

        .btn-next {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-next:hover {
            transform: translateX(3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-step:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-step:disabled:hover {
            transform: none;
        }

        /* Error Message in Steps */
        .step-error {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .step-error.show {
            display: block;
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .step-error i {
            color: #e53e3e;
            margin-right: 10px;
        }

        #selected-parameters-section {
            background: #f0f8ff;
            border: 2px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
        }

        #selected-parameters-section .alert {
            background: linear-gradient(135deg, #2196f3 0%, #21cbf3 100%);
            color: white;
            border: none;
            margin-bottom: 15px;
        }

        #selected-parameters-section .parameter-group {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        #selected-separator {
            border: 0;
            border-top: 3px solid #2196f3;
            margin: 30px 0;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Show More Button Styles */
        .show-more-btn {
            text-align: center;
            padding: 12px 15px;
            margin: 10px 0;
            cursor: pointer;
            color: #007bff;
            font-weight: 600;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.3s ease;
            user-select: none;
        }

        .show-more-btn:hover {
            background: linear-gradient(to bottom, #e9ecef, #dee2e6);
            color: #0056b3;
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
            transform: translateY(-1px);
        }

        .show-more-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(0, 123, 255, 0.2);
        }

        .show-more-btn i {
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .show-more-btn:hover i {
            transform: translateY(2px);
        }

        /* Cart Widget Styles */
        #parameter-cart {
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #parameter-cart .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 3px solid #5a67d8;
        }

        #parameter-cart .card-body {
            background: #fafafa;
        }

        #parameter-cart .card-footer {
            background: white;
            border-top: 2px solid #dee2e6;
        }

        .cart-item {
            background: white;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            position: relative;
        }

        .cart-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .cart-item-name {
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 4px;
            padding-right: 25px;
        }

        .cart-item-category {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 6px;
        }

        .cart-item-price {
            font-weight: 600;
            color: #28a745;
            font-size: 0.95rem;
        }

        .cart-item-remove {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            padding: 0;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-remove:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .cart-packet-badge {
            background: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 6px;
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 2px 8px;
        }

        #cart-total-price {
            animation: pulse 0.3s ease;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .badge-lg {
            padding: 6px 12px;
        }

        /* Cart Panel Styles for Review */
        .cart-panel {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .cart-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .cart-panel-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-top: 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cart-item-packet {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px solid #28a745;
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .cart-item-lab {
            color: #718096;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cart-divider {
            border: none;
            border-top: 2px dashed #cbd5e0;
            margin: 15px 0;
        }

        .cart-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-total-label {
            font-size: 16px;
            font-weight: 600;
        }

        .cart-total-price {
            font-size: 22px;
            font-weight: 700;
        }

        @media (max-width: 991px) {
            #parameter-cart {
                position: static !important;
                margin-top: 20px;
            }
        }

        /* Paket Button Styles - Enhanced Selection Visual */
        .btn-pick-paket {
            position: relative;
            padding: 10px 20px;
            font-weight: 500;
            border: 2px solid #28a745 !important;
            background-color: white;
            color: #28a745;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-pick-paket:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
            background-color: #f0fff4;
        }

        .btn-pick-paket.active {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .btn-pick-paket.active::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 8px;
            animation: checkmarkPop 0.3s ease;
        }

        @keyframes checkmarkPop {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .btn-pick-paket:active {
            transform: scale(0.95);
        }

        .btn-pick-paket:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
        }

        .packet-buttons-container {
            gap: 10px !important;
        }

        /* Searchable Dropdown (SDD) */
        .sdd-wrap { position: relative; user-select: none; }
        .sdd-display {
            display: block; width: 100%; padding: .375rem .75rem;
            font-size: 1rem; line-height: 1.5; color: #495057;
            background: #fff; border: 1px solid #ced4da; border-radius: .25rem;
            cursor: pointer; outline: none;
        }
        .sdd-display::after {
            content: '▾'; float: right; color: #888;
        }
        .sdd-placeholder { color: #aaa; }
        .sdd-wrap.sdd-open .sdd-display { border-color: #80bdff; box-shadow: 0 0 0 .2rem rgba(0,123,255,.25); }
        .sdd-panel {
            display: none; position: absolute; z-index: 9999;
            width: 100%; background: #fff;
            border: 1px solid #ced4da; border-top: none;
            border-radius: 0 0 .25rem .25rem;
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }
        .sdd-wrap.sdd-open .sdd-panel { display: block; }
        .sdd-search {
            display: block; width: 100%; padding: 6px 10px;
            border: none; border-bottom: 1px solid #e2e8f0;
            outline: none; font-size: 13px;
        }
        .sdd-list { list-style: none; margin: 0; padding: 0; max-height: 200px; overflow-y: auto; }
        .sdd-list li { padding: 7px 12px; cursor: pointer; font-size: 13px; }
        .sdd-list li:hover { background: #f0f4ff; }
        .sdd-list li[data-value=""] { color: #aaa; font-style: italic; }
    </style>
@endsection

@section('content')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-ui.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/jquery-ui.min.css') }}">



    <!-- Breadcrumb -->
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card" style="border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                <div class="card-body" style="padding: 15px 20px;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/home') }}">
                                    <i class="fa fa-home menu-icon mr-1"></i> Beranda
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ url('/elits-samples', [Request::segment(3)]) }}">Input Data Sampel</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah Sampel</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header-card-sample">
        <h2>
            <i class="fa fa-flask"></i>
            Input Data Sampel Kesmas
        </h2>
        <div class="subtitle">Tambahkan detail sampel untuk permohonan uji yang telah dibuat</div>
    </div>

    <!-- Permohonan Uji Info -->
    @if (isset($permohonan_uji))
        <div class="info-alert-custom">
            <div>
                <strong><i class="fa fa-file-alt"></i> Permohonan Uji</strong>
                @if (optional($permohonan_uji->customer)->name_customer)
                    — {{ optional($permohonan_uji->customer)->name_customer }}
                @endif
                <div style="font-size: 0.9rem; margin-top: 8px; opacity: 0.8;">
                    <i class="fa fa-info-circle"></i> Langkah 2 dari 2: Tambahkan satu atau lebih sampel untuk permohonan
                    uji di atas.
                </div>
            </div>
            <a href="{{ route('elits-permohonan-uji.edit', [$permohonan_uji->id_permohonan_uji]) }}" class="btn btn-sm"
                style="background: white; color: #00acc1; font-weight: 600; border-radius: 8px; padding: 8px 20px;">
                <i class="fa fa-arrow-left"></i> Kembali ke Permohonan Uji
            </a>
        </div>
    @endif

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; border-left: 5px solid #dc3545;">
            <strong><i class="fa fa-exclamation-triangle"></i> Terdapat kesalahan:</strong>
            <ul style="margin-top: 10px; margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('elits-samples.store', [Request::segment(3)]) }}" method="POST" id="form-create-sample"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

        @php
            $ksManual = $kesmasSampleSettings ?? \Smt\Masterweb\Models\KesmasSampleNumberSettings::getSettings();
            $ksTableOk = \Smt\Masterweb\Models\KesmasSampleNumberSettings::tableExists();
        @endphp

        @if (!$ksTableOk)
            <div class="alert alert-warning mb-4" style="border-radius: 12px; border-left: 5px solid #ffc107;">
                <strong><i class="fa fa-database"></i> Migrasi database diperlukan</strong>
                <p class="mb-0 mt-2 small">
                    Tabel <code>ms_kesmas_sample_number_settings</code> belum ada. Dari folder project Laravel, jalankan:
                    <kbd class="px-2 py-1 bg-light border rounded">php artisan migrate</kbd>
                    lalu muat ulang halaman ini. Tanpa itu, halaman tetap bisa dipakai (nomor otomatis), tetapi pengaturan
                    nomor manual Kesmas belum tersimpan.
                </p>
            </div>
        @endif

        @if ($ksManual->is_nomor_sampel_manual)
            <div class="alert alert-light border-warning mb-4" style="border-radius: 12px; border-left: 5px solid #ff9800;">
                <i class="fa fa-info-circle text-warning mr-2"></i>
                <span class="text-muted">Nomor sampel <strong>manual</strong> (Kesmas). Pengisian
                    dilakukan di <strong>langkah 3 — Review &amp; Simpan</strong>, per jenis sampel. Masukkan <strong>angka urut</strong>
                    saja; format penuh dibentuk otomatis.</span>
            </div>
        @else
            <div class="alert alert-light border mb-4" style="border-radius: 12px;">
                <i class="fa fa-cog text-secondary mr-2"></i>
                <span class="text-muted">Nomor sampel saat ini <strong>otomatis</strong>. Untuk mengisi
                    manual, buka
                    <a href="{{ route('kesmas-sample-number-settings.index') }}" class="font-weight-bold"
                        target="_blank">Setting Nomor Sampel Kesmas</a>
                    lalu aktifkan opsi yang diperlukan.</span>
            </div>
        @endif
        @if ($ksManual->is_nomor_laboratorium_manual)
            <div class="alert alert-light border-info mb-3" style="border-radius: 12px; border-left: 5px solid #17a2b8;">
                <i class="fa fa-hashtag text-info mr-2"></i>
                <span class="text-muted"><strong>Nomor laboratorium</strong> (format
                    <code>449.5/01|02/{urut}/{{ date('Y') }}</code>) ditetapkan di
                    <strong>akhir pemeriksaan / pengesahan hasil</strong> — tidak perlu diisi saat tambah sampel.
                    Kosongkan di pengesahan untuk penomoran otomatis.</span>
            </div>
        @else
            <div class="alert alert-info border mb-3" style="border-radius: 12px;">
                <i class="fa fa-hashtag mr-2"></i>
                <span><strong>Nomor laboratorium</strong> ditetapkan di akhir pemeriksaan / pengesahan hasil
                    (format <code>449.5/01|02/{urut}/{{ date('Y') }}</code>).</span>
            </div>
        @endif

        <!-- Info Box -->
        <div
            style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 5px solid #ff9800; border-radius: 12px; padding: 15px 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <i class="fa fa-lightbulb" style="font-size: 24px; color: #ff9800;"></i>
            <span style="color: #424242; font-size: 16px;">Anda dapat menambahkan lebih dari satu sampel untuk permohonan
                ini</span>
        </div>

        <!-- Kode Sampel Section (Below Alert) -->
        <div class="row mb-4" id="sample-codes-container-top" style="margin-left: -5px; margin-right: -5px; display: none;">
            <!-- Dynamic Sample Code Cards (for multiple sample types) -->
            <div id="dynamic-sample-codes-container" style="display: none; width: 100%;">
                <!-- Will be generated dynamically by JavaScript -->
            </div>

            <!-- Legacy Single Sample Code Cards (for backward compatibility) -->
            <div class="col-lg-6" id="code_sample_kimia_wrapper_top" style="display: none;">
                <div class="card"
                    style="border: 2px solid #11998e; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.15);">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 15px 20px;">
                        <h6 class="mb-0" style="color: white; font-weight: 600;">
                            <i class="fa fa-flask"></i> Kode Sampel Kimia
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-lg" name="code_sample_kimia"
                                id="input_code_sample_kimia" data-type="code_sample" data-idlabs="{{ $lab_keys['kimia'] }}"
                                placeholder="Masukkan kode sampel kimia" value="{{ $code_samples['kimia'] ?? '' }}"
                                style="border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; letter-spacing: 1px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" id="code_sample_mikro_wrapper_top" style="display: none;">
                <div class="card"
                    style="border: 2px solid #667eea; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 20px;">
                        <h6 class="mb-0" style="color: white; font-weight: 600;">
                            <i class="fa fa-microscope"></i> Kode Sampel Mikrobiologi
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-lg" name="code_sample_mikro"
                                id="input_code_sample_mikro" data-type="code_sample"
                                data-idlabs="{{ $lab_keys['mikrobiologi'] }}" placeholder="Masukkan kode sampel mikro"
                                value="{{ $code_samples['mikrobiologi'] ?? '' }}"
                                style="border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; letter-spacing: 1px;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden: nomor lab manual (diisi dari Review & Simpan) --}}
            <input type="hidden" name="manual_nomer_lab_kimia" id="manual_nomer_lab_kimia" value="">
            <input type="hidden" name="manual_nomer_lab_mikro" id="manual_nomer_lab_mikro" value="">

            <div class="col-lg-4" hidden>
                <div class="form-group">
                    <label for="name_pelanggan"> Nama Pelanggan:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="name_pelanggan" id="name_pelanggan"
                            data-type="name_pelanggan" placeholder="Nama Pelanggan"
                            value="{{ old('name_pelanggan') ?? $permohonan_uji->customer->name_customer }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-item active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Detail Sampel</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Jenis & Parameter</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title">Review & Simpan</div>
            </div>
        </div>

        <!-- Form Wizard Container -->
        <div class="form-wizard-container">

            <!-- STEP 1: Detail Sampel -->
            <div class="form-step active" data-step="1">
                <div class="step-error" id="step1-error">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span id="step1-error-message"></span>
                </div>

                <!-- Detail Sampel Section -->
                <div class="form-section-card-sample">
                    <div class="section-title-sample">
                        <i class="fa fa-vial"></i>
                        Detail Sampel
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            @php
                                $fpDatesampling = null;
                                $fpDateSending = null;
                                $fpDateSendingStop = null;
                                if (old('datesampling_samples')) {
                                    try {
                                        $fpDatesampling = \Carbon\Carbon::createFromFormat('d/m/Y H:i', old('datesampling_samples'))->format('d/m/Y H:i');
                                    } catch (\Throwable $e) {
                                        try {
                                            $fpDatesampling = \Carbon\Carbon::parse(old('datesampling_samples'))->format('d/m/Y H:i');
                                        } catch (\Throwable $e2) {
                                        }
                                    }
                                }
                                if (old('date_sending')) {
                                    try {
                                        $fpDateSending = \Carbon\Carbon::createFromFormat('d/m/Y H:i', old('date_sending'))->format('d/m/Y H:i');
                                    } catch (\Throwable $e) {
                                        try {
                                            $fpDateSending = \Carbon\Carbon::parse(old('date_sending'))->format('d/m/Y H:i');
                                        } catch (\Throwable $e2) {
                                        }
                                    }
                                }
                                if (old('date_sending_stop')) {
                                    try {
                                        $fpDateSendingStop = \Carbon\Carbon::createFromFormat('d/m/Y H:i', old('date_sending_stop'))->format('d/m/Y H:i');
                                    } catch (\Throwable $e) {
                                        try {
                                            $fpDateSendingStop = \Carbon\Carbon::parse(old('date_sending_stop'))->format('d/m/Y H:i');
                                        } catch (\Throwable $e2) {
                                        }
                                    }
                                }
                                if ($fpDateSending && !$fpDateSendingStop) {
                                    try {
                                        $fpDateSendingStop = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $fpDateSending)->addMinutes(10)->format('d/m/Y H:i');
                                    } catch (\Throwable $e) {
                                    }
                                }
                            @endphp

                            <div class="col-lg-12">
                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="datesampling_samples">Tanggal Pengambilan</label>

                                            <input id="datesampling_samples" class="form-control"
                                                name="datesampling_samples" placeholder="--/--/--- --:--"
                                                type="text" autocomplete="off" />

                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="date_sending">Tanggal Pengiriman</label>
                                            <input id="date_sending" class="form-control" name="date_sending"
                                                placeholder="--/--/--- --:--" type="text" autocomplete="off" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6" hidden>
                                        <div class="form-group">
                                            <label for="date_sending_stop">Tanggal Selesai Pengiriman</label>
                                            <input id="date_sending_stop" class="form-control" name="date_sending_stop"
                                                placeholder="--/--/--- --:--" type="text" autocomplete="off" />
                                        </div>
                                    </div>
                                    <script>
                                        window.sampleFpDefaults = {
                                            datesampling: {!! json_encode($fpDatesampling) !!},
                                            dateSending: {!! json_encode($fpDateSending) !!},
                                            dateSendingStop: {!! json_encode($fpDateSendingStop) !!}
                                        };
                                    </script>


                                </div>
                            </div>

                            {{-- <div class="col-lg-12"> --}}
                            {{-- <div class="form-group"> --}}
                            {{-- <label for="lokasi_pengambilan">Objek (Lokasi, Makanan, Minuman, Alat Makan, dll) --}}
                            {{-- Pengambilan:</label> --}}
                            {{-- <div class="input-group date"> --}}
                            {{-- <input type="text" class="form-control" name="lokasi_pengambilan" --}} {{-- id="lokasi_pengambilan"
                  placeholder="Lokasi Pengambilan" --}} {{-- value="{{ old('lokasi_pengambilan') }}"> --}}
                            {{-- </div> --}}
                            {{-- </div> --}}
                            {{-- </div> --}}

                        </li>
                    </ul>
                </div>
                <!-- End Detail Sampel Section -->

                <!-- Penerimaan Sampel Section -->
                <div class="form-section-card-sample mt-4">
                    <div class="section-title-sample">
                        <i class="fa fa-clipboard-check"></i>
                        Penerimaan Sampel <span style="color: #e53e3e; margin-left: 5px;">*</span>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="tempat_kemasan" style="font-size: 15px; color: #2d3748;">
                                <i class="fa fa-box" style="color: #11998e; margin-right: 8px;"></i>
                                <b>1. Tempat / Kemasan</b>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_tempat_kemasan" type="radio"
                                    value="layak" id="tempat_kemasan_layak" checked>
                                <label class="form-check-label" for="tempat_kemasan_layak">
                                    Layak
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_tempat_kemasan" type="radio"
                                    value="tidak layak" id="tempat_kemasan_tidak_layak">
                                <label class="form-check-label" for="tempat_kemasan_tidak_layak">
                                    Tidak Layak
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="berat_vol" style="font-size: 15px; color: #2d3748;">
                                <i class="fa fa-weight" style="color: #11998e; margin-right: 8px;"></i>
                                <b>2. Berat / Vol</b>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_berat_vol" type="radio" value="layak"
                                    id="berat_vol_layak" checked>
                                <label class="form-check-label" for="berat_vol_layak">
                                    Layak
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_berat_vol" type="radio"
                                    value="tidak layak" id="berat_vol_tidak_layak">
                                <label class="form-check-label" for="berat_vol_tidak_layak">
                                    Tidak Layak
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Penerimaan Sampel Section -->

                <!-- Biaya Sampling Section -->
                <div class="form-section-card-sample mt-4">
                    <div class="section-title-sample">
                        <i class="fa fa-money-bill-wave"></i>
                        Biaya Sampling
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="cost_sampling" style="font-size: 16px; color: #4a5568;">
                                <i class="fa fa-vial" style="color: #11998e; margin-right: 8px;"></i>
                                Biaya Sampling (Opsional)
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"
                                        style="background: #edf2f7; border-right: none; color: #4a5568; font-weight: 600;">Rp</span>
                                </div>
                                <input type="number" class="form-control" name="cost_sampling" id="cost_sampling"
                                    value="20000" placeholder="20000"
                                    style="border-left: none; font-weight: 600; color: #2d3748;">
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fa fa-info-circle"></i> Default: Rp 20.000 per jenis sampel. Biaya akan dikalikan dengan jumlah jenis sampel yang dipilih.
                            </small>
                        </div>
                    </div>
                </div>
                <!-- End Biaya Sampling Section -->

                <!-- Catatan Sampel Section -->
                <div class="form-section-card-sample mt-4">
                    <div class="section-title-sample">
                        <i class="fa fa-sticky-note"></i>
                        Catatan Sampel
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleFormControlTextarea1" style="font-size: 16px; color: #4a5568;">
                                <i class="fa fa-edit" style="color: #11998e; margin-right: 8px;"></i>
                                Tambahkan catatan atau informasi tambahan mengenai sampel
                            </label>
                            <textarea class="form-control" name="note" id="exampleFormControlTextarea1" rows="6"
                                placeholder="Masukkan catatan sampel di sini...">{{ old('note') ?? '-' }}</textarea>
                        </div>
                    </div>
                </div>
                <!-- End Catatan Sampel Section -->

                <!-- Step Navigation -->
                <div class="step-navigation">
                    <div></div>
                    <button type="button" class="btn-step btn-next" onclick="nextStep(1)">
                        Selanjutnya <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            <!-- END STEP 1 -->

            <!-- STEP 2: Jenis & Parameter -->
            <div class="form-step" data-step="2">
                <div class="step-error" id="step2-error">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span id="step2-error-message"></span>
                </div>

                <div class="col-lg-12 mt-2 mb-4">
                    <!-- Jenis Sampel Section - Multiple Selection -->
                    <div class="form-section-card-sample">
                        <div class="section-title-sample">
                            <i class="fa fa-list-alt"></i>
                            Pilih Jenis Sampel <span style="color: #e53e3e; margin-left: 5px;">*</span>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <small class="form-text text-muted mb-3">
                                    <i class="fa fa-info-circle"></i> Anda dapat memilih <strong>lebih dari 1 jenis
                                        sampel</strong>.
                                    Setiap jenis sampel akan memiliki paket & parameter tersendiri.
                                </small>
                                <div class="row" style="margin-top: 15px;">
                                    @foreach ($sampletypes as $sampletype)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <button type="button" class="btn btn-pick-jenis w-100"
                                                data-id="{{ $sampletype->id_sample_type }}"
                                                data-code="{{ $sampletype->code_sample_type }}"
                                                data-name="{{ $sampletype->name_sample_type }}"
                                                style="text-align: left; padding: 15px; height: auto; min-height: 60px; position: relative; border: 2px solid #e2e8f0; background: white; color: #2d3748; border-radius: 8px; transition: all 0.3s;">
                                                <span class="jenis-check-icon"
                                                    style="position: absolute; top: 8px; right: 8px; display: none;">
                                                    <i class="fa fa-check-circle"
                                                        style="font-size: 20px; color: #4caf50;"></i>
                                                </span>
                                                <strong>{{ $sampletype->code_sample_type }}</strong><br>
                                                <small>{{ $sampletype->name_sample_type }}</small>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" id="jenis_sampel" name="jenis_sampel" value="">

                                <!-- Display Selected Sample Types with Badge -->
                                <div id="selected-sampletypes-container" style="display: none; margin-top: 20px;">
                                    <div class="alert"
                                        style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4caf50; border-radius: 10px;">
                                        <strong><i class="fa fa-check-circle text-success"></i> Jenis Sampel
                                            Terpilih:</strong>
                                        <div id="selected-sampletypes-badges"
                                            style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Jenis Sampel Section -->

                <!-- Paket & Parameter Selection - Dynamic Tabs per Sample Type -->
                <div class="form-section-card-sample" id="paket-parameter-section" style="display: none;">
                    <div class="section-title-sample">
                        <i class="fa fa-list-check"></i>
                        Paket & Parameter Pengujian
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Informasi:</strong> Setiap jenis sampel dapat memilih paket atau parameter yang berbeda.
                    </div>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="sampleTypeTabs" role="tablist" style="margin-bottom: 20px;">
                        <!-- Tabs will be generated dynamically -->
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="sampleTypeTabsContent">
                        <!-- Tab panels will be generated dynamically -->
                    </div>
                    <!-- End Paket & Parameter Section -->

                    <!-- Legacy single sample type section (hidden, kept for backward compatibility) -->
                    <div style="display: none;">
                        <div class="is_rectal_swab" style="display: none">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Jenis Kelamin</label>

                                    <div class="row">
                                        <div class="col-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="gender_samples"
                                                        id="gender_samples_1" value="L">
                                                    Laki-Laki
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="gender_samples"
                                                        id="gender_samples_2" value="P">
                                                    Perempuan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="umur_samples">Umur</label>

                                    <input type="number" class="form-control" name="umur_samples" id="umur_samples"
                                        placeholder="Umur..">
                                </div>
                            </div>
                        </div>

                        <div class="is_paket" style="display: none">
                            <input type="hidden" id="is_paket" name="is_paket" value="false">
                        </div>

                        <div class="packet" style="display: none;">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="mb-2">Pilih Paket</label>
                                    <div class="d-flex flex-wrap packet-buttons-container" style="gap: 8px">
                                        <!-- Paket buttons will be dynamically loaded here based on jenis sampel -->
                                    </div>
                                    <select id="packet" name="packet[]" class="d-none" multiple>
                                        <!-- Options will be dynamically loaded -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Jenis sample --}}
                        <div id="jenis_sample_uji_usap" style="display: none">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <select class="form-control" name="jenis_sample_uji_usap">
                                        <option value="Alat Masak">Alat Masak</option>
                                        <option value="Alat Makan">Alat Makan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="container method">
                                    <div class="row">
                                        <!-- Left Column: Parameter List -->
                                        <div class="col-lg-8">
                                            <div class="form-section-card-sample" style="padding: 0; overflow: hidden;">
                                                <div class="card-header d-flex justify-content-between align-items-center"
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; margin: 0;">
                                                    <h5 class="mb-0" style="color: white; font-weight: 600;">
                                                        <i class="fa fa-microscope"></i> Parameter Pengujian
                                                    </h5>
                                                    <div class="d-flex align-items-center" style="gap: 10px;">
                                                        <input type="text" id="search-parameter" class="form-control"
                                                            placeholder="🔍 Cari parameter..."
                                                            style="width: 250px; background: white; border: 2px solid #e2e8f0; padding: 8px 15px;">
                                                        <button type="button" class="btn btn-sm" id="expand-all-params"
                                                            style="background: white; color: #667eea; border: 2px solid white; font-weight: 600; padding: 8px 15px;">
                                                            <i class="fas fa-expand-alt"></i> Expand
                                                        </button>
                                                        <button type="button" class="btn btn-sm"
                                                            id="collapse-all-params"
                                                            style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; font-weight: 600; padding: 8px 15px;">
                                                            <i class="fas fa-compress-alt"></i> Collapse
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <!-- Selected Parameters Section (Auto-sorted ke atas) -->
                                                    <div id="selected-parameters-section" class="mb-4"
                                                        style="display: none;">
                                                        <div class="alert alert-info">
                                                            <h5><i class="fas fa-check-circle"></i> Parameter Terpilih</h5>
                                                            <small>Parameter yang sudah dicentang dari paket akan muncul di
                                                                sini</small>
                                                        </div>
                                                        <div class="row" id="selected-parameters-container"></div>
                                                    </div>

                                                    <hr id="selected-separator" style="display: none;">

                                                    <!-- All Parameters Section -->
                                                    <div class="row" id="parameters-container">
                                                        @php
                                                            $char = 'A';
                                                        @endphp

                                                        @for ($i = 0; $i < count($data_methods); $i++)
                                                            @if ($i % 2 == 0 && $i != 0)
                                                                <div class="col-6 parameter-group"
                                                                    data-category="{{ $data_methods[$i]->name }}">
                                                                    <div class="parameter-group-header"
                                                                        data-toggle="collapse"
                                                                        data-target="#param-collapse-{{ $i }}"
                                                                        style="cursor: pointer; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                                                        <h5 class="mb-0">
                                                                            <i
                                                                                class="fas fa-chevron-down collapse-icon"></i>
                                                                            {{ $char }}. Parameter
                                                                            {{ $data_methods[$i]->name }}
                                                                            <span
                                                                                class="badge badge-primary ml-2 param-count">0</span>
                                                                        </h5>
                                                                        @php
                                                                            $char++;
                                                                        @endphp
                                                                    </div>
                                                                    <div class="collapse"
                                                                        id="param-collapse-{{ $i }}">
                                                                        <table>
                                                                            @foreach ($data_methods[$i]->method as $method)
                                                                                <tr class="method-row method-row-{{ $method->id_method }}"
                                                                                    data-method-name="{{ strtolower($method->name_method) }}"
                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}">
                                                                                    <td>
                                                                                        <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input name="method[]"
                                                                                                    class="form-check-input checkbox checkbox-{{ $method->id_method }}"
                                                                                                    data-default-price="{{ $method->price_method }}"
                                                                                                    data-prices-by-sample-type='@json($method->prices_by_sample_type ?? [])'
                                                                                                    data-price="{{ $method->price_method }}"
                                                                                                    data-idlabs="{{ $data_methods[$i]->id_lab }}"
                                                                                                    data-idmethod="{{ $method->id_method }}"
                                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}"
                                                                                                    type="checkbox"
                                                                                                    value="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}_{{ $method->price_method }}"
                                                                                                    id="defaultCheck3"
                                                                                                    disabled>
                                                                                                <label
                                                                                                    class="form-check-label"
                                                                                                    for="defaultCheck3">
                                                                                                    {{ $method->name_method }}
                                                                                                    <span
                                                                                                        class="text-muted method-param-price">(Rp
                                                                                                        {{ number_format($method->price_method, 0, ',', '.') }})</span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td style="display: none;">
                                                                                        <div class="form-group">
                                                                                            <input style="width: 150px"
                                                                                                type="text"
                                                                                                class="form-control"
                                                                                                id="input_price_method_{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}"
                                                                                                value="{{ $method->price_method }}"
                                                                                                placeholder="Harga">
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="col-6 parameter-group"
                                                                    data-category="{{ $data_methods[$i]->name }}">
                                                                    <div class="parameter-group-header"
                                                                        data-toggle="collapse"
                                                                        data-target="#param-collapse-{{ $i }}"
                                                                        style="cursor: pointer; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                                                        <h5 class="mb-0">
                                                                            <i
                                                                                class="fas fa-chevron-down collapse-icon"></i>
                                                                            {{ $char }}. Parameter
                                                                            {{ $data_methods[$i]->name }}
                                                                            <span
                                                                                class="badge badge-primary ml-2 param-count">0</span>
                                                                        </h5>
                                                                        @php
                                                                            $char++;
                                                                        @endphp
                                                                    </div>
                                                                    <div class="collapse"
                                                                        id="param-collapse-{{ $i }}">
                                                                        <table>
                                                                            @foreach ($data_methods[$i]->method as $method)
                                                                                <tr class="method-row method-row-{{ $method->id_method }}"
                                                                                    data-method-name="{{ strtolower($method->name_method) }}"
                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}">
                                                                                    <td>
                                                                                        <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input name="method[]"
                                                                                                    class="form-check-input checkbox checkbox-{{ $method->id_method }}"
                                                                                                    data-default-price="{{ $method->price_method }}"
                                                                                                    data-prices-by-sample-type='@json($method->prices_by_sample_type ?? [])'
                                                                                                    data-price="{{ $method->price_method }}"
                                                                                                    data-idlabs="{{ $data_methods[$i]->id_lab }}"
                                                                                                    data-idmethod="{{ $method->id_method }}"
                                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}"
                                                                                                    type="checkbox"
                                                                                                    value="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}_{{ $method->price_method }}"
                                                                                                    id="defaultCheck3"
                                                                                                    disabled>
                                                                                                <label
                                                                                                    class="form-check-label"
                                                                                                    for="defaultCheck3">
                                                                                                    {{ $method->name_method }}
                                                                                                    <span
                                                                                                        class="text-muted method-param-price">(Rp
                                                                                                        {{ number_format($method->price_method, 0, ',', '.') }})</span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td style="display: none;">
                                                                                        <div class="form-group">
                                                                                            <input style="width: 150px"
                                                                                                type="text"
                                                                                                class="form-control"
                                                                                                id="input_price_method_{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}"
                                                                                                value="{{ $method->price_method }}"
                                                                                                placeholder="Harga">
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endfor
                                                    </div>

                                                    <!-- Pagination Controls -->
                                                    <div class="d-flex justify-content-between align-items-center mt-4"
                                                        id="pagination-controls">
                                                        <div id="showing-info" class="text-muted"></div>
                                                        <nav>
                                                            <ul class="pagination mb-0" id="pagination">
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column: Floating Cart Widget -->
                                        <div class="col-lg-4">
                                            <div class="form-section-card-sample" id="parameter-cart"
                                                style="position: sticky; top: 20px; padding: 0; overflow: hidden;">
                                                <div class="card-header"
                                                    style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px; margin: 0; border: none;">
                                                    <h5 class="mb-0" style="color: white; font-weight: 600;">
                                                        <i class="fas fa-shopping-cart"></i> Parameter Terpilih
                                                    </h5>
                                                </div>
                                                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                                    <!-- Packet Info (if from packet) -->
                                                    <div id="cart-packet-info" style="display: none;"
                                                        class="alert alert-info mb-3">
                                                        <strong><i class="fas fa-box"></i> Paket:</strong>
                                                        <div id="cart-packet-name"></div>
                                                    </div>

                                                    <!-- Cart Items List -->
                                                    <div id="cart-items-list">
                                                        <div class="text-center text-muted py-5" id="cart-empty-state">
                                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                                            <p>Belum ada parameter dipilih</p>
                                                            <small>Centang parameter untuk menambahkan</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer"
                                                    style="background: #f8f9fa; padding: 20px; border-top: 2px solid #e2e8f0;">
                                                    <div class="d-flex justify-content-between align-items-center mb-3"
                                                        style="background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                                        <strong style="color: #4a5568;">Total Parameter:</strong>
                                                        <span class="badge badge-lg"
                                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 15px; font-size: 14px; border-radius: 8px;"
                                                            id="cart-total-items">0</span>
                                                    </div>

                                                    <!-- Price Breakdown -->
                                                    <div id="cart-price-breakdown" style="display: none;" class="mb-3">
                                                        <!-- Breakdown will be inserted here by JS -->
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center mb-3"
                                                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);">
                                                        <strong style="font-size: 1.1rem; color: white;">Total
                                                            Harga:</strong>
                                                        <span style="font-size: 1.4rem; font-weight: bold; color: white;"
                                                            id="cart-total-price">Rp 0</span>
                                                    </div>
                                                    <button type="button" class="btn btn-block"
                                                        style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; padding: 12px; font-weight: 600; border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3); transition: all 0.3s;"
                                                        id="cart-clear-all"
                                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(229, 62, 62, 0.4)'"
                                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(229, 62, 62, 0.3)'">
                                                        <i class="fas fa-trash"></i> Hapus Semua Parameter
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Right Column -->


                                    </div>
                                    <!-- End Row -->
                                </div>
                                <!-- End container method -->
                            </div>
                            <!-- End form-group parameter -->
                        </div>

                        <!-- Jenis Makanan — di bawah titik pengambilan -->
                        <div class="form-section-card-sample mt-3" id="form_jenis_makanan" style="display: none;">
                            <div class="section-title-sample" style="font-size:14px; padding:12px 16px;">
                                <i class="fa fa-utensils mr-1"></i> Jenis Makanan
                            </div>
                            <div style="padding: 16px;">
                                <input type="text" class="form-control" name="jenis_makanan_minuman"
                                    id="jenis_makanan_minuman" placeholder="Jenis makanan">
                            </div>
                        </div>

                        <!-- Step Navigation -->

                    </div>
                    <div class="step-navigation">
                        <button type="button" class="btn-step btn-prev" onclick="prevStep(2)">
                            <i class="fa fa-arrow-left"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn-step btn-next" id="btn-next-step-2" onclick="nextStep(2)"
                            style="display: none;">
                            Selanjutnya <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- END STEP 2 -->



            <!-- STEP 3: Review & Simpan -->
            <div class="form-step" data-step="3">
                <div class="form-section-card-sample">
                    <div class="section-title-sample">
                        <i class="fa fa-check-circle"></i>
                        Review Data Sampel
                    </div>
                    <div class="col-lg-12">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Periksa kembali data yang telah diisi sebelum menyimpan.</strong>
                        </div>
                        <div id="review-content" style="padding: 20px;">
                            <!-- Review content will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Step Navigation -->
                <div class="step-navigation">
                    <button type="button" class="btn-step btn-prev" onclick="prevStep(3)">
                        <i class="fa fa-arrow-left"></i> Sebelumnya
                    </button>
                    <button type="submit" id="submitAll" class="btn-step btn-next btn-simpan">
                        <i class="fa fa-save"></i> Simpan Sampel
                    </button>
                </div>
            </div>
            <!-- END STEP 3 -->

            <div class="col-lg-12" hidden>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="code_sample_customer"> Kode Sampel Pelanggan:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="code_sample_customer" value="-"
                                    id="code_sample_customer" placeholder="Kode Sampel Pelanggan"
                                    value="{{ old('code_sample_customer') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12" hidden>
                <div class="form-group">
                    <label for="customer_samples"><span style="color: red">*</span>Program:</label>
                    <select id="program_samples" name="program_samples"
                        class="js-customer-basic-multiple js-states form-control" style="width: 100%" required>
                        <option value="" disabled selected> Pilih Program</option>
                        <option value="{{ $programs[0]->id_program }}" selected>
                            {{ $programs[0]->name_program }}</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id_program }}">{{ $program->name_program }}</option>
                        @endforeach
                    </select>
                </div>
            </div>







            <!-- End col-lg-12 parameter section -->


            <div class="col-lg-12" hidden>
                <div class="form-group">
                    <label for="cost_samples"><strong>Harga Total</strong></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp.</span>
                        </div>
                        <input type="number" class="form-control form-control-lg" id="cost_samples" name="cost_samples"
                            value="0" placeholder="Harga Total" readonly required style="font-weight: 600;">
                    </div>
                </div>
            </div>
            <!-- End col-lg-12 hidden section -->

        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function deferCreatePageInit(callback) {
            if (window.requestIdleCallback) {
                requestIdleCallback(callback, { timeout: 2500 });
            } else {
                setTimeout(callback, 400);
            }
        }

        var sampleFlatpickrInitialized = false;

        function waitForFlatpickr(callback, maxAttempts) {
            maxAttempts = maxAttempts || 50;
            if (typeof flatpickr !== 'undefined') {
                callback();
                return;
            }
            if (maxAttempts <= 0) {
                return;
            }
            setTimeout(function() {
                waitForFlatpickr(callback, maxAttempts - 1);
            }, 100);
        }

        function initializeSampleFlatpickr() {
            if (sampleFlatpickrInitialized || !document.getElementById('datesampling_samples')) {
                return;
            }

            waitForFlatpickr(function() {
                if (sampleFlatpickrInitialized) {
                    return;
                }
                sampleFlatpickrInitialized = true;

                var defaults = window.sampleFpDefaults || {};
                var fpBase = {
                    enableTime: true,
                    allowInput: true,
                    locale: 'id',
                    dateFormat: 'd/m/Y H:i',
                    time_24hr: true,
                    appendTo: document.body
                };

                function addTenMinutes(d) {
                    return new Date(d.getTime() + 10 * 60000);
                }

                function parseDefaultDate(value) {
                    if (!value) {
                        return null;
                    }
                    if (typeof moment !== 'undefined') {
                        var parsed = moment(value, 'DD/MM/YYYY HH:mm');
                        if (parsed.isValid()) {
                            return parsed.toDate();
                        }
                    }
                    return value;
                }

                flatpickr('#datesampling_samples', Object.assign({}, fpBase, {
                    defaultDate: defaults.datesampling || new Date()
                }));

                var defaultStopDate = (function() {
                    var fromStop = parseDefaultDate(defaults.dateSendingStop);
                    if (fromStop) {
                        return fromStop;
                    }
                    var fromSending = parseDefaultDate(defaults.dateSending);
                    if (fromSending) {
                        return addTenMinutes(fromSending instanceof Date ? fromSending : new Date(fromSending));
                    }
                    return addTenMinutes(new Date());
                })();

                var fpStop = flatpickr('#date_sending_stop', Object.assign({}, fpBase, {
                    defaultDate: defaultStopDate
                }));

                flatpickr('#date_sending', Object.assign({}, fpBase, {
                    defaultDate: defaults.dateSending || new Date(),
                    onChange: function(selectedDates) {
                        if (selectedDates.length) {
                            fpStop.setDate(addTenMinutes(selectedDates[0]), false);
                        }
                    },
                    onClose: function(selectedDates) {
                        if (selectedDates.length) {
                            fpStop.setDate(addTenMinutes(selectedDates[0]), false);
                        }
                    }
                }));
            });
        }

        var sampleDatepickersInitialized = false;

        function initializeSampleDatepickers() {
            if (sampleDatepickersInitialized || typeof $.fn.datepicker === 'undefined') {
                return;
            }
            sampleDatepickersInitialized = true;

            if ($('.datepicker').length) {
                $('.datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('update', new Date());
            }

            if ($('.datelab_samples').length) {
                $('.datelab_samples').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('update', new Date());
            }

            if ($('.datesampling_samples').length) {
                $('.datesampling_samples').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('update', new Date());
            }
        }

        // Multi-Step Wizard Functions
        let currentStep = 1;
        const totalSteps = 3;
        window.kesmasIsNomorSampelManual = @json((bool) optional($kesmasSampleSettings ?? null)->is_nomor_sampel_manual);
        window.kesmasIsNomorLabManual = @json((bool) optional($kesmasSampleSettings ?? null)->is_nomor_laboratorium_manual);
        window.kesmasNextSampleNumber = @json((int) ($kesmasNextSampleNumber ?? 1));
        window.kesmasNextLabNumber = @json((int) ($kesmasNextLabNumber ?? ($kesmasNextSampleNumber ?? 1)));
        @php
            $__kesmasLabIdSuffix = [];
            foreach ($lab_keys ?? [] as $ln => $lid) {
                $__kesmasLabIdSuffix[$lid] = $ln === 'kimia' ? '01' : '02';
            }
        @endphp
        window.kesmasLabIdToSuffix = @json($__kesmasLabIdSuffix);

        function kesmasCurrentYear() {
            return new Date().getFullYear();
        }

        function kesmasComposeSampleCode(typeCode, labCodeStr, digits, year) {
            var d = String(digits || '').replace(/\D/g, '');
            if (!d) {
                return '';
            }
            var padded = d.length < 4 ? d.padStart(4, '0') : d;
            var y = year != null ? year : kesmasCurrentYear();
            return String(typeCode || '') + '.' + String(labCodeStr || '') + '/' + padded + '/' + String(y);
        }

        /**
         * Format manual spesimen Kesmas: prefix per lab (Kimia 01, Mikro 02), bukan 03 tunggal.
         * Disimpan sebagai {01|02}/urut/tahun agar konsisten dengan pemisahan laboratorium.
         */
        function kesmasComposeKlinikSpecimen(digits, year, labSeg) {
            var d = String(digits || '').replace(/\D/g, '');
            if (!d) {
                return '';
            }
            var padded = d.length < 4 ? d.padStart(4, '0') : d;
            var seg = String(labSeg || '01').replace(/\D/g, '');
            if (!seg) {
                seg = '01';
            }
            var y = year != null ? year : kesmasCurrentYear();
            return seg + '/' + padded + '/' + String(y);
        }

        /** Pratinjau nomor lab manual: 449.5/{01|02}/urut/tahun. */
        function kesmasComposeKlinikLabFull(digits, year, labSeg) {
            var d = String(digits || '').replace(/\D/g, '');
            if (!d) {
                return '';
            }
            var seg = String(labSeg || '01').replace(/\D/g, '');
            if (!seg) {
                seg = '01';
            }
            var y = year != null ? year : kesmasCurrentYear();
            return '449.5/' + seg + '/' + d + '/' + String(y);
        }

        function kesmasParseMiddleDigits(fullCode) {
            var parts = String(fullCode || '').split('/');
            if (parts.length >= 2) {
                return String(parts[1]).replace(/\D/g, '');
            }
            return '';
        }

        /**
         * Deteksi Kimia/Mikro untuk langkah review nomor manual.
         * Legacy single: pakai visibilitas kartu kode sampel atau checkbox parameter.
         * Multi jenis/paket: kartu legacy disembunyikan — baca sampleTypeConfigs & checkbox tab.
         */
        function kesmasGetLabsUsedForReview() {
            var useKimia = false,
                useMikro = false;
            var map = window.kesmasLabIdToSuffix || {};

            function noteLabFromMethodString(ms) {
                var parts = String(ms || '').split('_');
                if (parts.length < 2) {
                    return;
                }
                var suf = map[parts[1]];
                if (suf === '01') {
                    useKimia = true;
                }
                if (suf === '02') {
                    useMikro = true;
                }
            }

            if (window.selectedSampleTypes && window.selectedSampleTypes.length > 0) {
                window.selectedSampleTypes.forEach(function(type) {
                    var cfg = window.sampleTypeConfigs[type.id] || {};
                    (cfg.packets || []).forEach(function(p) {
                        (p.methods || []).forEach(noteLabFromMethodString);
                    });
                    (cfg.additional_methods || []).forEach(function(m) {
                        noteLabFromMethodString(m.method_string || m.method);
                    });
                });
                if (!useKimia && !useMikro) {
                    $('.method-checkbox-tab:checked').each(function() {
                        noteLabFromMethodString($(this).attr('data-method'));
                    });
                }
                return { useKimia: useKimia, useMikro: useMikro };
            }

            var kw = document.getElementById('code_sample_kimia_wrapper_top');
            var mw = document.getElementById('code_sample_mikro_wrapper_top');
            if (kw && kw.style.display !== 'none') {
                useKimia = true;
            }
            if (mw && mw.style.display !== 'none') {
                useMikro = true;
            }
            if (useKimia || useMikro) {
                return { useKimia: useKimia, useMikro: useMikro };
            }

            $('.checkbox:checked').each(function() {
                var $group = $(this).closest('.method-row').closest('.parameter-group');
                var title = ($group.find('.parameter-group-header h5').text() || '').toLowerCase();
                if (title.includes('kimia')) {
                    useKimia = true;
                }
                if (title.includes('mikro')) {
                    useMikro = true;
                }
            });
            return { useKimia: useKimia, useMikro: useMikro };
        }

        function bindKesmasDigitInput(digitInput, hiddenFullCodeEl, typeCode, labCodeStr) {
            if (!digitInput || !hiddenFullCodeEl) {
                return;
            }
            var run = function() {
                hiddenFullCodeEl.value = kesmasComposeSampleCode(typeCode, labCodeStr, digitInput.value,
                    kesmasCurrentYear());
            };
            digitInput.addEventListener('input', function() {
                this.value = String(this.value || '').replace(/\D/g, '');
                run();
            });
            run();
        }

        /** Input urut spesimen → hidden kode sampel dengan format {01|02}/urut/tahun (per lab). */
        function bindKesmasKlinikSpecimenInput(digitInput, hiddenFullCodeEls) {
            if (!digitInput || !hiddenFullCodeEls || !hiddenFullCodeEls.length) {
                return;
            }
            var labSeg = digitInput.getAttribute('data-specimen-lab-seg') || '01';
            var run = function() {
                var d = String(digitInput.value || '').replace(/\D/g, '');
                digitInput.value = d;
                var full = kesmasComposeKlinikSpecimen(d, kesmasCurrentYear(), labSeg);
                hiddenFullCodeEls.forEach(function(el) {
                    if (el) {
                        el.value = full;
                    }
                });
            };
            digitInput.addEventListener('input', run);
            run();
        }

        /** Input urut nomor lab (tampilan klinik); opsional sinkron ke hidden terpisah (mode legacy tunggal). */
        function bindKesmasKlinikLabUrutInput(digitInput, hiddenUrutEl) {
            if (!digitInput) {
                return;
            }
            var run = function() {
                var d = String(digitInput.value || '').replace(/\D/g, '');
                digitInput.value = d;
                if (hiddenUrutEl && hiddenUrutEl !== digitInput) {
                    hiddenUrutEl.value = d;
                }
                var seg = digitInput.getAttribute('data-lab-seg') || '01';
                var rid = digitInput.id || '';
                var preview = document.querySelector('.kesmas-lab-preview-text[data-for="' + rid + '"]');
                if (preview) {
                    preview.textContent = kesmasComposeKlinikLabFull(d, kesmasCurrentYear(), seg) || ('449.5/' + seg + '/—/' + kesmasCurrentYear());
                }
            };
            digitInput.addEventListener('input', run);
            run();
        }

        function kesmasAutoSamplePreviewHtml(typeCode, labSuffix, labLabel, sequenceNumber) {
            var yk = kesmasCurrentYear();
            var tc = String(typeCode || 'AM').replace(/</g, '');
            var seg = String(labSuffix || '01').replace(/\D/g, '') || '01';
            var n = String(sequenceNumber || window.kesmasNextSampleNumber || 1).replace(/\D/g, '');
            if (n.length < 4) {
                n = n.padStart(4, '0');
            }
            var full = tc + '.' + seg + '/' + n + '/' + yk;
            return `<div class="mb-3">
                <label class="small font-weight-bold d-block mb-1">No. Sampel (otomatis) — ${labLabel}</label>
                <div class="card border-0 shadow-sm mb-2" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                    <div class="card-body d-flex flex-wrap align-items-center py-2 px-3" style="gap: 8px; font-weight: 600;">
                        <span style="color: #2e7d32; letter-spacing: 0.5px;">${full}</span>
                    </div>
                </div>
                <small class="text-muted">Nomor ditetapkan otomatis saat simpan (pratinjau dari urutan global).</small>
            </div>`;
        }

        function kesmasLabReviewInputHtml(labSuffix, labLabel, hiddenTargetId, reviewInputId, defaultUrut) {
            var yk = kesmasCurrentYear();
            var hid = String(hiddenTargetId || '').replace(/"/g, '&quot;');
            var rid = String(reviewInputId || '').replace(/"/g, '&quot;');
            var seg = String(labSuffix || '01').replace(/\D/g, '') || '01';
            var init = '';
            if (hid) {
                var h = document.getElementById(hiddenTargetId);
                if (h && h.value) {
                    init = String(h.value).replace(/\D/g, '');
                }
            }
            if (!init && defaultUrut != null && String(defaultUrut) !== '') {
                init = String(defaultUrut).replace(/\D/g, '');
            }
            return `<div class="mb-3">
                <label class="small font-weight-bold d-block mb-1">No. Laboratorium — ${labLabel}</label>
                <p class="small text-muted mb-1">Format: <code>449.5/${seg}/[urut]/${yk}</code>. Masukkan <strong>angka urut</strong> saja.</p>
                <div class="card border-0 shadow-sm mb-2" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
                    <div class="card-body d-flex flex-wrap align-items-center py-2 px-3" style="gap: 8px; font-weight: 600;">
                        <span style="color: #e65100;">449.5/${seg}/</span>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control kesmas-klinik-lab-review" placeholder="no_urut"
                            id="${rid}"
                            data-lab-seg="${String(seg).replace(/"/g, '&quot;')}"
                            data-lab-target-id="${hid}"
                            style="max-width: 120px; font-weight: 600; color: #e65100; text-align: center; height: 32px;"
                            value="${String(init).replace(/"/g, '&quot;')}" />
                        <span style="color: #e65100; white-space: nowrap;">/${yk}</span>
                    </div>
                </div>
                <div class="small text-muted">Pratinjau: <strong class="kesmas-lab-preview-text" data-for="${rid}">449.5/${seg}/${init || '—'}/${yk}</strong></div>
            </div>`;
        }

        function kesmasGetLabIdsForSampleType(typeId) {
            var labIds = [];
            var map = window.kesmasLabIdToSuffix || {};
            var cfg = (window.sampleTypeConfigs || {})[typeId] || {};
            function note(ms) {
                var parts = String(ms || '').split('_');
                if (parts.length >= 2 && labIds.indexOf(parts[1]) === -1) {
                    labIds.push(parts[1]);
                }
            }
            (cfg.packets || []).forEach(function(p) {
                (p.methods || []).forEach(note);
            });
            (cfg.additional_methods || []).forEach(function(m) {
                note(m.method_string || m.method);
            });
            if (!labIds.length) {
                $('.method-checkbox-tab:checked').each(function() {
                    var $pane = $(this).closest('.tab-pane[data-sample-type-id]');
                    if (!$pane.length || String($pane.attr('data-sample-type-id')) !== String(typeId)) {
                        return;
                    }
                    note($(this).attr('data-method'));
                });
            }
            labIds.sort(function(a, b) {
                return (map[a] || '99').localeCompare(map[b] || '99');
            });
            return labIds;
        }

        function kesmasSyncSpecimenFromReview() {
            if (!window.kesmasIsNomorSampelManual) {
                return;
            }
            var review = document.getElementById('review-content');
            if (!review) {
                return;
            }
            review.querySelectorAll('.kesmas-klinik-specimen-review').forEach(function(el) {
                el.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        function kesmasSyncManualLabHiddenFields() {
            if (!window.kesmasIsNomorLabManual) {
                return;
            }
            var review = document.getElementById('review-content');
            if (!review) {
                return;
            }
            review.querySelectorAll('.kesmas-klinik-lab-review').forEach(function(el) {
                var tid = el.getAttribute('data-lab-target-id');
                if (!tid) {
                    return;
                }
                var t = document.getElementById(tid);
                if (t) {
                    t.value = String(el.value || '').replace(/\D/g, '');
                }
            });
        }

        function kesmasSpecimenReviewHasUrut(el, hiddenId) {
            var d = '';
            if (el) {
                d = String(el.value || '').replace(/\D/g, '');
            }
            if (!d && hiddenId) {
                var h = document.getElementById(hiddenId);
                if (h && h.value) {
                    d = kesmasParseMiddleDigits(h.value);
                }
            }
            return !!d;
        }

        function kesmasValidateManualSampleNumbers() {
            if (!window.kesmasIsNomorSampelManual) {
                return { ok: true };
            }
            var missing = [];
            var map = window.kesmasLabIdToSuffix || {};
            var review = document.getElementById('review-content');

            if (window.selectedSampleTypes && window.selectedSampleTypes.length > 0) {
                var checkedTypes = {};
                window.selectedSampleTypes.forEach(function(type) {
                    if (checkedTypes[type.id]) {
                        return;
                    }
                    checkedTypes[type.id] = true;
                    var clean = String(type.id).replace(/-/g, '');
                    var labIds = kesmasGetLabIdsForSampleType(type.id);
                    labIds.forEach(function(labId) {
                        var suf = map[labId] || '01';
                        var labLabel = suf === '01' ? 'Kimia' : 'Mikrobiologi';
                        var hiddenId = labIds.length > 1 ?
                            'input_code_sample_' + clean + '_' + suf :
                            'input_code_sample_' + clean;
                        var el = review ? review.querySelector(
                            '.kesmas-klinik-specimen-review[data-specimen-lab-seg="' + suf + '"][data-specimen-hidden-ids*="' + clean + '"]'
                        ) : null;
                        if (!el && review) {
                            review.querySelectorAll('.kesmas-klinik-specimen-review').forEach(function(inp) {
                                var hid = inp.getAttribute('data-specimen-hidden-ids') || '';
                                if (hid.indexOf(clean) !== -1 && (inp.getAttribute('data-specimen-lab-seg') || '') === suf) {
                                    el = inp;
                                }
                            });
                        }
                        if (!kesmasSpecimenReviewHasUrut(el, hiddenId)) {
                            missing.push((type.code || type.name || 'Jenis sampel') + ' — ' + labLabel);
                        }
                    });
                });
            } else {
                var labs = kesmasGetLabsUsedForReview();
                if (labs.useKimia) {
                    var elK = review ? review.querySelector('.kesmas-klinik-specimen-review[data-specimen-lab-seg="01"]') : null;
                    if (!kesmasSpecimenReviewHasUrut(elK, 'input_code_sample_kimia')) {
                        missing.push('Kimia');
                    }
                }
                if (labs.useMikro) {
                    var elM = review ? review.querySelector('.kesmas-klinik-specimen-review[data-specimen-lab-seg="02"]') : null;
                    if (!kesmasSpecimenReviewHasUrut(elM, 'input_code_sample_mikro')) {
                        missing.push('Mikrobiologi');
                    }
                }
            }

            if (missing.length) {
                return {
                    ok: false,
                    message: 'Nomor sampel manual wajib diisi untuk: ' + missing.join(', ') +
                        '. Lengkapi di langkah Review & Simpan.'
                };
            }
            return { ok: true };
        }

        function kesmasValidateManualLabNumbers() {
            if (!window.kesmasIsNomorLabManual) {
                return { ok: true };
            }
            var missing = [];
            var map = window.kesmasLabIdToSuffix || {};
            var review = document.getElementById('review-content');

            if (window.selectedSampleTypes && window.selectedSampleTypes.length > 0) {
                var checkedTypes = {};
                window.selectedSampleTypes.forEach(function(type) {
                    if (checkedTypes[type.id]) {
                        return;
                    }
                    checkedTypes[type.id] = true;
                    var clean = String(type.id).replace(/-/g, '');
                    var labIds = kesmasGetLabIdsForSampleType(type.id);
                    labIds.forEach(function(labId) {
                        var suf = map[labId] || '01';
                        var labLabel = suf === '01' ? 'Kimia' : 'Mikrobiologi';
                        var el = document.getElementById('review_nomer_lab_' + clean + '_' + suf);
                        var d = el ? String(el.value || '').replace(/\D/g, '') : '';
                        if (!d) {
                            missing.push((type.code || type.name || 'Jenis sampel') + ' — ' + labLabel);
                        }
                    });
                });
            } else {
                var labs = kesmasGetLabsUsedForReview();
                if (labs.useKimia) {
                    var dK = '';
                    if (review) {
                        var elK = review.querySelector('.kesmas-klinik-lab-review[data-lab-seg="01"]');
                        if (elK) {
                            dK = String(elK.value || '').replace(/\D/g, '');
                        }
                    }
                    if (!dK) {
                        var hK = document.getElementById('manual_nomer_lab_kimia');
                        dK = hK ? String(hK.value || '').replace(/\D/g, '') : '';
                    }
                    if (!dK) {
                        missing.push('Kimia');
                    }
                }
                if (labs.useMikro) {
                    var dM = '';
                    if (review) {
                        var elM = review.querySelector('.kesmas-klinik-lab-review[data-lab-seg="02"]');
                        if (elM) {
                            dM = String(elM.value || '').replace(/\D/g, '');
                        }
                    }
                    if (!dM) {
                        var hM = document.getElementById('manual_nomer_lab_mikro');
                        dM = hM ? String(hM.value || '').replace(/\D/g, '') : '';
                    }
                    if (!dM) {
                        missing.push('Mikrobiologi');
                    }
                }
            }

            if (missing.length) {
                return {
                    ok: false,
                    message: 'Nomor laboratorium manual wajib diisi untuk: ' + missing.join(', ') +
                        '. Lengkapi di langkah Review & Simpan.'
                };
            }
            return { ok: true };
        }

        /** Kode jenis sampel (code_sample_type) yang wajib mengisi titik pengambilan — tambah kode jika perlu */
        const TITIK_WAJIB_SAMPLE_TYPE_CODES = ['MM'];

        function normalizeSampleTypeCode(code) {
            return String(code || '').trim().toUpperCase();
        }

        function sampleTypeRequiresTitikPengambilan(meta) {
            if (!meta) {
                return false;
            }
            return TITIK_WAJIB_SAMPLE_TYPE_CODES.indexOf(normalizeSampleTypeCode(meta.code)) !== -1;
        }

        function resolveSampleTypeMeta(typeId) {
            var idStr = String(typeId || '').trim();
            var sts = window.selectedSampleTypes || [];
            for (var i = 0; i < sts.length; i++) {
                if (String(sts[i].id) === idStr) {
                    return sts[i];
                }
            }
            var esc = idStr.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
            var $b = $('.btn-pick-jenis[data-id="' + esc + '"]');
            if ($b.length) {
                return {
                    id: idStr,
                    code: $b.attr('data-code') || '',
                    name: $b.attr('data-name') || ''
                };
            }
            return {
                id: idStr,
                code: '',
                name: ''
            };
        }

        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(function(s) {
                s.classList.remove('active');
            });

            const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
            if (stepElement) {
                stepElement.classList.add('active');
            }

            document.querySelectorAll('.step-item').forEach((item, index) => {
                item.classList.remove('active', 'completed');
                const stepNum = index + 1;

                if (stepNum < step) {
                    item.classList.add('completed');
                } else if (stepNum === step) {
                    item.classList.add('active');
                }
            });

            window.scrollTo(0, 0);

            if (step === 2) {
                setTimeout(function() {
                    updateNextStepButton();
                }, 100);
            }

            if (step === 3) {
                setTimeout(function() {
                    var selectedSampleTypes = window.selectedSampleTypes || [];
                    var sampleTypeConfigs = window.sampleTypeConfigs || {};

                    if (selectedSampleTypes && selectedSampleTypes.length > 0) {
                        if (typeof window.populateReviewMultiple === 'function') {
                            window.populateReviewMultiple();
                        }
                    } else if (typeof populateReview === 'function') {
                        populateReview();
                    }

                    var $submitBtn = $('#submitAll');
                    if ($submitBtn.length > 0) {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.removeAttr('disabled');
                        $submitBtn.removeClass('disabled');
                    }
                }, 300);
            }
        }

        function activateSampleTypeTab(sampleTypeId) {
            if (!sampleTypeId) {
                return;
            }
            var tabId = 'tab-' + String(sampleTypeId).replace(/-/g, '');
            var $tab = $('a.nav-link[href="#' + tabId + '"]');
            if ($tab.length && typeof $tab.tab === 'function') {
                $tab.tab('show');
            } else if ($tab.length) {
                $tab[0].click();
            }
        }

        function focusInvalidStep1Field(kind) {
            setTimeout(function() {
                var targetId = kind === 'berat' ? 'berat_vol_layak' : 'tempat_kemasan_layak';
                var el = document.getElementById(targetId);
                var card = el ? el.closest('.form-section-card-sample') : null;
                if (card) {
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                if (el) {
                    el.focus();
                }
            }, 120);
        }

        function focusFirstInvalidStep2Field() {
            var sts = window.selectedSampleTypes || [];
            var stc = window.sampleTypeConfigs || {};

            if (sts.length > 0) {
                sts.some(function(type) {
                    if (!type || !type.id) {
                        return false;
                    }
                    var config = stc[type.id] || {};
                    var hasPacket = (config.packets && config.packets.length > 0);
                    var hasAdditionalMethods = (config.additional_methods && config.additional_methods.length > 0);

                    if (!hasPacket && !hasAdditionalMethods) {
                        activateSampleTypeTab(type.id);
                        var sid = type.id;
                        setTimeout(function() {
                            var $search = $('.search-parameter-tab[data-sample-type-id="' + sid + '"]');
                            var $pc = $('.packet-buttons-container-tab[data-sample-type-id="' + sid + '"]');
                            var el = $search.length ? $search[0] : ($pc.length ? $pc[0] : null);
                            if (el) {
                                el.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                            if ($search.length) {
                                $search.trigger('focus');
                            }
                        }, 280);
                        return true;
                    }
                    var metaTitik = type.code !== undefined && type.code !== null ? type : resolveSampleTypeMeta(type
                        .id);
                    if (sampleTypeRequiresTitikPengambilan(metaTitik) && !((config.titik_pengambilan || '')
                            .trim())) {
                        activateSampleTypeTab(type.id);
                        var sidT = type.id;
                        setTimeout(function() {
                            var inp = document.getElementById('titik_pengambilan_' + sidT);
                            if (inp) {
                                inp.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                inp.focus();
                            }
                        }, 280);
                        return true;
                    }
                    return false;
                });
                return;
            }

            var jenisEl = document.getElementById('jenis_sampel');
            if (!jenisEl || !jenisEl.value) {
                setTimeout(function() {
                    var picker = document.querySelector('.btn-pick-jenis');
                    if (picker) {
                        picker.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }, 120);
                return;
            }

            setTimeout(function() {
                var searchParam = document.getElementById('search-parameter');
                var pktCont = document.querySelector('.packet-buttons-container');
                if (searchParam && searchParam.offsetParent !== null) {
                    searchParam.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    searchParam.focus();
                    return;
                }
                if (pktCont && pktCont.offsetParent !== null) {
                    pktCont.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                var firstCb = document.querySelector(
                    '#form-create-sample input[name="method[]"]:not([disabled])');
                if (firstCb) {
                    var wrap = firstCb.closest('.form-section-card-sample') || firstCb.closest('.container.method');
                    if (wrap) {
                        wrap.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    firstCb.focus();
                }
            }, 120);
        }

        function validateStep(step) {
            const errorDiv = document.getElementById(`step${step}-error`);
            const errorMessage = document.getElementById(`step${step}-error-message`);

            errorDiv.classList.remove('show');

            if (step === 1) {
                // Validate Step 1: Penerimaan Sampel
                const tempatKemasan = document.querySelector('input[name="kelayakan_tempat_kemasan"]:checked');
                const beratVol = document.querySelector('input[name="kelayakan_berat_vol"]:checked');

                if (!tempatKemasan) {
                    errorMessage.textContent = 'Pilih kelayakan Tempat/Kemasan (Layak/Tidak Layak)';
                    errorDiv.classList.add('show');
                    errorDiv.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                    focusInvalidStep1Field('tempat');
                    return false;
                }

                if (!beratVol) {
                    errorMessage.textContent = 'Pilih kelayakan Berat/Vol (Layak/Tidak Layak)';
                    errorDiv.classList.add('show');
                    errorDiv.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                    focusInvalidStep1Field('berat');
                    return false;
                }

                return true;
            }



            if (step === 2) {
                // Validate Step 2: Jenis & Parameter
                // Check if using multiple sample types mode
                var stsValidate = window.selectedSampleTypes || [];
                var stcValidate = window.sampleTypeConfigs || {};

                if (stsValidate.length > 0) {
                    // Multiple sample types mode
                    let hasValidConfig = true;
                    let errorMsg = '';

                    stsValidate.forEach(function(type) {
                        var config = stcValidate[type.id] || {};
                        var hasPacket = (config.packets && config.packets.length > 0);
                        var hasAdditionalMethods = (config.additional_methods && config.additional_methods.length >
                            0);
                        var hasParameterOrPacket = hasPacket || hasAdditionalMethods;

                        if (!hasParameterOrPacket) {
                            hasValidConfig = false;
                            var linePaket =
                                `Jenis sampel "${type.code} - ${type.name}" belum memiliki paket atau parameter!`;
                            errorMsg = errorMsg ? (errorMsg + '\n' + linePaket) : linePaket;
                            return;
                        }

                        var metaTitik = type.code !== undefined && type.code !== null ? type : resolveSampleTypeMeta(
                            type.id);
                        if (sampleTypeRequiresTitikPengambilan(metaTitik) && hasParameterOrPacket) {
                            var titikVal = (config.titik_pengambilan || '').trim();
                            if (!titikVal) {
                                hasValidConfig = false;
                                var lineTitik =
                                    `Jenis sampel "${type.code} - ${type.name}" wajib mengisi titik pengambilan!`;
                                errorMsg = errorMsg ? (errorMsg + '\n' + lineTitik) : lineTitik;
                            }
                        }
                    });

                    if (!hasValidConfig) {
                        errorMessage.textContent = errorMsg;
                        errorDiv.classList.add('show');
                        errorDiv.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                        focusFirstInvalidStep2Field();
                        return false;
                    }

                    // Populate review for multiple samples
                    if (typeof window.populateReviewMultiple === 'function') {
                        window.populateReviewMultiple();
                    }

                    // Enable submit button
                    var $submitBtn = $('#submitAll');
                    if ($submitBtn.length > 0) {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.removeAttr('disabled');
                    }

                    return true;
                } else {
                    // Single sample type mode (legacy)
                    const jenisSampel = document.getElementById('jenis_sampel').value;
                    const selectedParams = document.querySelectorAll('input[name="method[]"]:checked');
                    const packetSelect = document.getElementById('packet');

                    let hasPacket = false;
                    if (packetSelect) {
                        const selectedOptions = Array.from(packetSelect.selectedOptions);
                        hasPacket = selectedOptions.length > 0 && selectedOptions.some(opt => opt.value !== '');
                    }

                    const hasParameterOrPacket = selectedParams.length > 0 || hasPacket;

                    if (!jenisSampel) {
                        errorMessage.textContent = 'Pilih jenis sampel terlebih dahulu';
                        errorDiv.classList.add('show');
                        errorDiv.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                        focusFirstInvalidStep2Field();
                        return false;
                    }

                    if (!hasParameterOrPacket) {
                        errorMessage.textContent = 'Pilih minimal 1 paket atau parameter pengujian';
                        errorDiv.classList.add('show');
                        errorDiv.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                        focusFirstInvalidStep2Field();
                        return false;
                    }

                    // Populate review
                    if (typeof populateReview === 'function') {
                        populateReview();
                    }

                    // Enable submit button
                    var $submitBtn = $('#submitAll');
                    if ($submitBtn.length > 0) {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.removeAttr('disabled');
                    }

                    return true;
                }
            }

            return true;
        }

        // Function to update Next Step button visibility based on form validation
        function updateNextStepButton() {
            var $nextBtn = $('#btn-next-step-2');
            if ($nextBtn.length === 0) {
                return;
            }

            var shouldShow = false;

            // Check if using multiple sample types mode (check both selectedSampleTypes and sampleTypeConfigs)
            // Use window object to access global variables
            var selectedSampleTypes = window.selectedSampleTypes || [];
            var sampleTypeConfigs = window.sampleTypeConfigs || {};
            var hasMultipleMode = (selectedSampleTypes && selectedSampleTypes.length > 0) ||
                (sampleTypeConfigs && Object.keys(sampleTypeConfigs).length > 0);

            if (hasMultipleMode) {
                // Tampilkan Selanjutnya bila ada jenis terpilih. Titik wajib (mis. MM) hanya dicek saat
                // klik validateStep — jangan sembunyikan tombol saat user di tab jenis lain (mis. AB) padahal MM belum isi titik.
                var checkedSampleTypes = [];

                if (selectedSampleTypes && selectedSampleTypes.length > 0) {
                    checkedSampleTypes = selectedSampleTypes;
                } else if (sampleTypeConfigs && Object.keys(sampleTypeConfigs).length > 0) {
                    checkedSampleTypes = Object.keys(sampleTypeConfigs).map(function(id) {
                        return {
                            id: id
                        };
                    });
                }

                shouldShow = checkedSampleTypes.length > 0;
            } else {
                var jenisSampel = document.getElementById('jenis_sampel')?.value;
                shouldShow = !!jenisSampel;
            }

            if (shouldShow) {
                $nextBtn.css('display', 'block');
            } else {
                $nextBtn.css('display', 'none');
            }
        }

        function nextStep(step) {
            if (validateStep(step)) {
                currentStep = step + 1;
                if (currentStep <= totalSteps) {
                    showStep(currentStep);
                }
            }
        }

        function prevStep(step) {
            currentStep = step - 1;
            if (currentStep >= 1) {
                showStep(currentStep);
            }
        }

        function populateReview() {
            const reviewContent = document.getElementById('review-content');

            // Get form data
            const jenisSampel = document.getElementById('jenis_sampel').value;
            const jenisSampelText = document.querySelector('.btn-pick-jenis.active')?.textContent.trim() || '-';
            const tempatKemasan = document.querySelector('input[name="kelayakan_tempat_kemasan"]:checked')?.value || '-';
            const beratVol = document.querySelector('input[name="kelayakan_berat_vol"]:checked')?.value || '-';
            const selectedParams = document.querySelectorAll('input[name="method[]"]:checked');
            const totalHarga = document.getElementById('cart-total-price')?.textContent || 'Rp 0';

            // Get kode sampel
            const kodeSampelKimia = document.getElementById('input_code_sample_kimia')?.value || '-';
            const kodeSampelMikro = document.getElementById('input_code_sample_mikro')?.value || '-';

            // Check if using packet
            const isPaket = document.getElementById('is_paket')?.value === 'true';
            const selectedPackets = [];
            if (isPaket) {
                const packetSelect = document.getElementById('packet');
                if (packetSelect) {
                    Array.from(packetSelect.selectedOptions).forEach(option => {
                        if (option.value) {
                            selectedPackets.push(option.text.trim());
                        }
                    });
                }
            }

            let html = '<div style="display: grid; gap: 20px;">';

            // Kode sampel / nomor lab — manual Kesmas: hanya di review (angka + format statis)
            const labsForManual = typeof kesmasGetLabsUsedForReview === 'function' ? kesmasGetLabsUsedForReview() : {
                useKimia: document.getElementById('code_sample_kimia_wrapper_top')?.style.display !== 'none',
                useMikro: document.getElementById('code_sample_mikro_wrapper_top')?.style.display !== 'none'
            };
            const kimiaVisible = !!labsForManual.useKimia;
            const mikroVisible = !!labsForManual.useMikro;
            const jenisSampelIdSingle = document.getElementById('jenis_sampel')?.value;
            var typeCodeRaw = '';
            if (jenisSampelIdSingle && typeof resolveSampleTypeMeta === 'function') {
                typeCodeRaw = String(resolveSampleTypeMeta(jenisSampelIdSingle).code || '');
            }
            if (!typeCodeRaw && window.selectedSampleTypes && window.selectedSampleTypes.length > 0) {
                typeCodeRaw = String(window.selectedSampleTypes[0].code || '');
            }
            const typeCodeSingle = String(typeCodeRaw).replace(/"/g, '&quot;');

            const tcSingle = typeCodeSingle || '...';

            if (window.kesmasIsNomorSampelManual && (kimiaVisible || mikroVisible)) {
                html +=
                    '<div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 20px; border-radius: 10px; border-left: 5px solid #2196f3; margin-bottom: 15px;">';
                html +=
                    `<h6 style="color: #1976d2; margin-bottom: 10px;"><i class="fa fa-barcode"></i> Nomor sampel manual (wajib) — ${tcSingle}</h6>`;
                const yk = kesmasCurrentYear();
                html +=
                    `<p class="small text-muted mb-3">Isi <strong>angka urut</strong> saja. Kimia → <code>${tcSingle}.01/[urut]/tahun</code>; Mikro → <code>${tcSingle}.02/[urut]/tahun</code>.</p>`;
                if (kimiaVisible) {
                        var _hk = document.getElementById('input_code_sample_kimia');
                        if (_hk) {
                            var initK = window.kesmasIsNomorSampelManual ? '' : kesmasParseMiddleDigits(_hk.value);
                            html += `<div class="mb-3">
                            <label class="small font-weight-bold d-block mb-1">No. sampel (spesimen) — Kimia (${tcSingle})</label>
                            <div class="card border-0 shadow-sm mb-2" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                <div class="card-body d-flex flex-wrap align-items-center py-2 px-3" style="gap: 8px; font-weight: 600;">
                                    <span style="color: #667eea;">${tcSingle}.01/</span>
                                    <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control kesmas-klinik-specimen-review" placeholder="no_urut"
                                        data-specimen-lab-seg="01"
                                        data-specimen-hidden-ids="input_code_sample_kimia"
                                        style="max-width: 120px; font-weight: 600; color: #667eea; text-align: center; height: 32px;"
                                        value="${String(initK).replace(/"/g, '&quot;')}" />
                                    <span style="color: #667eea; white-space: nowrap;">/${yk}</span>
                                </div>
                            </div>
                        </div>`;
                        }
                    }
                    if (mikroVisible) {
                        var _hm = document.getElementById('input_code_sample_mikro');
                        if (_hm) {
                            var initM = window.kesmasIsNomorSampelManual ? '' : kesmasParseMiddleDigits(_hm.value);
                            html += `<div class="mb-3">
                            <label class="small font-weight-bold d-block mb-1">No. sampel (spesimen) — Mikrobiologi (${tcSingle})</label>
                            <div class="card border-0 shadow-sm mb-2" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                <div class="card-body d-flex flex-wrap align-items-center py-2 px-3" style="gap: 8px; font-weight: 600;">
                                    <span style="color: #667eea;">${tcSingle}.02/</span>
                                    <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control kesmas-klinik-specimen-review" placeholder="no_urut"
                                        data-specimen-lab-seg="02"
                                        data-specimen-hidden-ids="input_code_sample_mikro"
                                        style="max-width: 120px; font-weight: 600; color: #667eea; text-align: center; height: 32px;"
                                        value="${String(initM).replace(/"/g, '&quot;')}" />
                                    <span style="color: #667eea; white-space: nowrap;">/${yk}</span>
                                </div>
                            </div>
                        </div>`;
                        }
                    }
                html += '</div>';
            }
            if (!window.kesmasIsNomorSampelManual && (kimiaVisible || mikroVisible)) {
                var autoBase = parseInt(window.kesmasNextSampleNumber || 1, 10) || 1;
                html +=
                    '<div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 20px; border-radius: 10px; border-left: 5px solid #4caf50; margin-bottom: 15px;">';
                html += '<h6 style="color: #2e7d32; margin-bottom: 15px;"><i class="fa fa-barcode"></i> No. Sampel (otomatis)</h6>';
                var pos = 0;
                if (kimiaVisible) {
                    html += kesmasAutoSamplePreviewHtml(tcSingle, '01', 'Kimia', autoBase + pos);
                    pos++;
                }
                if (mikroVisible) {
                    html += kesmasAutoSamplePreviewHtml(tcSingle, '02', 'Mikrobiologi', autoBase + pos);
                }
                html += '</div>';
            }

            if (kimiaVisible || mikroVisible) {
                var ykLab = kesmasCurrentYear();
                html +=
                    '<div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 16px 20px; border-radius: 10px; border-left: 5px solid #2196f3; margin-bottom: 15px;">';
                html += '<h6 style="color: #1565c0; margin-bottom: 8px;"><i class="fa fa-flask"></i> Nomor laboratorium</h6>';
                html += '<p class="mb-1 text-muted" style="font-size: 14px;">Ditetapkan otomatis di <strong>akhir pemeriksaan / pengesahan hasil</strong> (tidak perlu diisi di sini).</p>';
                html += '<p class="mb-0 small text-muted">Format: <code>449.5/01/[urut]/' + ykLab + '</code> (Kimia) · <code>449.5/02/[urut]/' + ykLab + '</code> (Mikro)</p>';
                html += '</div>';
            }

            // Detail Sampel
            html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">';
            html += '<h6 style="color: #11998e; margin-bottom: 15px;"><i class="fa fa-vial"></i> Detail Sampel</h6>';
            html += `<p><strong>Jenis Sampel:</strong> ${jenisSampelText}</p>`;
            html +=
                `<p><strong>Kelayakan Tempat/Kemasan:</strong> <span style="color: ${tempatKemasan === 'layak' ? '#28a745' : '#dc3545'}">${tempatKemasan}</span></p>`;
            html +=
                `<p><strong>Kelayakan Berat/Vol:</strong> <span style="color: ${beratVol === 'layak' ? '#28a745' : '#dc3545'}">${beratVol}</span></p>`;
            html += '</div>';

            // Separate parameters into packet and additional (satuan)
            let packetParams = [];
            let additionalParams = [];
            let packetPrice = 0;
            let additionalPrice = 0;

            // Get actual packet price from window (not sum of parameters)
            if (window.packetPrice) {
                packetPrice = parseInt(window.packetPrice) || 0;
            }

            if (selectedParams.length > 0) {
                selectedParams.forEach((param) => {
                    const paramId = param.getAttribute('data-idmethod');
                    const paramName = param.closest('.method-row')?.querySelector('label')?.textContent.trim() ||
                        'Unknown';
                    const paramPrice = parseInt(param.getAttribute('data-price')) || 0;

                    // Check if parameter is from packet
                    const isFromPacket = window.packetParameterIds && window.packetParameterIds.includes(paramId);

                    if (isFromPacket) {
                        packetParams.push({
                            name: paramName,
                            price: paramPrice
                        });
                    } else {
                        additionalParams.push({
                            name: paramName,
                            price: paramPrice
                        });
                        additionalPrice += paramPrice;
                    }
                });
            }

            // Paket Section (if applicable)
            if (isPaket && selectedPackets.length > 0) {
                html +=
                    '<div style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); padding: 20px; border-radius: 10px; border-left: 5px solid #ff9800;">';
                html += '<h6 style="color: #f57c00; margin-bottom: 15px;"><i class="fa fa-box"></i> Paket Terpilih</h6>';
                html += '<ul style="margin: 0; padding-left: 20px;">';
                selectedPackets.forEach(packet => {
                    html += `<li style="margin-bottom: 8px;"><strong>${packet}</strong></li>`;
                });
                html += '</ul>';

                // Show packet parameters (without individual prices)
                if (packetParams.length > 0) {
                    html += '<div style="margin-top: 15px;">';
                    html += '<strong style="color: #f57c00;">Parameter dalam Paket:</strong>';
                    html += '<ul style="margin-top: 10px; padding-left: 20px;">';
                    packetParams.forEach((param, index) => {
                        html +=
                            `<li style="margin-bottom: 5px; color: #666;">${index + 1}. ${param.name}</li>`;
                    });
                    html += '</ul>';
                    html += '</div>';
                }

                // Show packet price (actual packet price, not sum of parameters)
                if (packetPrice > 0) {
                    const packetPriceFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(packetPrice);
                    html += `<div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #ff9800;">`;
                    html +=
                        `<p style="margin: 0;"><strong>Harga Paket:</strong> <span style="color: #f57c00; font-size: 20px; font-weight: 700;">${packetPriceFormatted}</span></p>`;
                    html += `</div>`;
                }

                html += '</div>';
            }

            // Additional Parameters (Satuan) Section
            if (additionalParams.length > 0) {
                html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">';
                html +=
                    '<h6 style="color: #667eea; margin-bottom: 15px;"><i class="fa fa-plus-circle"></i> Parameter Tambahan (Satuan)</h6>';
                html += `<p><strong>Total Parameter Tambahan:</strong> ${additionalParams.length}</p>`;

                html += '<div style="margin-top: 15px;">';
                html += '<ul style="margin-top: 10px; padding-left: 20px; max-height: 300px; overflow-y: auto;">';

                additionalParams.forEach((param, index) => {
                    const priceFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(param.price);
                    html += `<li style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 5px;">`;
                    html += `<span style="color: #2d3748;">${index + 1}. ${param.name}</span>`;
                    html +=
                        ` <span style="color: #667eea; font-weight: 600; float: right;">${priceFormatted}</span>`;
                    html += `</li>`;
                });

                html += '</ul>';
                html += '</div>';

                // Show additional total price
                const additionalPriceFormatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(additionalPrice);
                html += `<div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #667eea;">`;
                html +=
                    `<p style="margin: 0;"><strong>Harga Parameter Tambahan:</strong> <span style="color: #667eea; font-size: 18px; font-weight: 700;">${additionalPriceFormatted}</span></p>`;
                html += `</div>`;

                html += '</div>';
            }

            // All Parameters (if no packet mode)
            if (!isPaket && selectedParams.length > 0) {
                html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">';
                html +=
                    '<h6 style="color: #667eea; margin-bottom: 15px;"><i class="fa fa-microscope"></i> Parameter Terpilih</h6>';
                html += `<p><strong>Total Parameter:</strong> ${selectedParams.length}</p>`;

                html += '<div style="margin-top: 15px;">';
                html += '<strong>Detail Parameter:</strong>';
                html += '<ul style="margin-top: 10px; padding-left: 20px; max-height: 300px; overflow-y: auto;">';

                selectedParams.forEach((param, index) => {
                    const paramName = param.closest('.method-row')?.querySelector('label')?.textContent.trim() ||
                        'Unknown';
                    const paramPrice = param.getAttribute('data-price') || '0';
                    const priceFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(paramPrice);

                    html += `<li style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 5px;">`;
                    html += `<span style="color: #2d3748;">${index + 1}. ${paramName}</span>`;
                    html +=
                        ` <span style="color: #11998e; font-weight: 600; float: right;">${priceFormatted}</span>`;
                    html += `</li>`;
                });

                html += '</ul>';
                html += '</div>';

                // Show total price for all parameters (satuan mode)
                let totalSatuanPrice = 0;
                selectedParams.forEach((param) => {
                    totalSatuanPrice += parseInt(param.getAttribute('data-price')) || 0;
                });

                if (totalSatuanPrice > 0) {
                    const totalSatuanFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(totalSatuanPrice);
                    html += `<div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #667eea;">`;
                    html +=
                        `<p style="margin: 0;"><strong>Total Harga Satuan:</strong> <span style="color: #667eea; font-size: 20px; font-weight: 700;">${totalSatuanFormatted}</span></p>`;
                    html += `</div>`;
                }

                html += '</div>';
            }

            // Grand Total Section
            html +=
                '<div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 25px; border-radius: 10px; border-left: 5px solid #4caf50;">';
            html +=
                '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">';

            if (isPaket && packetPrice > 0) {
                const packetPriceFormatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(packetPrice);
                html += '<div>';
                html += '<p style="margin: 0; color: #666; font-size: 16px;">Harga Paket</p>';
                html +=
                    `<p style="margin: 5px 0 0 0; color: #f57c00; font-size: 18px; font-weight: 600;">${packetPriceFormatted}</p>`;
                html += '</div>';
            }

            if (additionalPrice > 0) {
                const additionalPriceFormatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(additionalPrice);
                html += '<div>';
                html += '<p style="margin: 0; color: #666; font-size: 16px;">Harga Satuan</p>';
                html +=
                    `<p style="margin: 5px 0 0 0; color: #667eea; font-size: 18px; font-weight: 600;">${additionalPriceFormatted}</p>`;
                html += '</div>';
            }

            html += '<div style="flex-grow: 1; text-align: right;">';
            html += '<p style="margin: 0; color: #2d3748; font-size: 16px; font-weight: 600;">TOTAL HARGA</p>';
            html += `<p style="margin: 5px 0 0 0; color: #11998e; font-size: 28px; font-weight: 700;">${totalHarga}</p>`;
            html += '</div>';

            html += '</div>';
            html += '</div>';

            html += '</div>';

            reviewContent.innerHTML = html;
            reviewContent.querySelectorAll('.kesmas-klinik-specimen-review').forEach(function(el) {
                var raw = el.getAttribute('data-specimen-hidden-ids') || '';
                var ids = raw.split(',').map(function(s) {
                    return s.trim();
                }).filter(Boolean);
                var hiddens = ids.map(function(id) {
                    return document.getElementById(id);
                }).filter(Boolean);
                if (hiddens.length) {
                    bindKesmasKlinikSpecimenInput(el, hiddens);
                }
            });
            reviewContent.querySelectorAll('.kesmas-klinik-lab-review').forEach(function(el) {
                var tid = el.getAttribute('data-lab-target-id');
                var t = tid ? document.getElementById(tid) : null;
                bindKesmasKlinikLabUrutInput(el, t || el);
            });
        }

        // Form Validation for Sticky Button
        function validateForm() {
            const submitBtn = document.getElementById('submitAll');
            const validationStatus = document.getElementById('validation-status');
            const validationMessage = document.getElementById('validation-message');

            if (!validationStatus || !validationMessage || !submitBtn) return;

            // Check required fields
            const jenisSampel = document.getElementById('jenis_sampel').value;
            const tempatKemasan = document.querySelector('input[name="kelayakan_tempat_kemasan"]:checked');
            const beratVol = document.querySelector('input[name="kelayakan_berat_vol"]:checked');

            // Check if at least one parameter OR packet is selected
            const selectedParams = document.querySelectorAll('input[name="method[]"]:checked');
            const selectedPackets = document.querySelectorAll('select[name="packet[]"] option:checked');
            const packetSelect = document.getElementById('packet');

            // Check if packet is selected (either via select or hidden input)
            let hasPacket = false;
            if (packetSelect) {
                const selectedOptions = Array.from(packetSelect.selectedOptions);
                hasPacket = selectedOptions.length > 0 && selectedOptions.some(opt => opt.value !== '');
            }

            // Valid if either has parameters OR has packet
            const hasParameterOrPacket = selectedParams.length > 0 || hasPacket;

            let isValid = true;
            let message = '';

            if (!jenisSampel) {
                isValid = false;
                message = 'Pilih jenis sampel terlebih dahulu';
            } else if (!hasParameterOrPacket) {
                isValid = false;
                message = 'Pilih minimal 1 paket atau parameter pengujian';
            } else if (!tempatKemasan) {
                isValid = false;
                message = 'Pilih kelayakan tempat/kemasan';
            } else if (!beratVol) {
                isValid = false;
                message = 'Pilih kelayakan berat/vol';
            }

            if (isValid) {
                submitBtn.disabled = false;
                validationStatus.style.display = 'none';
            } else {
                submitBtn.disabled = true;
                validationStatus.style.display = 'flex';
                validationStatus.style.alignItems = 'center';
                validationMessage.textContent = message;
            }
        }

        // Initialize everything on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const stepIndicator = document.querySelector('.step-indicator');
                const formSteps = document.querySelectorAll('.form-step');

                if (stepIndicator && formSteps.length > 0) {
                    showStep(1);
                }

                validateForm();

                const form = document.getElementById('form-create-sample');
                if (form) {
                    form.addEventListener('change', validateForm);
                    form.addEventListener('input', validateForm);
                }
            }, 100);

            // Specific listeners for dynamic elements
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-pick-jenis') ||
                    e.target.classList.contains('btn-pick-paket') ||
                    e.target.name === 'method[]' ||
                    e.target.name === 'kelayakan_tempat_kemasan' ||
                    e.target.name === 'kelayakan_berat_vol') {
                    setTimeout(validateForm, 100);
                }
            });

            // Listen to packet select changes
            const packetSelect = document.getElementById('packet');
            if (packetSelect) {
                packetSelect.addEventListener('change', function() {
                    setTimeout(validateForm, 100);
                });
            }

            // Observer for dynamic packet buttons
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        validateForm();
                    }
                });
            });

            const packetContainer = document.querySelector('.packet-buttons-container');
            if (packetContainer) {
                observer.observe(packetContainer, {
                    childList: true,
                    subtree: true
                });
            }

        });

        var methods

        var methods_sample_type = []
        var jenis_sample, jenis_makanan

        var price_sample_type = 0;

        let is_multiple_labs = false;
        let select_multiple_codes = false;

        let lab_id = null;
        let lab_keys = [];
        let lab_keys_sequences = [];

        function integerToRoman(integer) {
            // Convert the integer into an integer (just to make sure)
            integer = parseInt(integer);
            let result = '';

            // Create a lookup array that contains all of the Roman numerals.
            const lookup = {
                'M': 1000,
                'CM': 900,
                'D': 500,
                'CD': 400,
                'C': 100,
                'XC': 90,
                'L': 50,
                'XL': 40,
                'X': 10,
                'IX': 9,
                'V': 5,
                'IV': 4,
                'I': 1
            };

            for (const roman in lookup) {
                // Determine the number of matches
                const value = lookup[roman];
                const matches = Math.floor(integer / value);

                // Add the same number of characters to the string
                result += roman.repeat(matches);

                // Set the integer to be the remainder of the integer and the value
                integer = integer % value;
            }

            // The Roman numeral should be built, return it
            return result;
        }



        $('#ispacket').val("false")

        $(".checkbox").change(function() {
            var total = 0;
            methods = [];
            $(".checkbox:checked").each(function() {
                var idmethod = $(this).data('idmethod');
                var foundMethod = methods.find(function(item) {
                    return item == idmethod;
                });
                if (!foundMethod) {
                    total = total + parseInt($(this).data('price'))
                    methods.push(idmethod)
                }
            });
            let difference = methods.filter(x => !methods_sample_type.includes(x));

            if (arrayContainsArray(methods, methods_sample_type)) {
                $('#ispacket').val("true")
                if (price_sample_type != 0) {
                    let difference = methods.filter(x => !methods_sample_type.includes(x));
                    var total_difference = 0;
                    $(".checkbox:checked").each(function() {

                        var idmethod = $(this).data('idmethod');
                        var foundMethod = difference.find(function(item) {
                            return item == idmethod;
                        });
                        if (foundMethod) {
                            total_difference = total_difference + $(this).data('price')
                            // total=total+parseInt($(this).data('price'))
                            // methods.push(idmethod)
                        }
                    });
                    $('#cost_samples').val(price_sample_type + total_difference)
                } else {
                    $('#cost_samples').val(total)
                }
            } else {
                $('#ispacket').val("false")
                $('#cost_samples').val(total)
            }

            checkMultipleLabs();
        });

        let arrayContainsArray = (a_array, b_array) => {


            for (let i = 0; i < b_array.length; i++) {
                if (a_array.includes(b_array[i])) {
                    let index = a_array.indexOf(b_array[i])
                    a_array.splice(index, 1)
                } else {
                    return false
                }
            }
            return true
        }

        // Paket always visible now - auto-detect mode



        // $code_year       $(".checkbox").prop("disabled", true);

        $("#is_paket").change(function() {

            $(".checkbox").prop("disabled", false);
            $(".checkbox").attr("data-toggle", "tooltip");
            $(".checkbox").attr("data-placement", "right");
            $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
            $("[data-toggle='tooltip']").tooltip();
            $('#cost_samples').val(0);
            $(".checkbox").prop("readonly", false);

            var jenis_sample_text = $("#jenis_sampel").children(":selected").text();


            var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
            url = url.replace('#', jenis_sample);



            $.ajax({
                url: url,
                type: "GET",
                datatype: 'json',
                success: function(response) {
                    var results = response.data;
                    results.forEach(result => {
                        $(".checkbox-" + result.id_method).prop("disabled", false);
                        $(".checkbox-" + result.id_method).removeAttr("title")
                        $(".checkbox-" + result.id_method).removeAttr(
                            "data-toggle");
                        $(".checkbox-" + result.id_method).removeAttr(
                            "data-placement");
                        $(".checkbox-" + result.id_method).removeAttr(
                            "data-original-title");

                    })
                },
            })

            if (this.checked) {
                // Paket always visible in auto-detect mode

                // Initialize Select2 with proper options for hidden multiple select
                // if ($('#packet').length && !$('#packet').hasClass('select2-hidden-accessible')) {
                //     $('#packet').select2({
                //         theme: 'classic',
                //         width: '100%',
                //         allowClear: true
                //     });
                // }

                methods_sample_type = [];
                price_sample_type = 0;
                $(".checkbox").prop("checked", false);
                $('#cost_samples').val(0);


                if (jenis_sample != null && jenis_sample != undefined) {

                    $(".checkbox").prop("checked", false);



                    var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
                    url = url.replace('#', jenis_sample);


                    $.ajax({
                        url: url,
                        type: "GET",
                        datatype: 'json',
                        success: function(response) {
                            var results = response.data;

                            results.forEach(result => {
                                $(".checkbox-" + result.id_method).prop("disabled", false);
                                $(".checkbox-" + result.id_method).removeAttr("title")
                                $(".checkbox-" + result.id_method).removeAttr(
                                    "data-toggle");
                                $(".checkbox-" + result.id_method).removeAttr(
                                    "data-placement");
                                $(".checkbox-" + result.id_method).removeAttr(
                                    "data-original-title");

                            })
                        },
                    })
                    var url = "/api/packet/#"
                    url = url.replace('#', jenis_sample);


                    $.ajax({
                        url: url,
                        type: "POST",
                        datatype: 'json',
                        success: function(response) {
                            $(".is_paket").css('display', 'block');

                            // $(".packet").css('display', 'none');
                            // $("#is_paket").prop('checked', false);
                            // $('#packet').val(null).trigger("change");
                            $('#packet')
                                .find('option')
                                .remove()
                                .end();
                            var results = response.results
                            results.forEach(result => {
                                $('#packet')
                                    .append('<option value="' + result.id +
                                        '" data-extra="' + result.data_extra + '">' +
                                        result.text + '</option>');
                            })

                            $("#packet").change(function() {



                                var packet = $(this).val();

                                var data = $("#packet option:selected").text();

                                if (data === 'ALT/AKK') {
                                } else {
                                    $('#jenis_sample_uji_usap').css('display', 'none');
                                }

                                // $("#test").val(data);

                                // console.log(data);
                                if (data.includes("Fisika")) {
                                    let parsed_sample_code = $('#input_code_sample_kimia')
                                        .val();
                                    let result_fisika = parsed_sample_code.replace("- K",
                                        "- F");

                                    $('#input_code_sample_kimia').val(result_fisika);

                                } else {
                                    let parsed_sample_code = $('#input_code_sample_kimia')
                                        .val();
                                    let result_fisika = parsed_sample_code.replace("- F",
                                        "- K");

                                    $('#input_code_sample_kimia').val(result_fisika);

                                }
                                var url =
                                    "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}"
                                url = url.replace('#', packet);


                                $('#ispacket').val("true")

                                $.ajax({
                                    url: url,
                                    type: "GET",
                                    datatype: 'json',
                                    success: function(response) {


                                        methods_sample_type = response.methods;


                                        $(".checkbox").prop("checked", false);
                                        var harga = 0;

                                        // Validasi response.data sebelum forEach
                                        if (response.data && Array.isArray(response
                                                .data)) {
                                            response.data.forEach(data => {
                                                harga = harga + resolveMethodPriceForJenisSample(
                                                    data['method_id'],
                                                    data['price_total_method']
                                                );
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "checked", true);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "readonly", true);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "disabled", false);

                                                let current_lab_id = $(
                                                        ".checkbox-" + data[
                                                            'method_id'])
                                                    .data(
                                                        'idlabs');
                                                if (!!lab_id && lab_id !=
                                                    current_lab_id) {
                                                    is_multiple_labs = true;
                                                }
                                                lab_id = current_lab_id;
                                            });
                                        }

                                        multipleLabs();

                                        if (response['price'] == 0) {
                                            $('#cost_samples').val(harga)
                                            window.packetPrice = harga;
                                        } else {
                                            $('#cost_samples').val(response[
                                                'price'])

                                            price_sample_type = response[
                                                'price'];
                                            window.packetPrice = response['price'];
                                        }

                                        if (typeof window.applyMethodPricesForJenisSampel === 'function') {
                                            window.applyMethodPricesForJenisSampel($('#jenis_sampel').val() || '');
                                        }

                                        //   var url = "{{ route('elits-packet.index') }}";
                                        //   window.location.href = url;

                                    },
                                    error: function(XMLHttpRequest, textStatus,
                                        errorThrown) {
                                        alert(XMLHttpRequest.responseJSON
                                            .message);
                                    }
                                });
                            })
                        },
                    })
                }
            } else {
                // Paket always visible in auto-detect mode
                methods_sample_type = [];
                price_sample_type = 0;
                $(".checkbox").prop("checked", false);
                $('#cost_samples').val(0);
            }

        })

        function ajax_getNewSampleNumberSequence(lab_key, id_permohonan_uji, is_makanan = false) {
            let url = "{{ route('elits-samples.getNewNumberSequence', '#') }}";
            url = url.replace('#', lab_key);
            url = url + "/#";
            url = url.replace('#', id_permohonan_uji);
            url = url + "/#";
            url = url.replace('#', is_makanan);


            $.ajax({
                url: url,
                type: "GET",
                datatype: 'json',
                success: function(response) {
                    // console.log(response)
                    return response;
                    // $('#code_sample').val(response)
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseJSON.message);
                }
            })
        }

        function checkMultipleLabs() {
            let checkbockCheckeds = $(".checkbox:checked");
            lab_keys = [];

            checkbockCheckeds.each((index, element) => {
                let current_lab_id = $(element).data('idlabs');

                if (!!lab_id && lab_id != current_lab_id) {
                    is_multiple_labs = true;
                }

                lab_id = current_lab_id;
                lab_keys = [...lab_keys, lab_id];
            })

            lab_keys = [...new Set(lab_keys)];
        }

        function multipleLabs() {
            return;
            if (lab_keys.length > 1 && $('#code_sample').prop('disabled')) {


                select_multiple_codes = true;

                // Get the lab num sequence each lab keys.
                // todo: make this more efficient
                $code_sample_type = $('#jenis_sample').children(":selected").data('code');

                for (let lab_key of lab_keys) {

                    let lab_sequence = ajax_getNewSampleNumberSequence(lab_key, '{{ $id }}');
                    lab_keys_sequences[lab_key] = lab_sequence;
                }

                // Disable and hide the original input element
                $('#code_sample').prop('disabled', true);
                $('#code_sample_form_group').hide();

                // IMPORTANT: Disable original code sample inputs to prevent duplication
                $('#input_code_sample_kimia').prop('disabled', true);
                $('#input_code_sample_mikro').prop('disabled', true);

                // Clone the form group twice
                var clone1 = $('#code_sample_form_group').clone(true, true);
                var clone2 = $('#code_sample_form_group').clone(true, true);

                // Get the sample code
                $code_sample_type = $('#jenis_sample').children(":selected").data('code');
                let parsed_sample_code = $('input#code_sample').val().split('/');

                // Modify the first clone - Kimia (lab code: 01)
                let kimia_parsed_sample_code = [...parsed_sample_code];
                kimia_parsed_sample_code[0] += '.01'; // Use .01 for Kimia
                let kimia_sample_code = kimia_parsed_sample_code.join('/');
                clone1.find('label').text('Kode Sample Kimia:');
                clone1.find('input').prop('disabled', false)
                    .attr('id', 'code_sample_kimia')
                    .attr('name', 'code_sample_kimia')
                    .val(kimia_sample_code)
                clone1.show(); // Ensure it's visible

                // Modify the second clone - Mikrobiologi (lab code: 02)
                let mikrobiologi_parsed_sample_code = [...parsed_sample_code];
                mikrobiologi_parsed_sample_code[0] += '.02'; // Use .02 for Mikro
                let mikrobiologi_sample_code = mikrobiologi_parsed_sample_code.join('/');
                clone2.find('label').text('Kode Sample Mikrobiologi:');
                clone2.find('input').prop('disabled', false)
                    .attr('id', 'code_sample_mikrobiologi')
                    .attr('name', 'code_sample_mikro') // Changed to 'code_sample_mikro' to match controller
                    .val(mikrobiologi_sample_code)
                clone2.show(); // Ensure it's visible


                // Append the clones to the form
                $('#code_sample_form_group').after(clone1);
                clone1.after(clone2);
            } else {
                select_multiple_codes = false;

                $('#code_sample').prop('disabled', false);
                $('#code_sample_form_group').show();
                $('#code_sample_form_group').nextAll().remove();

                // Re-enable original code sample inputs when not using clones
                $('#input_code_sample_kimia').prop('disabled', false);
                $('#input_code_sample_mikro').prop('disabled', false);
            }
        }

        function pad(n) {
            var s = "000" + n;
            return s.substr(s.length - 4);
        }

        tinymce.init({
            selector: 'textarea#address_location_pdam',
            height: 50,
            menubar: false,
            plugins: [
                'advlist autolink autosave lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount',
            ],
            toolbar: 'undo redo | bold italic | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('change blur', function() {
                    tinymce.triggerSave();
                });
            }

        });

        window.resolvePriceFromMap = function(prices, sampleTypeId, defaultPrice) {
            var sid = String(sampleTypeId || '').trim();
            var def = parseFloat(defaultPrice);
            if (isNaN(def)) {
                def = 0;
            }
            if (!sid || !prices || typeof prices !== 'object') {
                return def;
            }
            if (Array.isArray(prices)) {
                return def;
            }
            if (prices[sid] != null && prices[sid] !== '') {
                var p0 = parseFloat(prices[sid]);
                if (!isNaN(p0)) {
                    return p0;
                }
            }
            var keys = Object.keys(prices);
            for (var i = 0; i < keys.length; i++) {
                if (String(keys[i]).trim() === sid) {
                    var p1 = parseFloat(prices[keys[i]]);
                    if (!isNaN(p1)) {
                        return p1;
                    }
                }
            }
            return def;
        };

        /**
         * Harga parameter untuk jenis sampel terpilih (ms_method_sample_type_price).
         * Jika jenis sampel kosong → data-default-price (harga global ms_method).
         */
        function resolveMethodPriceForJenisSample(methodId, fallbackPrice) {
            var jenisId = ($('#jenis_sampel').val() || '').trim();
            var $cb = $('.checkbox-' + methodId).first();
            if (!$cb.length) {
                return parseInt(fallbackPrice, 10) || 0;
            }
            var def = parseFloat($cb.attr('data-default-price'));
            if (isNaN(def)) {
                def = parseFloat($cb.attr('data-price')) || 0;
            }
            if (isNaN(def) || def === 0) {
                def = parseFloat(fallbackPrice) || 0;
            }
            var resolved = def;
            if (jenisId) {
                var raw = $cb.attr('data-prices-by-sample-type');
                var prices = {};
                try {
                    prices = raw ? JSON.parse(raw) : {};
                } catch (e) {
                    prices = {};
                }
                resolved = window.resolvePriceFromMap(prices, jenisId, def);
            }
            return Math.round(resolved);
        }

        $("#jenis_sampel").change(async function() {

            jenis_sample = $(this).val();
            var jenis_sample_text = $(this).children(":selected").text();

            let codes = ['input_code_sample_kimia', 'input_code_sample_mikro'];


            // Format: {code_sample_type}.{lab_code}/{number}/{year}
            let codesConfig = [{
                    input: 'input_code_sample_kimia',
                    labCode: '01'
                },
                {
                    input: 'input_code_sample_mikro',
                    labCode: '02'
                }
            ];

            for (let i = 0; i < codes.length; i++) {
                let code = codes[i];
                let inputCodeSampleElement = $('#' + code);
                var code_sample_type = $(this).children(":selected").data('code') /* .toUpperCase() */ || '...';
                let currentValue = $(inputCodeSampleElement).val() || '';

                // Determine lab code
                let labCode = code === 'input_code_sample_mikro' ? '02' : '01';

                // Split by '/' to get parts: ["{code}.{lab}", "number", "year"]
                let parsed_sample_code = currentValue.split('/');

                if (parsed_sample_code.length >= 3) {
                    // Split first part by '.' to get ONLY 2 parts: [code, lab]
                    // Use limit parameter to prevent multiple splits
                    let firstPart = parsed_sample_code[0].split('.', 2);

                    // If firstPart doesn't have 2 elements, initialize them
                    if (firstPart.length === 0) {
                        firstPart = [code_sample_type, labCode];
                    } else if (firstPart.length === 1) {
                        firstPart = [code_sample_type, labCode];
                    } else {
                        // Update both parts
                        firstPart[0] = code_sample_type;
                        firstPart[1] = labCode;
                    }

                    // Reconstruct first part with only 2 elements
                    parsed_sample_code[0] = firstPart[0] + '.' + firstPart[1];
                }

                if (jenis_sample_text.includes("Makanan/Minuman/Lainnya")) {
                    let url = "{{ route('elits-samples.getNewNumberSequence', '#') }}";
                    url = url.replace('#', "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5");
                    url = url + "/#";
                    url = url.replace('#', '{{ $id }}');
                    url = url + "/#";
                    url = url.replace('#', true);
                    $.ajax({
                        url: url,
                        type: "GET",
                        datatype: 'json',
                        success: function(response) {
                            // Update the number part (index 1)
                            parsed_sample_code[1] = pad(parseInt(response) + 1);
                            $(inputCodeSampleElement).val(parsed_sample_code.join('/'));
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseJSON.message);
                        }
                    })
                } else {
                    // Just update the code
                    $(inputCodeSampleElement).val(parsed_sample_code.join('/'));
                }
            }

            if (jenis_sample_text.includes("Rectal Swab (Jasa Boga)") || jenis_sample ==
                "ab516530-aed0-481b-ab9c-86c8ccbcabb3" || jenis_sample_text.includes("Rectal Swab")) {
                $('.is_rectal_swab').show();
            } else {
                $('.is_rectal_swab').hide();
            }

            // if (jenis_sample_text.includes("Uji Usap")) {
            //     $(".jenis_sarana_id").css("display", "block")
            // } else {
            //     $(".jenis_sarana_id").css("display", "none")
            // }

            $(".jenis_makanan_id").css("display", "none")
            methods_sample_type = [];
            price_sample_type = 0;
            $(".checkbox").prop("checked", false);
            $(".checkbox").prop("disabled", false);
            $(".checkbox").attr("data-toggle", "tooltip");
            $(".checkbox").attr("data-placement", "right");
            $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
            $("[data-toggle='tooltip']").tooltip();
            $('#cost_samples').val(0);

            $(".checkbox").prop("readonly", false);

            if (jenis_sample != undefined) {

                var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
                url = url.replace('#', jenis_sample);


                $('#ispacket').val("true")

                $.ajax({
                    url: url,
                    type: "GET",
                    datatype: 'json',
                    success: function(response) {
                        var results = response.data;
                        results.forEach(result => {
                            $(".checkbox-" + result.id_method).prop("disabled", false);
                            $(".checkbox-" + result.id_method).removeAttr("title")
                            $(".checkbox-" + result.id_method).removeAttr("data-toggle");
                            $(".checkbox-" + result.id_method).removeAttr("data-placement");
                            $(".checkbox-" + result.id_method).removeAttr(
                                "data-original-title");

                        })
                    },
                })

                var url = "/api/packet/#"
                url = url.replace('#', jenis_sample);


                $.ajax({
                    url: url,
                    type: "POST",
                    datatype: 'json',
                    success: function(response) {
                        $(".is_paket").css('display', 'block');

                        // Clear existing paket buttons and options
                        $('.packet-buttons-container').empty();
                        $('#packet').find('option').remove().end();

                        var results = response.results;

                        // Show packet section if there are available packets
                        if (results && results.length > 0) {
                            $('.packet').show();

                            // Dynamically create paket buttons
                            results.forEach(result => {
                                // Add button
                                $('.packet-buttons-container').append(
                                    '<button type="button" class="btn btn-outline-success btn-sm btn-pick-paket" data-id="' +
                                    result.id + '">' +
                                    result.text + '</button>'
                                );

                                // Add option to hidden select
                                $('#packet').append('<option value="' + result.id +
                                    '" data-extra="' + result.data_extra + '">' + result
                                    .text + '</option>');
                            });
                        } else {
                            $('.packet').hide();
                        }

                        $("#is_paket").prop('checked', false);

                        $("#packet").change(function() {
                            var packet = $(this).val();
                            var url =
                                "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}"
                            url = url.replace('#', packet);


                            $('#ispacket').val("true")

                            $.ajax({
                                url: url,
                                type: "GET",
                                datatype: 'json',
                                success: function(response) {


                                    methods_sample_type = response.methods;
                                    $(".checkbox").prop("checked", false);
                                    var harga = 0;

                                    // Reset packet parameter tracking
                                    window.packetParameterIds = [];

                                    // Validasi response.data sebelum forEach
                                    if (response.data && Array.isArray(response
                                            .data)) {
                                        response.data.forEach(data => {
                                            harga = harga + resolveMethodPriceForJenisSample(
                                                data['method_id'],
                                                data['price_total_method']
                                            );
                                            $(".checkbox-" + data[
                                                'method_id']).prop(
                                                "checked", true);
                                            $(".checkbox-" + data[
                                                'method_id']).prop(
                                                "readonly", true);

                                            // Track parameter from packet
                                            window.packetParameterIds
                                                .push(data[
                                                    'method_id']);
                                        });
                                    }

                                    if (response['price'] == 0) {
                                        $('#cost_samples').val(harga)
                                        window.packetPrice = harga;
                                    } else {
                                        $('#cost_samples').val(response[
                                            'price'])

                                        price_sample_type = response['price'];
                                        window.packetPrice = response['price'];
                                    }

                                    if (typeof window.applyMethodPricesForJenisSampel === 'function') {
                                        window.applyMethodPricesForJenisSampel($('#jenis_sampel').val() || '');
                                    }

                                    // Trigger collapse & auto-sort update after checkboxes loaded
                                    if (typeof window.updateParameterCounts ===
                                        'function') {
                                        setTimeout(function() {
                                            window
                                                .updateParameterCounts();
                                            window
                                                .moveCheckedParametersToTop();
                                        }, 300);
                                    }


                                    //   var url = "{{ route('elits-packet.index') }}";
                                    //   window.location.href = url;

                                },
                                error: function(XMLHttpRequest, textStatus,
                                    errorThrown) {
                                    alert(XMLHttpRequest.responseJSON.message);
                                }
                            });

                        })
                    },
                })


            }

            if (typeof window.filterParametersByJenisSampel === 'function') {
                setTimeout(function() {
                    window.filterParametersByJenisSampel(jenis_sample);
                }, 150);
            }
        });

        $(document).ready(function() {
            function parsePricesBySampleTypeFromEl(el) {
                var raw = el.getAttribute('data-prices-by-sample-type');
                if (!raw) {
                    return {};
                }
                try {
                    return JSON.parse(raw);
                } catch (e) {
                    return {};
                }
            }

            function fmtRpInt(n) {
                n = parseInt(n, 10) || 0;
                return new Intl.NumberFormat('id-ID').format(n);
            }

            /** Harga per jenis sampel; jika sampletypeId kosong → harga global (data-default-price = ms_method.price_total_method) */
            window.applyMethodPricesForJenisSampel = function(sampletypeId) {
                var sid = String(sampletypeId || '').trim();
                $('.method-row .checkbox').each(function() {
                    var $cb = $(this);
                    var prices = parsePricesBySampleTypeFromEl(this);
                    var def = parseFloat($cb.attr('data-default-price'));
                    if (isNaN(def)) {
                        def = parseFloat($cb.attr('data-price')) || 0;
                    }
                    var resolved = sid ? window.resolvePriceFromMap(prices, sid, def) : def;
                    $cb.attr('data-price', resolved);
                    $cb.data('price', resolved);
                    var parts = String($cb.val() || '').split('_');
                    if (parts.length >= 3) {
                        parts[2] = String(Math.round(resolved));
                        $cb.val(parts.join('_'));
                    }
                    var idm = String($cb.attr('data-idmethod') || '').trim();
                    var idlabs = String($cb.attr('data-idlabs') || '').trim();
                    $('#input_price_method_' + idm + '_' + idlabs).val(resolved);
                    var $priceSpan = $cb.closest('label').find('span.method-param-price');
                    if ($priceSpan.length) {
                        $priceSpan.text('(Rp ' + fmtRpInt(resolved) + ')');
                    }
                });
                $('.checkbox:checked').trigger('change');
            };

            window.applyTabMethodPricesForSampleType = function(sampleTypeId) {
                var sid = String(sampleTypeId || '').trim();
                if (!sid) {
                    return;
                }
                $('.method-checkbox-tab').each(function() {
                    var $cb = $(this);
                    if (String($cb.attr('data-sample-type-id') || '').trim() !== sid) {
                        return;
                    }
                    var prices = parsePricesBySampleTypeFromEl(this);
                    var def = parseFloat($cb.attr('data-default-price'));
                    if (isNaN(def)) {
                        def = parseFloat($cb.attr('data-price')) || 0;
                    }
                    var resolved = window.resolvePriceFromMap(prices, sid, def);
                    $cb.attr('data-price', resolved);
                    $cb.data('price', resolved);
                    var parts = String($cb.attr('data-method') || '').split('_');
                    if (parts.length >= 3) {
                        parts[2] = String(Math.round(resolved));
                        $cb.attr('data-method', parts.join('_'));
                    }
                    var $span = $cb.closest('label').find('span.text-muted');
                    if ($span.length) {
                        $span.text('(Rp ' + fmtRpInt(resolved) + ')');
                    }
                });
            };

            // Function to filter parameters based on selected jenis sampel
            function filterParametersByJenisSampel(sampletypeId) {
                const selectedSampletype = String(sampletypeId || '').trim();
                // Uncheck all checkboxes when jenis sampel changes
                $('.method-row .checkbox').prop('checked', false);

                // Reset harga total
                $('#cost_samples').val('0');

                // Deselect all paket buttons
                $('.btn-pick-paket').removeClass('active');
                $('#packet option').prop('selected', false);

                // Remove old show more buttons
                $('.show-more-btn').remove();

                if (!selectedSampletype) {
                    // Jenis sampel kosong: kembalikan harga ke global (bukan override per jenis sampel)
                    if (typeof window.applyMethodPricesForJenisSampel === 'function') {
                        window.applyMethodPricesForJenisSampel('');
                    }
                    // No jenis sampel selected: sembunyikan semua parameter
                    $('.method-row').hide();
                    $('.method-row .checkbox').prop('disabled', true);
                    // Apply show more to reset view
                    $('.parameter-group').each(function() {
                        applyShowMoreLogic($(this));
                    });
                    return;
                }

                // Enable/disable checkboxes based on baku mutu
                $('.method-row').each(function() {
                    const bakuMutuSampletypes = $(this).data('baku-mutu-sampletypes');
                    const row = $(this);
                    const checkbox = $(this).find('.checkbox');
                    let hasBakuMutu = false;

                    // Check if this parameter has baku mutu for the selected jenis sampel
                    if (bakuMutuSampletypes && Array.isArray(bakuMutuSampletypes) &&
                        bakuMutuSampletypes.length > 0) {
                        hasBakuMutu = bakuMutuSampletypes.some(function(id) {
                            return String(id) === selectedSampletype;
                        });
                    }

                    if (hasBakuMutu) {
                        // Has baku mutu - tampilkan dan enable checkbox
                        row.removeClass('no-baku').show();
                        checkbox.prop('disabled', false);
                    } else {
                        // No baku mutu - jangan tampilkan
                        row.addClass('no-baku').hide();
                        checkbox.prop('disabled', true);
                    }
                });

                // Apply show more logic to each group IMMEDIATELY
                $('.parameter-group').each(function() {
                    const $group = $(this);
                    const maxVisible = 20;
                    const $rows = $group.find('.method-row:visible');

                    // Remove old state
                    $rows.removeClass('hidden-row');

                    // Apply new visibility
                    $rows.each(function(index) {
                        if (index >= maxVisible) {
                            $(this).addClass('hidden-row').hide();
                        } else {
                            $(this).show();
                        }
                    });

                    // Count hidden and add button
                    const hiddenCount = $rows.filter('.hidden-row').length;
                    if (hiddenCount > 0) {
                        const $showMoreBtn = $(
                            '<div class="show-more-btn" style="text-align: center; padding: 10px; cursor: pointer; color: #007bff; font-weight: bold;">' +
                            '<i class="fas fa-chevron-down"></i> Tampilkan ' + hiddenCount +
                            ' parameter lainnya' +
                            '</div>');

                        $showMoreBtn.on('click', function() {
                            $group.find('.method-row.hidden-row:visible').removeClass('hidden-row').show();
                            $(this).remove();
                        });

                        $group.find('.collapse').append($showMoreBtn);
                    }
                });

                // Update counts
                if (typeof window.updateParameterCounts === 'function') {
                    window.updateParameterCounts();
                }

                if (selectedSampletype && typeof window.applyMethodPricesForJenisSampel === 'function') {
                    window.applyMethodPricesForJenisSampel(selectedSampletype);
                }
            }

            window.filterParametersByJenisSampel = filterParametersByJenisSampel;

            // Jenis sampel as cashier-like pick buttons
            $(document).on('click', '.btn-pick-jenis', function() {
                $('.btn-pick-jenis').removeClass('active');
                $(this).addClass('active');
                // Gunakan attr('data-id'), bukan .data('id'), agar UUID tidak di-cast jQuery ke angka.
                const id = String($(this).attr('data-id') || '').trim();
                const code = $(this).attr('data-code') || '';

                $('#jenis_sampel').val(id).trigger('change');

                // Filter parameters based on selected jenis sampel
                filterParametersByJenisSampel(id);

                // Update code samples when jenis changes
                // Format: {code_sample_type}.{lab_code}/{number}/{year}
                // Example: AM.01/0013/2025 (Kimia) or AM.02/0014/2025 (Mikro)
                if (code) {
                    // Update Kimia code
                    let kimiaInput = $('#input_code_sample_kimia');
                    let kimiaValue = kimiaInput.val() || '';
                    if (kimiaValue.includes('/')) {
                        let parts = kimiaValue.split('/');
                        if (parts.length >= 3) {
                            kimiaInput.val(code + '.01/' + parts[1] + '/' + parts[2]);
                        }
                    }

                    // Update Mikro code
                    let mikroInput = $('#input_code_sample_mikro');
                    let mikroValue = mikroInput.val() || '';
                    if (mikroValue.includes('/')) {
                        let parts = mikroValue.split('/');
                        if (parts.length >= 3) {
                            mikroInput.val(code + '.02/' + parts[1] + '/' + parts[2]);
                        }
                    }
                }
            });

            // Auto-detect mode: Paket pick buttons toggle selection
            $(document).on('click', '.btn-pick-paket', function() {
                $(this).toggleClass('active');
                const id = String($(this).attr('data-id') || '').trim();
                const select = $('#packet');
                const option = select.find('option[value="' + id + '"]');
                option.prop('selected', $(this).hasClass('active'));

                // Auto set to paket mode when any paket is clicked
                $('#is_paket').val('true');

                // Trigger change event to load paket parameters via AJAX
                select.trigger('change');
            });

            // Track which parameters are from packet (for distinguishing in cart)
            window.packetParameterIds = [];

            // Auto-detect mode: When parameter checkbox is clicked manually (not from paket)
            $(document).on('change', '.checkbox', function() {
                // Don't switch to satuan mode automatically
                // Allow adding extra parameters when packet is selected
                // Update cart to show the difference
            });
            // $.fn.select2.defaults.set("theme", "classic");
            // $('#jenis_sampel').select2();
        })

        deferCreatePageInit(initializeSampleDatepickers);

        $(document).ready(function() {
            initializeSampleFlatpickr();
            // $.fn.select2.defaults.set("theme", "classic");

            // $('#unitAttributes').select2({
            //     placeholder: "Pilih Unit",
            //     allowClear: true
            // });

            // $('.js-unit-basic-multiple').select2({
            //     placeholder: "Pilih Unit",
            //     allowClear: true,
            //     ajax: {
            //         url: "{{ url('/api/unit/') }}",
            //         method: "post",
            //         dataType: 'json',
            //         params: { // extra parameters that will be passed to ajax
            //             contentType: "application/json;",
            //         },
            //         data: function(term) {
            //             return {
            //                 term: term.term || '',
            //                 page: term.page || 1
            //             };
            //         },
            //         cache: true
            //     }
            // });

            var element2 = document.getElementById('pengawet_others');


            $('input[type=radio][name=pengawet]').change(function() {

                if (this.value == '0') {
                    element2.style.display = 'block';
                } else {
                    element2.style.display = 'none';
                }

            });

            var CSRF_TOKEN = $('#csrf-token').val();

            $("#form-create-sample").validate({
                // in 'rules' user have to specify all the constraints for respective fields
                rules: {
                    jenis_sampel: "required",
                    cost_samples: "required",
                    program_samples: "required",

                },
                // in 'messages' user have to specify message as per rules
                messages: {
                    jenis_sampel: " Masukan Jenis Sample",
                    cost_samples: " Masukan harga",
                    program_samples: " Masukkan Program",
                },
                submitHandler: function(form) {
                    kesmasSyncSpecimenFromReview();
                    var sampleCheckSingle = kesmasValidateManualSampleNumbers();
                    if (!sampleCheckSingle.ok) {
                        swal({
                            title: 'Perhatian',
                            text: sampleCheckSingle.message,
                            icon: 'warning'
                        });
                        return false;
                    }
                    $('.btn-simpan').prop("disabled", true);
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        success: function(response) {
                            if (response.status == true) {


                                swal({
                                        title: "Success!",
                                        text: response.pesan,
                                        icon: "success"
                                    })
                                    .then(function() {
                                        document.location = response.url_redirect;
                                    });
                            } else {
                                var pesan = "";
                                var data_pesan = response.pesan;
                                const wrapper = document.createElement('div');

                                $('.btn-simpan').prop("disabled", false);

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
                                    swal({
                                        title: "Error!",
                                        text: response.pesan,
                                        icon: "warning"
                                    });
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            $('.btn-simpan').prop("disabled", false);

                            var err = eval("(" + xhr.responseText + ")");
                            swal("Error!", err.Message, "error");
                        }
                    })
                }
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            function toggleJenisMakanan() {
                var selectedValue = $('#jenis_sampel').val();
                if (selectedValue === "d34b4a50-4560-4fce-96c3-046c7080a986") {
                    $('#form_jenis_makanan').show();
                    $('#jenis_makanan_minuman').val('');
                } else {
                    $('#form_jenis_makanan').hide();
                    $('#jenis_makanan_minuman').val('');
                }
            }
            $('#jenis_sampel').on('change', toggleJenisMakanan);
            toggleJenisMakanan();
        });

        // Parameter Search and Pagination
        (function() {
            let currentPage = 1;
            let itemsPerPage = 20;
            let allGroups = [];
            let filteredGroups = [];

            function initializeParameterSearch() {
                // Get all parameter groups
                allGroups = Array.from(document.querySelectorAll('.parameter-group'));
                filteredGroups = [...allGroups];

                // Initialize pagination
                updatePagination();

                // Search functionality
                $('#search-parameter').on('keyup', function() {
                    const searchTerm = $(this).val().toLowerCase();
                    filterParameters(searchTerm);
                });

                // Items per page change
                $('#items-per-page').on('change', function() {
                    const value = $(this).val();
                    itemsPerPage = value === 'all' ? filteredGroups.length : parseInt(value);
                    currentPage = 1;
                    updatePagination();
                });
            }

            function filterParameters(searchTerm) {
                if (!searchTerm) {
                    filteredGroups = [...allGroups];
                } else {
                    filteredGroups = allGroups.filter(group => {
                        const category = group.getAttribute('data-category').toLowerCase();
                        const methods = Array.from(group.querySelectorAll('.method-row:not(.no-baku)'));

                        // Check if category matches
                        if (category.includes(searchTerm)) {
                            return true;
                        }

                        // Check if any method name matches
                        const hasMatchingMethod = methods.some(method => {
                            const methodName = method.getAttribute('data-method-name');
                            return methodName && methodName.includes(searchTerm);
                        });

                        if (hasMatchingMethod) {
                            // Show only matching methods within this group
                            methods.forEach(method => {
                                const methodName = method.getAttribute('data-method-name');
                                if (methodName && methodName.includes(searchTerm)) {
                                    $(method).show();
                                } else {
                                    $(method).hide();
                                }
                            });
                            return true;
                        }

                        return false;
                    });
                }

                // Reset to first page after filter
                currentPage = 1;
                updatePagination();
            }

            function updatePagination() {
                const totalGroups = filteredGroups.length;
                const totalPages = Math.ceil(totalGroups / itemsPerPage);

                // Hide all groups first
                allGroups.forEach(group => $(group).hide());

                // Show current page groups
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalGroups);

                for (let i = startIndex; i < endIndex; i++) {
                    const group = filteredGroups[i];
                    $(group).show();

                    // Show all methods in the group if no search term
                    if (!$('#search-parameter').val()) {
                        $(group).find('.method-row:not(.no-baku)').show();
                    }
                }

                // Update showing info
                if (totalGroups === 0) {
                    $('#showing-info').text('Tidak ada parameter ditemukan');
                } else {
                    $('#showing-info').text(
                        `Menampilkan ${startIndex + 1}-${endIndex} dari ${totalGroups} kategori parameter`);
                }

                // Render pagination buttons
                renderPaginationButtons(totalPages);
            }

            function renderPaginationButtons(totalPages) {
                const $pagination = $('#pagination');
                $pagination.empty();

                if (totalPages <= 1) {
                    return;
                }

                // Previous button
                $pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Page numbers
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    $pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                    `);
                    if (startPage > 2) {
                        $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    $pagination.append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                    $pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                        </li>
                    `);
                }

                // Next button
                $pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                `);

                // Add click handlers
                $pagination.find('a.page-link').on('click', function(e) {
                    e.preventDefault();
                    if (!$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                        currentPage = parseInt($(this).data('page'));
                        updatePagination();
                        // Scroll to top of parameters
                        $('html, body').animate({
                            scrollTop: $('#parameters-container').offset().top - 100
                        }, 300);
                    }
                });
            }

            // Initialize on document ready
            $(document).ready(function() {
                initializeParameterSearch();
            });
        })();

        // Parameter Collapse & Auto-Sort Functionality
        $(document).ready(function() {

            // Collapse ALL by default - semua tertutup saat load
            $('.collapse').removeClass('show');

            // Auto-expand first 3 groups untuk menampilkan show more buttons
            $('.parameter-group').slice(0, 3).each(function() {
                $(this).find('.collapse').addClass('show');
                $(this).find('.collapse-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            });

            // Rotate icon on collapse/expand
            $('.parameter-group-header').on('click', function() {
                const icon = $(this).find('.collapse-icon');
                const $group = $(this).closest('.parameter-group');
                setTimeout(function() {
                    if (icon.closest('.parameter-group-header').attr('aria-expanded') === 'true') {
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        // Apply show more when expanded
                        applyShowMoreLogic($group);
                    } else {
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                }, 100);
            });

            // Expand all button
            $('#expand-all-params').on('click', function() {
                $('.collapse').collapse('show');
                $('.collapse-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                // Apply show more to all groups
                $('.parameter-group').each(function() {
                    applyShowMoreLogic($(this));
                });
            });

            // Collapse all button  
            $('#collapse-all-params').on('click', function() {
                $('.collapse').collapse('hide');
                $('.collapse-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            });

            // Update parameter counts in badges (GLOBAL FUNCTION)
            window.updateParameterCounts = function() {
                $('.parameter-group').each(function() {
                    const checkedCount = $(this).find('input[type="checkbox"]:checked').length;
                    $(this).find('.param-count').text(checkedCount);

                    // Change badge color if has checked items
                    if (checkedCount > 0) {
                        $(this).find('.param-count').removeClass('badge-secondary').addClass(
                            'badge-success');
                    } else {
                        $(this).find('.param-count').removeClass('badge-success').addClass(
                            'badge-secondary');
                    }
                });
            }

            // Auto-expand and sort checked parameters to top (GLOBAL FUNCTION)
            window.moveCheckedParametersToTop = function() {
                // Instead of moving to top section, just auto-expand groups with checked items
                let firstCheckedGroup = null;

                $('.parameter-group').each(function() {
                    const $group = $(this);
                    const checkedCount = $group.find('input[type="checkbox"]:checked').length;

                    if (checkedCount > 0) {
                        // Auto-expand this group
                        $group.find('.collapse').addClass('show');
                        $group.find('.collapse-icon').removeClass('fa-chevron-down').addClass(
                            'fa-chevron-up');

                        // Sort: Move checked parameters to top within group
                        const $rows = $group.find('.method-row:not(.no-baku)');
                        const $checkedRows = $rows.filter(function() {
                            return $(this).find('input[type="checkbox"]').is(':checked');
                        });
                        const $uncheckedRows = $rows.filter(function() {
                            return !$(this).find('input[type="checkbox"]').is(':checked');
                        });

                        // Reorder: checked first, then unchecked
                        if ($checkedRows.length > 0) {
                            const $container = $rows.first().parent();
                            $checkedRows.detach().prependTo($container);
                            $uncheckedRows.detach().appendTo($container);
                        }

                        // Apply show more logic: show checked + first 20 items
                        applyShowMoreLogic($group);

                        // Remember first checked group for scrolling
                        if (!firstCheckedGroup) {
                            firstCheckedGroup = $group;
                        }
                    } else {
                        // For groups without checked items, still apply show more
                        applyShowMoreLogic($group);
                    }
                });

                // Scroll to first checked group
                if (firstCheckedGroup) {
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: firstCheckedGroup.offset().top - 150
                        }, 500);
                    }, 100);
                }
            }

            // Show More Logic
            function applyShowMoreLogic($group) {
                const maxVisible = 20;
                const $rows = $group.find('.method-row:not(.no-baku)');
                const $checkedRows = $rows.filter(function() {
                    return $(this).find('input[type="checkbox"]').is(':checked');
                });

                // Hide rows after maxVisible (but keep checked visible)
                $rows.each(function(index) {
                    const $row = $(this);
                    const isChecked = $row.find('input[type="checkbox"]').is(':checked');

                    if (index >= maxVisible && !isChecked) {
                        $row.addClass('hidden-row').hide();
                    } else {
                        $row.removeClass('hidden-row').show();
                    }
                });

                // Count hidden rows
                const hiddenCount = $group.find('.method-row:not(.no-baku).hidden-row').length;

                // Remove existing show more button
                $group.find('.show-more-btn').remove();

                // Add show more button if needed
                if (hiddenCount > 0) {
                    const $showMoreBtn = $(
                        '<div class="show-more-btn" style="text-align: center; padding: 10px; cursor: pointer; color: #007bff; font-weight: bold;">' +
                        '<i class="fas fa-chevron-down"></i> Tampilkan ' + hiddenCount + ' parameter lainnya' +
                        '</div>');

                    $showMoreBtn.on('click', function() {
                        $group.find('.method-row:not(.no-baku).hidden-row').removeClass('hidden-row').show();
                        $(this).remove();
                    });

                    $group.find('.collapse').append($showMoreBtn);
                }
            }

            // Listen to checkbox changes
            $(document).on('change', 'input[type="checkbox"]', function() {
                window.updateParameterCounts();
                window.moveCheckedParametersToTop();

                // Auto-expand group that has checked items
                const $group = $(this).closest('.parameter-group');
                const checkedCount = $group.find('input[type="checkbox"]:checked').length;

                if (checkedCount > 0) {
                    $group.find('.collapse').collapse('show');
                    $group.find('.collapse-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }

                // Update cart widget
                updateCartWidget();
            });

            // Format currency helper
            function formatRupiah(amount) {
                return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
            }

            // Update Cart Widget Function
            function updateCartWidget() {
                const $checkedParams = $('.checkbox:checked');
                const $cartList = $('#cart-items-list');
                const $emptyState = $('#cart-empty-state');

                // Check if from packet - get from select option (more accurate)
                const isPacket = $('#is_paket').val() === 'true';
                const $selectedPacketOption = $('#packet option:selected');
                const packetName = $selectedPacketOption.length > 0 ? $selectedPacketOption.text().trim() : '';

                if (isPacket && packetName) {
                    $('#cart-packet-info').show();
                    $('#cart-packet-name').html(`<strong class="text-info">${packetName}</strong>`);
                } else {
                    $('#cart-packet-info').hide();
                }

                // Clear cart list
                $cartList.empty();

                if ($checkedParams.length === 0) {
                    // Show empty state
                    $cartList.html(`
                        <div class="text-center text-muted py-5" id="cart-empty-state">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada parameter dipilih</p>
                            <small>Centang parameter untuk menambahkan</small>
                        </div>
                    `);
                    $('#cart-total-items').text('0');
                    $('#cart-total-price').text('Rp 0');
                    return;
                }

                let totalPrice = 0;
                let cartHTML = '';

                // Separate parameters from packet and additional (satuan)
                let packetParams = [];
                let additionalParams = [];
                let additionalPrice = 0;

                $checkedParams.each(function() {
                    const $checkbox = $(this);
                    const methodName = $checkbox.closest('.method-row').find('label').text().trim();
                    const price = parseInt($checkbox.data('price')) || 0;
                    const methodId = $checkbox.data('idmethod');
                    const categoryName = $checkbox.closest('.parameter-group').find(
                        '.parameter-group-header h5').text().trim();

                    const paramData = {
                        methodId: methodId,
                        methodName: methodName,
                        price: price,
                        categoryName: categoryName
                    };

                    // Check if from packet
                    if (window.packetParameterIds && window.packetParameterIds.includes(methodId)) {
                        packetParams.push(paramData);
                    } else {
                        additionalParams.push(paramData);
                        additionalPrice += price; // Sum only additional params
                    }
                });

                // Calculate total price
                if (packetParams.length > 0 && window.packetPrice) {
                    // Use packet price (not sum of individual)
                    totalPrice = parseInt(window.packetPrice) + additionalPrice;
                } else {
                    // Pure satuan mode (no packet)
                    totalPrice = additionalPrice;
                }

                // Build HTML for packet parameters
                if (packetParams.length > 0) {
                    packetParams.forEach(param => {
                        cartHTML += `
                            <div class="cart-item" data-method-id="${param.methodId}">
                                <button type="button" class="cart-item-remove" data-method-id="${param.methodId}" title="Hapus">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="cart-item-category">
                                    <i class="fas fa-flask"></i> ${param.categoryName}
                                    <span class="cart-packet-badge">Paket</span>
                                </div>
                                <div class="cart-item-name">${param.methodName}</div>
                                <div class="cart-item-price">${formatRupiah(param.price)}</div>
                            </div>
                        `;
                    });
                }

                // Build HTML for additional (satuan) parameters
                if (additionalParams.length > 0) {
                    additionalParams.forEach(param => {
                        cartHTML += `
                            <div class="cart-item" data-method-id="${param.methodId}">
                                <button type="button" class="cart-item-remove" data-method-id="${param.methodId}" title="Hapus">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="cart-item-category">
                                    <i class="fas fa-flask"></i> ${param.categoryName}
                                    <span class="badge badge-warning badge-sm ml-1">Satuan</span>
                                </div>
                                <div class="cart-item-name">${param.methodName}</div>
                                <div class="cart-item-price">${formatRupiah(param.price)}</div>
                            </div>
                        `;
                    });
                }

                $cartList.html(cartHTML);
                $('#cart-total-items').text($checkedParams.length);

                // Update price breakdown if packet exists
                if (packetParams.length > 0 && window.packetPrice) {
                    let breakdownHTML = `
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-box"></i> Harga Paket: ${formatRupiah(window.packetPrice)}
                        </small>
                    `;
                    if (additionalParams.length > 0) {
                        breakdownHTML += `
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-plus"></i> Satuan Tambahan: ${formatRupiah(additionalPrice)}
                            </small>
                        `;
                    }
                    $('#cart-price-breakdown').html(breakdownHTML).show();
                } else {
                    $('#cart-price-breakdown').hide();
                }

                $('#cart-total-price').text(formatRupiah(totalPrice));

                // Sync with form field
                $('#cost_samples').val(totalPrice);
            }

            // Remove item from cart
            $(document).on('click', '.cart-item-remove', function(e) {
                e.preventDefault();
                const methodId = $(this).data('method-id');

                // Uncheck the corresponding checkbox
                $(`.checkbox[data-idmethod="${methodId}"]`).prop('checked', false).trigger('change');
            });

            // Clear all cart items
            $('#cart-clear-all').on('click', function() {
                if ($('.checkbox:checked').length === 0) return;

                if (confirm('Hapus semua parameter terpilih?')) {
                    $('.checkbox:checked').prop('checked', false);
                    updateCartWidget();
                    window.updateParameterCounts();
                }
            });

            // Function to update sample code visibility based on selected parameters
            function updateSampleCodeVisibility() {
                const checkedParams = $('.checkbox:checked');
                let hasKimia = false;
                let hasMikro = false;

                // Check each selected parameter
                checkedParams.each(function() {
                    const $row = $(this).closest('.method-row');
                    const $group = $row.closest('.parameter-group');
                    const groupTitle = $group.find('.parameter-group-header h5').text().trim()
                        .toLowerCase();

                    // Determine if parameter is Kimia or Mikro based on group title
                    if (groupTitle.includes('kimia')) {
                        hasKimia = true;
                    } else if (groupTitle.includes('mikro')) {
                        hasMikro = true;
                    }
                });

                // Update visibility and layout
                const $kimiaWrapper = $('#code_sample_kimia_wrapper_top');
                const $mikroWrapper = $('#code_sample_mikro_wrapper_top');

                // If no parameters selected, show both (initial state)
                if (checkedParams.length === 0) {
                    $kimiaWrapper.removeClass('col-lg-12').addClass('col-lg-6').show();
                    $mikroWrapper.removeClass('col-lg-12').addClass('col-lg-6').show();
                } else if (hasKimia && hasMikro) {
                    // Both: show both with col-lg-6
                    $kimiaWrapper.removeClass('col-lg-12').addClass('col-lg-6').show();
                    $mikroWrapper.removeClass('col-lg-12').addClass('col-lg-6').show();
                } else if (hasKimia) {
                    $kimiaWrapper.removeClass('col-lg-6').addClass('col-lg-12').show();
                    $mikroWrapper.hide();
                } else if (hasMikro) {
                    $kimiaWrapper.hide();
                    $mikroWrapper.removeClass('col-lg-6').addClass('col-lg-12').show();
                }
            }

            // Listen to checkbox changes
            $(document).on('change', '.checkbox', function() {
                updateSampleCodeVisibility();
            });

            // Initial update
            window.updateParameterCounts();
            window.moveCheckedParametersToTop();
            updateSampleCodeVisibility();

            // Apply show more to all groups on initial load (with delay to ensure DOM ready)
            setTimeout(function() {
                $('.parameter-group').each(function() {
                    const $group = $(this);
                    // Always apply show more logic, even if collapsed
                    applyShowMoreLogic($group);
                });
            }, 500);

            // Listen to packet selection (if exists)
            $('#packet').on('change', function() {
                setTimeout(function() {
                    window.updateParameterCounts();
                    // Update sample code visibility after packet parameters are loaded
                    updateSampleCodeVisibility();
                    window.moveCheckedParametersToTop();
                    updateCartWidget(); // Update cart widget after packet selection
                }, 500); // Give time for checkboxes to update
            });

            // ============================================
            // MULTIPLE SAMPLE TYPES FUNCTIONALITY
            // ============================================
            // Make these global so they can be accessed from updateNextStepButton()
            window.selectedSampleTypes = window.selectedSampleTypes || [];
            window.sampleTypeConfigs = window.sampleTypeConfigs || {}; // Store paket & parameters per sample type
            const selectedSampleTypes = window.selectedSampleTypes;
            const sampleTypeConfigs = window.sampleTypeConfigs;
            let currentSequenceNumber = 0; // Current global sequence number
            let sequenceCounter = 0; // Counter for preview (starts from current + 1)
            const sampleCodeSequenceMap = {}; // Track sequence per sample_type + lab combination
            const sequenceOrder = []; // Track order of clicks: [{sampleTypeId, labId, sequenceNumber}]

            // Get current sequence number on page load (deferred)
            deferCreatePageInit(function() {
            $.ajax({
                url: "{{ route('elits-samples.getCurrentSequence') }}",
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        currentSequenceNumber = response.current_number || 0;
                        sequenceCounter = currentSequenceNumber; // Start counter from current number
                    }
                },
                error: function(xhr) {
                    console.error('Error getting current sequence number:', xhr);
                }
            });
            });

            // Handle jenis sampel button click (Multiple Selection)
            $(document).on('click', '.btn-pick-jenis', function() {
                var sampleTypeId = String($(this).attr('data-id') || '').trim();
                var sampleTypeCode = $(this).attr('data-code');
                var sampleTypeName = $(this).attr('data-name');
                var $button = $(this);
                var $checkIcon = $button.find('.jenis-check-icon');

                // Toggle selection
                var index = selectedSampleTypes.findIndex(function(item) {
                    return item.id === sampleTypeId;
                });

                if (index > -1) {
                    // Deselect
                    selectedSampleTypes.splice(index, 1);
                    delete sampleTypeConfigs[sampleTypeId];
                    $button.removeClass('active').css({
                        'background': 'white',
                        'border-color': '#e2e8f0',
                        'color': '#2d3748'
                    });
                    $checkIcon.hide();
                } else {
                    // Select
                    selectedSampleTypes.push({
                        id: sampleTypeId,
                        code: sampleTypeCode,
                        name: sampleTypeName
                    });

                    // Initialize config for this sample type
                    sampleTypeConfigs[sampleTypeId] = {
                        packets: [], // Array of packets: [{packet_id, packet_name, packet_price, methods}]
                        additional_methods: [], // Methods selected individually (not from packet)
                        cost: 0
                    };

                    $button.addClass('active').css({
                        'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        'border-color': '#667eea',
                        'color': 'white'
                    });
                    $checkIcon.show();
                }

                // Update display and regenerate tabs
                updateSelectedSampleTypesDisplay();
                generateSampleTypeTabs();
                updateSampleCodeCards();
                updateNextStepButton(); // Update Next Step button visibility

                // Show/hide paket & parameter section
                if (selectedSampleTypes.length > 0) {
                    $('#paket-parameter-section').show();
                } else {
                    $('#paket-parameter-section').hide();
                    $('#selected-sampletypes-container').hide();
                }
            });

            // Update selected sample types display
            function updateSelectedSampleTypesDisplay() {
                if (selectedSampleTypes.length > 0) {
                    var badgesHtml = '';
                    selectedSampleTypes.forEach(function(type) {
                        var config = sampleTypeConfigs[type.id] || {};
                        // Count includes all packet methods and additional methods
                        var totalPacketMethods = 0;
                        if (config.packets && config.packets.length > 0) {
                            config.packets.forEach(function(p) {
                                totalPacketMethods += (p.methods || []).length;
                            });
                        }
                        var additionalMethodsCount = (config.additional_methods || []).length;
                        var paramCount = totalPacketMethods + additionalMethodsCount;

                        // Only show badge count if there are parameters selected
                        var countBadgeHtml = '';
                        if (paramCount > 0) {
                            countBadgeHtml =
                                `<span id="count-${type.id}" class="badge badge-light ml-2" style="background: rgba(255,255,255,0.3); color: white; font-weight: 700; padding: 5px 10px; border-radius: 6px;">${paramCount}</span>`;
                        }

                        badgesHtml += `
                            <span class="badge badge-lg" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                       color: white; padding: 10px 15px; border-radius: 8px; font-size: 16px; margin-right: 10px; margin-bottom: 10px;">
                                <i class="fa fa-vial"></i> ${type.code} - ${type.name}
                                ${countBadgeHtml}
                            </span>
                        `;
                    });
                    $('#selected-sampletypes-badges').html(badgesHtml);
                    $('#selected-sampletypes-container').show();
                } else {
                    $('#selected-sampletypes-container').hide();
                }
            }

            // Generate sample code based on format: {kode_jenis_sample}.{kode_lab}/{urutan_sample}/{tahun}
            // Urutan berdasarkan urutan klik dari jenis sampel dan labnya
            // Note: Sequence number should already be assigned when parameter is selected
            function generateSampleCode(sampleTypeId, sampleTypeCode, labIds) {
                if (!labIds || labIds.length === 0) {
                    return '';
                }

                // Map lab IDs to lab codes (01 for kimia, 02 for mikro)
                var labCodeMap = {};
                @if (isset($lab_keys))
                    @foreach ($lab_keys as $lab_name => $lab_id)
                        labCodeMap['{{ $lab_id }}'] = '{{ $lab_name === 'kimia' ? '01' : '02' }}';
                    @endforeach
                @endif

                // Generate code for each lab
                // Urutan sudah di-assign saat parameter dipilih, hanya ambil dari map
                var codes = [];
                labIds.forEach(function(labId) {
                    var labCode = labCodeMap[labId] || '01'; // Default to 01 if not found

                    // Create unique key for this sample_type + lab combination
                    var sequenceKey = sampleTypeId + '_' + labId;

                    // Get sequence number (should already be assigned)
                    var sequenceNumber = sampleCodeSequenceMap[sequenceKey];

                    if (sequenceNumber) {
                        var year = new Date().getFullYear();
                        var sequencePadded = String(sequenceNumber).padStart(4, '0');

                        var code = sampleTypeCode + '.' + labCode + '/' + sequencePadded + '/' + year;
                        codes.push(code);
                    }
                });

                // Return first code (or combine if multiple labs - for now return first)
                return codes.length > 0 ? codes[0] : '';
            }

            // Update sample code cards based on selected sample types
            function updateSampleCodeCards() {
                var $dynamicContainer = $('#dynamic-sample-codes-container');
                var $legacyKimia = $('#code_sample_kimia_wrapper_top');
                var $legacyMikro = $('#code_sample_mikro_wrapper_top');

                // If multiple sample types selected, show dynamic cards
                if (selectedSampleTypes.length > 0) {
                    // Hide legacy cards
                    $legacyKimia.hide();
                    $legacyMikro.hide();

                    // Generate dynamic cards
                    var cardsHtml = '';
                    var colClass = 'col-lg-6'; // Default: 2 columns

                    // Adjust column class based on number of sample types
                    if (selectedSampleTypes.length === 1) {
                        colClass = 'col-lg-12';
                    } else if (selectedSampleTypes.length === 2) {
                        colClass = 'col-lg-6';
                    } else if (selectedSampleTypes.length === 3) {
                        colClass = 'col-lg-4';
                    } else if (selectedSampleTypes.length >= 4) {
                        colClass = 'col-lg-3';
                    }

                    // Color gradients for different sample types
                    var colorGradients = [{
                            start: '#11998e',
                            end: '#38ef7d'
                        }, // Green (Kimia-like)
                        {
                            start: '#667eea',
                            end: '#764ba2'
                        }, // Purple (Mikro-like)
                        {
                            start: '#f093fb',
                            end: '#f5576c'
                        }, // Pink
                        {
                            start: '#4facfe',
                            end: '#00f2fe'
                        }, // Blue
                        {
                            start: '#43e97b',
                            end: '#38f9d7'
                        }, // Teal
                        {
                            start: '#fa709a',
                            end: '#fee140'
                        }, // Orange-Pink
                    ];

                    selectedSampleTypes.forEach(function(type, index) {
                        var colors = colorGradients[index % colorGradients.length];
                        var sampleTypeIdClean = type.id.replace(/-/g, '');

                        // Generate sample code
                        var config = sampleTypeConfigs[type.id] || {};
                        var allMethods = [];
                        if (config.methods) {
                            allMethods = allMethods.concat(config.methods);
                        }
                        if (config.additional_methods) {
                            config.additional_methods.forEach(function(m) {
                                allMethods.push(m.method);
                            });
                        }

                        // Extract lab IDs from methods
                        var labIds = [];
                        allMethods.forEach(function(methodString) {
                            var parts = methodString.split('_');
                            if (parts.length >= 2 && !labIds.includes(parts[1])) {
                                labIds.push(parts[1]);
                            }
                        });

                        // Generate codes for each lab
                        var sampleCodes = [];
                        if (labIds.length > 0) {
                            // Map lab IDs to lab codes
                            var labCodeMap = {};
                            @if (isset($lab_keys))
                                @foreach ($lab_keys as $lab_name => $lab_id)
                                    labCodeMap['{{ $lab_id }}'] =
                                        '{{ $lab_name === 'kimia' ? '01' : '02' }}';
                                @endforeach
                            @endif

                            labIds.forEach(function(labId) {
                                var labCode = labCodeMap[labId] || '01';
                                var sequenceKey = type.id + '_' + labId;
                                var sequenceNumber = sampleCodeSequenceMap[sequenceKey];

                                if (sequenceNumber) {
                                    var year = new Date().getFullYear();
                                    var sequencePadded = String(sequenceNumber).padStart(4, '0');
                                    var code = type.code + '.' + labCode + '/' + sequencePadded +
                                        '/' + year;
                                    sampleCodes.push({
                                        code: code,
                                        labCode: labCode,
                                        labName: labCode === '01' ? 'Kimia' :
                                            'Mikrobiologi',
                                        labId: labId
                                    });
                                }
                            });
                        }

                        // Sort sampleCodes: Kimia first (01), then Mikro (02)
                        sampleCodes.sort(function(a, b) {
                            return a.labCode.localeCompare(b.labCode);
                        });

                        // If multiple labs, create separate cards side by side
                        if (sampleCodes.length > 1) {
                            // Create a wrapper row for multiple lab codes
                            cardsHtml +=
                                `<div class="col-12 mb-3" id="code_sample_${sampleTypeIdClean}_wrapper_row"><div class="row">`;

                            sampleCodes.forEach(function(codeData, codeIndex) {
                                var labColors = codeData.labCode === '01' ? {
                                    start: '#11998e',
                                    end: '#38ef7d'
                                } : {
                                    start: '#667eea',
                                    end: '#764ba2'
                                };

                                cardsHtml += `
                                    <div class="col-lg-6 mb-3 px-2" id="code_sample_${sampleTypeIdClean}_${codeData.labCode}_wrapper" style="display: flex;">
                                        <div class="card w-100"
                                            style="border: 2px solid ${labColors.start}; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column;">
                                            <div class="card-header"
                                                style="background: linear-gradient(135deg, ${labColors.start} 0%, ${labColors.end} 100%); padding: 12px 18px; flex-shrink: 0;">
                                                <h6 class="mb-0" style="color: white; font-weight: 600; font-size: 16px;">
                                                    <i class="fa fa-vial"></i> ${type.code} - ${codeData.labName}
                                                </h6>
                                            </div>
                                            <div class="card-body" style="padding: 18px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                                                <div class="form-group mb-2">
                                                    <input type="text" 
                                                        class="form-control form-control-lg code-sample-input" 
                                                        name="code_sample_${sampleTypeIdClean}_${codeData.labCode}"
                                                        id="input_code_sample_${sampleTypeIdClean}_${codeData.labCode}" 
                                                        data-sample-type-id="${type.id}"
                                                        data-sample-type-code="${type.code}"
                                                        data-lab-code="${codeData.labCode}"
                                                        placeholder="${window.kesmasIsNomorSampelManual ? 'Isi kode sampel (manual)' : 'Kode akan di-generate otomatis'}"
                                                        value="${codeData.code}"
                                                        ${window.kesmasIsNomorSampelManual ? '' : 'readonly'}
                                                        style="border: 2px solid ${labColors.start}; border-radius: 8px; font-weight: 700; font-size: 17px; text-align: center; letter-spacing: 1.5px; background: #ffffff; color: ${labColors.start}; padding: 12px 8px; min-height: 50px; width: 100%;">
                                                </div>
                                                <small class="text-muted d-block text-center" style="font-size: 13px; line-height: 1.4; margin-top: auto;">
                                                    <i class="fa fa-info-circle"></i> ${window.kesmasIsNomorSampelManual ? 'Kode sampel (dapat diubah)' : 'Kode sampel preview'}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            cardsHtml += `</div></div>`;
                        } else if (sampleCodes.length === 1) {
                            // Single lab code
                            var codeData = sampleCodes[0];
                            var codesDisplay = codeData.code;

                            cardsHtml += `
                                <div class="${colClass} mb-3 px-2" id="code_sample_${sampleTypeIdClean}_wrapper" style="display: flex;">
                                    <div class="card w-100"
                                        style="border: 2px solid ${colors.start}; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column;">
                                        <div class="card-header"
                                            style="background: linear-gradient(135deg, ${colors.start} 0%, ${colors.end} 100%); padding: 12px 18px; flex-shrink: 0;">
                                            <h6 class="mb-0" style="color: white; font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <i class="fa fa-vial"></i> Kode Sampel ${type.code} - ${type.name}
                                            </h6>
                                        </div>
                                        <div class="card-body" style="padding: 18px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                                            <div class="form-group mb-2">
                                                <input type="text" 
                                                    class="form-control form-control-lg code-sample-input" 
                                                    name="code_sample_${sampleTypeIdClean}"
                                                    id="input_code_sample_${sampleTypeIdClean}" 
                                                    data-sample-type-id="${type.id}"
                                                    data-sample-type-code="${type.code}"
                                                    placeholder="${window.kesmasIsNomorSampelManual ? 'Isi kode sampel (manual)' : 'Kode akan di-generate otomatis'}"
                                                    value="${codesDisplay}"
                                                    ${window.kesmasIsNomorSampelManual ? '' : 'readonly'}
                                                    style="border: 2px solid ${colors.start}; border-radius: 8px; font-weight: 700; font-size: 17px; text-align: center; letter-spacing: 1.5px; background: #ffffff; color: ${colors.start}; padding: 12px 8px; min-height: 50px; width: 100%;">
                                            </div>
                                            <small class="text-muted d-block text-center" style="font-size: 13px; line-height: 1.4; margin-top: auto;">
                                                <i class="fa fa-info-circle"></i> ${window.kesmasIsNomorSampelManual ? 'Kode sampel (dapat diubah)' : 'Kode sampel preview'}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            // No codes yet
                            cardsHtml += `
                                <div class="${colClass} mb-3 px-2" id="code_sample_${sampleTypeIdClean}_wrapper" style="display: flex;">
                                    <div class="card w-100"
                                        style="border: 2px solid ${colors.start}; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column;">
                                        <div class="card-header"
                                            style="background: linear-gradient(135deg, ${colors.start} 0%, ${colors.end} 100%); padding: 12px 18px; flex-shrink: 0;">
                                            <h6 class="mb-0" style="color: white; font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <i class="fa fa-vial"></i> Kode Sampel ${type.code} - ${type.name}
                                            </h6>
                                        </div>
                                        <div class="card-body" style="padding: 18px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                                            <div class="form-group mb-2">
                                                <input type="text" 
                                                    class="form-control form-control-lg code-sample-input" 
                                                    name="code_sample_${sampleTypeIdClean}"
                                                    id="input_code_sample_${sampleTypeIdClean}" 
                                                    data-sample-type-id="${type.id}"
                                                    data-sample-type-code="${type.code}"
                                                    placeholder="${window.kesmasIsNomorSampelManual ? 'Isi kode sampel (manual)' : 'Kode akan di-generate otomatis'}"
                                                    value=""
                                                    ${window.kesmasIsNomorSampelManual ? '' : 'readonly'}
                                                    style="border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 700; font-size: 17px; text-align: center; letter-spacing: 1.5px; background: #f8f9fa; color: #6c757d; padding: 12px 8px; min-height: 50px; width: 100%;">
                                            </div>
                                            <small class="text-muted d-block text-center" style="font-size: 13px; line-height: 1.4; margin-top: auto;">
                                                <i class="fa fa-info-circle"></i> ${window.kesmasIsNomorSampelManual ? 'Kode sampel (dapat diubah)' : 'Pilih parameter untuk generate kode'}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });

                    $dynamicContainer.html(cardsHtml).hide(); // Hide container but keep inputs for form submission
                    if (window.kesmasIsNomorSampelManual) {
                        $dynamicContainer.find('input[id^="input_code_sample_"]').each(function() {
                            var id = this.id;
                            if (!id) {
                                return;
                            }
                            $(this).replaceWith($('<input type="hidden">').attr('id', id).val(''));
                        });
                    }
                } else {
                    // Show legacy cards if no multiple selection
                    $dynamicContainer.hide();
                    $legacyKimia.show();
                    $legacyMikro.show();
                }
            }

            // Generate tabs for each selected sample type
            function generateSampleTypeTabs() {
                if (selectedSampleTypes.length === 0) {
                    $('#sampleTypeTabs').html('');
                    $('#sampleTypeTabsContent').html('');
                    return;
                }

                var tabsHtml = '';
                var contentHtml = '';

                selectedSampleTypes.forEach(function(type, index) {
                    var isActive = index === 0 ? 'active' : '';
                    var tabId = 'tab-' + type.id.replace(/-/g, '');

                    // Tab navigation
                    var config = sampleTypeConfigs[type.id] || {};
                    var paramCount = (config.methods || []).length + (config.additional_methods || [])
                        .length;
                    var countBadgeHtml = paramCount > 0 ?
                        `<span id="count-${type.id}" class="badge badge-primary ml-2">${paramCount}</span>` :
                        '';

                    var titikWajibJenis = typeof sampleTypeRequiresTitikPengambilan === 'function' ?
                        sampleTypeRequiresTitikPengambilan(type) : false;
                    var titikInputEnabled = (config.packets && config.packets.length > 0) ||
                        (config.additional_methods && config.additional_methods.length > 0);
                    var titikLabelSuffix = titikWajibJenis ?
                        '<small class="text-danger">*</small> <small class="text-muted">Wajib</small>' :
                        '<small class="text-muted">(Opsional)</small>';
                    var titikHelperText = titikInputEnabled ?
                        `<i class="fa fa-info-circle"></i> Lokasi pengambilan sampel untuk jenis sampel <strong>${type.name}</strong>` :
                        '<i class="fa fa-info-circle"></i> Pilih paket atau parameter terlebih dahulu, lalu isi titik pengambilan.';

                    tabsHtml += `
                        <li class="nav-item">
                            <a class="nav-link ${isActive}" id="${tabId}-tab" data-toggle="tab" 
                               href="#${tabId}" role="tab" aria-controls="${tabId}" aria-selected="${index === 0}">
                                ${type.code} - ${type.name}
                                ${countBadgeHtml}
                            </a>
                        </li>
                    `;

                    // Tab content
                    contentHtml += `
                        <div class="tab-pane fade show ${isActive}" id="${tabId}" role="tabpanel" 
                             aria-labelledby="${tabId}-tab" data-sample-type-id="${type.id}">
                            <div class="row">
                                <div class="col-lg-8">

                                    <!-- Banner: muncul saat belum ada paket/parameter, letak di atas label Pilih Paket -->
                                    <div class="titik-locked-banner" id="titik-locked-${type.id}"
                                        style="${titikInputEnabled ? 'display:none;' : ''}
                                            background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
                                            border: 2px dashed #f59e0b;
                                            border-radius: 12px;
                                            padding: 16px 18px;
                                            margin-bottom: 18px;
                                            display: flex;
                                            align-items: flex-start;
                                            gap: 12px;">
                                        <div style="flex-shrink: 0; width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                                                    border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-lock" style="color: white; font-size: 16px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: #92400e; font-size: 14px; margin-bottom: 4px;">
                                                <i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>Pilih Paket atau Parameter Dahulu
                                            </div>
                                            <div style="color: #78350f; font-size: 13px; line-height: 1.5;">
                                                Titik pengambilan <strong>${type.code} - ${type.name}</strong> baru bisa diisi
                                                setelah Anda memilih minimal <strong>1 paket</strong> atau <strong>1 parameter</strong> di bawah.
                                            </div>
                                            <div style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                                                <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; border:1px solid #f59e0b; border-radius:20px; padding:3px 10px; font-size:12px; color:#92400e;">
                                                    <i class="fa fa-cube"></i> Pilih Paket
                                                </span>
                                                <span style="font-size:12px; color:#b45309; line-height:22px;">atau</span>
                                                <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; border:1px solid #f59e0b; border-radius:20px; padding:3px 10px; font-size:12px; color:#92400e;">
                                                    <i class="fa fa-check-square"></i> Centang Parameter
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4 paket-section-tab" id="paket-section-${type.id}">
                                        <div class="d-flex align-items-center justify-content-between mb-2" style="margin-top:4px;">
                                            <label class="paket-section-label-tab mb-0" id="paket-label-${type.id}"><i class="fa fa-cube"></i> Pilih Paket (Opsional)</label>
                                            <button type="button" class="btn btn-sm btn-success btn-tambah-paket"
                                                data-sample-type-id="${type.id}"
                                                data-sample-type-name="${type.name}"
                                                style="font-size:12px; padding:4px 10px;"
                                                title="Tambah paket baru untuk jenis sampel ini">
                                                <i class="fa fa-plus"></i> Tambah Paket
                                            </button>
                                        </div>
                                        <div class="row packet-buttons-container-tab" data-sample-type-id="${type.id}" style="margin-top: 10px;">
                                            @php $displayedPackets = []; @endphp
                                            @foreach ($packets as $packet)
                                                @if (!in_array($packet->id_packet, $displayedPackets))
                                                    @php $displayedPackets[] = $packet->id_packet; @endphp
                                                    <div class="col-md-6 col-lg-4 mb-3 packet-button-item-tab"
                                                        data-sample-type-id="{{ $packet->sample_type_id ?? '' }}"
                                                        data-packet-id="{{ $packet->id_packet }}" 
                                                        style="display: none;">
                                                        <div style="position:relative;">
                                                            <button type="button" class="btn btn-pick-paket-tab w-100"
                                                                data-sample-type-id="${type.id}"
                                                                data-packet-id="{{ $packet->id_packet }}"
                                                                data-price="{{ $packet->price_total_packet }}"
                                                                data-name="{{ $packet->name_packet }}"
                                                                style="text-align: left; padding: 15px; height: auto; min-height: 80px; border: 2px solid #e2e8f0; background: white; color: #2d3748; border-radius: 8px; transition: all 0.3s;">
                                                                <strong class="paket-name-text">{{ $packet->name_packet }}</strong><br>
                                                                <small style="color: #28a745; font-weight: 500;" class="paket-price-text">
                                                                    <i class="fa fa-tag"></i> Rp {{ number_format($packet->price_total_packet, 0, ',', '.') }}
                                                                </small>
                                                            </button>
                                                            <button type="button" class="btn btn-edit-paket"
                                                                data-packet-id="{{ $packet->id_packet }}"
                                                                data-sample-type-id="{{ $packet->sample_type_id ?? '' }}"
                                                                title="Edit paket ini"
                                                                style="position:absolute; top:5px; right:5px; background:rgba(255,255,255,0.9); border:1px solid #ced4da; border-radius:4px; padding:2px 7px; font-size:11px; cursor:pointer; z-index:2;">
                                                                <i class="fa fa-pencil-alt"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <hr class="paket-section-hr-tab" id="paket-hr-${type.id}" style="margin: 20px 0; border-top: 2px dashed #e2e8f0;">

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0"><i class="fa fa-microscope"></i> Pilih Parameter Pengujian</label>
                                        </div>
                                        <input type="text" class="form-control mb-3 search-parameter-tab" 
                                               data-sample-type-id="${type.id}"
                                               placeholder="🔍 Cari parameter...">
                                        
                                        <div class="parameter-list-tab" data-sample-type-id="${type.id}">
                                            @php $char = 'A'; @endphp
                                            @for ($i = 0; $i < count($data_methods); $i++)
                                                <div class="parameter-group-tab mb-3 parameter-group-item" 
                                                     data-lab-group="{{ $data_methods[$i]->id_lab }}"
                                                     data-lab-name="{{ $data_methods[$i]->name }}"
                                                     data-sample-type-id="${type.id}">
                                                    <div class="d-flex align-items-stretch" style="gap:0;">
                                                        <div class="parameter-group-header flex-grow-1"
                                                             data-toggle="collapse"
                                                             data-target="#lab-${type.id.replace(/-/g, '')}-{{ $data_methods[$i]->id_lab }}"
                                                             style="flex:1;">
                                                            <i class="fa fa-chevron-down collapse-icon"></i>
                                                            <strong>{{ $data_methods[$i]->name }}</strong>
                                                            <span class="param-count-tab" id="count-${type.id.replace(/-/g, '')}-{{ $data_methods[$i]->id_lab }}">0</span>
                                                        </div>
                                                        <div class="d-flex align-items-stretch" style="flex-shrink:0;">
                                                            <button type="button"
                                                                class="btn btn-success btn-tambah-parameter"
                                                                data-lab-id="{{ $data_methods[$i]->id_lab }}"
                                                                data-lab-name="{{ $data_methods[$i]->name }}"
                                                                data-sample-type-id="${type.id}"
                                                                data-sample-type-name="${type.name}"
                                                                style="font-size:12px; padding:4px 10px; border-radius:0; flex-shrink:0;"
                                                                title="Tambah Parameter Baru">
                                                                <i class="fa fa-plus"></i> Tambah Baru
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-info btn-tambah-baku-mutu-exist"
                                                                data-lab-id="{{ $data_methods[$i]->id_lab }}"
                                                                data-lab-name="{{ $data_methods[$i]->name }}"
                                                                data-sample-type-id="${type.id}"
                                                                data-sample-type-name="${type.name}"
                                                                style="font-size:12px; padding:4px 10px; border-radius:0; flex-shrink:0; border-left:0;"
                                                                title="Pilih parameter yang belum punya baku mutu untuk ditambahkan ke sampel">
                                                                <i class="fa fa-plus-square"></i> Tambah Parameter
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-secondary btn-toggle-edit-parameter"
                                                                data-lab-id="{{ $data_methods[$i]->id_lab }}"
                                                                data-sample-type-id="${type.id}"
                                                                style="font-size:12px; padding:4px 10px; border-radius:0 6px 0 0; flex-shrink:0; border-left:0;"
                                                                title="Tampilkan ikon edit pada setiap parameter">
                                                                <i class="fa fa-edit"></i> Edit
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="lab-${type.id.replace(/-/g, '')}-{{ $data_methods[$i]->id_lab }}" class="collapse">
                                                        <div class="card-body" style="background: #f8f9fa;">
                                                            @foreach ($data_methods[$i]->method as $method)
                                                                @php
                                                                    $baku_mutu_sampletypes = $method->baku_mutu_sampletypes ?? [];
                                                                @endphp
                                                                <div class="method-row-tab"
                                                                     data-sample-type-id="${type.id}"
                                                                     data-method-id="{{ $method->id_method }}"
                                                                     data-method-name="{{ strtolower($method->name_method) }}"
                                                                     data-baku-mutu-sampletypes="{{ json_encode($baku_mutu_sampletypes) }}">
                                                                    <label>
                                                                        <input type="checkbox" 
                                                                               class="method-checkbox-tab"
                                                                               data-sample-type-id="${type.id}"
                                                                               data-default-price="{{ $method->price_method }}"
                                                                               data-prices-by-sample-type='@json($method->prices_by_sample_type ?? [])'
                                                                               data-method="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}_{{ $method->price_method }}"
                                                                               data-method-id="{{ $method->id_method }}"
                                                                               data-lab="{{ $data_methods[$i]->id_lab }}"
                                                                               data-labname="{{ $data_methods[$i]->name }}"
                                                                               data-name="{{ $method->name_method }}"
                                                                               data-price="{{ $method->price_method }}">
                                                                        <strong>{{ $method->name_method }}</strong>
                                                                        <span class="text-muted">(Rp {{ number_format($method->price_method, 0, ',', '.') }})</span>
                                                                    </label>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary btn-pencil-edit-method"
                                                                        data-method-id="{{ $method->id_method }}"
                                                                        data-method-name="{{ $method->name_method }}"
                                                                        title="Edit parameter dan harga per jenis sampel">
                                                                        <i class="fa fa-pencil-alt"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                        
                                        <!-- Pagination for parameters -->
                                        <div class="parameter-pagination-tab mt-3" data-sample-type-id="${type.id}" style="display: none;">
                                            <nav aria-label="Parameter pagination">
                                                <ul class="pagination justify-content-center mb-0">
                                                    <li class="page-item disabled" id="prev-page-${type.id.replace(/-/g, '')}">
                                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                                    </li>
                                                    <li class="page-item active" id="page-1-${type.id.replace(/-/g, '')}">
                                                        <a class="page-link" href="#" data-page="1">1</a>
                                                    </li>
                                                    <li class="page-item disabled" id="next-page-${type.id.replace(/-/g, '')}">
                                                        <a class="page-link" href="#">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                            <div class="text-center mt-2">
                                                <small class="text-muted" id="page-info-${type.id.replace(/-/g, '')}">Menampilkan 1-10 dari 0 parameter</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Titik Pengambilan (Per Jenis Sample) — di bawah section parameter -->
                                    <div class="form-group mt-4 titik-pengambilan-wrapper" id="titik-wrapper-${type.id}">
                                        <label for="titik_pengambilan_${type.id}" style="${titikInputEnabled ? '' : 'display:none;'}">
                                            <i class="fa fa-map-marker-alt"></i> Titik Pengambilan
                                            ${titikLabelSuffix}
                                        </label>
                                        <input type="text" class="form-control titik-pengambilan-input-tab" 
                                            id="titik_pengambilan_${type.id}"
                                            data-sample-type-id="${type.id}"
                                            data-titik-wajib="${titikWajibJenis ? '1' : '0'}"
                                            aria-required="${titikWajibJenis ? 'true' : 'false'}"
                                            ${titikInputEnabled ? '' : 'disabled'}
                                            style="${titikInputEnabled ? '' : 'display:none;'}"
                                            placeholder="Misal: Jl. Sudirman No. 123, Kota ABC">
                                        <small class="form-text titik-helper-${type.id}"
                                            style="${titikInputEnabled ? 'color:#6c757d;' : 'display:none;'}">
                                            ${titikHelperText}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-section-card-sample" 
                                         style="position: sticky; top: 20px; padding: 0; overflow: hidden;">
                                        <div class="card-header"
                                             style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px;">
                                            <h5 class="mb-0" style="color: white; font-weight: 600;">
                                                <i class="fas fa-shopping-cart"></i> Parameter Terpilih
                                            </h5>
                                        </div>
                                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                            <div class="cart-items-list-tab" data-sample-type-id="${type.id}">
                                                <div class="text-center text-muted py-5">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>Belum ada parameter dipilih</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer" style="background: #f8f9fa; padding: 20px;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <strong>Total Parameter:</strong>
                                                <span class="badge badge-lg" id="cart-total-items-${type.id}">0</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>Total Harga:</strong>
                                                <span id="cart-total-price-${type.id}" style="font-size: 1.2rem; font-weight: bold; color: #11998e;">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#sampleTypeTabs').html(tabsHtml);
                $('#sampleTypeTabsContent').html(contentHtml);

                // Filter packets and parameters by sample type for each tab
                selectedSampleTypes.forEach(function(type) {
                    filterPacketsBySampleType(type.id);
                    filterParametersBySampleType(type.id);

                    // Initialize pagination for this tab, lalu terapkan ulang harga (pagination tidak mengubah DOM harga)
                    setTimeout(function() {
                        initParameterPagination(type.id);
                        if (typeof window.applyTabMethodPricesForSampleType === 'function') {
                            window.applyTabMethodPricesForSampleType(type.id);
                        }
                    }, 100);
                });

                // Initialize event handler for titik pengambilan input per sample type
                $(document).off('input change', '.titik-pengambilan-input-tab').on('input change',
                    '.titik-pengambilan-input-tab',
                    function() {
                        var sampleTypeId = $(this).data('sample-type-id');
                        var titikPengambilan = $(this).val() || '';

                        if (sampleTypeConfigs[sampleTypeId]) {
                            sampleTypeConfigs[sampleTypeId].titik_pengambilan = titikPengambilan;
                        }

                        // Update next step button visibility after titik pengambilan changes
                        setTimeout(function() {
                            updateNextStepButton();
                        }, 100);
                    });

                updateAllTitikPengambilanState();
            }

            function hasParameterOrPacketForSampleType(sampleTypeId) {
                var cfg = sampleTypeConfigs[sampleTypeId] || {};
                var hasPacket = cfg.packets && cfg.packets.length > 0;
                var hasAdditionalMethods = cfg.additional_methods && cfg.additional_methods.length > 0;
                return !!(hasPacket || hasAdditionalMethods);
            }

            function updateTitikPengambilanState(sampleTypeId, options) {
                var $input = $('#titik_pengambilan_' + sampleTypeId);
                if ($input.length === 0) {
                    return;
                }

                var canInputTitik = hasParameterOrPacketForSampleType(sampleTypeId);
                var $banner = $('#titik-locked-' + sampleTypeId);
                var $wrapper = $('#titik-wrapper-' + sampleTypeId);
                var $label = $wrapper.find('label[for="titik_pengambilan_' + sampleTypeId + '"]');
                var $helper = $wrapper.find('.titik-helper-' + sampleTypeId);

                if (canInputTitik) {
                    $banner.hide();
                    $label.show();
                    $input.prop('disabled', false).removeAttr('title');
                    $input.show();
                    $helper.show();

                    if ($helper.length) {
                        var sampleTypeName = resolveSampleTypeMeta(sampleTypeId).name || '';
                        $helper.html(
                            '<i class="fa fa-map-marker-alt" style="color:#11998e;"></i> Lokasi pengambilan sampel untuk jenis sampel <strong>' +
                            sampleTypeName + '</strong>');
                        $helper.css('color', '#6c757d');
                    }

                    if (options && options.autoFocus && !($input.val() || '').trim()) {
                        setTimeout(function() { $input.trigger('focus'); }, 250);
                    }
                    return;
                }

                $label.hide();
                $input.prop('disabled', true).attr('title', 'Pilih paket atau parameter terlebih dahulu');
                $input.hide();
                $helper.hide();

                if ($banner.css('display') === 'none') {
                    $banner.css({display: 'flex', opacity: 1});
                }
            }

            function updateAllTitikPengambilanState() {
                var sts = window.selectedSampleTypes || [];
                sts.forEach(function(type) {
                    updateTitikPengambilanState(type.id);
                });
            }

            // Filter packets by sample type
            function filterPacketsBySampleType(sampleTypeId) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var hasPackets = false;

                $tabContent.find('.packet-button-item-tab').each(function() {
                    var $item = $(this);
                    var packetSampleTypeId = $item.attr('data-sample-type-id');

                    if (packetSampleTypeId && packetSampleTypeId === sampleTypeId) {
                        $item.show();
                        hasPackets = true;
                    } else {
                        $item.hide();
                    }
                });

                // Sembunyikan label, section, dan HR jika tidak ada paket untuk jenis ini
                var $paketSection = $('#paket-section-' + sampleTypeId);
                var $paketHr = $('#paket-hr-' + sampleTypeId);
                if (hasPackets) {
                    $paketSection.show();
                    $paketHr.show();
                } else {
                    $paketSection.hide();
                    $paketHr.hide();
                }
            }

            // Filter parameters by sample type (based on baku mutu + jenis makanan untuk makmin)
            function filterParametersBySampleType(sampleTypeId) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');

                $tabContent.find('.parameter-group-item').removeClass('has-filtered-params');

                // Lacak grup mana saja yang punya parameter valid
                var groupsWithParams = {};

                $tabContent.find('.method-row-tab').each(function() {
                    var $row = $(this);
                    var bakuMutuAttr = $row.attr('data-baku-mutu-sampletypes');
                    var bakuMutuSampleTypes = [];

                    if (bakuMutuAttr) {
                        try {
                            bakuMutuSampleTypes = JSON.parse(bakuMutuAttr);
                        } catch (e) {
                            console.error('Error parsing baku_mutu_sampletypes:', e);
                        }
                    }

                    var isAllowed = bakuMutuSampleTypes.some(function(id) {
                        return String(id) === String(sampleTypeId);
                    });

                    if (isAllowed) {
                        $row.show();
                        $row.find('.method-checkbox-tab').prop('disabled', false);
                        // Catat grup induk row ini
                        var $group = $row.closest('.parameter-group-item');
                        if ($group.length) {
                            var groupId = $group.attr('data-lab-group');
                            if (groupId) groupsWithParams[groupId] = $group;
                        }
                    } else {
                        $row.hide();
                        $row.find('.method-checkbox-tab').prop('disabled', true);
                    }
                });

                // Tampilkan & expand grup yang punya parameter; sembunyikan yang kosong
                $tabContent.find('.parameter-group-item').each(function() {
                    var $group = $(this);
                    var groupId = $group.attr('data-lab-group');
                    if (groupsWithParams[groupId]) {
                        $group.show().addClass('has-filtered-params');
                        $group.find('.collapse').addClass('show');
                        $group.find('.collapse-icon').css('transform', 'rotate(180deg)');
                    } else {
                        $group.hide().removeClass('has-filtered-params');
                        $group.find('.collapse').removeClass('show');
                    }
                });

                if (sampleTypeId && typeof window.applyTabMethodPricesForSampleType === 'function') {
                    window.applyTabMethodPricesForSampleType(sampleTypeId);
                }
            }

            $(document).on('shown.bs.tab', '#sampleTypeTabs a[data-toggle="tab"]', function() {
                var href = $(this).attr('href');
                if (!href) {
                    return;
                }
                var $pane = $(href);
                if (!$pane.length) {
                    return;
                }
                var stid = String($pane.attr('data-sample-type-id') || '').trim();
                if (stid && typeof window.applyTabMethodPricesForSampleType === 'function') {
                    window.applyTabMethodPricesForSampleType(stid);
                }
            });

            // Handle packet selection per tab (MULTIPLE SELECTION - no deselect others)
            $(document).on('click', '.btn-pick-paket-tab', function() {
                var sampleTypeId = $(this).data('sample-type-id');
                var packetId = $(this).data('packet-id');
                var packetName = $(this).data('name');
                var packetPrice = $(this).data('price');
                var $button = $(this);

                // Ensure config exists
                if (!sampleTypeConfigs[sampleTypeId]) {
                    sampleTypeConfigs[sampleTypeId] = {
                        packets: [],
                        additional_methods: [],
                        cost: 0,
                        titik_pengambilan: '' // Titik pengambilan per sample type
                    };
                }

                // Toggle selection (MULTIPLE SELECTION - don't deselect others)
                if ($button.hasClass('active')) {
                    // Deselect this packet only
                    $button.removeClass('active').css({
                        'background': 'white',
                        'border-color': '#e2e8f0',
                        'color': '#2d3748'
                    });
                    // Reset price text color to green when inactive
                    $button.find('small').css('color', '#28a745');

                    // Remove packet from config
                    var config = sampleTypeConfigs[sampleTypeId];
                    if (config.packets) {
                        config.packets = config.packets.filter(function(p) {
                            return p.packet_id !== packetId;
                        });
                    }

                    // Uncheck and enable all checkboxes that were from this packet
                    $('.method-checkbox-tab[data-sample-type-id="' + sampleTypeId + '"][data-packet-id="' +
                        packetId + '"]').each(
                        function() {
                            var $checkbox = $(this);
                            if ($checkbox.closest('.method-row-tab').hasClass('from-packet')) {
                                $checkbox.prop('checked', false).prop('disabled', false);
                                $checkbox.closest('.method-row-tab').removeClass('from-packet');
                            }
                        });

                    // Update display and codes
                    updateTabCart(sampleTypeId);
                    updateSelectedSampleTypesDisplay();
                    updateSampleCodeCards();
                    updateTitikPengambilanState(sampleTypeId);
                    updateNextStepButton();
                } else {
                    // Select this packet (MULTIPLE SELECTION - keep others selected)
                    $button.addClass('active').css({
                        'background': 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)',
                        'border-color': '#4caf50',
                        'color': 'white'
                    });
                    // Update price text color to white when active
                    $button.find('small').css('color', 'rgba(255, 255, 255, 0.95)');

                    // Add packet to config (if not already exists)
                    var config = sampleTypeConfigs[sampleTypeId];
                    if (!config.packets) {
                        config.packets = [];
                    }

                    // Check if packet already exists
                    var existingPacket = config.packets.find(function(p) {
                        return p.packet_id === packetId;
                    });

                    if (!existingPacket) {
                        // Add new packet entry
                        config.packets.push({
                            packet_id: packetId,
                            packet_name: packetName,
                            packet_price: parseFloat(packetPrice) || 0,
                            methods: [] // Will be populated by loadPacketMethodsForTab
                        });
                    }

                    // Load packet methods via AJAX (this will assign sequence and update codes)
                    loadPacketMethodsForTab(sampleTypeId, packetId);

                    // Update Next Step button visibility after packet is selected
                    setTimeout(function() {
                        updateTitikPengambilanState(sampleTypeId, {
                            autoFocus: true
                        });
                        updateNextStepButton();
                    }, 300);
                }
            });

            // Load packet methods for a specific tab
            function loadPacketMethodsForTab(sampleTypeId, packetId) {
                var url = "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}";
                url = url.replace('#', packetId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        id: packetId
                    },
                    success: function(response) {
                        // Check response structure (controller returns 'success' not 'status')
                        if (response.success && response.data && Array.isArray(response.data)) {
                            var config = sampleTypeConfigs[sampleTypeId];
                            if (!config) {
                                config = {
                                    packets: [],
                                    additional_methods: [],
                                    cost: 0
                                };
                                sampleTypeConfigs[sampleTypeId] = config;
                            }

                            // Find or create packet entry
                            if (!config.packets) {
                                config.packets = [];
                            }

                            var packetEntry = config.packets.find(function(p) {
                                return p.packet_id === packetId;
                            });

                            if (!packetEntry) {
                                // Create new packet entry
                                packetEntry = {
                                    packet_id: packetId,
                                    packet_name: null,
                                    packet_price: 0,
                                    methods: []
                                };
                                config.packets.push(packetEntry);
                            }

                            // Clear previous methods from this packet
                            packetEntry.methods = [];

                            // Update packet price from response
                            if (response.price) {
                                packetEntry.packet_price = parseFloat(response.price) || 0;
                            }

                            // FIRST: Extract all unique lab IDs from packet methods
                            var labIdsFromPacket = [];
                            response.data.forEach(function(item) {
                                // Get full method string from checkbox data-method attribute
                                // item.id_method is the method ID from PacketDetail
                                var methodId = item.id_method;
                                var $checkbox = $('.method-checkbox-tab[data-sample-type-id="' +
                                    sampleTypeId + '"][data-method-id="' + methodId + '"]');

                                // If not found, try without sample-type-id filter (fallback)
                                if ($checkbox.length === 0) {
                                    $checkbox = $('.method-checkbox-tab[data-method-id="' +
                                            methodId + '"]')
                                        .filter(function() {
                                            return $(this).closest('.tab-pane').attr(
                                                'data-sample-type-id') === sampleTypeId;
                                        });
                                }

                                if ($checkbox.length) {
                                    var methodString = $checkbox.attr('data-method');
                                    if (methodString) {
                                        // Extract lab ID from method string (format: method_id_lab_id_price)
                                        var parts = methodString.split('_');
                                        if (parts.length >= 2 && !labIdsFromPacket.includes(
                                                parts[1])) {
                                            labIdsFromPacket.push(parts[1]);
                                        }
                                    }
                                } else {
                                    console.warn('Checkbox not found for method_id:', methodId,
                                        'sampleTypeId:', sampleTypeId);
                                }
                            });

                            // SECOND: Assign sequence numbers for new lab combinations BEFORE processing methods
                            // This ensures sequence numbers are assigned immediately when packet is selected
                            labIdsFromPacket.forEach(function(labId) {
                                var sequenceKey = sampleTypeId + '_' + labId;
                                if (!sampleCodeSequenceMap[sequenceKey]) {
                                    sequenceCounter++;
                                    sampleCodeSequenceMap[sequenceKey] = sequenceCounter;
                                    sequenceOrder.push({
                                        sampleTypeId: sampleTypeId,
                                        labId: labId,
                                        sequenceNumber: sequenceCounter
                                    });
                                }
                            });

                            // THIRD: Now process methods and check checkboxes
                            response.data.forEach(function(item) {
                                // Get full method string from checkbox data-method attribute
                                var methodId = item.id_method;
                                var $checkbox = $('.method-checkbox-tab[data-sample-type-id="' +
                                    sampleTypeId + '"][data-method-id="' + methodId + '"]');

                                // If not found, try without sample-type-id filter (fallback)
                                if ($checkbox.length === 0) {
                                    $checkbox = $('.method-checkbox-tab[data-method-id="' +
                                            methodId + '"]')
                                        .filter(function() {
                                            return $(this).closest('.tab-pane').attr(
                                                'data-sample-type-id') === sampleTypeId;
                                        });
                                }

                                if ($checkbox.length) {
                                    var methodString = $checkbox.attr('data-method');
                                    if (methodString) {
                                        // Store method with packet_id attribute for tracking
                                        $checkbox.attr('data-packet-id', packetId);
                                        packetEntry.methods.push(methodString);
                                        $checkbox.prop('checked', true).prop('disabled', true);
                                        $checkbox.closest('.method-row-tab').addClass(
                                            'from-packet');
                                    }
                                } else {
                                    console.warn('Checkbox not found for method_id:', methodId,
                                        'sampleTypeId:', sampleTypeId);
                                }
                            });

                            // Update cart and badge count
                            updateTabCart(sampleTypeId);
                            updateSelectedSampleTypesDisplay();
                            updateNextStepButton(); // Update Next Step button visibility

                            // Update tab badge count (include all packet methods + additional methods)
                            var totalPacketMethods = 0;
                            if (config.packets) {
                                config.packets.forEach(function(p) {
                                    totalPacketMethods += (p.methods || []).length;
                                });
                            }
                            var totalParams = totalPacketMethods + (config.additional_methods || [])
                                .length;
                            var $countBadge = $('#count-' + sampleTypeId);
                            if (totalParams > 0) {
                                if ($countBadge.length === 0) {
                                    // Add badge if it doesn't exist
                                    var tabId = sampleTypeId.replace(/-/g, '');
                                    $('#' + tabId + '-tab').append(
                                        `<span id="count-${sampleTypeId}" class="badge badge-primary ml-2">${totalParams}</span>`
                                    );
                                } else {
                                    $countBadge.text(totalParams).show();
                                }
                            } else {
                                // Hide badge if no parameters
                                $countBadge.hide();
                            }

                            // Update parameter counts per lab group (including packet methods)
                            updateParameterCountsForTab(sampleTypeId);

                            // Update sample code cards AFTER sequence numbers are assigned
                            updateSampleCodeCards();

                            // Update Next Step button visibility (with delay to ensure all updates are done)
                            setTimeout(function() {
                                updateNextStepButton();
                            }, 200);
                        } else {
                            console.error('Invalid response structure:', response);
                            swal({
                                title: "Error!",
                                text: "Format response tidak valid",
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading packet methods:', xhr);
                        swal({
                            title: "Error!",
                            text: "Gagal memuat detail paket",
                            icon: "error"
                        });

                        // Reset packet selection on error
                        if (sampleTypeConfigs[sampleTypeId]) {
                            sampleTypeConfigs[sampleTypeId].packet_id = null;
                            sampleTypeConfigs[sampleTypeId].packet_name = null;
                            sampleTypeConfigs[sampleTypeId].packet_price = 0;
                        }
                        $button.removeClass('active').css({
                            'background': 'white',
                            'border-color': '#e2e8f0',
                            'color': '#2d3748'
                        });
                        // Reset price text color to green when inactive
                        $button.find('small').css('color', '#28a745');
                        updateTabCart(sampleTypeId);
                        updateSelectedSampleTypesDisplay();
                    }
                });
            }

            /* ── URL AJAX PAKET ── */
            var _packetGetDataUrl  = "{{ url('elits-packet') }}";
            var _packetStoreUrl    = "{{ route('elits-packet.ajax-store') }}";
            var _packetUpdateUrl   = "{{ url('elits-packet') }}";
            var _csrfTokenPaket    = "{{ csrf_token() }}";

            /* ── Helper: hitung & tampilkan total harga paket ── */
            function updateModalPaketTotal() {
                var b = parseInt($('#modal-paket-bahan').val())  || 0;
                var s = parseInt($('#modal-paket-sarana').val()) || 0;
                var j = parseInt($('#modal-paket-jasa').val())   || 0;
                var t = b + s + j;
                $('#modal-paket-total').val(t);
                $('#modal-paket-total-display').text('Rp ' + t.toLocaleString('id-ID'));
            }
            $(document).on('input change', '.modal-paket-price-input', updateModalPaketTotal);

            /* ── Helper: update preview parameter yang dipilih ── */
            function updatePaketSelectedPreview() {
                var items = [];
                $('#modal-paket-method-list .paket-method-cb:checked').each(function() {
                    var label = $(this).parent().text().trim();
                    items.push(label);
                });
                if (!items.length) {
                    $('#modal-paket-selected-preview').html(
                        '<span class="text-muted" id="modal-paket-no-param-msg">Belum ada parameter dipilih</span>');
                } else {
                    var html = '';
                    items.forEach(function(n) {
                        html += '<span class="badge badge-primary mr-1 mb-1" style="font-size:11px;padding:4px 7px;">' +
                            $('<span>').text(n).html() + '</span>';
                    });
                    html += ' <small class="text-muted ml-1">(' + items.length + ' terpilih)</small>';
                    $('#modal-paket-selected-preview').html(html);
                }
            }
            $(document).on('change', '.paket-method-cb', updatePaketSelectedPreview);

            /* ── Helper: bangun daftar method untuk modal paket ── */
            function buildPaketMethodList(stId, checkedIds) {
                var $list = $('#modal-paket-method-list').empty();
                var methods = [];
                var seen    = {};
                // Kumpulkan dari semua method-checkbox-tab yang ada di DOM untuk stId
                $('.method-checkbox-tab').each(function() {
                    var mid  = $(this).data('method-id');
                    var stid = String($(this).data('sample-type-id') || '');
                    if (!mid || seen[mid] || stid !== String(stId)) return;
                    seen[mid] = true;
                    var name = $(this).data('name') ||
                               $(this).closest('.method-row-tab').find('strong').first().text().trim();
                    if (name) methods.push({ id: mid, name: name });
                });

                if (!methods.length) {
                    $list.append('<p class="text-muted text-center p-3 small">Tidak ada parameter tersedia untuk jenis sampel ini.</p>');
                    return;
                }

                var html = '';
                methods.forEach(function(m) {
                    var isChecked = checkedIds && checkedIds.indexOf(m.id) !== -1;
                    var safeName  = $('<span>').text(m.name).html();
                    html += '<label class="paket-method-item" style="display:flex;align-items:center;padding:6px 8px;cursor:pointer;border-bottom:1px solid #f0f0f0;margin:0;font-weight:normal;font-size:13px;">' +
                        '<input type="checkbox" class="paket-method-cb mr-2" value="' + m.id + '"' +
                        (isChecked ? ' checked' : '') + '> ' + safeName + '</label>';
                });
                $list[0].innerHTML = html;
                updatePaketSelectedPreview();
            }

            /* ── Helper: reset form modal paket ── */
            function resetModalPaket() {
                $('#modal-paket-id').val('');
                $('#modal-paket-sample-type-id').val('');
                $('#modal-paket-name').val('');
                $('#modal-paket-bahan').val(0);
                $('#modal-paket-sarana').val(0);
                $('#modal-paket-jasa').val(0);
                $('#modal-paket-total').val(0);
                $('#modal-paket-total-display').text('Rp 0');
                $('#modal-paket-method-search').val('');
                $('#modal-paket-method-list').empty();
                $('#modal-paket-selected-preview').html('<span class="text-muted">Belum ada parameter dipilih</span>');
                $('#modal-paket-alert').hide().text('');
                paketMethodFilter('');
            }

            /* ── TOMBOL TAMBAH PAKET ── */
            $(document).on('click', '.btn-tambah-paket', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var stId   = $(this).data('sample-type-id');
                var stName = $(this).data('sample-type-name') || '';

                resetModalPaket();
                $('#modal-paket-title').html('<i class="fa fa-plus-circle mr-2"></i>Tambah Paket Baru');
                $('#modal-paket-sample-type-id').val(stId);
                buildPaketMethodList(stId, []);
                updateModalPaketTotal();
                $('#modal-paket').modal('show');
            });

            /* ── TOMBOL EDIT PAKET ── */
            $(document).on('click', '.btn-edit-paket', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var packetId = $(this).data('packet-id');
                var stId     = $(this).data('sample-type-id') ||
                               $(this).closest('.packet-button-item-tab').data('sample-type-id');

                resetModalPaket();
                $('#modal-paket-title').html('<i class="fa fa-pencil-alt mr-2"></i>Edit Paket');
                $('#modal-paket-id').val(packetId);
                $('#modal-paket-sample-type-id').val(stId);

                // Tampilkan loading
                $('#modal-paket-form').hide();
                $('#modal-paket-loading').show();
                $('#btn-modal-paket-save').prop('disabled', true);
                $('#modal-paket').modal('show');

                $.ajax({
                    url: _packetGetDataUrl + '/' + packetId + '/data',
                    type: 'GET',
                    success: function(resp) {
                        $('#modal-paket-loading').hide();
                        $('#modal-paket-form').show();
                        $('#btn-modal-paket-save').prop('disabled', false);

                        if (!resp.status) {
                            $('#modal-paket-alert').addClass('alert-danger').text('Gagal memuat data paket.').show();
                            return;
                        }
                        $('#modal-paket-name').val(resp.name_packet);
                        $('#modal-paket-bahan').val(resp.price_bahan_packet  || 0);
                        $('#modal-paket-sarana').val(resp.price_sarana_packet || 0);
                        $('#modal-paket-jasa').val(resp.price_jasa_packet    || 0);
                        updateModalPaketTotal();

                        buildPaketMethodList(stId || resp.sample_type_id, resp.method_ids || []);
                    },
                    error: function() {
                        $('#modal-paket-loading').hide();
                        $('#modal-paket-form').show();
                        $('#btn-modal-paket-save').prop('disabled', false);
                        $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                            .text('Gagal memuat data paket.').show();
                    }
                });
            });

            /* ── SIMPAN (Tambah / Edit) ── */
            $(document).on('click', '#btn-modal-paket-save', function() {
                var packetId  = $('#modal-paket-id').val();
                var stId      = $('#modal-paket-sample-type-id').val();
                var name      = $.trim($('#modal-paket-name').val());
                var bahan     = parseInt($('#modal-paket-bahan').val())  || 0;
                var sarana    = parseInt($('#modal-paket-sarana').val()) || 0;
                var jasa      = parseInt($('#modal-paket-jasa').val())   || 0;
                var total     = bahan + sarana + jasa;
                var methodIds = [];

                $('#modal-paket-method-list .paket-method-cb:checked').each(function() {
                    methodIds.push($(this).val());
                });

                $('#modal-paket-alert').hide();

                if (!name) {
                    $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                        .text('Nama paket tidak boleh kosong.').show();
                    return;
                }
                if (!methodIds.length) {
                    $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                        .text('Pilih minimal satu parameter pengujian.').show();
                    return;
                }

                var isEdit = !!packetId;
                var url    = isEdit
                    ? (_packetUpdateUrl + '/' + packetId + '/ajax/update')
                    : _packetStoreUrl;

                var data = {
                    _token:              _csrfTokenPaket,
                    name_packet:         name,
                    sample_type_id:      stId,
                    price_bahan_packet:  bahan,
                    price_sarana_packet: sarana,
                    price_jasa_packet:   jasa,
                    price_total_packet:  total,
                    methodAttributes:    methodIds
                };

                $('#btn-modal-paket-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

                $.ajax({
                    url:  url,
                    type: 'POST',
                    data: data,
                    success: function(resp) {
                        $('#btn-modal-paket-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                        if (!resp.status) {
                            $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                                .text(resp.pesan || 'Gagal menyimpan.').show();
                            return;
                        }

                        if (isEdit) {
                            // Update teks & harga pada kartu paket di halaman
                            var $card = $('.btn-pick-paket-tab[data-packet-id="' + packetId + '"]');
                            $card.find('.paket-name-text').text(resp.name_packet);
                            var priceFormatted = parseInt(resp.price_total_packet).toLocaleString('id-ID');
                            $card.find('.paket-price-text').html(
                                '<i class="fa fa-tag"></i> Rp ' + priceFormatted);
                            $card.attr('data-price', resp.price_total_packet)
                                .attr('data-name', resp.name_packet);
                        } else {
                            // Tambah kartu paket baru ke semua tab dengan sample_type_id yang sama
                            var newId    = resp.id_packet;
                            var newName  = resp.name_packet;
                            var newTotal = parseInt(resp.price_total_packet) || 0;
                            var priceFormatted = newTotal.toLocaleString('id-ID');

                            var safeId   = String(newId).replace(/"/g, '');
                            var safeName = $('<span>').text(newName).html();

                            var $containers = $('.packet-buttons-container-tab[data-sample-type-id="' + stId + '"]');
                            $containers.each(function() {
                                var $colDiv = $('<div class="col-md-6 col-lg-4 mb-3 packet-button-item-tab">')
                                    .attr('data-sample-type-id', stId)
                                    .attr('data-packet-id', safeId);
                                var $wrap = $('<div style="position:relative;">');
                                var $btn = $('<button type="button" class="btn btn-pick-paket-tab w-100">')
                                    .attr('data-sample-type-id', stId)
                                    .attr('data-packet-id', safeId)
                                    .attr('data-price', newTotal)
                                    .attr('data-name', newName)
                                    .css({ textAlign:'left', padding:'15px', minHeight:'80px',
                                           border:'2px solid #e2e8f0', background:'white',
                                           color:'#2d3748', borderRadius:'8px' })
                                    .html('<strong class="paket-name-text">' + safeName + '</strong><br>' +
                                          '<small class="paket-price-text" style="color:#28a745;font-weight:500;">' +
                                          '<i class="fa fa-tag"></i> Rp ' + priceFormatted + '</small>');
                                var $editBtn = $('<button type="button" class="btn btn-edit-paket">')
                                    .attr('data-packet-id', safeId)
                                    .attr('data-sample-type-id', stId)
                                    .attr('title', 'Edit paket ini')
                                    .css({ position:'absolute', top:'5px', right:'5px',
                                           background:'rgba(255,255,255,0.9)', border:'1px solid #ced4da',
                                           borderRadius:'4px', padding:'2px 7px', fontSize:'11px',
                                           cursor:'pointer', zIndex:2 })
                                    .html('<i class="fa fa-pencil-alt"></i>');
                                $wrap.append($btn, $editBtn);
                                $colDiv.append($wrap);
                                $(this).append($colDiv);
                                // Filter visibility sesuai sample type
                                $colDiv.show();
                            });
                        }

                        swal({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1500, buttons: false });
                        $('#modal-paket').modal('hide');
                    },
                    error: function(xhr) {
                        $('#btn-modal-paket-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                        var msg = 'Gagal menyimpan paket.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.pesan)  msg = xhr.responseJSON.pesan;
                            if (xhr.responseJSON.errors) {
                                var lines = [];
                                $.each(xhr.responseJSON.errors, function(k, v) {
                                    lines.push($.isArray(v) ? v.join(' ') : String(v));
                                });
                                if (lines.length) msg = lines.join('\n');
                            }
                        }
                        $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger').text(msg).show();
                    }
                });
            });

            /* ── Reset modal saat ditutup ── */
            $(document).on('hidden.bs.modal', '#modal-paket', function() {
                resetModalPaket();
            });

            // Handle search parameter in tab
            $(document).on('keyup', '.search-parameter-tab', function() {
                var sampleTypeId = $(this).data('sample-type-id');
                var searchTerm = $(this).val().toLowerCase();
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var $groups = $tabContent.find('.parameter-group-item');

                if (searchTerm === '') {
                    $tabContent.find('.no-results-message').remove();

                    // Kembalikan ke tampilan awal: hanya parameter yang sudah punya baku mutu
                    // untuk jenis sampel yang sedang aktif
                    filterParametersBySampleType(sampleTypeId);

                    return;
                }

                // Filter groups based on search term
                var visibleCount = 0;
                $groups.each(function() {
                    var $group = $(this);
                    var groupName = $group.find('.parameter-group-header strong').text()
                        .toLowerCase();
                    var $methods = $group.find('.method-row-tab');
                    var hasMatch = false;

                    // Check if group name matches
                    if (groupName.includes(searchTerm)) {
                        hasMatch = true;
                    }

                    // Check if any method name matches
                    var methodMatchCount = 0;
                    $methods.each(function() {
                        var $methodRow = $(this);
                        var methodName = $methodRow.find('strong').text().toLowerCase();
                        var methodLabel = $methodRow.find('label').text().toLowerCase();

                        if (methodName.includes(searchTerm) || methodLabel.includes(
                                searchTerm)) {
                            hasMatch = true;
                            methodMatchCount++;
                            // Show matching method rows
                            $methodRow.show();
                        } else {
                            // Hide non-matching method rows
                            $methodRow.hide();
                        }
                    });

                    if (hasMatch) {
                        $group.show();
                        // Expand group if it has matches
                        var $collapse = $group.find('.collapse');
                        if (!$collapse.hasClass('show')) {
                            $collapse.addClass('show');
                        }
                        visibleCount++;
                    } else {
                        $group.hide();
                        // Hide all methods in this group
                        $methods.hide();
                    }
                });

                // Update pagination for filtered results
                if (visibleCount > 0) {
                    // Get visible groups
                    var $allGroups = $tabContent.find('.parameter-group-item');
                    var $visibleGroups = $allGroups.filter(':visible');

                    // Reinitialize pagination with visible groups only
                    initParameterPaginationForFiltered(sampleTypeId, $visibleGroups);
                } else {
                    // No results found
                    $tabContent.find('.parameter-pagination-tab').hide();
                    // Show message if needed
                    var $parameterList = $tabContent.find('.parameter-list-tab');
                    if ($parameterList.find('.no-results-message').length === 0) {
                        $parameterList.prepend(
                            '<div class="no-results-message alert alert-info text-center mt-3">Tidak ada parameter yang ditemukan</div>'
                        );
                    }
                }
            });

            // Handle parameter checkbox change per tab
            $(document).on('change', '.method-checkbox-tab', function() {
                var $t = $(this);
                var sampleTypeId = String($t.attr('data-sample-type-id') || '').trim();
                var methodString = $t.attr('data-method');
                var config = sampleTypeConfigs[sampleTypeId] || {
                    additional_methods: []
                };

                if ($t.is(':checked')) {
                    if (!config.additional_methods) config.additional_methods = [];
                    var alreadyExists = (config.additional_methods || []).some(function(m) {
                        return m.method === methodString;
                    });
                    if (!alreadyExists) {
                        config.additional_methods.push({
                            method: methodString,
                            name: $t.attr('data-name'),
                            price: parseFloat($t.attr('data-price')) || 0,
                            lab_name: $t.attr('data-labname')
                        });
                    }

                    // Extract lab ID and assign sequence number if new combination
                    var parts = methodString.split('_');
                    if (parts.length >= 2) {
                        var labId = parts[1];
                        var sequenceKey = sampleTypeId + '_' + labId;

                        // Assign sequence number for new lab combination
                        if (!sampleCodeSequenceMap[sequenceKey]) {
                            sequenceCounter++;
                            sampleCodeSequenceMap[sequenceKey] = sequenceCounter;
                            sequenceOrder.push({
                                sampleTypeId: sampleTypeId,
                                labId: labId,
                                sequenceNumber: sequenceCounter
                            });
                        }
                    }
                } else {
                    config.additional_methods = (config.additional_methods || []).filter(function(m) {
                        return m.method !== methodString;
                    });

                    // Untuk parameter yang ditambahkan via "Tambah Parameter",
                    // saat di-uncheck sembunyikan lagi dari daftar agar kembali rapi.
                    var $row = $t.closest('.method-row-tab');
                    if ($row.attr('data-auto-added') === '1') {
                        $row.hide();
                        var $group = $row.closest('.parameter-group-item');
                        if ($group.length && $group.find('.method-row-tab:visible').length === 0) {
                            $group.hide();
                            $group.find('.collapse').removeClass('show');
                            $group.find('.collapse-icon').css('transform', '');
                        }
                    }
                }

                updateTabCart(sampleTypeId);

                // Update tab badge count
                var config = sampleTypeConfigs[sampleTypeId] || {};
                var totalParamsCount = (config.methods || []).length + (config.additional_methods || [])
                    .length;
                var $countBadge = $('#count-' + sampleTypeId);
                if (totalParamsCount > 0) {
                    if ($countBadge.length === 0) {
                        // Add badge if it doesn't exist
                        var tabId = sampleTypeId.replace(/-/g, '');
                        $('#' + tabId + '-tab').append(
                            `<span id="count-${sampleTypeId}" class="badge badge-primary ml-2">${totalParamsCount}</span>`
                        );
                    } else {
                        $countBadge.text(totalParamsCount).show();
                    }
                } else {
                    // Hide badge if no parameters
                    $countBadge.hide();
                }

                updateSelectedSampleTypesDisplay();

                // Update parameter counts per lab group (including packet methods)
                updateParameterCountsForTab(sampleTypeId);

                // Update sample code cards when parameters change
                updateSampleCodeCards();

                updateTitikPengambilanState(sampleTypeId);
                updateNextStepButton();
            });

            // Update cart for specific tab
            function updateTabCart(sampleTypeId) {
                var config = sampleTypeConfigs[sampleTypeId] || {};
                var $cartList = $('.cart-items-list-tab[data-sample-type-id="' + sampleTypeId + '"]');
                var $totalItems = $('#cart-total-items-' + sampleTypeId);
                var $totalPrice = $('#cart-total-price-' + sampleTypeId);

                var totalCost = 0;
                var totalItems = 0;
                var cartHtml = '';

                // Multiple packets info
                if (config.packets && config.packets.length > 0) {
                    config.packets.forEach(function(packet) {
                        cartHtml += `
                            <div class="alert alert-info mb-3" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px;">
                                <strong><i class="fas fa-box"></i> Paket:</strong> ${packet.packet_name || 'Paket Terpilih'}
                                <br><small style="color: #1976d2;">${formatRupiah(packet.packet_price || 0)}</small>
                            </div>
                        `;
                        totalCost += parseFloat(packet.packet_price) || 0;
                        // Count packet methods as items
                        if (packet.methods && packet.methods.length > 0) {
                            totalItems += packet.methods.length;
                        }
                    });
                }

                // Additional methods
                if (config.additional_methods && config.additional_methods.length > 0) {
                    config.additional_methods.forEach(function(method) {
                        cartHtml += `
                            <div class="cart-item mb-2 p-2" style="background: white; border-radius: 5px;">
                                <strong>${method.name}</strong>
                                <br><small>${method.lab_name}</small>
                                <div class="text-success">${formatRupiah(method.price)}</div>
                            </div>
                        `;
                        totalCost += parseFloat(method.price) || 0;
                        totalItems++;
                    });
                }

                // Show empty message only if no packets and no additional methods
                var hasPackets = (config.packets && config.packets.length > 0);
                if (cartHtml === '' && !hasPackets && (!config.additional_methods || config.additional_methods
                        .length === 0)) {
                    cartHtml =
                        '<div class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><p>Belum ada parameter dipilih</p></div>';
                }

                $cartList.html(cartHtml);
                $totalItems.text(totalItems);
                $totalPrice.text(formatRupiah(totalCost));

                config.cost = totalCost;

                // Update badge count in tab and selected types display
                // Include all packet methods in total count
                var totalParamsCount = totalItems;
                if (hasPackets) {
                    var totalPacketMethods = 0;
                    config.packets.forEach(function(p) {
                        totalPacketMethods += (p.methods || []).length;
                    });
                    totalParamsCount = totalPacketMethods + (config.additional_methods ? config.additional_methods
                        .length : 0);
                }

                // Update tab badge count (show/hide based on count)
                var $countBadge = $('#count-' + sampleTypeId);
                if (totalParamsCount > 0) {
                    if ($countBadge.length === 0) {
                        // Add badge if it doesn't exist
                        var tabId = sampleTypeId.replace(/-/g, '');
                        $('#' + tabId + '-tab').append(
                            `<span id="count-${sampleTypeId}" class="badge badge-primary ml-2">${totalParamsCount}</span>`
                        );
                    } else {
                        $countBadge.text(totalParamsCount).show();
                    }
                } else {
                    // Hide badge if no parameters
                    $countBadge.hide();
                }

                updateSelectedSampleTypesDisplay();

                // Update parameter counts per lab group (including packet methods)
                updateParameterCountsForTab(sampleTypeId);

                // Update sample code cards
                updateSampleCodeCards();

                updateTitikPengambilanState(sampleTypeId);

                // Update Next Step button visibility (with small delay to ensure all updates complete)
                setTimeout(function() {
                    updateNextStepButton();
                }, 100);
            }

            // Format Rupiah helper - make it globally accessible
            window.formatRupiah = function formatRupiah(angka) {
                if (!angka && angka !== 0) return '0';
                var number_string = angka.toString(),
                    sisa = number_string.length % 3,
                    rupiah = number_string.substr(0, sisa),
                    ribuan = number_string.substr(sisa).match(/\d{3}/g);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return rupiah;
            }

            // Store pagination state per tab
            var paginationState = {};

            // Update parameter counts for specific tab (including packet methods)
            function updateParameterCountsForTab(sampleTypeId) {
                var config = sampleTypeConfigs[sampleTypeId] || {};
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');

                // Get all lab groups in this tab
                $tabContent.find('.parameter-group-tab').each(function() {
                    var $group = $(this);
                    var labId = $group.data('lab-group');

                    // Count checked checkboxes in this group
                    var checkedCount = $group.find('.method-checkbox-tab:checked').length;

                    // Also count packet methods for this lab
                    if (config.methods && config.methods.length > 0) {
                        config.methods.forEach(function(methodString) {
                            var parts = methodString.split('_');
                            if (parts.length >= 2 && parts[1] === labId) {
                                // Check if this method is already counted (checkbox might be checked)
                                var methodId = parts[0];
                                var $checkbox = $group.find(
                                    '.method-checkbox-tab[data-method-id="' + methodId + '"]');
                                if ($checkbox.length && !$checkbox.is(':checked')) {
                                    checkedCount++;
                                } else if ($checkbox.length === 0) {
                                    // Method from packet but checkbox not found (shouldn't happen, but count anyway)
                                    checkedCount++;
                                }
                            }
                        });
                    }

                    // Update count badge
                    var $countBadge = $group.find('.param-count-tab');
                    $countBadge.text(checkedCount);

                    // Change badge color
                    if (checkedCount > 0) {
                        $countBadge.removeClass('badge-secondary').addClass('badge-success');
                    } else {
                        $countBadge.removeClass('badge-success').addClass('badge-secondary');
                    }
                });
            }

            // Initialize pagination for parameters (with optional filtered groups)
            function initParameterPagination(sampleTypeId, $filteredGroups) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var $parameterList = $tabContent.find('.parameter-list-tab');
                var $pagination = $tabContent.find('.parameter-pagination-tab');
                var itemsPerPage = 20; // Changed from 10 to 20

                // Get parameter groups (use filtered if provided, otherwise hanya grup yang lolos filter baku mutu)
                var $groups = $filteredGroups;
                if (!$groups || $groups.length === 0) {
                    $groups = $parameterList.find('.parameter-group-item.has-filtered-params');
                }
                var totalGroups = $groups.length;

                if (totalGroups === 0) {
                    $pagination.hide().empty();
                    return;
                }

                // Hide all groups first - pagination will show only the current page
                $groups.hide();

                // Store pagination state
                if (!paginationState[sampleTypeId]) {
                    paginationState[sampleTypeId] = {};
                }
                paginationState[sampleTypeId].groups = $groups;
                paginationState[sampleTypeId].currentPage = 1;
                paginationState[sampleTypeId].itemsPerPage = itemsPerPage;

                // Always show pagination, even if items fit in one page
                // This ensures consistent UI and better performance for large lists

                // Show pagination (make sure it's visible)
                $pagination.show().css('display', 'block');

                // Calculate total pages
                var totalPages = Math.ceil(totalGroups / itemsPerPage);
                var sampleTypeIdClean = sampleTypeId.replace(/-/g, '');

                // Generate pagination HTML
                var paginationHtml =
                    '<nav aria-label="Parameter pagination"><ul class="pagination justify-content-center mb-0">';

                // Previous button
                paginationHtml += '<li class="page-item disabled" id="prev-page-' + sampleTypeIdClean + '">';
                paginationHtml += '<a class="page-link" href="#" tabindex="-1">Previous</a></li>';

                // Page numbers
                for (var i = 1; i <= totalPages; i++) {
                    var activeClass = i === 1 ? 'active' : '';
                    paginationHtml += '<li class="page-item ' + activeClass + '" id="page-' + i + '-' +
                        sampleTypeIdClean + '">';
                    paginationHtml += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                }

                // Next button (always show, disabled only if on last page)
                paginationHtml += '<li class="page-item disabled" id="next-page-' + sampleTypeIdClean + '">';
                paginationHtml += '<a class="page-link" href="#">Next</a></li>';
                paginationHtml += '</ul></nav>';

                // Page info
                var startItem = 1;
                var endItem = Math.min(itemsPerPage, totalGroups);
                paginationHtml += '<div class="text-center mt-2">';
                paginationHtml += '<small class="text-muted" id="page-info-' + sampleTypeIdClean + '">';
                paginationHtml += 'Menampilkan ' + startItem + '-' + endItem + ' dari ' + totalGroups +
                    ' parameter';
                paginationHtml += '</small></div>';

                $pagination.html(paginationHtml);

                // Ensure Next button exists and is visible
                var $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                if ($nextBtn.length === 0) {
                    // If Next button doesn't exist, add it
                    $pagination.find('ul').append('<li class="page-item disabled" id="next-page-' +
                        sampleTypeIdClean + '"><a class="page-link" href="#">Next</a></li>');
                    $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                }
                $nextBtn.show().css('display', 'list-item');

                // Show first page (this will also update Next button state)
                showParameterPage(sampleTypeId, 1, itemsPerPage, $groups);

                // Ensure Next button is properly initialized
                if (totalPages <= 1) {
                    $nextBtn.addClass('disabled');
                } else {
                    $nextBtn.removeClass('disabled');
                }

                // Remove existing handlers to prevent duplicates
                $pagination.find('.page-link').off('click');

                // Handle pagination clicks
                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    var $link = $(this);
                    var page = parseInt($link.data('page'));
                    var $li = $link.closest('.page-item');
                    var sampleTypeIdClean = sampleTypeId.replace(/-/g, '');

                    if ($li.hasClass('disabled')) {
                        return;
                    }

                    // Handle Previous/Next
                    if ($link.text().trim() === 'Previous') {
                        var $activePage = $pagination.find('.page-item.active');
                        page = parseInt($activePage.find('.page-link').data('page')) - 1;
                    } else if ($link.text().trim() === 'Next') {
                        var $activePage = $pagination.find('.page-item.active');
                        page = parseInt($activePage.find('.page-link').data('page')) + 1;
                    }

                    var state = paginationState[sampleTypeId] || {};
                    var $groupsToUse = state.groups || $groups;
                    var totalPages = Math.ceil($groupsToUse.length / itemsPerPage);

                    if (page >= 1 && page <= totalPages) {
                        showParameterPage(sampleTypeId, page, itemsPerPage, $groupsToUse);
                        if (paginationState[sampleTypeId]) {
                            paginationState[sampleTypeId].currentPage = page;
                        }
                    }
                });
            }

            // Initialize pagination for filtered groups
            function initParameterPaginationForFiltered(sampleTypeId, $filteredGroups) {
                initParameterPagination(sampleTypeId, $filteredGroups);
            }

            // Show specific page of parameters
            function showParameterPage(sampleTypeId, page, itemsPerPage, $groupsToUse) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var $parameterList = $tabContent.find('.parameter-list-tab');

                // Use provided groups or get from state
                var $groups = $groupsToUse;
                if (!$groups || $groups.length === 0) {
                    var state = paginationState[sampleTypeId];
                    if (state && state.groups) {
                        $groups = state.groups;
                    } else {
                        $groups = $parameterList.find('.parameter-group-item');
                    }
                }

                var totalGroups = $groups.length;
                var totalPages = Math.ceil(totalGroups / itemsPerPage);

                // Hide all groups first
                $parameterList.find('.parameter-group-item').hide();

                // Show groups for current page
                var startIndex = (page - 1) * itemsPerPage;
                var endIndex = Math.min(startIndex + itemsPerPage, totalGroups);
                $groups.slice(startIndex, endIndex).show();

                // Update pagination UI
                var sampleTypeIdClean = sampleTypeId.replace(/-/g, '');
                var $pagination = $tabContent.find('.parameter-pagination-tab');

                // Update active page
                $pagination.find('.page-item').removeClass('active');
                $pagination.find('#page-' + page + '-' + sampleTypeIdClean).addClass('active');

                // Update Previous button
                var $prevBtn = $pagination.find('#prev-page-' + sampleTypeIdClean);
                if (page === 1) {
                    $prevBtn.addClass('disabled');
                } else {
                    $prevBtn.removeClass('disabled');
                }

                // Update Next button (ensure it exists and is visible)
                var $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                if ($nextBtn.length === 0) {
                    // If Next button doesn't exist, add it
                    $pagination.find('ul').append('<li class="page-item" id="next-page-' + sampleTypeIdClean +
                        '"><a class="page-link" href="#">Next</a></li>');
                    $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                }
                $nextBtn.show().css('display', 'list-item');

                if (page >= totalPages) {
                    $nextBtn.addClass('disabled');
                } else {
                    $nextBtn.removeClass('disabled');
                }

                // Update page info
                var startItem = startIndex + 1;
                var endItem = endIndex;
                $pagination.find('#page-info-' + sampleTypeIdClean).text(
                    'Menampilkan ' + startItem + '-' + endItem + ' dari ' + totalGroups + ' parameter'
                );
            }

            // Populate review for multiple samples
            // Make populateReviewMultiple globally accessible
            window.populateReviewMultiple = function populateReviewMultiple() {
                const reviewContent = document.getElementById('review-content');
                if (!reviewContent) {
                    console.error('Review content element not found');
                    return;
                }

                // Get global variables
                var selectedSampleTypes = window.selectedSampleTypes || [];
                var sampleTypeConfigs = window.sampleTypeConfigs || {};

                if (selectedSampleTypes.length === 0) {
                    reviewContent.innerHTML =
                        '<div class="alert alert-warning">Belum ada jenis sampel yang dipilih.</div>';
                    return;
                }

                var html = '';
                var grandTotal = 0;

                // Info message will be added after calculating total samples

                // Calculate total samples that will be created
                var totalSamplesToCreate = 0;
                selectedSampleTypes.forEach(function(type) {
                    var config = sampleTypeConfigs[type.id] || {};
                    if (config.packets && config.packets.length > 0) {
                        totalSamplesToCreate += config.packets.length;
                    }
                    if (config.additional_methods && config.additional_methods.length > 0) {
                        totalSamplesToCreate += 1; // Additional methods create 1 sample
                    }
                });

                // Update info message about multiple samples
                if (totalSamplesToCreate > 1) {
                    html += `
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Catatan:</strong> Sistem akan membuat <strong>${totalSamplesToCreate} sample</strong> 
                            dengan konfigurasi berbeda. Setiap paket akan menjadi sample terpisah dengan group_id yang sama.
                        </div>
                    `;
                }

                // Loop through each selected sample type
                var _kesmasAutoSampleCursor = parseInt(window.kesmasNextSampleNumber || 1, 10) || 1;
                selectedSampleTypes.forEach(function(type, index) {
                    var config = sampleTypeConfigs[type.id] || {};

                    // Skip if config is empty (no packets or additional methods)
                    var hasPackets = (config.packets && config.packets.length > 0);
                    var hasAdditionalMethods = (config.additional_methods && config.additional_methods
                        .length > 0);

                    if (!config || (!hasPackets && !hasAdditionalMethods)) {
                        return;
                    }

                    // Calculate cost for this sample type (from all packets + additional methods)
                    var sampleTypeCost = 0;
                    if (config.packets && config.packets.length > 0) {
                        config.packets.forEach(function(packet) {
                            sampleTypeCost += parseFloat(packet.packet_price) || 0;
                        });
                    }
                    if (config.additional_methods && config.additional_methods.length > 0) {
                        config.additional_methods.forEach(function(method) {
                            sampleTypeCost += parseFloat(method.price) || 0;
                        });
                    }
                    grandTotal += sampleTypeCost;

                    var sampleTypeIdClean = type.id.replace(/-/g, '');
                    var labIds = [];
                    var allMethodsCollect = [];
                    if (config.methods) {
                        allMethodsCollect = allMethodsCollect.concat(config.methods);
                    }
                    if (config.additional_methods) {
                        config.additional_methods.forEach(function(m) {
                            allMethodsCollect.push(m.method);
                        });
                    }
                    if (config.packets && config.packets.length > 0) {
                        config.packets.forEach(function(p) {
                            (p.methods || []).forEach(function(m) {
                                allMethodsCollect.push(m);
                            });
                        });
                    }
                    allMethodsCollect.forEach(function(methodString) {
                        var parts = String(methodString).split('_');
                        if (parts.length >= 2 && labIds.indexOf(parts[1]) === -1) {
                            labIds.push(parts[1]);
                        }
                    });
                    if (labIds.length === 0) {
                        $('.method-checkbox-tab:checked').each(function() {
                            var $pane = $(this).closest('.tab-pane[data-sample-type-id]');
                            if (!$pane.length || String($pane.attr('data-sample-type-id')) !== String(type.id)) {
                                return;
                            }
                            var parts = String($(this).attr('data-method') || '').split('_');
                            if (parts.length >= 2 && labIds.indexOf(parts[1]) === -1) {
                                labIds.push(parts[1]);
                            }
                        });
                    }
                    var labIdToSuffix = window.kesmasLabIdToSuffix || {};
                    labIds.sort(function(a, b) {
                        return (labIdToSuffix[a] || '99').localeCompare(labIdToSuffix[b] || '99');
                    });
                    var showKodeMan = window.kesmasIsNomorSampelManual;
                    var showKodeAuto = !showKodeMan;
                    var hasKesmasReview = (showKodeMan || showKodeAuto) && labIds.length > 0;

                    // Calculate total parameters for this sample type
                    var totalPacketMethods = 0;
                    if (config.packets && config.packets.length > 0) {
                        config.packets.forEach(function(p) {
                            totalPacketMethods += (p.methods || []).length;
                        });
                    }
                    var totalParams = totalPacketMethods + (config.additional_methods ? config
                        .additional_methods.length : 0);

                    html += `
                        <div class="cart-panel mb-4" style="border: 2px solid ${index === 0 ? '#667eea' : '#e2e8f0'};">
                            <div class="cart-panel-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0;">
                                <div class="cart-panel-title" style="color: white;">
                                    <i class="fa fa-vial"></i>
                                    ${type.code} - ${type.name}
                                </div>
                                <span class="badge" style="background: white; color: #667eea; font-weight: 700;">
                                    ${totalParams} Parameter
                                </span>
                            </div>
                            <div style="padding: 20px;">
                    `;

                    if (hasKesmasReview) {
                        var yRev = kesmasCurrentYear();
                        var typeCodeEsc = String(type.code || '').replace(/</g, '');

                        if (showKodeMan) {
                            html += `
                            <div class="cart-section-title">
                                <i class="fa fa-barcode"></i> Nomor sampel manual (wajib)
                            </div>
                        `;
                            labIds.forEach(function(labId) {
                                var labSuffix = labIdToSuffix[labId] || '01';
                                var labLabel = labSuffix === '01' ? 'Kimia' : 'Mikrobiologi';
                                var hiddenId = labIds.length > 1 ?
                                    'input_code_sample_' + sampleTypeIdClean + '_' + labSuffix :
                                    'input_code_sample_' + sampleTypeIdClean;
                                var hidEl = document.getElementById(hiddenId);
                                var initSpecDigits = window.kesmasIsNomorSampelManual ? '' :
                                    (hidEl ? kesmasParseMiddleDigits(hidEl.value) : '');
                                var fullPrefix = typeCodeEsc + '.' + labSuffix + '/';
                                html += `
                            <div class="mb-3">
                                <label class="small font-weight-bold d-block mb-1">No. sampel (spesimen) — ${labLabel} (${typeCodeEsc})</label>
                                <p class="small text-muted mb-1" style="margin-top:-2px;">Format: <code>${fullPrefix}[urut]/${yRev}</code></p>
                                <div class="card border-0 shadow-sm mb-2" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                    <div class="card-body d-flex flex-wrap align-items-center py-2 px-3" style="gap: 8px; font-weight: 600;">
                                        <span style="color: #667eea;">${fullPrefix}</span>
                                        <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control kesmas-klinik-specimen-review" placeholder="no_urut"
                                            data-specimen-lab-seg="${String(labSuffix).replace(/"/g, '&quot;')}"
                                            data-specimen-hidden-ids="${String(hiddenId).replace(/"/g, '&quot;')}"
                                            style="max-width: 120px; font-weight: 600; color: #667eea; text-align: center; height: 32px;"
                                            value="${String(initSpecDigits).replace(/"/g, '&quot;')}" />
                                        <span style="color: #667eea; white-space: nowrap;">/${yRev}</span>
                                    </div>
                                </div>
                            </div>`;
                            });
                        }

                        if (showKodeAuto) {
                            html += `
                            <div class="cart-section-title">
                                <i class="fa fa-barcode"></i> No. Sampel (otomatis)
                            </div>
                        `;
                            labIds.forEach(function(labId) {
                                var labSuffix = labIdToSuffix[labId] || '01';
                                var labLabel = labSuffix === '01' ? 'Kimia' : 'Mikrobiologi';
                                html += kesmasAutoSamplePreviewHtml(typeCodeEsc, labSuffix, labLabel, _kesmasAutoSampleCursor);
                                _kesmasAutoSampleCursor++;
                            });
                        }

                        if (labIds.length > 0) {
                            var ykInfo = kesmasCurrentYear();
                            html += `
                            <div class="mb-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 14px 16px; border-radius: 8px; border-left: 4px solid #2196f3;">
                                <div class="font-weight-bold mb-1" style="color: #1565c0;"><i class="fa fa-flask"></i> Nomor laboratorium</div>
                                <div class="small text-muted mb-1">Ditetapkan otomatis di <strong>akhir pemeriksaan / pengesahan hasil</strong> — tidak perlu diisi di sini.</div>
                                <div class="small text-muted">Format: <code>449.5/01/[urut]/${ykInfo}</code> (Kimia) · <code>449.5/02/[urut]/${ykInfo}</code> (Mikro)</div>
                            </div>`;
                        }
                    }

                    // Multiple Packets Section
                    if (config.packets && config.packets.length > 0) {
                        html += `
                            <div class="cart-section-title" style="margin-top: ${hasKesmasReview ? '15px' : '0'};">
                                <i class="fa fa-cube"></i> Paket (${config.packets.length} paket)
                            </div>
                        `;

                        config.packets.forEach(function(packet, packetIndex) {
                            html += `
                                <div class="cart-item cart-item-packet" style="margin-bottom: 10px;">
                                    <div class="cart-item-header">
                                        <span class="cart-item-name">
                                            <i class="fa fa-box"></i> ${packet.packet_name || 'Paket ' + (packetIndex + 1)}
                                        </span>
                                        <span class="cart-item-price">Rp ${(window.formatRupiah || formatRupiah)(packet.packet_price || 0)}</span>
                                    </div>
                                    <div class="cart-item-lab" style="color: #666; font-size: 12px;">
                                        <i class="fa fa-list"></i> ${(packet.methods || []).length} parameter
                                    </div>
                                </div>
                            `;
                        });
                    }

                    // Additional Methods Section
                    if (config.additional_methods && config.additional_methods.length > 0) {
                        var hasPackets = (config.packets && config.packets.length > 0);
                        html += `
                            <div class="cart-section-title" style="margin-top: ${hasPackets || hasKesmasReview ? '15px' : '0'};">
                                <i class="fa fa-flask"></i> Parameter ${hasPackets ? 'Tambahan' : 'Dipilih'} (${config.additional_methods.length})
                            </div>
                        `;

                        config.additional_methods.forEach(function(method) {
                            html += `
                                <div class="cart-item">
                                    <div class="cart-item-header">
                                        <span class="cart-item-name">${method.name}</span>
                                        <span class="cart-item-price">Rp ${(window.formatRupiah || formatRupiah)(method.price || 0)}</span>
                                    </div>
                                    <div class="cart-item-lab">
                                        <i class="fa fa-building"></i> ${method.lab_name}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    // Subtotal for this sample type
                    html += `
                                <hr class="cart-divider">
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span style="font-size: 16px; font-weight: 600;">
                                            <i class="fa fa-calculator"></i> Subtotal
                                        </span>
                                        <span style="font-size: 20px; font-weight: 700;">Rp ${(window.formatRupiah || formatRupiah)(sampleTypeCost || 0)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                // Grand Total
                var samplingFeePerType = parseFloat(document.getElementById('cost_sampling').value) || 0;
                var totalSamplingFee = samplingFeePerType * selectedSampleTypes.length;
                var finalTotal = grandTotal + totalSamplingFee;

                html += `
                    <div class="cart-total" style="margin-top: 20px;">
                        <div class="cart-total-row" style="background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 10px; border-radius: 10px; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                             <span class="cart-total-label" style="color: #4a5568; font-weight: 600;">
                                <i class="fa fa-vial"></i> TOTAL BIAYA SAMPLING
                            </span>
                            <span class="cart-total-price" style="color: #2d3748; font-weight: 700; font-size: 1.1rem;">
                                Rp ${(window.formatRupiah || formatRupiah)(totalSamplingFee || 0)}
                            </span>
                        </div>
                        <div class="cart-total-row" style="font-size: 1.3rem; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <span class="cart-total-label" style="color: white; font-weight: 600;">
                                <i class="fa fa-money-bill-wave"></i> TOTAL KESELURUHAN
                            </span>
                            <span class="cart-total-price" style="color: white; font-weight: 700; font-size: 1.5rem;">
                                Rp ${(window.formatRupiah || formatRupiah)(finalTotal || 0)}
                            </span>
                        </div>
                    </div>
                `;

                reviewContent.innerHTML = html;
                reviewContent.querySelectorAll('.kesmas-klinik-specimen-review').forEach(function(el) {
                    var raw = el.getAttribute('data-specimen-hidden-ids') || '';
                    var ids = raw.split(',').map(function(s) {
                        return s.trim();
                    }).filter(Boolean);
                    var hiddens = ids.map(function(id) {
                        return document.getElementById(id);
                    }).filter(Boolean);
                    if (hiddens.length) {
                        bindKesmasKlinikSpecimenInput(el, hiddens);
                    }
                });
                reviewContent.querySelectorAll('.kesmas-klinik-lab-review').forEach(function(el) {
                    bindKesmasKlinikLabUrutInput(el, el);
                });

                // Enable submit button after review is populated
                var $submitBtn = $('#submitAll');
                if ($submitBtn.length > 0) {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.removeAttr('disabled');
                }
            }

            function getPreviewCodesForSampleType(typeId) {
                var clean = String(typeId || '').replace(/-/g, '');
                var o = {};
                var typeCode = '';
                (window.selectedSampleTypes || []).forEach(function(t) {
                    if (String(t.id) === String(typeId)) {
                        typeCode = String(t.code || '');
                    }
                });

                // Primary: read directly from live review-panel specimen inputs.
                // Hidden inputs in $dynamicContainer can be stale (reset by updateSampleCodeCards
                // called from async AJAX callbacks while the user is on step 3), so we prefer
                // the live review inputs which are always up-to-date.
                var reviewContent = document.getElementById('review-content');
                if (window.kesmasIsNomorSampelManual && reviewContent) {
                    reviewContent.querySelectorAll('.kesmas-klinik-specimen-review').forEach(function(el) {
                        var hiddenIds = el.getAttribute('data-specimen-hidden-ids') || '';
                        if (hiddenIds.indexOf(clean) === -1) return;
                        var labSeg = el.getAttribute('data-specimen-lab-seg') || '01';
                        var d = String(el.value || '').replace(/\D/g, '');
                        if (!d) return;
                        var full = typeCode ?
                            kesmasComposeSampleCode(typeCode, labSeg, d, kesmasCurrentYear()) :
                            kesmasComposeKlinikSpecimen(d, kesmasCurrentYear(), labSeg);
                        if (labSeg === '01') o.code_sample_kimia = full;
                        else if (labSeg === '02') o.code_sample_mikro = full;
                        else o.code_sample = full;
                    });
                }

                // Fallback: hidden inputs (hindari preview otomatis 0001 di mode manual)
                if (!window.kesmasIsNomorSampelManual) {
                    var k01 = document.getElementById('input_code_sample_' + clean + '_01');
                    var k02 = document.getElementById('input_code_sample_' + clean + '_02');
                    var single = document.getElementById('input_code_sample_' + clean);
                    if (!o.code_sample_kimia && k01 && k01.value) o.code_sample_kimia = k01.value.trim();
                    if (!o.code_sample_mikro && k02 && k02.value) o.code_sample_mikro = k02.value.trim();
                    if (!o.code_sample && single && single.value) o.code_sample = single.value.trim();
                }
                return o;
            }

            function getKesmasNomerLabsForSampleType(typeId) {
                if (!window.kesmasIsNomorLabManual) {
                    return {};
                }
                var clean = String(typeId || '').replace(/-/g, '');
                var o = {};
                var k1 = document.getElementById('review_nomer_lab_' + clean + '_01');
                var k2 = document.getElementById('review_nomer_lab_' + clean + '_02');
                if (k1 && String(k1.value).trim() !== '') {
                    var n1 = parseInt(String(k1.value).replace(/\D/g, ''), 10);
                    if (!isNaN(n1)) o.nomer_lab_kimia = n1;
                }
                if (k2 && String(k2.value).trim() !== '') {
                    var n2 = parseInt(String(k2.value).replace(/\D/g, ''), 10);
                    if (!isNaN(n2)) o.nomer_lab_mikro = n2;
                }
                return o;
            }

            // Update form submission to send array of samples
            $('#form-create-sample').on('submit', function(e) {
                // Check if multiple sample types are selected
                if (typeof selectedSampleTypes !== 'undefined' && selectedSampleTypes && selectedSampleTypes
                    .length > 0) {
                    e.preventDefault();

                    kesmasSyncSpecimenFromReview();
                    var sampleCheckMulti = kesmasValidateManualSampleNumbers();
                    if (!sampleCheckMulti.ok) {
                        swal({
                            title: 'Perhatian',
                            text: sampleCheckMulti.message,
                            icon: 'warning'
                        });
                        return false;
                    }

                    // Build samples array
                    // IMPORTANT: Each packet creates a separate sample with same group_id
                    var samples = [];
                    selectedSampleTypes.forEach(function(type) {
                        var config = sampleTypeConfigs[type.id] || {};

                        // Get titik_pengambilan for this sample type
                        var titikPengambilan = (config && config.titik_pengambilan) ? config
                            .titik_pengambilan : '';
                        var previewCodes = getPreviewCodesForSampleType(type.id);

                        // Process each packet separately (each packet = 1 sample)
                        if (config.packets && config.packets.length > 0) {
                            config.packets.forEach(function(packet) {
                                samples.push(Object.assign({
                                    sample_type_id: type.id,
                                    packet_id: packet.packet_id,
                                    packet_name: packet.packet_name,
                                    packet_price: packet.packet_price,
                                    methods: packet.methods || [],
                                    cost_samples: packet.packet_price || 0,
                                    titik_pengambilan: titikPengambilan
                                }, previewCodes));
                            });
                        }

                        // If there are additional methods (not from packet), create a sample for them too
                        if (config.additional_methods && config.additional_methods.length > 0) {
                            var additionalMethods = [];
                            config.additional_methods.forEach(function(m) {
                                additionalMethods.push(m.method);
                            });

                            // Calculate cost for additional methods
                            var additionalCost = 0;
                            additionalMethods.forEach(function(methodString) {
                                var parts = methodString.split('_');
                                if (parts.length >= 3) {
                                    additionalCost += parseFloat(parts[2]) || 0;
                                }
                            });

                            samples.push(Object.assign({
                                sample_type_id: type.id,
                                packet_id: null,
                                packet_name: null,
                                packet_price: 0,
                                methods: additionalMethods,
                                cost_samples: additionalCost,
                                titik_pengambilan: titikPengambilan
                            }, previewCodes));
                        }
                    });

                    // Create FormData
                    var formData = new FormData(this);

                    // Append samples as JSON string
                    formData.append('samples', JSON.stringify(samples));

                    // Get CSRF token
                    var csrfToken = $('meta[name="csrf-token"]').attr('content') ||
                        $('input[name="_token"]').val() ||
                        $('#csrf-token').val();
                    if (csrfToken) {
                        formData.append('_token', csrfToken);
                    }

                    // Debug: Log FormData

                    // Submit via AJAX
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status) {
                                swal({
                                    title: "Berhasil!",
                                    text: response.pesan,
                                    icon: "success",
                                    buttons: false,
                                    timer: 1500
                                }).then(function() {
                                    window.location.href = response.url_redirect ||
                                        "{{ route('elits-samples.index', $id) }}";
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "error"
                                });
                            }
                        },
                        error: function(xhr) {
                            var message = "Gagal menyimpan sample!";
                            if (xhr.responseJSON && xhr.responseJSON.pesan) {
                                message = xhr.responseJSON.pesan;
                            } else if (xhr.status === 419) {
                                message =
                                    "Session expired. Silakan refresh halaman dan coba lagi.";
                            }
                            swal({
                                title: "Error!",
                                text: message,
                                icon: "error"
                            });
                        }
                    });
                }
                // If no multiple selection, use original form submission
            });
        });
        // ============================================================
        // MODAL TAMBAH PARAMETER - 2 Step
        // ============================================================
        var _methodDataBaseUrl   = @json(rtrim(url('/elits-samples/method'), '/'));
        var _methodUpdateBaseUrl = @json(rtrim(url('/elits-samples/method'), '/'));
        var _csrfTokenEdit       = @json(csrf_token());

        $(document).on('click', '.btn-toggle-edit-parameter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $group = $(this).closest('.parameter-group-item');
            var on = !$group.hasClass('parameter-edit-mode');
            $group.toggleClass('parameter-edit-mode', on);
            $(this).toggleClass('active', on);
        });

        /* ── POPUP EDIT METHOD ── */
        $(document).on('click', '.btn-pencil-edit-method', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var methodId = $(this).data('method-id');
            var methodName = $(this).data('method-name') || '—';
            if (!methodId) return;

            // Ambil konteks jenis sampel dari baris parameter yang diklik
            var currentStId = $(this).closest('.method-row-tab').data('sample-type-id') || '';
            var $tabAnchor  = currentStId
                ? $('[href="#tab-' + currentStId.replace(/-/g, '') + '"]')
                : $([]);
            var currentStName = $tabAnchor.length
                ? $.trim($tabAnchor.clone().children('.badge').remove().end().text())
                : '';

            $('#modal-edit-param-method').data('current-st-id',   currentStId);
            $('#modal-edit-param-method').data('current-st-name', currentStName);

            $('#mepm-title').html('<i class="fa fa-pencil-alt mr-2"></i>' + $('<span>').text(methodName).html());
            $('#mepm-body-wrap').hide();
            $('#mepm-loading').show();
            $('#mepm-alert').hide();
            $('#btn-mepm-save').hide();
            $('#mepm-method-id').val(methodId);
            $('#modal-edit-param-method').modal('show');

            $.ajax({
                url: _methodDataBaseUrl + '/' + encodeURIComponent(methodId) + '/data',
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': _csrfTokenEdit, 'X-Requested-With': 'XMLHttpRequest' },
                success: function(r) {
                    if (!r.status) { mepmShowAlert('danger', r.pesan || 'Gagal memuat data'); return; }
                    var m = r.method;
                    // Text fields
                    $('#mepm-params-method').val(m.params_method || '');
                    $('#mepm-name-method').val(m.name_method || '');
                    $('#mepm-price-bahan').val(m.price_bahan || 0);
                    $('#mepm-price-sarana').val(m.price_sarana || 0);
                    $('#mepm-price-jasa').val(m.price_jasa || 0);
                    $('#mepm-price-total').val(m.price_total_method || 0);
                    // Radios
                    $('input[name="mepm_id_pdam_method"]').prop('checked', false);
                    $('input[name="mepm_id_pdam_method"][value="' + (m.id_pdam_method || '0') + '"]').prop('checked', true);
                    $('input[name="mepm_berhubungan_kesehatan"]').prop('checked', false);
                    $('input[name="mepm_berhubungan_kesehatan"][value="' + (m.berhubungan_kesehatan ?? '') + '"]').prop('checked', true);
                    $('input[name="mepm_jenis_parameter_kimia"]').prop('checked', false);
                    $('input[name="mepm_jenis_parameter_kimia"][value="' + (m.jenis_parameter_kimia ?? '') + '"]').prop('checked', true);
                    $('input[name="mepm_is_ready"]').prop('checked', false);
                    $('input[name="mepm_is_ready"][value="' + (m.is_ready || '1') + '"]').prop('checked', true);
                    // Opsi hasil
                    var hasOption = m.is_option == 1;
                    $('#mepm-is-option').prop('checked', hasOption);
                    $('#mepm-option-wrap').toggle(hasOption);
                    $('#mepm-option-rows').empty();
                    if (hasOption && m.option) {
                        var opts = m.option.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                        opts.forEach(function(v, idx) { mepmAddOptionRow(v, idx === 0 && opts.length === 1); });
                    } else if (hasOption) {
                        mepmAddOptionRow('', true);
                    }
                    mepmUpdateOptionHidden();
                    // Harga per jenis sampel — isi nilai lalu filter
                    $('#mepm-stp-table tbody tr').removeClass('mepm-current-st-row').each(function() {
                        var stId = $(this).data('st-id');
                        $(this).find('input').val(r.sample_type_prices[stId] !== undefined ? r.sample_type_prices[stId] : '');
                    });
                    mepmFilterStpTable(currentStId, currentStName);
                    // Laboratorium checkboxes
                    $('#mepm-lab-list input[type="checkbox"]').each(function() {
                        var labId = $(this).val();
                        $(this).prop('checked', r.method_laboratorium_ids.indexOf(labId) !== -1);
                    });
                    $('#mepm-loading').hide();
                    $('#mepm-body-wrap').show();
                    $('#btn-mepm-save').show();
                },
                error: function(xhr) {
                    mepmShowAlert('danger', 'Gagal memuat data parameter.');
                }
            });
        });

        /* Filter tabel harga per jenis sampel sesuai konteks */
        function mepmFilterStpTable(stId, stName) {
            var $bar  = $('#mepm-stp-filter-bar');
            var $rows = $('#mepm-stp-table tbody tr');
            if (!stId) {
                $rows.show();
                $bar.addClass('d-none').css('display', '');
                return;
            }
            $rows.hide();
            $rows.filter('[data-st-id="' + stId + '"]').addClass('mepm-current-st-row').show();
            $('#mepm-stp-filter-label').text(stName || 'Jenis sampel ini');
            $('#mepm-stp-toggle-all').text('Tampilkan semua jenis');
            $bar.removeClass('d-none').css('display', 'flex');
        }

        $(document).on('click', '#mepm-stp-toggle-all', function() {
            var $rows = $('#mepm-stp-table tbody tr');
            if ($rows.filter(':hidden').length === 0) {
                // Sudah tampil semua → kembali ke filter
                var stId   = $('#modal-edit-param-method').data('current-st-id');
                var stName = $('#modal-edit-param-method').data('current-st-name');
                mepmFilterStpTable(stId, stName);
            } else {
                // Tampil semua
                $rows.show();
                $(this).text('Filter jenis ini saja');
                // Scroll ke baris yang di-highlight
                var $cur = $rows.filter('.mepm-current-st-row');
                if ($cur.length) {
                    var $wrap = $cur.closest('.table-responsive');
                    $wrap.scrollTop($wrap.scrollTop() + $cur.position().top - $wrap.position().top);
                }
            }
        });

        function mepmShowAlert(type, msg) {
            $('#mepm-loading').hide();
            $('#mepm-body-wrap').hide();
            $('#mepm-alert').removeClass('alert-danger alert-success alert-warning').addClass('alert-' + type).text(msg).show();
        }

        function mepmAddOptionRow(value, isSingle) {
            var $btn = isSingle
                ? '<button type="button" class="btn btn-success mepm-btn-add-option" title="Tambah"><i class="fa fa-plus"></i></button>'
                : '<button type="button" class="btn btn-danger mepm-btn-remove-option" title="Hapus"><i class="fa fa-times"></i></button>';
            var $row = $('<div class="input-group mb-2 mepm-option-row">' +
                '<input type="text" class="form-control mepm-option-input" placeholder="Masukkan opsi" value="' + $('<span>').text(value).html() + '">' +
                '<div class="input-group-append">' + $btn + '</div></div>');
            $('#mepm-option-rows').append($row);
        }

        function mepmUpdateOptionHidden() {
            var opts = [];
            $('.mepm-option-input').each(function() {
                var v = $(this).val().trim(); if (v) opts.push(v);
            });
            $('#mepm-option-hidden').val(opts.join(', '));
        }

        $(document).on('input', '.mepm-option-input', mepmUpdateOptionHidden);
        $(document).on('click', '.mepm-btn-add-option', function() {
            $(this).closest('.mepm-option-row').find('.mepm-btn-add-option')
                .removeClass('btn-success mepm-btn-add-option')
                .addClass('btn-danger mepm-btn-remove-option')
                .html('<i class="fa fa-times"></i>').attr('title', 'Hapus');
            mepmAddOptionRow('', false);
        });
        $(document).on('click', '.mepm-btn-remove-option', function() {
            $(this).closest('.mepm-option-row').remove();
            mepmUpdateOptionHidden();
            if ($('#mepm-option-rows .mepm-option-row').length === 1) {
                $('#mepm-option-rows .mepm-btn-remove-option')
                    .removeClass('btn-danger mepm-btn-remove-option')
                    .addClass('btn-success mepm-btn-add-option')
                    .html('<i class="fa fa-plus"></i>').attr('title', 'Tambah');
            }
        });
        $('#mepm-is-option').on('change', function() {
            var on = $(this).is(':checked');
            $('#mepm-option-wrap').toggle(on);
            if (on && $('#mepm-option-rows .mepm-option-row').length === 0) mepmAddOptionRow('', true);
            mepmUpdateOptionHidden();
        });
        $(document).on('input', '#mepm-price-bahan, #mepm-price-sarana, #mepm-price-jasa', function() {
            var t = (parseInt($('#mepm-price-bahan').val()) || 0)
                  + (parseInt($('#mepm-price-sarana').val()) || 0)
                  + (parseInt($('#mepm-price-jasa').val()) || 0);
            $('#mepm-price-total').val(t);
        });

        $(document).on('click', '#btn-mepm-save', function() {
            var methodId = $('#mepm-method-id').val();
            if (!methodId) return;
            var labIds = [];
            $('#mepm-lab-list input[type="checkbox"]:checked').each(function() { labIds.push($(this).val()); });

            var stPrices = {};
            $('#mepm-stp-table tbody tr').each(function() {
                var stId = $(this).data('st-id');
                var val  = $(this).find('input').val().trim();
                if (val !== '') stPrices['sample_type_price[' + stId + ']'] = val;
            });

            var payload = {
                _token:                _csrfTokenEdit,
                params_method:         $('#mepm-params-method').val(),
                name_method:           $('#mepm-name-method').val(),
                id_pdam_method:        $('input[name="mepm_id_pdam_method"]:checked').val() || '0',
                berhubungan_kesehatan: $('input[name="mepm_berhubungan_kesehatan"]:checked').val() ?? '',
                jenis_parameter_kimia: $('input[name="mepm_jenis_parameter_kimia"]:checked').val() ?? '',
                is_ready:              $('input[name="mepm_is_ready"]:checked').val() || '1',
                price_bahan:           parseInt($('#mepm-price-bahan').val()) || 0,
                price_sarana:          parseInt($('#mepm-price-sarana').val()) || 0,
                price_jasa:            parseInt($('#mepm-price-jasa').val()) || 0,
                price_total_method:    parseInt($('#mepm-price-total').val()) || 0,
                'laboratoriumAttributes[]': labIds,
                option:                $('#mepm-option-hidden').val() || '',
            };
            if ($('#mepm-is-option').is(':checked')) { payload['is_option'] = '1'; }
            // Merge harga per jenis sampel
            $.extend(payload, stPrices);

            $('#btn-mepm-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');
            $.ajax({
                url: _methodUpdateBaseUrl + '/' + encodeURIComponent(methodId) + '/update',
                type: 'POST',
                data: payload,
                headers: { 'X-CSRF-TOKEN': _csrfTokenEdit, 'X-Requested-With': 'XMLHttpRequest' },
                success: function(r) {
                    $('#btn-mepm-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                    if (r.status) {
                        var defaultPrice  = r.price_total_method || 0;
                        var newPricesMap  = r.sample_type_prices  || {};
                        var newPricesJson = JSON.stringify(newPricesMap);

                        // Update SEMUA checkbox untuk method ini (bisa ada di banyak tab)
                        $('.method-checkbox-tab[data-method-id="' + methodId + '"]').each(function() {
                            var $cb   = $(this);
                            var stId  = String($cb.attr('data-sample-type-id') || '').trim();

                            // Perbarui data-prices-by-sample-type (map lengkap)
                            $cb.attr('data-prices-by-sample-type', newPricesJson);

                            // Perbarui default price (fallback)
                            $cb.attr('data-default-price', defaultPrice).data('default-price', defaultPrice);

                            // Hitung harga yang berlaku untuk jenis sampel ini
                            var resolved = defaultPrice;
                            if (stId && newPricesMap[stId] !== undefined) {
                                resolved = newPricesMap[stId];
                            } else if (typeof window.resolvePriceFromMap === 'function') {
                                resolved = window.resolvePriceFromMap(newPricesMap, stId, defaultPrice);
                            }
                            resolved = Math.round(parseFloat(resolved) || defaultPrice);

                            // Perbarui data-price dan atribut data-method (bagian harga di value)
                            $cb.attr('data-price', resolved).data('price', resolved);
                            var parts = String($cb.attr('data-method') || '').split('_');
                            if (parts.length >= 3) {
                                parts[2] = String(resolved);
                                $cb.attr('data-method', parts.join('_'));
                            }

                            // Perbarui teks label harga
                            $cb.closest('label').find('span.text-muted')
                               .text('(Rp ' + resolved.toLocaleString('id-ID') + ')');

                            // Jika sudah tercentang, trigger change agar total terupdate
                            if ($cb.is(':checked')) { $cb.trigger('change'); }
                        });

                        $('#modal-edit-param-method').modal('hide');
                        swal('Berhasil!', r.pesan, 'success');
                    } else {
                        mepmShowAlert('danger', r.pesan || 'Gagal menyimpan');
                    }
                },
                error: function(xhr) {
                    $('#btn-mepm-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                    var msg = 'Gagal menyimpan perubahan.';
                    if (xhr.responseJSON && xhr.responseJSON.pesan) msg = xhr.responseJSON.pesan;
                    mepmShowAlert('danger', msg);
                }
            });
        });

        $(document).on('hidden.bs.modal', '#modal-edit-param-method', function() {
            $('#mepm-alert').hide();
            $('#mepm-body-wrap').hide();
            $('#btn-mepm-save').hide();
            $('#mepm-loading').show();
            // Reset filter tabel
            $('#mepm-stp-table tbody tr').show().removeClass('mepm-current-st-row');
            $('#mepm-stp-filter-bar').addClass('d-none').css('display', '');
            $('#modal-edit-param-method').removeData('current-st-id').removeData('current-st-name');
        });

        /* ── TAMBAH BAKU MUTU DARI PARAMETER YANG SUDAH ADA ── */
        $(document).on('click', '.btn-tambah-baku-mutu-exist', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var labId   = $(this).data('lab-id');
            var labName = $(this).data('lab-name');
            var stId    = $(this).data('sample-type-id');
            var stName  = $(this).data('sample-type-name') || '';

            var isKimia      = labId === '3416ca19-6c69-4e5f-a004-ae8275de7644';
            var isMikro      = labId === 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';
            var isMakMinLain = /makanan|minuman|lainnya/i.test(stName);

            // Kumpulkan method yang BELUM punya baku mutu untuk jenis sampel ini
            var $group = $('.parameter-group-item[data-lab-group="' + labId + '"][data-sample-type-id="' + stId + '"]');
            var methods = [];
            $group.find('.method-row-tab').each(function() {
                var $row = $(this);
                var $cb  = $row.find('.method-checkbox-tab').first();
                var mid  = $cb.data('method-id') || $row.data('method-id');
                if (!mid) return;

                // Cek apakah method ini SUDAH punya baku mutu untuk sample type saat ini
                var bakuMutuRaw = $row.attr('data-baku-mutu-sampletypes') || '[]';
                var bakuMutuIds = [];
                try { bakuMutuIds = JSON.parse(bakuMutuRaw); } catch (e) {}
                var hasBakuMutu = bakuMutuIds.some(function(id) {
                    return String(id) === String(stId);
                });
                if (hasBakuMutu) return; // sudah ada baku mutu → skip

                var name  = $cb.data('name') || $row.find('strong').first().text().trim();
                var price = parseInt($cb.attr('data-default-price')) || 0;
                methods.push({ id: mid, name: name, price: price });
            });

            if (!methods.length) {
                swal(
                    'Tidak Ada Parameter Tanpa Baku Mutu',
                    'Semua parameter di grup ini sudah memiliki baku mutu untuk jenis sampel ini.',
                    'info'
                );
                return;
            }

            // Set konteks modal
            $('#modal-tambah-param').data('lab-id',          labId);
            $('#modal-tambah-param').data('lab-name',         labName);
            $('#modal-tambah-param').data('sample-type-id',   stId);
            $('#modal-tambah-param').data('sample-type-name', stName);
            $('#modal-tambah-param').data('is-kimia',         isKimia);
            $('#modal-tambah-param').data('is-mikro',         isMikro);
            $('#modal-tambah-param').data('is-mml',           isMakMinLain);

            // Badge lab
            $('#modal-param-lab-badge').text(labName).removeClass('badge-success badge-info badge-secondary');
            if (isKimia) $('#modal-param-lab-badge').addClass('badge-success');
            else if (isMikro) $('#modal-param-lab-badge').addClass('badge-info');
            else $('#modal-param-lab-badge').addClass('badge-secondary');

            // Bangun daftar parameter di Step 0 via innerHTML (paling andal)
            var listHtml = '';
            methods.forEach(function(m) {
                // HTML-escape nama agar aman untuk atribut & teks
                var safeName  = $('<span>').text(m.name).html();
                var safeId    = String(m.id).replace(/"/g, '');
                var safePrice = parseInt(m.price) || 0;
                var priceFormatted = safePrice.toLocaleString('id-ID');
                listHtml +=
                    '<div class="mpicker-row align-items-center p-2"' +
                    ' data-method-id="' + safeId + '"' +
                    ' data-method-price="' + safePrice + '"' +
                    ' style="display:flex;cursor:pointer;border-bottom:1px solid #f0f0f0;border-radius:4px;margin-bottom:2px;">' +
                    '<span class="flex-grow-1" style="font-size:13px;"><strong>' + safeName + '</strong></span>' +
                    '<span class="text-muted ml-2" style="font-size:12px;white-space:nowrap;">Rp ' + priceFormatted + '</span>' +
                    '<span class="btn btn-sm btn-primary ml-2 py-0 px-2" style="font-size:12px;">Pilih</span>' +
                    '</div>';
            });
            document.getElementById('mpicker-list').innerHTML = listHtml;
            $('#mpicker-count').text(methods.length);
            $('#mpicker-search').val('');
            bindMpickerSearch(); // ikat event langsung setelah elemen siap

            // Tampilkan Step 0, sembunyikan step lain
            $('#modal-param-step0').show();
            $('#modal-param-step1').hide();
            $('#modal-param-step2').hide();
            $('#modal-footer-step0').show();
            $('#modal-footer-step1').hide();
            $('#modal-footer-step2').hide();
            $('#modal-param-step-indicator-1').removeClass('active done');
            $('#modal-param-step-indicator-2').removeClass('active done');
            $('#modal-tambah-param-title').text('Tambah Parameter — Pilih Parameter Tanpa Baku Mutu');
            $('#modal-tambah-param').modal('show');
        });

        // Filter list parameter di Step 0
        // Gunakan delegasi ke #modal-tambah-param (bukan document) agar tidak
        // terganggu oleh focus-trapping Bootstrap
        $('#modal-tambah-param').on('input keyup', '#mpicker-search', function() {
            var q = $.trim($(this).val()).toLowerCase();
            $('#mpicker-list .mpicker-row').each(function() {
                var name = $(this).find('strong').first().text().toLowerCase();
                if (q === '' || name.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Setelah modal fully shown: fokus ke search input (jika Step 0 aktif)
        $('#modal-tambah-param').on('shown.bs.modal', function() {
            if ($('#modal-param-step0').is(':visible')) {
                $('#mpicker-search').trigger('focus');
            }
        });

        function bindMpickerSearch() {
            // Rebind langsung sebagai fallback tambahan
            $('#mpicker-search').off('.mpicker').on('input.mpicker keyup.mpicker', function() {
                var q = $.trim($(this).val()).toLowerCase();
                $('#mpicker-list .mpicker-row').each(function() {
                    var name = $(this).find('strong').first().text().toLowerCase();
                    if (q === '' || name.indexOf(q) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        }

        // Hover style pada baris picker
        $(document).on('mouseenter', '.mpicker-row', function() {
            $(this).css('background', '#f0f4ff');
        }).on('mouseleave', '.mpicker-row', function() {
            $(this).css('background', '');
        });

        // Pilih parameter → loncat ke Step 2
        // Nama dibaca dari <strong> (bukan data attribute) agar encoding aman
        $(document).on('click', '.mpicker-row', function() {
            var methodId   = $(this).attr('data-method-id');
            var paramName  = $(this).find('strong').first().text().trim();
            var priceTotal = parseInt($(this).attr('data-method-price')) || 0;
            addExistingParameterWithoutBakuMutu(methodId, paramName, priceTotal);
        });

        function addExistingParameterWithoutBakuMutu(methodId, paramName, priceTotal) {
            $('#modal-tambah-param').data('new-method-id',   methodId);
            $('#modal-tambah-param').data('new-param-name',  paramName);
            $('#modal-tambah-param').data('new-price-total', priceTotal);
            $('#modal-tambah-param').data('new-method-has-bm', false);
            injectNewParameter(true);
            $('#modal-tambah-param').modal('hide');
            swal('Berhasil', 'Parameter ditambahkan tanpa baku mutu. Lengkapi baku mutu saat Baca Hasil.', 'success');
        }

        $(document).on('click', '.btn-tambah-parameter', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var labId     = $(this).data('lab-id');
            var labName   = $(this).data('lab-name');
            var stId      = $(this).data('sample-type-id');
            var stName    = $(this).data('sample-type-name') || '';

            // Tentukan tipe lab
            var isKimia = labId === '3416ca19-6c69-4e5f-a004-ae8275de7644';
            var isMikro = labId === 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';

            // Deteksi apakah jenis sampel Makanan/Minuman/Lainnya
            var isMakMinLain = /makanan|minuman|lainnya/i.test(stName);

            // Set state modal
            $('#modal-tambah-param').data('lab-id',        labId);
            $('#modal-tambah-param').data('lab-name',      labName);
            $('#modal-tambah-param').data('sample-type-id',   stId);
            $('#modal-tambah-param').data('sample-type-name', stName);
            $('#modal-tambah-param').data('is-kimia',      isKimia);
            $('#modal-tambah-param').data('is-mikro',      isMikro);
            $('#modal-tambah-param').data('is-mml',        isMakMinLain);

            // Reset form step 1
            $('#form-step1-param')[0].reset();
            // Pre-set laboratorium hidden field
            $('#modal-param-lab-id').val(labId);

            // Badge lab
            $('#modal-param-lab-badge').text(labName).removeClass('badge-success badge-info badge-secondary');
            if (isKimia) $('#modal-param-lab-badge').addClass('badge-success');
            else if (isMikro) $('#modal-param-lab-badge').addClass('badge-info');
            else $('#modal-param-lab-badge').addClass('badge-secondary');

            // Tampilkan step 1, sembunyikan step 0 & 2
            $('#modal-param-step0').hide();
            $('#modal-param-step1').show();
            $('#modal-param-step2').hide();
            $('#modal-footer-step0').hide();
            $('#modal-footer-step1').show();
            $('#modal-footer-step2').hide();
            $('#modal-param-step-indicator-1').addClass('active').removeClass('done');
            $('#modal-param-step-indicator-2').removeClass('active done');
            $('#modal-tambah-param-title').text('Tambah Parameter Baru — Step 1: Detail Parameter');
            $('#modal-param-result-method-id').val('');

            $('#modal-tambah-param').modal('show');
        });

        // ---- Searchable Dropdown (SDD) — tanpa Select2 ----
        // Tutup semua panel SDD saat klik di luar
        $(document).on('mousedown', function(e) {
            if (!$(e.target).closest('.sdd-wrap').length) {
                $('.sdd-wrap').removeClass('sdd-open');
            }
        });
        // Buka / tutup panel saat klik area display
        $(document).on('mousedown', '.sdd-display', function(e) {
            e.stopPropagation();
            var $wrap = $(this).closest('.sdd-wrap');
            var wasOpen = $wrap.hasClass('sdd-open');
            $('.sdd-wrap').removeClass('sdd-open');
            if (!wasOpen) {
                $wrap.addClass('sdd-open');
                $wrap.find('.sdd-search').val('').trigger('input').focus();
            }
        });
        // Filter list saat mengetik
        $(document).on('input', '.sdd-search', function() {
            var q = $(this).val().toLowerCase();
            $(this).closest('.sdd-panel').find('.sdd-list li').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(q) !== -1);
            });
        });
        // Pilih item
        $(document).on('mousedown', '.sdd-list li', function(e) {
            e.stopPropagation();
            var $wrap  = $(this).closest('.sdd-wrap');
            var val    = $(this).data('value');
            var label  = $(this).text();
            $wrap.find('.sdd-display').text(label).toggleClass('sdd-placeholder', !val);
            // tulis nilai ke hidden input berdasarkan id wrap
            if ($wrap.attr('id') === 'sdd-unit')          { $('#modal-bm-unit-id').val(val); }
            if ($wrap.attr('id') === 'sdd-library')       { $('#modal-bm-library-id').val(val); }
            if ($wrap.attr('id') === 'sdd-jenis-makanan') { $('#modal-bm-jenis-makanan-id').val(val); }
            $wrap.removeClass('sdd-open');
        });
        // Reset SDD dan field MML saat modal ditutup
        $(document).on('hidden.bs.modal', '#modal-tambah-param', function() {
            $('#sdd-unit').removeClass('sdd-open')
                .find('.sdd-display').text('— Pilih Satuan —').addClass('sdd-placeholder');
            $('#modal-bm-unit-id').val('');
            $('#sdd-library').removeClass('sdd-open')
                .find('.sdd-display').text('— Pilih Acuan —').addClass('sdd-placeholder');
            $('#modal-bm-library-id').val('');
            $('#sdd-jenis-makanan').removeClass('sdd-open')
                .find('.sdd-display').text('— Pilih Jenis Makanan —').addClass('sdd-placeholder');
            $('#modal-bm-jenis-makanan-id').val('');
            $('#modal-bm-mml-section').hide();
            $('input[name="modal_bm_tipe_nilai"][value="kuantitatif"]').prop('checked', true);
        });

        // Step 1 → Step 2
        $(document).on('click', '#btn-modal-param-next', function() {
            var labId       = $('#modal-tambah-param').data('lab-id');
            var isKimia     = $('#modal-tambah-param').data('is-kimia');
            var isMikro     = $('#modal-tambah-param').data('is-mikro');
            var stId        = $('#modal-tambah-param').data('sample-type-id');

            var paramsMethod = $.trim($('#modal-param-params-method').val());
            var nameMethod   = $.trim($('#modal-param-name-method').val());
            if (!paramsMethod || !nameMethod) {
                swal('Peringatan', 'Nama Parameter dan Metode wajib diisi!', 'warning');
                return;
            }

            // AJAX store method
            var formData = {
                _token: $('#csrf-token').val() || $('input[name="_token"]').first().val(),
                params_method: paramsMethod,
                name_method: nameMethod,
                berhubungan_kesehatan: $('input[name="modal_berhubungan_kesehatan"]:checked').val() || '0',
                jenis_parameter_kimia: $('input[name="modal_jenis_parameter_kimia"]:checked').val() || '',
                is_ready: $('input[name="modal_is_ready"]:checked').val() || '1',
                price_bahan: parseInt($('#modal-param-price-bahan').val()) || 0,
                price_sarana: parseInt($('#modal-param-price-sarana').val()) || 0,
                price_jasa: parseInt($('#modal-param-price-jasa').val()) || 0,
                price_total_method: parseInt($('#modal-param-price-total').val()) || 0,
                id_pdam_method: '0',
                laboratoriumAttributes: [labId]
            };

            $('#btn-modal-param-next').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            var csrfToken = $('#csrf-token').val() || $('input[name="_token"]').first().val();
            $.ajax({
                url: "{{ route('elits-methods.store') }}",
                type: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    $('#btn-modal-param-next').prop('disabled', false).html('<i class="fa fa-check mr-1"></i>Simpan Parameter');
                    if (resp.status) {
                        // Simpan method_id untuk inject langsung ke daftar parameter
                        $('#modal-param-result-method-id').val(resp.method_id);
                        $('#modal-tambah-param').data('new-method-has-bm', false);

                        // Simpan info untuk inject parameter setelah baku mutu berhasil
                        var priceTotal = parseInt($('#modal-param-price-total').val()) || 0;
                        $('#modal-tambah-param').data('new-method-id',   resp.method_id);
                        $('#modal-tambah-param').data('new-param-name',  paramsMethod);
                        $('#modal-tambah-param').data('new-price-total', priceTotal);
                        injectNewParameter(true);
                        $('#modal-tambah-param').modal('hide');
                        swal('Berhasil', 'Parameter baru ditambahkan tanpa baku mutu. Lengkapi baku mutu saat Baca Hasil.', 'success');
                    } else {
                        swal('Gagal', resp.pesan || 'Gagal menyimpan parameter!', 'error');
                    }
                },
                error: function(xhr) {
                    $('#btn-modal-param-next').prop('disabled', false).html('<i class="fa fa-check mr-1"></i>Simpan Parameter');
                    var msg = 'Gagal menyimpan parameter!';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    swal('Error', msg, 'error');
                }
            });
        });

        // Toggle sub baku mutu di modal
        $(document).on('change', 'input[name="modal_bm_is_sub"]', function() {
            if ($(this).val() === 'true') {
                $('#modal-bm-no-sub').hide();
                $('#modal-bm-sub-container').show();
            } else {
                $('#modal-bm-no-sub').show();
                $('#modal-bm-sub-container').hide();
            }
        });

        // Harga total otomatis di modal
        $(document).on('input', '#modal-param-price-bahan, #modal-param-price-sarana, #modal-param-price-jasa', function() {
            var total = (parseInt($('#modal-param-price-bahan').val()) || 0)
                      + (parseInt($('#modal-param-price-sarana').val()) || 0)
                      + (parseInt($('#modal-param-price-jasa').val()) || 0);
            $('#modal-param-price-total').val(total);
        });

        // Simpan baku mutu (step 2)
        $(document).on('click', '#btn-modal-bm-save', function() {
            var storeUrl = $('#modal-bm-store-url').val();
            var isSub    = $('input[name="modal_bm_is_sub"]:checked').val() === 'true';

            var isMML    = $('#modal-tambah-param').data('is-mml');
            var tipeNilai = $('input[name="modal_bm_tipe_nilai"]:checked').val() || '';

            // Validasi client-side untuk Makanan/Minuman/Lainnya
            if (isMML && !tipeNilai) {
                swal('Peringatan', 'Tipe Nilai Baku Mutu wajib dipilih untuk jenis sampel ini!', 'warning');
                return;
            }

            var bmData = {
                _token: $('#csrf-token').val() || $('input[name="_token"]').first().val(),
                sampletype_id: $('#modal-bm-sampletype-id').val(),
                method_id: $('#modal-bm-method-id').val(),
                unit_id: $('#modal-bm-unit-id').val() || null,
                library_id: $('#modal-bm-library-id').val() || null,
                is_sub: isSub ? 'true' : 'false',
                min_no_sub: $('#modal-bm-min').val(),
                max_no_sub: $('#modal-bm-max').val(),
                equal_no_sub: $('#modal-bm-equal').val(),
                nilai_baku_mutu_no_sub: $('#modal-bm-nilai').val(),
                jenis_makanan_id: $('#modal-bm-jenis-makanan-id').val() || null,
                tipe_nilai_baku_mutu: isMML ? tipeNilai : null,
            };

            if (!bmData.sampletype_id || !bmData.method_id) {
                swal('Peringatan', 'Jenis sampel dan parameter wajib ada!', 'warning');
                return;
            }

            $('#btn-modal-bm-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: storeUrl,
                type: 'POST',
                data: bmData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': bmData._token
                },
                success: function(resp) {
                    $('#btn-modal-bm-save').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Simpan Baku Mutu');
                    if (resp.status) {
                        $('#modal-tambah-param').data('new-method-has-bm', true);
                        // Update data-baku-mutu-sampletypes pada row DOM agar
                        // tidak muncul lagi di picker "Tambah Baku Mutu" tanpa refresh
                        var savedMethodId = bmData.method_id;
                        var savedStId     = bmData.sampletype_id;
                        if (savedMethodId && savedStId) {
                            var $rows = $('.method-row-tab[data-method-id="' + savedMethodId + '"]');
                            $rows.each(function() {
                                var cur = [];
                                try { cur = JSON.parse($(this).attr('data-baku-mutu-sampletypes') || '[]'); } catch(e) {}
                                if (!cur.includes(savedStId)) { cur.push(savedStId); }
                                $(this).attr('data-baku-mutu-sampletypes', JSON.stringify(cur));
                            });
                        }
                        injectNewParameter();
                        $('#modal-tambah-param').modal('hide');
                        swal('Berhasil!', 'Parameter dan baku mutu berhasil ditambahkan!', 'success');
                    } else {
                        swal('Gagal', resp.pesan || 'Gagal menyimpan baku mutu!', 'warning');
                    }
                },
                error: function(xhr) {
                    $('#btn-modal-bm-save').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Simpan Baku Mutu');
                    swal('Error', 'Gagal menyimpan baku mutu!', 'error');
                }
            });
        });

        // Lewati step 2 (simpan tanpa baku mutu)
        $(document).on('click', '#btn-modal-bm-skip', function() {
            swal({
                title: 'Lewati Baku Mutu?',
                text: 'Parameter akan disimpan tanpa baku mutu. Baku mutu bisa ditambahkan nanti.',
                icon: 'info',
                buttons: { cancel: 'Batal', confirm: 'Ya, Lewati' }
            }).then(function(ok) {
                if (ok) {
                    $('#modal-tambah-param').data('new-method-has-bm', false);
                    injectNewParameter();
                    $('#modal-tambah-param').modal('hide');
                }
            });
        });

        // ---- Inject parameter baru ke DOM dan auto-centang ----
        function injectNewParameter(forceSelect) {
            forceSelect = !!forceSelect;
            var $modal    = $('#modal-tambah-param');
            var methodId  = $modal.data('new-method-id');
            var paramName = $modal.data('new-param-name');
            var price     = $modal.data('new-price-total') || 0;
            var labId     = $modal.data('lab-id');
            var labName   = $modal.data('lab-name');
            var stId      = $modal.data('sample-type-id');
            var hasBakuMutuForCurrentSampleType = !!$modal.data('new-method-has-bm');

            if (!methodId || !stId || !labId) return;

            // Cek apakah method row sudah ada (hindari duplikasi)
            var $existingCheckbox = $('.method-checkbox-tab[data-method-id="' + methodId + '"][data-sample-type-id="' + stId + '"]');
            if ($existingCheckbox.length) {
                var $existingRow = $existingCheckbox.closest('.method-row-tab');
                var $existingGroup = $existingRow.closest('.parameter-group-item');

                // Pastikan parameter yang dipilih terlihat di daftar meski sebelumnya tersembunyi oleh filter
                $existingRow.show();
                $existingGroup.show();
                $existingGroup.find('.collapse').addClass('show');
                $existingGroup.find('.collapse-icon').css('transform', 'rotate(180deg)');

                if (forceSelect && !$existingCheckbox.prop('checked')) {
                    $existingRow.attr('data-auto-added', '1');
                    $existingCheckbox.prop('checked', true).trigger('change');
                }
                if (hasBakuMutuForCurrentSampleType) {
                    var currentIds = [];
                    try { currentIds = JSON.parse($existingRow.attr('data-baku-mutu-sampletypes') || '[]'); } catch(e) {}
                    if (!currentIds.includes(stId) && !currentIds.includes(String(stId))) {
                        currentIds.push(stId);
                    }
                    $existingRow.attr('data-baku-mutu-sampletypes', JSON.stringify(currentIds));
                }

                // Scroll agar user langsung tahu parameter yang ditambahkan
                $('html, body').animate({ scrollTop: $existingRow.offset().top - 120 }, 300);
                return;
            }

            var priceFormatted = price.toLocaleString('id-ID');
            var methodKey      = methodId + '_' + labId + '_' + price;

            var $row = $('<div class="method-row-tab">')
                .attr('data-sample-type-id', stId)
                .attr('data-method-id',      methodId)
                .attr('data-method-name',    paramName.toLowerCase())
                .attr('data-auto-added',     forceSelect ? '1' : '0')
                .attr('data-baku-mutu-sampletypes', JSON.stringify(
                    hasBakuMutuForCurrentSampleType ? [stId] : []
                ))
                .html(
                    '<label>' +
                    '<input type="checkbox" class="method-checkbox-tab"' +
                    ' data-sample-type-id="' + stId + '"' +
                    ' data-default-price="'  + price + '"' +
                    ' data-prices-by-sample-type="{}"' +
                    ' data-method="'         + methodKey + '"' +
                    ' data-method-id="'      + methodId + '"' +
                    ' data-lab="'            + labId + '"' +
                    ' data-labname="'        + labName + '"' +
                    ' data-name="'           + paramName + '"' +
                    ' data-price="'          + price + '">' +
                    '<strong>' + $('<span>').text(paramName).html() + '</strong> ' +
                    '<span class="text-muted">(Rp ' + priceFormatted + ')</span>' +
                    '</label>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-pencil-edit-method"' +
                    ' data-method-id="' + methodId + '"' +
                    ' data-method-name="' + $('<span>').text(paramName).html() + '"' +
                    ' title="Edit parameter dan harga per jenis sampel">' +
                    '<i class="fa fa-pencil-alt"></i></button>'
                );

            // Cari card-body grup yang sesuai (lab + sample type)
            var $group = $('.parameter-group-item[data-lab-group="' + labId + '"][data-sample-type-id="' + stId + '"]');
            if (!$group.length) return;

            $group.find('.card-body').first().append($row);

            // Pastikan grup terlihat & terbuka
            $group.show();
            $group.find('.collapse').addClass('show');
            $group.find('.collapse-icon').css('transform', 'rotate(180deg)');

            if (forceSelect) {
                var $cb = $row.find('.method-checkbox-tab');
                $cb.prop('checked', true).trigger('change');
            }

            // Scroll ringan ke parameter baru
            $('html, body').animate({ scrollTop: $row.offset().top - 120 }, 300);
        }

    </script>

    <script>
    /* Filter picker Step 0 — didefinisikan di script terpisah agar pasti tersedia
       saat oninput/onkeyup dipanggil */
    function mpickerFilter(val) {
        var q    = (val || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#mpicker-list .mpicker-row');
        for (var i = 0; i < rows.length; i++) {
            var strong = rows[i].querySelector('strong');
            var name   = strong ? strong.textContent.toLowerCase() : '';
            // Inline style langsung — tidak ada Bootstrap !important yang mengganggu
            rows[i].style.display = (q === '' || name.indexOf(q) !== -1) ? 'flex' : 'none';
        }
    }
    </script>

    {{-- ============================================================
         MODAL TAMBAH / EDIT PAKET (popup, tanpa iframe)
         ============================================================ --}}
    <div class="modal fade" id="modal-paket" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header py-2"
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h5 class="modal-title mb-0" id="modal-paket-title">
                        <i class="fa fa-cube mr-2"></i>Tambah Paket
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-paket-id">
                    <input type="hidden" id="modal-paket-sample-type-id">

                    {{-- Loading --}}
                    <div id="modal-paket-loading" class="text-center py-4" style="display:none;">
                        <i class="fa fa-spinner fa-spin fa-2x text-success"></i>
                        <p class="mt-2 text-muted small">Memuat data...</p>
                    </div>

                    {{-- Form --}}
                    <div id="modal-paket-form">
                        {{-- Alert --}}
                        <div id="modal-paket-alert" class="alert py-2" style="display:none; font-size:13px;"></div>

                        {{-- Nama Paket --}}
                        <div class="form-group">
                            <label><i class="fa fa-tag mr-1"></i>Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal-paket-name"
                                   placeholder="Contoh: Kimia Air Lengkap">
                        </div>

                        {{-- Parameter --}}
                        <div class="form-group">
                            <label><i class="fa fa-microscope mr-1"></i>Parameter Pengujian <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Centang parameter yang akan masuk dalam paket ini.</p>

                            {{-- Selected preview --}}
                            <div id="modal-paket-selected-preview"
                                 style="min-height:38px; border:1px solid #e2e8f0; border-radius:6px; padding:6px 10px; margin-bottom:8px; background:#f8f9fa; font-size:12px;">
                                <span class="text-muted" id="modal-paket-no-param-msg">Belum ada parameter dipilih</span>
                            </div>

                            {{-- Search --}}
                            <input type="text" class="form-control mb-2" id="modal-paket-method-search"
                                   placeholder="🔍 Cari parameter..."
                                   oninput="paketMethodFilter(this.value)"
                                   onkeyup="paketMethodFilter(this.value)">

                            {{-- List --}}
                            <div id="modal-paket-method-list"
                                 style="max-height:240px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; padding:4px;">
                                <p class="text-muted text-center p-3 small">Buka tab ini untuk memuat daftar parameter.</p>
                            </div>
                        </div>

                        {{-- Harga --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:13px;">Harga Bahan (Rp)</label>
                                    <input type="number" class="form-control modal-paket-price-input"
                                           id="modal-paket-bahan" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:13px;">Harga Sarana (Rp)</label>
                                    <input type="number" class="form-control modal-paket-price-input"
                                           id="modal-paket-sarana" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:13px;">Harga Jasa (Rp)</label>
                                    <input type="number" class="form-control modal-paket-price-input"
                                           id="modal-paket-jasa" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success py-2 mb-0" style="font-size:13px;">
                            <i class="fa fa-calculator mr-1"></i>
                            Total: <strong id="modal-paket-total-display">Rp 0</strong>
                            <input type="hidden" id="modal-paket-total" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-modal-paket-save">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    /* Filter method list di modal paket */
    function paketMethodFilter(val) {
        var q    = (val || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#modal-paket-method-list .paket-method-item');
        for (var i = 0; i < rows.length; i++) {
            var text = rows[i].textContent.toLowerCase();
            rows[i].style.display = (q === '' || text.indexOf(q) !== -1) ? '' : 'none';
        }
    }
    </script>

    {{-- ============================================================
         MODAL EDIT METHOD/PARAMETER (popup, tanpa iframe)
         ============================================================ --}}
    <div class="modal fade" id="modal-edit-param-method" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: min(95vw, 1100px);">
            <div class="modal-content">
                <div class="modal-header py-2"
                     style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                    <h5 class="modal-title mb-0" id="mepm-title">
                        <i class="fa fa-pencil-alt mr-2"></i>Edit Parameter
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 78vh; overflow-y: auto;">
                    <input type="hidden" id="mepm-method-id">

                    {{-- Loading --}}
                    <div id="mepm-loading" class="text-center py-5">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Memuat data parameter...</p>
                    </div>

                    {{-- Alert --}}
                    <div id="mepm-alert" class="alert" style="display:none;"></div>

                    {{-- Form body --}}
                    <div id="mepm-body-wrap" style="display:none;">
                        <div class="row">
                            {{-- Kolom kiri --}}
                            <div class="col-md-7">

                                <div class="form-group">
                                    <label>Nama Parameter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mepm-params-method"
                                           placeholder="Nama parameter" required>
                                </div>

                                <div class="form-group">
                                    <label>Metode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mepm-name-method"
                                           placeholder="Metode" required>
                                </div>

                                <div class="form-group">
                                    <label>Apakah bagian PDAM?</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check mr-3">
                                            <input class="form-check-input" type="radio" name="mepm_id_pdam_method"
                                                   id="mepm-pdam-ya" value="1">
                                            <label class="form-check-label" for="mepm-pdam-ya">Ya</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="mepm_id_pdam_method"
                                                   id="mepm-pdam-tidak" value="0">
                                            <label class="form-check-label" for="mepm-pdam-tidak">Tidak</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Berhubungan dengan Kesehatan</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_berhubungan_kesehatan" id="mepm-kes-ya" value="1">
                                        <label class="form-check-label" for="mepm-kes-ya">Berhubungan dengan Kesehatan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_berhubungan_kesehatan" id="mepm-kes-tidak" value="0">
                                        <label class="form-check-label" for="mepm-kes-tidak">Tidak Berhubungan dengan Kesehatan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_berhubungan_kesehatan" id="mepm-kes-mikro" value="">
                                        <label class="form-check-label" for="mepm-kes-mikro">Mikrobiologi</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Jenis Parameter</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-org" value="kimia organik">
                                        <label class="form-check-label" for="mepm-jenis-org">Kimia an organik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-kimiawi" value="kimiawi">
                                        <label class="form-check-label" for="mepm-jenis-kimiawi">Kimiawi</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-fisika" value="fisika">
                                        <label class="form-check-label" for="mepm-jenis-fisika">Parameter Fisik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-mikro" value="">
                                        <label class="form-check-label" for="mepm-jenis-mikro">Mikrobiologi</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Alat dan Reagen</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_is_ready" id="mepm-ready-ya" value="1">
                                        <label class="form-check-label" for="mepm-ready-ya">Tersedia</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_is_ready" id="mepm-ready-tidak" value="0">
                                        <label class="form-check-label" for="mepm-ready-tidak">Belum Tersedia</label>
                                    </div>
                                </div>

                                {{-- Opsi Hasil --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fa fa-check-square mr-1"></i>Opsi Hasil (Opsional)</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="mepm-is-option" value="1">
                                            <label class="form-check-label" for="mepm-is-option">
                                                <strong>Hasil Opsional</strong> – Pakai opsi pilihan (contoh: Positif/Negatif)
                                            </label>
                                        </div>
                                        <div id="mepm-option-wrap" style="display:none;">
                                            <div id="mepm-option-rows"></div>
                                            <input type="hidden" id="mepm-option-hidden">
                                            <small class="text-muted">
                                                <i class="fa fa-info-circle"></i>
                                                Klik <span class="badge badge-success"><i class="fa fa-plus"></i></span> untuk menambah opsi
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Harga --}}
                                <div class="form-group">
                                    <label>Harga Bahan</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-bahan"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Sarana</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-sarana"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Jasa</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-jasa"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Total</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-total"
                                               min="0" placeholder="0" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom kanan --}}
                            <div class="col-md-5">

                                {{-- Laboratorium --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fa fa-flask mr-1"></i>Laboratorium</h6>
                                    </div>
                                    <div class="card-body py-2" style="max-height:160px; overflow-y:auto;">
                                        <div id="mepm-lab-list">
                                            @foreach ($data_methods as $lm)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           value="{{ $lm->id_lab }}"
                                                           id="mepm-lab-{{ $lm->id_lab }}">
                                                    <label class="form-check-label" for="mepm-lab-{{ $lm->id_lab }}">
                                                        {{ $lm->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Harga per Jenis Sampel --}}
                                <div class="card border-info">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 text-info">
                                            <i class="fa fa-tags mr-1"></i>Harga per Jenis Sampel
                                        </h6>
                                        <small class="text-muted">
                                            Kosong = pakai Harga Total. Untuk permohonan Kesmas/non-klinik.
                                        </small>
                                    </div>
                                    <div class="card-body p-0">
                                        {{-- Filter bar: muncul saat ada konteks jenis sampel --}}
                                        <div id="mepm-stp-filter-bar"
                                             class="d-none align-items-center justify-content-between px-3 py-2"
                                             style="background:#e8f5e9; border-bottom:1px solid #c3e6cb;">
                                            <div style="font-size:12px; color:#155724;">
                                                <i class="fa fa-filter mr-1"></i>
                                                Menampilkan harga untuk:
                                                <strong id="mepm-stp-filter-label">—</strong>
                                            </div>
                                            <button type="button" id="mepm-stp-toggle-all"
                                                    class="btn btn-link btn-sm p-0"
                                                    style="font-size:12px; color:#0056b3;">
                                                Tampilkan semua jenis
                                            </button>
                                        </div>
                                        <div class="table-responsive" style="max-height:300px; overflow-y:auto;">
                                            <table class="table table-sm table-bordered mb-0" id="mepm-stp-table">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:60%">Jenis Sampel</th>
                                                        <th>Harga (Rp)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sampletypes as $st)
                                                        <tr data-st-id="{{ $st->id_sample_type }}">
                                                            <td><small>{{ $st->name_sample_type }}</small></td>
                                                            <td>
                                                                <input type="number" min="0" step="1"
                                                                       class="form-control form-control-sm"
                                                                       placeholder="— default —">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /mepm-body-wrap --}}
                </div>{{-- /modal-body --}}
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-mepm-save" style="display:none;">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL TAMBAH PARAMETER (2-Step)
         ============================================================ --}}
    <div class="modal fade" id="modal-tambah-param" tabindex="-1" role="dialog" aria-labelledby="modal-tambah-param-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title" id="modal-tambah-param-title">
                        <i class="fa fa-plus-circle mr-2"></i>Tambah Parameter Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    {{-- Step Indicator --}}
                    <div class="d-flex" style="border-bottom: 1px solid #e2e8f0; background: #f8f9fa;">
                        <div id="modal-param-step-indicator-1" class="flex-1 text-center py-3 active"
                            style="flex:1; border-right:1px solid #e2e8f0; font-size:13px;">
                            <span class="step-num" style="display:inline-block; width:24px; height:24px; border-radius:50%; background:#667eea; color:white; line-height:24px; font-weight:700; margin-right:6px;">1</span>
                            <strong>Detail Parameter</strong>
                        </div>
                        <div id="modal-param-step-indicator-2" class="flex-1 text-center py-3 text-muted"
                            style="flex:1; font-size:13px;">
                            <span class="step-num" style="display:inline-block; width:24px; height:24px; border-radius:50%; background:#cbd5e0; color:white; line-height:24px; font-weight:700; margin-right:6px;">2</span>
                            <strong>Baku Mutu</strong>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Info Lab --}}
                        <div class="alert alert-light border mb-3 py-2" style="font-size:13px;">
                            <i class="fa fa-flask mr-1"></i> Laboratorium:
                            <span class="badge ml-1" id="modal-param-lab-badge"></span>
                        </div>

                        {{-- STEP 0: Pilih parameter yang sudah ada --}}
                        <div id="modal-param-step0" style="display:none;">
                            <p class="mb-2" style="font-size:13px;">
                                <i class="fa fa-info-circle mr-1 text-info"></i>
                                Parameter berikut <strong>belum memiliki baku mutu</strong> untuk jenis sampel ini.
                                Pilih salah satu untuk menambahkan baku mutunya.
                                <span class="badge badge-info ml-1" id="mpicker-count">0</span>
                            </p>
                            <input type="text" id="mpicker-search" class="form-control mb-3"
                                   placeholder="🔍 Cari parameter..." autocomplete="off"
                                   oninput="mpickerFilter(this.value)"
                                   onkeyup="mpickerFilter(this.value)">
                            <div id="mpicker-list"
                                 style="max-height: 340px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;">
                            </div>
                        </div>

                        {{-- STEP 1 --}}
                        <div id="modal-param-step1">
                            <form id="form-step1-param">
                                <input type="hidden" id="modal-param-lab-id" name="modal_lab_id">
                                <input type="hidden" id="modal-param-result-method-id">
                                <input type="hidden" id="modal-bm-store-url">

                                <div class="form-group">
                                    <label><i class="fa fa-tag mr-1"></i>Nama Parameter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="modal-param-params-method" placeholder="Contoh: BOD5, TSS, Coliform">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-flask mr-1"></i>Metode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="modal-param-name-method" placeholder="Contoh: SNI 6989.72:2009">
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Harga Bahan (Rp)</label>
                                            <input type="number" class="form-control" id="modal-param-price-bahan" value="0" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Harga Sarana (Rp)</label>
                                            <input type="number" class="form-control" id="modal-param-price-sarana" value="0" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Harga Jasa (Rp)</label>
                                            <input type="number" class="form-control" id="modal-param-price-jasa" value="0" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Total (Rp)</label>
                                    <input type="number" class="form-control" id="modal-param-price-total" value="0" readonly style="background:#f8f9fa;">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hubungan Kesehatan</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_berhubungan_kesehatan" value="1" id="modal-kes-ya">
                                                    <label class="form-check-label" for="modal-kes-ya">Ya (Kimia)</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_berhubungan_kesehatan" value="0" id="modal-kes-tidak" checked>
                                                    <label class="form-check-label" for="modal-kes-tidak">Tidak</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_berhubungan_kesehatan" value="" id="modal-kes-mikro">
                                                    <label class="form-check-label" for="modal-kes-mikro">Mikro</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Alat & Reagen</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_is_ready" value="1" id="modal-ready-ya" checked>
                                                    <label class="form-check-label" for="modal-ready-ya">Tersedia</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_is_ready" value="0" id="modal-ready-tidak">
                                                    <label class="form-check-label" for="modal-ready-tidak">Belum Tersedia</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- STEP 2 --}}
                        <div id="modal-param-step2" style="display:none;">
                            <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
                                <i class="fa fa-check-circle mr-1"></i> Parameter <strong id="modal-step2-param-name"></strong>
                                berhasil disimpan di lab <strong id="modal-step2-lab-name"></strong>.
                            </div>

                            <div class="card mb-3">
                                <div class="card-header py-2" style="background:#f8f9fa;">
                                    <strong id="modal-bm-form-title"><i class="fa fa-balance-scale mr-1"></i> Baku Mutu</strong>
                                    <small class="text-muted ml-2">(Opsional — bisa diisi nanti)</small>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" id="modal-bm-method-id">
                                    <input type="hidden" id="modal-bm-sampletype-id">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Satuan</label>
                                                <input type="hidden" id="modal-bm-unit-id">
                                                <div class="sdd-wrap" id="sdd-unit">
                                                    <div class="sdd-display sdd-placeholder" tabindex="0">— Pilih Satuan —</div>
                                                    <div class="sdd-panel">
                                                        <input type="text" class="sdd-search" placeholder="Cari satuan...">
                                                        <ul class="sdd-list">
                                                            <li data-value="">— Pilih Satuan —</li>
                                                            @foreach ($units as $unit)
                                                                <li data-value="{{ $unit->id_unit }}">{!! $unit->shortname_unit !!}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Acuan Baku Mutu</label>
                                                <input type="hidden" id="modal-bm-library-id">
                                                <div class="sdd-wrap" id="sdd-library">
                                                    <div class="sdd-display sdd-placeholder" tabindex="0">— Pilih Acuan —</div>
                                                    <div class="sdd-panel">
                                                        <input type="text" class="sdd-search" placeholder="Cari acuan...">
                                                        <ul class="sdd-list">
                                                            <li data-value="">— Pilih Acuan —</li>
                                                            @foreach ($libraries as $lib)
                                                                <li data-value="{{ $lib->id_library }}">{{ $lib->title_library }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section khusus Makanan / Minuman / Lainnya --}}
                                    <div id="modal-bm-mml-section" style="display:none;">
                                        <hr style="border-top:1px dashed #e2e8f0; margin:8px 0 14px;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Tipe Nilai Baku Mutu <span class="text-danger">*</span></label>
                                                    <div class="mt-1">
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="radio" name="modal_bm_tipe_nilai" value="kuantitatif" id="modal-bm-tipe-kuantitatif" checked>
                                                            <label class="form-check-label" for="modal-bm-tipe-kuantitatif">
                                                                <strong>Kuantitatif</strong>
                                                                <small class="text-muted d-block" style="font-size:11px;">Nilai berupa angka, mis: ≤ 5 mg/kg, 0–100 CFU/g</small>
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="modal_bm_tipe_nilai" value="kualitatif" id="modal-bm-tipe-kualitatif">
                                                            <label class="form-check-label" for="modal-bm-tipe-kualitatif">
                                                                <strong>Kualitatif</strong>
                                                                <small class="text-muted d-block" style="font-size:11px;">Nilai berupa kategori, mis: Negatif, Positif, MS / TMS</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Jenis Makanan <small class="text-muted">(opsional)</small></label>
                                                    <input type="hidden" id="modal-bm-jenis-makanan-id">
                                                    <div class="sdd-wrap" id="sdd-jenis-makanan">
                                                        <div class="sdd-display sdd-placeholder" tabindex="0">— Pilih Jenis Makanan —</div>
                                                        <div class="sdd-panel">
                                                            <input type="text" class="sdd-search" placeholder="Cari jenis makanan...">
                                                            <ul class="sdd-list">
                                                                <li data-value="">— Pilih Jenis Makanan —</li>
                                                                @foreach ($all_jenis_makanan as $jm)
                                                                    <li data-value="{{ $jm->id_jenis_makanan }}">{{ $jm->name_jenis_makanan }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Punya Sub Baku Mutu?</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="modal_bm_is_sub" value="false" id="modal-bm-nosub" checked>
                                                <label class="form-check-label" for="modal-bm-nosub">Tidak</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="modal_bm_is_sub" value="true" id="modal-bm-issub">
                                                <label class="form-check-label" for="modal-bm-issub">Ya</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="modal-bm-no-sub">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Min</label>
                                                    <input type="text" class="form-control" id="modal-bm-min" placeholder="Contoh: 4.0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Max</label>
                                                    <input type="text" class="form-control" id="modal-bm-max" placeholder="Contoh: 6.5">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Nilai Sama Dengan (non-range)</label>
                                            <input type="text" class="form-control" id="modal-bm-equal" placeholder="Contoh: Negatif">
                                        </div>
                                        <div class="form-group">
                                            <label>Nilai Baku Mutu di Laporan</label>
                                            <input type="text" class="form-control" id="modal-bm-nilai" placeholder="Contoh: ≤ 6.5">
                                        </div>
                                    </div>

                                    <div id="modal-bm-sub-container" style="display:none;">
                                        <div class="alert alert-info py-2" style="font-size:12px;">
                                            <i class="fa fa-info-circle"></i> Sub baku mutu dengan detail lebih kompleks dapat diisi lengkap di halaman
                                            <a href="{{ route('elits-baku-mutu-kimia.index') }}" target="_blank">Baku Mutu Kimia</a> /
                                            <a href="{{ route('elits-baku-mutu-mikro.index') }}" target="_blank">Baku Mutu Mikro</a>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- Footer Step 0: Pilih Parameter --}}
                    <div id="modal-footer-step0" style="display:none;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i> Batal
                        </button>
                        <small class="text-muted ml-2">Klik <strong>Pilih</strong> pada parameter di atas</small>
                    </div>
                    {{-- Footer Step 1 --}}
                    <div id="modal-footer-step1" style="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i> Batal
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-modal-param-next">
                            <i class="fa fa-check mr-1"></i>Simpan Parameter
                        </button>
                    </div>
                    {{-- Footer Step 2 (hidden initially via JS) --}}
                    <div id="modal-footer-step2" style="display:none;">
                        <button type="button" class="btn btn-outline-secondary" id="btn-modal-bm-skip">
                            <i class="fa fa-forward mr-1"></i> Lewati
                        </button>
                        <button type="button" class="btn btn-success" id="btn-modal-bm-save">
                            <i class="fa fa-check mr-1"></i> Simpan Baku Mutu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
