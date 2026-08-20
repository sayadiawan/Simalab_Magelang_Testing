<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Amplop Haji</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
    <style>
        #draggable1,
        #draggable2 {
            /* border: 1px solid #ccc; */
            padding: 10px;

            /* Pengaturan font */
            font-family: Cambria, serif;
            font-size: 12pt;
            line-height: 1;
            /* Spasi antar baris */

            margin: 10px;
            width: fit-content;
            background-color: #f9f9f9;
            cursor: move;
            /* Menunjukkan elemen dapat dipindah */
        }

        #print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            background-color: #007bff;
            /* Warna latar belakang tombol biru */
            color: white;
            /* Warna teks putih */
            border: none;
            /* Menghilangkan border */
            border-radius: 5px;
            /* Sudut bulat */
            transition: background-color 0.3s;
            /* Transisi untuk efek hover */
        }

        #print-button:hover {
            background-color: #0056b3;
            /* Warna saat hover */
        }

        /* CSS untuk menyembunyikan tombol saat mencetak */
        @media print {
            #print-button {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div id="draggable1" class="draggable" style="position: relative; left :70px; top :328px;">
        <p><b>{{ $data->no_amplop_1 }}/{{ $data->no_amplop_2 }}/{{ $data->no_amplop_3 }}/{{ $data->no_amplop_4 }}/{{ $data->no_amplop_5 }}</b>
        </p>
    </div>

    <div id="draggable2" class="draggable" style="position: relative; left :611px; top :350px;">
        <p><b>{{ $pasien->nama_pasien }}</b></p>
        <p><b>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($pasien) }}</b></p>
    </div>

    <button id="print-button" class="btn btn-primary">Print</button>

    <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
    <script>
        $(function() {
            // Membuat elemen dapat dipindah
            $(".draggable").draggable();

            // Fungsi untuk menangani print
            $("#print-button").on("click", function() {
                window.print();
            });
        });
    </script>
</body>

</html>
