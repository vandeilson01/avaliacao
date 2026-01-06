<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivo - IA</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/icon.min.css">
    <style>
        .arquivoaceito, .iauploaderror, .iaenabled { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Enviar Arquivo para IA</h2>

        <div class="space-y-4">
            <div class="relative">
                <input type="file" id="inputSend" class="hidden">
                <label for="inputSend" id="btnUploadLabel" class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-blue-400 rounded-lg cursor-pointer hover:bg-blue-50 transition duration-300">
                    <span class="text-blue-600 font-medium" id="uploadStatusText">Clique para selecionar um arquivo</span>
                </label>
            </div>

            <div class="arquivoaceito p-3 bg-green-100 text-green-700 rounded-lg text-sm font-medium"></div>
            <div class="iauploaderror p-3 bg-red-100 text-red-700 rounded-lg text-sm font-medium"></div>
            <div class="iaenabled p-3 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium text-center">IA Enabled!</div>

            <button id="btnEnviar" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition duration-300 disabled:opacity-50">
                Enviar Arquivo
            </button>
        </div>
    </div>

    <script>
        // --- CONFIGURAÇÃO IMPORTANTE ---
        // Certifique-se de que gb.link_download contenha uma URL válida (http/https)
        var gb = {
            link_download: 'https://octorlink.com/dist/gabarito.pdf' // Exemplo de URL válida
        };

        function change_button_upload(status) {
            const btn = $('#btnEnviar');
            const text = $('#uploadStatusText');
            if (status === 'upload') {
                btn.prop('disabled', true).text('Enviando...');
                text.text('Processando arquivo...');
            } else {
                btn.prop('disabled', false).text('Enviar Arquivo');
                text.text('Clique para selecionar um arquivo');
            }
        }

        function onChangeEnviarArquivoNew() {
            console.log('Sucesso!');
        }

        async function valida_arquivo() {
            if ($('#inputSend')[0].files.length > 0) {
                $('.arquivoaceito, .iauploaderror, .iaenabled').hide();
                change_button_upload('upload');
                
                var file = $('#inputSend')[0].files[0];
                const webhookUrl = 'https://n8n.sistemastrace.online/webhook/upload-binario';
                
                // --- CORREÇÃO DO ERRO "URL UNDEFINED" ---
                // Garantimos que a URL enviada seja válida ou enviamos uma string vazia tratada
                const urlDownload = (gb && gb.link_download) ? gb.link_download : '';
                
                try {
                    const res = await fetch(webhookUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': file.type || 'application/octet-stream',
                            'X-Filename': encodeURIComponent(file.name),
                            // Enviamos a URL codificada. No n8n, o nó HTTP Request usa:
                            // {{ decodeURIComponent($('Webhook → Upload Binário').item.json.headers["url_download"]) }}
                            'url_download': encodeURIComponent(urlDownload),
                        },
                        body: file
                    });

                    const text = await res.text();
                    let parsedResponse = JSON.parse(text);

                    if (parsedResponse.status === 'OK') {
                        onChangeEnviarArquivoNew();
                        $('.arquivoaceito').html('<i class="check circle outline icon"></i> Seu arquivo aceito!');
                        $('.arquivoaceito').show();
                    } else {
                        let msg = 'Erro desconhecido';
                        if (parsedResponse.motivo == 'extensão') msg = 'Extensão não permitida.';
                        if (parsedResponse.motivo == 'vazio') msg = 'O arquivo está vazio.';
                        if (parsedResponse.motivo == 'similaridade') msg = 'O arquivo é muito semelhante ao modelo.';
                        
                        $('.iauploaderror').html(msg);
                        $('.iauploaderror').show();
                        $('.iaenabled').show();
                    }
                } catch (err) {
                    $('.iauploaderror').html('Erro na comunicação com o servidor.');
                    $('.iauploaderror').show();
                } finally {
                    change_button_upload('reset');
                    resetInput();
                }
            }
        }

        function resetInput() {
            $('#inputSend').val(null);
        }

        $(document).ready(function() {
            $('#inputSend').on('change', valida_arquivo);
            $('#btnEnviar').on('click', function() { $('#inputSend').click(); });
        });
    </script>
</body>
</html>
