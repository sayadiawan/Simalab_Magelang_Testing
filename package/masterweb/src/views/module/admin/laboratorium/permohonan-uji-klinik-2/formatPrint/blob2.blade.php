<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drag and Drop Signature on PDF</title>
    <style>
        #pdf-canvas {
            width: 600px;
            height: 800px;
            position: relative;
            border: 1px solid #ccc;
            overflow: hidden;
        }

        .signature {
            width: 100px;
            cursor: grab;
            position: absolute;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>

    <h2>Drag and Drop Signature Position on PDF Preview</h2>
    <div id="pdf-preview">
        <canvas id="pdf-canvas"></canvas>
        <img id="signature" class="signature" src="{{ asset('assets/admin/images/logo/logo-bsre.png') }}" />
    </div>

    <button onclick="savePosition()">Save PDF with Signature Position</button>

    <script>
        const signature = document.getElementById('signature');
        let isDragging = false;
        let offsetX, offsetY;

        const pdfCanvas = document.getElementById('pdf-canvas');

        async function loadPdf() {
            const loadingTask = pdfjsLib.getDocument("{{ '/storage/temp/' . $nameFile }}");
            const pdf = await loadingTask.promise;
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({
                scale: 1.5
            });
            const context = pdfCanvas.getContext('2d');
            pdfCanvas.width = viewport.width;
            pdfCanvas.height = viewport.height;

            const renderContext = {
                canvasContext: context,
                viewport: viewport,
            };
            await page.render(renderContext);
        }

        loadPdf();

        // Drag functionality
        signature.addEventListener('mousedown', (e) => {
            isDragging = true;
            offsetX = e.offsetX;
            offsetY = e.offsetY;
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                const x = e.clientX - pdfCanvas.offsetLeft - offsetX;
                const y = e.clientY - pdfCanvas.offsetTop - offsetY;
                signature.style.left = `${x}px`;
                signature.style.top = `${y}px`;
            }
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        // Save the final position and send data to server
        function savePosition() {
            const positionX = parseInt(signature.style.left, 10) || 0;
            const positionY = parseInt(signature.style.top, 10) || 0;

            fetch("{{ route('elits-permohonan-uji-klinik-2.post-print-permohonan-uji-klinik-hasil', [$id_permohonan_uji_klinik]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        _token: "{{ csrf_token() }}",
                        signature: signature.src,
                        positionX: positionX,
                        positionY: positionY
                    })
                })
                .then(response => response.blob())
                .then(blob => {
                    // Create a URL for the Blob
                    const blobUrl = URL.createObjectURL(blob);

                    // Create a link element to download the PDF
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = 'document.pdf';
                    link.click();

                    // Clean up the Blob URL after download
                    URL.revokeObjectURL(blobUrl);
                    // Code to handle PDF saving if needed
                });
        }
    </script>
</body>

</html>
