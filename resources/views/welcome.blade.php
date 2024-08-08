<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertir PDF a Word</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="form-container">
        <div class="form-title">Convertir PDF a Word</div>
        <form id="convertForm" action="{{ route('convert.pdf.to.word') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" class="file-input" accept="application/pdf" required>
            <button type="submit" class="submit-button">
                Convertir a Word
                <div class="spinner"></div>
            </button>
        </form>
    </div>

    <script>
        document.getElementById('convertForm').onsubmit = function(event) {
            event.preventDefault();

            let submitButton = document.querySelector('.submit-button');
            let spinner = document.querySelector('.spinner');
            submitButton.disabled = true;
            spinner.style.display = 'inline-block';

            let formData = new FormData(this);

            fetch("{{ route('convert.pdf.to.word') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {

                    submitButton.disabled = false;
                    spinner.style.display = 'none';

                    if (response.ok) {
                        response.blob().then(blob => {
                            let link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = 'converted.docx';
                            link.click();
                        });
                        Swal.fire({
                            icon: 'success',
                            title: '¡Conversión exitosa!',
                            text: 'El archivo PDF ha sido convertido a Word.',
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al convertir el archivo.',
                        });
                    }
                })
                .catch(error => {

                    submitButton.disabled = false;
                    spinner.style.display = 'none';

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al enviar la solicitud.',
                    });
                });
        }
    </script>

</body>

</html>
