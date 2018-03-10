<?php
// QUESTO FILE GESTISCE L'UPLOAD DI NUOVI FORMATI DELL'ALLEGATO

// GESTISCO LA RICHIESTA DI UPLOAD
function aiv_start_upload() {

    // OTTENGO LE INFORMAZIONI DELL'UTENTE ATTIVO
    global $current_user;
    
    // CREO LA VARIABILE VUOTA PER IL RISULTATO
    $result = '';

    // QUANDO VIENE INVIATO IL FORM DI UPLOAD CON UN NONCE VALIDO
    if ((isset($_POST['aiv_upload_form_submitted'])) && (wp_verify_nonce($_POST['aiv_upload_form_submitted'], 'aiv_upload_form'))) {

        // ELABORO GLI ERRORI
        $check = aiv_parse_file_errors($_FILES['aiv_file']);

        // SE HO ERRORI LI SEGNALO
        if ($check) {
            $result .= __('File error: ', 'advanced-image-viewer') . $check['error'];
        }

        // ALTRIMENTI INVIO L'IMMAGINE ALL'ELABORAZIONE
        else {
            $upload = aiv_process_image('aiv_file');
            
            // SE OTTENGO IN RISPOSTA L'URL DELL'IMMAGINE CARICATA LA CONSIDERO ELABORATA CORRETTAMENTE
            if (isset($upload['url'])) {
                $result .= __('File successfully uploaded', 'advanced-image-viewer');
            }
            
            // ALTRIMENTI SEGNALO L'ERRORE
            else {
                $result .= __('Upload error: ', 'advanced-image-viewer') . $upload['error'];
            }
        }
        
        // MOSTRO LA RISPOSTA
        echo '<script>alert("' . $result . '");</script>';
        
    }
}

// GESTISCO GLI ERRORI DI UPLOAD
function aiv_parse_file_errors($file) {
    
    // OTTENGO LE ESTENSIONI AMMESSE
    global $aivAllowedFileTypes, $allowedextensions;
    
    // CREO LA VARIABILE VUOTA PER IL RISULTATO
    $result = '';
    
    // SE IL FILE CONTIENE DEGLI ERRORI LO SEGNALO
    if ($file['error']) {
        $result = __('No file has been uploaded, or the uploaded file is corrupted', 'advanced-image-viewer');
    }
    
    // SE HO UN'ESTENSIONE NON AMMESSA LO SEGNALO
    if (!in_array($file['type'], $aivAllowedFileTypes)) {
        $result['error'] = sprintf(__('The type of the uploaded file is not supported. Only %s are allowed', 'advanced-image-viewer'), $allowedextensions);
    }
    
    // RESTITUISCO LA RISPOSTA
    return $result;
}

// GESTISCO L'UPLOAD VERO E PROPRIO
function aiv_process_image($file) {
    
    // OTTENGO LE ESTENSIONI AMMESSE
    global $aivAllowedFileTypes;
    
    // LEGGO IL PERCORSO IN CUI SI TROVA L'ALLEGATO ATTUALE
    $imgpath = get_attached_file(get_the_ID());
    
    // LEGGO LA CARTELLA DI UPLOAD PREDEFINITA DI WORDPRESS
    $uploaddir = wp_upload_dir();
    $uploaddir = $uploaddir['basedir'];
    
    // RIMUOVO DAL PERCORSO DELL'ALLEGATO IL PERCORSO DELLA CARTELLA DI UPLOAD OTTENENDO IL PERCORSO RELATIVO
    $relpath = str_replace($uploaddir,'',$imgpath);
    
    // RIMUOVO L'ESTENSIONE DAL PERCORSO OTTENENDO UN PERCORSO RELATIVO DALLA CARTELLA PRINCIPALE DI UPLOAD A UNA CARTELLA CON LO STESSO NOME DEL FILE
    $dirpath = substr($relpath, 0, strrpos($relpath, "."));
    
    // MEMORIZZO IL PERCORSO RELATIVO E IL NOME DEL FILE PRINCIPALE
    define('DIRPATH',$dirpath);
    
    // MODIFICO IL PERCORSO DI SALVATAGGIO DEL FILE
    add_filter('upload_dir', 'aiv_change_uploads_dir');
    
    // SE IL FILE CHE STO CARICANDO ESISTE GIÀ LO ELIMINO
    if (is_file($uploaddir . DIRPATH . '/' . $_FILES[$file]['name'])) {
        unlink($uploaddir . DIRPATH . '/' . $_FILES[$file]['name']);
    }
    
    // RICHIAMO IL FILE PER L'ESECUZIONE DELL'UPLOAD
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    
    // MODIFICO I CONTROLLI
    $overrides['test_form'] = false;
    $overrides['mimes'] = $aivAllowedFileTypes;
        
    // ESEGUO L'UPLOAD
    $filedata = wp_handle_upload($_FILES[$file], $overrides, current_time('mysql'));
    
    // RIPRISTINO IL PERCORSO DI SALVATAGGIO DEL FILE E IL NOME DEL FILE DI DESTINAZIONE
    remove_filter('upload_dir', 'aiv_change_uploads_dir');
    
    // RESTITUISCO I DATI DELL'UPLOAD
    return $filedata;
}

// MODIFICO IL PERCORSO DI SALVATAGGIO DEL FILE
function aiv_change_uploads_dir($dirs) {
    
    // IMPOSTO COME DESTINAZIONE LA CARTELLA CON IL NOME DEL FILE CORRENTE, SOTTOCARTELLA DELLA CARTELLA PRINCIPALE DI UPLOAD
    $dirs['subdir'] = DIRPATH;
    $dirs['path'] = $dirs['basedir'] . DIRPATH;
    $dirs['url'] = $dirs['baseurl'] . DIRPATH;
    
    // RESTITUISCO LE NUOVE CARTELLE
    return $dirs;
}

// MODIFICO IL NOME DEL FILE DI DESTINAZIONE
function aiv_main_file_filename($filename) {
    
    // RICAVO SEPARATAMENTE NOME ED ESTENSIONE DEL FILE CHE STO CARICANDO
    $info = pathinfo($filename);
    $ext  = empty($info['extension']) ? '' : '.' . $info['extension'];
    $name = basename($filename, $ext);
    
    // RICAVO IL NOME DEL FILE PRINCIPALE PRIVO DI ESTENSIONE
    $mainfilename = substr(MAINFILENAME, 0, strrpos(MAINFILENAME, "."));

    // SE ESISTE UN FILE CON IL NOME DEL FILE PRINCIPALE E L'ESTENSIONE DEL NUOVO FILE LO ELIMINO
    $uploaddir = wp_upload_dir();
    $uploaddir = $uploaddir['basedir'];
    if (is_file($uploaddir . DIRPATH . '/' . $mainfilename . $ext)) {
        unlink($uploaddir . DIRPATH . '/' . $mainfilename . $ext);
    }
    
    // ASSEGNO AL NUOVO FILE IN UPLOAD IL NOME DEL PRINCIPALE MANTENENDO L'ESTENSIONE CHE HA
    return $mainfilename . $ext;
}
?>