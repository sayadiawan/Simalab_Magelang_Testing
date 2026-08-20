<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="shortcut icon"
        href="{{ asset('assets/admin/images/' . \Smt\Masterweb\Models\Option::first()->favicon) }}" />
  <link rel="shortcut icon" href="{{ asset('assets/admin/images/logo/logo-silaboy-mini.png') }}">
  <title>Progress</title>
  <style>
    .container {
      width: 60%;
      margin: auto;
      margin-top: 50px;
      background-color: rgba(211, 211, 211, 0.34);
      padding: 20px;
    }

    .step {

      padding: 10px;

      display: flex;
      flex-direction: row;
      justify-content: flex-start;

      background-color: cream;
    }

    .v-stepper {
      position: relative;
    }

    .step .circle {
      background-color: white;
      border: 3px solid gray;
      border-radius: 100%;
      width: 20px;
      height: 20px;
      display: inline-block;
    }

    .step .line {
      top: 23px;
      left: 12px;
      height: 100%;

      position: absolute;
      border-left: 3px solid gray;
    }

    .step.completed .circle {
      visibility: visible;
      background-color: rgb(6,150,215);
      border-color: rgb(6,150,215);
    }

    .step.completed .line {
      border-left: 3px solid rgb(6,150,215);
    }

    .step.active .circle {
      visibility: visible;
      border-color: rgb(6,150,215);
    }

    .step.empty .circle {
      visibility: hidden;
    }

    .step.empty .line {
      top: 0;
      height: 150%;
    }


    .step:last-child .line {
      border-left: 3px solid white;
      z-index: -1;
    }

    .content {
      margin-left: 20px;
      display: inline-block;
    }

    html *
    {
      font-family: Arial !important;
    }

    .title {
      font-size: 20px;
    }
  </style>
</head>
<body>
    <div class="container">

      <h4>Nomor Sampel : {{ $nomorSampel }}</h4>
      <h4>Tanggal Register : {{ $tanggalRegister }}</h4>

      @foreach($listVerifications as $index => $listVerification)
        <div class="step completed">
          <div class="v-stepper">
            <div class="circle"></div>
            <div class="line"></div>
          </div>

          <div class="content">
            <h1 class="title">{{ $listVerification->name }}</h1>
            <p>Ditandatangani oleh <strong>{{ $listVerification->nama_petugas }}</strong> pada {{ \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($listVerification->start_date) }}</p>
          </div>
        </div>
      @endforeach

        @if($isClinic == "1")
          @if(count($listVerifications) < 6)
            <div class="step active">
              <div class="v-stepper">
                <div class="circle"></div>
                <div class="line"></div>
              </div>

              <div class="content">
                <h1 class="title">{{ $listActivity[$index + 1]->name }}</h1>
                <p>-</p>
              </div>
            </div>
          @endif
        @else
          @if(count($listVerifications) < 5)
            <div class="step active">
              <div class="v-stepper">
                <div class="circle"></div>
                <div class="line"></div>
              </div>

              <div class="content">
                <h1 class="title">{{ $listActivity[$index + 1]->name }}</h1>
                <p>-</p>
              </div>
            </div>
          @endif
        @endif
    </div>

</body>
</html>
