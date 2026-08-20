<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View PDF</title>
    <style>
        /* Reset body margin and padding to ensure the iframe uses full viewport */
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        /* Make the iframe fill the entire viewport */
        iframe {
            width: 100vw;
            height: 100vh;
            border: none;
            /* Optional: removes the iframe border */
        }
    </style>
</head>

<body>
    {{-- <h1>PDF Viewer</h1> --}}

    <!-- iframe untuk menampilkan PDF -->
    <iframe id="pdfViewer" width="100%" height="100%"></iframe>

    <script>
        // Ambil data base64 dari backend
        const pdfBase64 = @json($data);

        // Konversi base64 ke blob PDF
        const byteCharacters = atob(pdfBase64);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const pdfBlob = new Blob([byteArray], {
            type: 'application/pdf'
        });

        // Buat URL object untuk PDF
        const pdfURL = URL.createObjectURL(pdfBlob);

        // Tampilkan PDF di iframe
        document.getElementById('pdfViewer').src = pdfURL;
    </script>
</body>

</html>
