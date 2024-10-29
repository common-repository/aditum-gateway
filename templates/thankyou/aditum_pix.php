<div class="pix-thankyou">
    <div class="codigo-qr">
        <h3>Pagar com código Copia e Cola:</h3>
        <div class="pix">
            <input  id="codigo" type="text" value="<?php echo $aditum_data['qrCode']; ?>">
            <button class="btn" data-clipboard-target="#codigo">
                <img width="30" src="<?php echo plugin_dir_url(dirname(__DIR__)); ?>assets/clippy.svg" alt="Copiar código">
            </button>
        </div>
        <div id="msg-copy"></div>
        <script>
            var clipboard = new ClipboardJS('.btn');
            clipboard.on('success', function(e) {
                e.clearSelection();
                var msg = document.querySelector('#msg-copy');
                msg.innerHTML = 'Código copiado!'
                setTimeout(() => msg.innerHTML = '', 3000)
            });

        </script>
        <div class="alert alert-error">O código Pix é válido por 30 minutos</div>
    </div>
    <br/>
    <br/>
    <div class="image-qr">  
        <h3>Pagar com QR Code:</h3>
        <div class="pix-qr">
            <img width="300" src="data:image/jpeg;base64,<?php echo $aditum_data['qrCodeBase64']; ?>">
        </div>
        <div class="alert alert-error">O QR Code é válido por 30 minutos</div>      
    </div>
    <br/>
    <br/>
</div>