import axios from 'axios';
import FormData from 'form-data';
import fs from 'fs';

// Reemplaza con tus credenciales
const PUBLIC_ID = 'project_public_edb54ddf1573071c2340773c557bc279_8FzHqdefe83795878e714437fd04711c233d6';
const SECRET_KEY = 'secret_key_b7f4d7b82055c1b5df4d016dcc172ac2_LwIEff6a93681c0aad3854fa9eea96d8ebf63';

// Obtén las rutas desde los argumentos de la línea de comandos
const [,, pdfPath, outputPath] = process.argv;

async function convertPdfToWord(pdfPath, outputPath) {
    try {
        console.log('Iniciando el proceso de conversión...');
        console.log('PDF Path:', pdfPath);
        console.log('Output Path:', outputPath);

        // Paso 1: Autenticarse
        const authResponse = await axios.post('https://api.ilovepdf.com/v1/auth', {
            public_key: PUBLIC_ID,
            secret_key: SECRET_KEY
        });

        const authToken = authResponse.data.token;
        console.log('Token de autenticación obtenido:', authToken);

        // Paso 2: Iniciar el proceso de conversión
        const form = new FormData();
        form.append('file', fs.createReadStream(pdfPath));
        form.append('tool', 'pdf2word');

        const startResponse = await axios.post('https://api.ilovepdf.com/v1/start', form, {
            headers: {
                ...form.getHeaders(),
                'Authorization': `Bearer ${authToken}`
            }
        });

        // Verifica la respuesta de inicio
        if (!startResponse.data || !startResponse.data.task_id) {
            console.error('Respuesta de inicio de conversión:', startResponse.data);
            throw new Error('No se recibió un task_id de la respuesta de inicio.');
        }

        const taskId = startResponse.data.task_id;
        console.log('ID de tarea obtenido:', taskId);

        // Paso 3: Consultar el estado del proceso
        let resultResponse;
        let retries = 0;
        while (retries < 10) {
            await new Promise(r => setTimeout(r, 5000)); // Esperar 5 segundos
            resultResponse = await axios.get(`https://api.ilovepdf.com/v1/task/${taskId}`, {
                headers: {
                    'Authorization': `Bearer ${authToken}`
                }
            });

            console.log('Estado de la tarea:', resultResponse.data.status);

            if (resultResponse.data.status === 'finished') {
                break;
            }

            retries++;
        }

        if (resultResponse.data.status !== 'finished') {
            throw new Error('La conversión no se completó.');
        }

        // Descargar el archivo convertido
        const fileUrl = resultResponse.data.file_url;
        console.log('URL del archivo convertido:', fileUrl);

        const fileResponse = await axios.get(fileUrl, { responseType: 'arraybuffer' });
        fs.writeFileSync(outputPath, fileResponse.data);
        console.log('Conversión completada con éxito.');
    } catch (error) {
        console.error('Error durante la conversión:', error.response ? error.response.data : error.message);
        process.exit(1);
    }
}


// Ejecutar la conversión
convertPdfToWord(pdfPath, outputPath);
