<?php
// QUESTO FILE GESTISCE I DOWNLOAD DELL'ALLEGATO. LO SHORTCODE VA INCOLLATO NEL FILE image.php DEL TEMPLATE

add_action('get_header', 'aiv_start_download');

// GESTISCO LE RICHIESTE DI DOWNLOAD
function aiv_start_download() {
    
    // OTTENGO LE ESTENSIONI AMMESSE
    global $aivAllowedFileTypes;
    
    // SE HO AVVIATO UN DOWNLOAD
    if (isset($_POST['startDownload'])) {

        // LEGGO IL FORMATO RICHIESTO E RECUPERO IL PERCORSO DEL FILE ORIGINALE
        $file = $_POST['file'];
        $filepath = get_attached_file(get_the_ID());
        
        // RICAVO L'ESTENSIONE DELLA RICHIESTA
        $dirpath = substr($filepath, 0, strrpos($filepath, "."));
        $extension = strtoupper(pathinfo($dirpath . '/' . $file, PATHINFO_EXTENSION));
        
        // CREO UN ARRAY DI FORMATI AGGIUNTI AMMESSI
        $allowedextensions = array_keys($aivAllowedFileTypes);
        $allowedextensions = array_map(strtoupper, $allowedextensions);
        
        // SE L'ESTENSIONE È UNO DEI FORMATI AGGIUNTI DEVO MODIFICARE IL PERCORSO DEL FILE RICHIESTO
        if (in_array($extension,$allowedextensions)) {

            // SE ESISTE UNA CARTELLA RELATIVA AL FILE
            if (file_exists($dirpath)) {
                
                // GENERO IL PERCORSO DEL FILE RICHIESTO
                $filepath = $dirpath . '/' . $file;
            }
        }

        // SE INVECE HO RICHIESTO UN'IMMAGINE INTERMEDIA DEVO MODIFICARE IL PERCORSO DEL FILE RICHIESTO
        elseif (in_array(strtolower($file),array('thumbnail', 'medium', 'medium_large', 'large'))) {
            $info = image_get_intermediate_size(get_the_ID(), $file);
            $filepath = str_replace(wp_basename($filepath), $info['file'], $filepath);
        }
        
        // SE ESISTE IL FILE NEL PERCORSO OTTENUTO LO SERVO COME DOWNLOAD E TERMINO, MA SOLO SE L'UTENTE HA ESEGUITO IL LOGIN
        if (file_exists($filepath)) {
            if (is_user_logged_in()) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit;
            }

            // SE NON HA ESEGUITO IL LOGIN MOSTRO UN AVVISO IN POPUP
            else {
                unauthorized_download(sprintf( __('Sorry, you have to <a href="%s">log in</a> to download this file', 'advanced-image-viewer'), wp_login_url(get_permalink())));                
            }
        }
    }
}

// GESTISCO GLI ERRORI PER I TENTATIVI DI DOWNLOAD NON AUTORIZZATI
function unauthorized_download($message) {
    
    // RISPONDO CON LO STATO 401
    status_header('401');
    
    // INCLUDO IL DIALOG
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');
    
    // MOSTRO L'AVVISO
    echo '
    <script>
        window.onload = function() {
            if (window.jQuery) {  
                jQuery(\'<div title="' . __('Error', 'advanced-image-viewer') . '">' . $message . '</div>\').dialog({modal: true});
            } else {
                alert(\'' . $message . '\');
            }
        }
    </script>';
    
    // TERMINO SENZA RIMANDARE AD ALTRE PAGINE
    return;
}
?>