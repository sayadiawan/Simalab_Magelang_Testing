@extends('masterweb::template.admin.layout')
@section('title')
  Verifikasi Dokumen PDF
@endsection

@section('content')
  <div id="document-upload" class="mx-auto w-80">
    <form id="uploadForm" enctype="multipart/form-data">
      @csrf
      <div class="d-flex justify-content-center w-100">
        <label for="dropzone-file" class="d-flex flex-column align-items-center justify-content-center w-100 h-100 border border-secondary border-dashed rounded-lg cursor-pointer bg-light hover-bg-light">
          <div class="d-flex flex-column align-items-center justify-content-center py-5">
            <svg class="w-8 h-8 mb-3 text-muted" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
            </svg>
            <p class="mb-2 text-muted"><span class="font-weight-bold">Click to upload</span> or drag and drop</p>
            <p class="text-muted small"><b>PDF File</b></p>
          </div>
          <input id="dropzone-file" type="file" name="document" accept=".pdf" class="d-none"/>
        </label>
      </div>

      <div class="file-upload-temporary d-none" id="file-upload-temporary">
        <div class="bg-light p-2 w-100 d-flex mr-2 rounded">
          <div class="bg-white p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
              <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1"/>
              <path d="M4.603 12.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.7 7.7 0 0 1 1.482-.645 20 20 0 0 0 1.062-2.227 7.3 7.3 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a11 11 0 0 0 .98 1.686 5.8 5.8 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.86.86 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.7 5.7 0 0 1-.911-.95 11.6 11.6 0 0 0-1.997.406 11.3 11.3 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.8.8 0 0 1-.58.029m1.379-1.901q-.25.115-.459.238c-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361q.016.032.026.044l.035-.012c.137-.056.355-.235.635-.572a8 8 0 0 0 .45-.606m1.64-1.33a13 13 0 0 1 1.01-.193 12 12 0 0 1-.51-.858 21 21 0 0 1-.5 1.05zm2.446.45q.226.244.435.41c.24.19.407.253.498.256a.1.1 0 0 0 .07-.015.3.3 0 0 0 .094-.125.44.44 0 0 0 .059-.2.1.1 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a4 4 0 0 0-.612-.053zM8.078 5.8a7 7 0 0 0 .2-.828q.046-.282.038-.465a.6.6 0 0 0-.032-.198.5.5 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822q.036.167.09.346z"/>
            </svg>
          </div>
          <div class="d-flex align-items-center">
            <span class="ml-4" id="file-name">image.pdf</span>
          </div>
        </div>
        <div class="btn btn-danger" id="btn-delete-temp">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
            <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
          </svg>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-4 py-3" style="cursor: pointer;">Upload</button>
    </form>
  </div>

  <div id="document-result" class="d-none">
    <div class="d-flex justify-content-between">
      <h3 class="d-flex align-items-center">Informasi Dokumen</h3>
      <div class="btn btn-danger" id="btn-delete">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
          <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
        </svg>
      </div>
    </div>
    <hr>
    <div id="box-text-result" class="d-flex p-4 rounded font-16" style="background-color: #D1E7DD">
      <div class="d-flex align-items-center">
        <b><span id="text-result"></span></b>
      </div>
      <button id="btn-show-result" class="btn btn-success ml-4">Tampilkan</button>
    </div>

    <div id="pdfContainer" style="margin-top: 20px;">
      <iframe id="pdfViewer" width="100%" height="600px" style="display: none;"></iframe>
    </div>

    <div id="result-detail" class="align-items-start" style="display:none; margin-top: 20px;">
      <div class="card w-25">
        <ul class="list-group list-group-flush" id="list-petugas">

        </ul>
      </div>
      <div class="card w-75 ml-4">
        <div class="card-header">
          <span id="header-ttd"></span>
        </div>
        <div class="card-body">
          <table>
            <tr>
              <td width="30%">Ditandatangani oleh</td>
              <td class="text-center" width="10%">:</td>
              <td width="60%"><span id="text-result-petugas"></span></td>
            </tr>
            <tr>
              <td width="30%">Lokasi</td>
              <td class="text-center" width="10%">:</td>
              <td width="60%"><span id="text-result-location"></span></td>
            </tr>
            <tr>
              <td width="30%">Alasan</td>
              <td class="text-center" width="10%">:</td>
              <td width="60%"><span id="text-result-reason"></span></td>
            </tr>
            <tr>
              <td width="30%">Ditandatangani pada</td>
              <td class="text-center" width="10%">:</td>
              <td width="60%"><span id="text-result-signedIn"></span></td>
            </tr>
            <tr>
              <td width="30%">Diterbitkan oleh</td>
              <td class="text-center" width="10%">:</td>
              <td width="60%">Badan Siber dan Sandi Negara</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>


@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert2.min.js')}}"></script>
  <script>
    function dataPetugas(i, petugas, location, reason, signedIn) {
      $('#list-petugas li').removeClass('bg-primary text-white');

      $(`#petugas-${i}`).addClass('bg-primary text-white');

      $('#header-ttd').text(`Tanda Tangan #${i}`)
      $('#text-result-petugas').text(petugas);
      $('#text-result-location').text(location);
      $('#text-result-reason').text(reason);
      $('#text-result-signedIn').text(signedIn);
    }

    $(document).ready(function () {
      $('#uploadForm').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);


        $.ajax({
          url: "{{ route('signature-verify-post') }}",
          type: "POST",
          data: formData,
          contentType: false,
          processData: false,
          beforeSend: function () {
            Swal.fire({
              title: 'Memproses...',
              text: 'Harap tunggu sementara file diverifikasi.',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
          },
          success: function (response) {

            Swal.close();

            if (response.success) {

              const result = response.result.data;

              $('#document-result').removeClass('d-none');

              if (response.result.message === "VALID"){
                $('#box-text-result').css('background-color', '#D1E7DD');
                $('#btn-show-result').removeClass('d-none');
                $('#text-result').text('Dokumen ini memiliki tanda tangan digital').css('color', '#0e8696');
              }else {
                $('#box-text-result').css('background-color', '#FFF3CD');
                $('#btn-show-result').addClass('d-none');
                $('#text-result').text(response.result.message).css('color', '#a47e05');
              }

              $('#document-upload').addClass('d-none');

              const binaryData = atob(response.file);
              const len = binaryData.length;
              const uintArray = new Uint8Array(len);
              for (let i = 0; i < len; i++) {
                uintArray[i] = binaryData.charCodeAt(i);
              }

              const blob = new Blob([uintArray], { type: 'application/pdf' });
              pdfViewer.src = URL.createObjectURL(blob);
              pdfViewer.style.display = 'block';


              // result detail
              for (let i = 0; i < result.details.length; i++) {
                let petugas = result.details[i].info_signer.signer_name;
                let location = result.details[i].signature_document.location;
                let reason = result.details[i].signature_document.reason;
                let signed_in = result.details[i].signature_document.signed_in;

                $('#list-petugas').append(`<li class="list-group-item" id="petugas-${i + 1}" onclick="dataPetugas(${i + 1}, '${petugas}', '${location}', '${reason}', '${signed_in}')" style="cursor: pointer;">Tanda Tangan #${i + 1}</li>`);

                if (i === 0) {
                  $('#petugas-1').addClass('bg-primary text-white');

                  $('#header-ttd').text(`Tanda Tangan #${i + 1}`)
                  $('#text-result-petugas').text(petugas);
                  $('#text-result-location').text(location);
                  $('#text-result-reason').text(reason);
                  $('#text-result-signedIn').text(signed_in);
                }
              }

            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal memverifikasi file.',
              });
            }
          },
          error: function (xhr) {
            Swal.close();

            let error = xhr.responseJSON?.errors || 'Terjadi kesalahan.';
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: error,
            });
          }
        });
      });

      $('#dropzone-file').on('change', function(event) {

        $('#file-upload-temporary').addClass('d-flex');

        var fileName = event.target.files[0].name;
        $('#file-name').text(fileName);
      });

      $('#btn-delete-temp').on('click', function (){
        $('#file-upload-temporary').removeClass('d-flex');
        $('#dropzone-file').val(null);
      });

      $('#btn-delete').on('click', function (){
        $('#document-result').addClass('d-none');
        $('#document-upload').removeClass('d-none');
        $('#file-upload-temporary').removeClass('d-flex');
        $('#dropzone-file').val(null);
        $('#list-petugas').empty();
      });

      const pdfContainer = document.getElementById('pdfContainer');
      const pdfViewer = document.getElementById('pdfViewer');
      const resultDetail = document.getElementById('result-detail');

      $('#btn-show-result').on('click', function() {
        if (pdfContainer.style.display === 'none' || pdfContainer.style.display === '') {
          pdfContainer.style.display = 'block';
          pdfViewer.style.display = 'block';
          resultDetail.style.display = 'none';
        } else {
          pdfContainer.style.display = 'none';
          pdfViewer.style.display = 'none';
          resultDetail.style.display = 'flex';
        }
      });

    });
  </script>

@endsection
