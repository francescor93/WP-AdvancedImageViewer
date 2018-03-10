<?php
// QUESTO FILE GENERA LA PAGINA AMMINISTRATIVA PER LE IMPOSTAZIONI DEL PLUGIN

add_action('admin_init', 'aiv_settings_init');

// AL CARICAMENTO DELL'AMMINISTRAZIONE AGGIUNGO LA SEZIONE DI CONFIGURAZIONE AIV
function aiv_settings_init() {
    
    // CREO IL NUOVO GRUPPO DI IMPOSTAZIONI "AIV OPTIONS"
    register_setting('aiv', 'aiv_options', array('sanitize_callback' => 'aiv_validate'));

    // CREO UNA NUOVA SEZIONE PER L'IMPOSTAZIONE DELLE DIMENSIONI NELLA PAGINA DI OPZIONI DI AIV
    add_settings_section(
        'aiv_settings_sizes_section', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Images Sizes', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_settings_sizes_section', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings' // PAGINA A CUI ASSEGNARE L'ELEMENTO
    );

    // NELLA SEZIONE CREATA CREO I NUOVI CAMPI PER L'INPUT DELL'UTENTE
    add_settings_field(
        'aiv_thumb_size', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Watermarked Thumbnail Size', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_sizes', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'aiv_settings_sizes_section', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'aiv_thumb_', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'size_min' => '20',
            'size_max' => '200'
        )
    );
    add_settings_field(
        'aiv_medium_size', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Watermarked Medium Size', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_sizes', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'aiv_settings_sizes_section', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'aiv_medium_', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'size_min' => '200',
            'size_max' => '800'
        )
    );
    add_settings_field(
        'aiv_large_size', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Watermarked Large Size', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_sizes', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'aiv_settings_sizes_section', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'aiv_large_', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'size_min' => '800',
            'size_max' => '1800'
        )
    );
    
    // CREO UNA NUOVA SEZIONE PER L'IMPOSTAZIONE DELLE ESTENSIONI NELLA PAGINA DI OPZIONI DI AIV
    add_settings_section(
        'aiv_settings_extensions_section', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Attachments Extensions', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_settings_extensions_section', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings' // PAGINA A CUI ASSEGNARE L'ELEMENTO
    );

    // NELLA SEZIONE CREATA CREO I NUOVI CAMPI PER L'INPUT DELL'UTENTE
    add_settings_field(
        'aiv_extensions_list', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Allowed Attachment Extensions', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_extensions_list', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'aiv_settings_extensions_section', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'aiv_extensions_list', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
        )
    );
    
    // CREO UNA NUOVA SEZIONE PER L'IMPOSTAZIONE DEL NUMERO DI IMMAGINI CORRELATE NELLA PAGINA DI OPZIONI DI AIV
    add_settings_section(
        'aiv_settings_related_section', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Related Images', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_settings_related_section', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings' // PAGINA A CUI ASSEGNARE L'ELEMENTO
    );

    // NELLA SEZIONE CREATA CREO I NUOVI CAMPI PER L'INPUT DELL'UTENTE
    add_settings_field(
        'aiv_related', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Max Related Images', 'advanced-image-viewer'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'aiv_show_related', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'aiv_settings', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'aiv_settings_related_section', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'aiv_related', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
        )
    );
}

// FUNZIONE CHE MOSTRA LA DESCRIZIONE DELLA SEZIONE PER L'IMPOSTAZIONE DELLE DIMENSIONI
function aiv_show_settings_sizes_section($args) {
    echo '<p>' . __('Enter the thumbnail, medium and large image formats sizes that will be generated with the watermark and shown on the site pages', 'advanced-image-viewer') . '</p>';
    echo '<p class="notice notice-info">' . __('Note: these formats are different than the default ones in Wordpress and must have different sizes', 'advanced-image-viewer') . '</p>';
    echo '<p class="notice notice-info">' . sprintf(__('Note: to generate watermarked images you must enable the <a href="%s">Image Watermark</a> plugin and select for process the "thumbnailWM", "mediumWM" and "largeWM" formats', 'advanced-image-viewer'), 'https://wordpress.org/plugins/image-watermark/') . '</p>';
}

// FUNZIONE CHE MOSTRA I CAMPI DI INPUT DELLA SEZIONE PER L'IMPOSTAZIONE DELLE DIMENSIONI
function aiv_show_sizes($args) {
    
    // OTTENGO I VALORI SALVATI ATTUALI
    $options = get_option('aiv_options');
    
    // MOSTRO GLI ELEMENTI PER L'INSERIMENTO
    echo '
    <label for="' . esc_attr($args['label_for']) . 'width">' . __('Width', 'advanced-image-viewer') . '</label>
    <input type="number" min="' . $args['size_min'] . '" max="' . $args['size_max'] . '" id="' . esc_attr($args['label_for']) . 'width" name="aiv_options[' . esc_attr($args['label_for']) . 'width]"' . ((isset($options[esc_attr($args['label_for']) . 'width'])) ? 'value="' . $options[esc_attr($args['label_for']) . 'width'] . '"' : '') .  '>
    <label for="' . esc_attr($args['label_for']) . 'height">' . __('Height', 'advanced-image-viewer') . '</label>
    <input type="number" min="' . $args['size_min'] . '" max="' . $args['size_max'] . '" id="' . esc_attr($args['label_for']) . 'height" name="aiv_options[' . esc_attr($args['label_for']) . 'height]"' . ((isset($options[esc_attr($args['label_for']) . 'height'])) ? 'value="' . $options[esc_attr($args['label_for']) . 'height'] . '"' : '') .  '>';
}

// FUNZIONE CHE MOSTRA LA DESCRIZIONE DELLA SEZIONE PER L'IMPOSTAZIONE DELLE ESTENSIONI
function aiv_show_settings_extensions_section($args) {
    echo '<p>' . __('Enter non-image file formats for which you want to allow uploading as additional versions via the attachment view page', 'advanced-image-viewer') . '</p>';
}

// FUNZIONE CHE MOSTRA I CAMPI DI INPUT DELLA SEZIONE PER L'IMPOSTAZIONE DELLE ESTENSIONI
function aiv_show_extensions_list($args) {
    
    // OTTENGO I VALORI SALVATI ATTUALI
    $options = get_option('aiv_options');
    
    // INCLUDO L'ELENCO DELLE ESTENSIONI
    require 'lib/extensions.php';
    
    // CREO LA DATALIST CON L'ELENCO DELLE ESTENSIONI
    echo '<datalist id="extensionslist">';
    foreach($filetypelist as $filetype) {
        echo '<option value="' . $filetype['ext'] . ' (' . $filetype['mime'] . ')">';
    }
    echo '</datalist>';
    
    // MOSTRO IL CAMPO PER L'AGGIUNTA DI UN'ESTENSIONE AMMESSA
    echo '
    <label for="' . esc_attr($args['label_for']) . '_add">' . __('Add extension to list', 'advanced-image-viewer') . '</label>
    <br>
    <input list="extensionslist" id="' . esc_attr($args['label_for']) . '_add" name="aiv_options[' . esc_attr($args['label_for']) . '_add]">
    <br>
    <br>';
    
    
    // MOSTRO L'ELENCO DELLE ESTENSIONI AMMESSE
    echo '
    <label for="' . esc_attr($args['label_for']) . '_add">' . __('Allowed extensions', 'advanced-image-viewer') . '</label>
    <br>
    <select name="aiv_options[' . esc_attr($args['label_for']) . ']" multiple>';
    if (isset($options[esc_attr($args['label_for'])])) {
        $allowedfiletypes = unserialize($options[esc_attr($args['label_for'])]);
        foreach($allowedfiletypes as $filetype) {
            echo '<option value="' . $filetype['ext'] . ' (' . $filetype['mime'] . ')">' . $filetype['ext'] . ' (' . $filetype['mime'] . ')</option>';
        }
    }
        echo '
    </select>
    <p><i>' . __('Select an item and save changes to remove it from this list', 'advanced-image-viewer') . '<i></p>';
}

// FUNZIONE CHE MOSTRA LA DESCRIZIONE DELLA SEZIONE PER L'IMPOSTAZIONE DEL NUMERO DI IMMAGINI CORRELATE
function aiv_show_settings_related_section($args) {
    echo '<p>' . __('Enter the maximum number of related pictures you want to show on the attachment page under active item data', 'advanced-image-viewer') . '</p>';
}

// FUNZIONE CHE MOSTRA I CAMPI DI INPUT DELLA SEZIONE PER L'IMPOSTAZIONE DEL NUMERO DI IMMAGINI CORRELATE
function aiv_show_related($args) {
    
    // OTTENGO I VALORI SALVATI ATTUALI
    $options = get_option('aiv_options');
    
    // MOSTRO GLI ELEMENTI PER L'INSERIMENTO
    echo '
    <label for="' . esc_attr($args['label_for']) . '">' . __('Show this maximum number of related images', 'advanced-image-viewer') . '</label>
    <input type="number" min="1" max="100" id="' . esc_attr($args['label_for']) . '" name="aiv_options[' . esc_attr($args['label_for']) . ']"' . ((isset($options[esc_attr($args['label_for'])])) ? 'value="' . $options[esc_attr($args['label_for'])] . '"' : '') .  '>';
}

// FUNZIONE CHE CONTROLLA L'INPUT DELL'UTENTE AL SALVATAGGIO
function aiv_validate($input) {
    
    // CREO UN ARRAY VUOTO IN CUI SALVARE I VALORI CORRETTI
    $sanitizedinputs = array();
    
    // VERIFICO I VALORI DELLE DIMENSIONI    
    $sanitizedinputs['aiv_thumb_width'] = (filter_var($input['aiv_thumb_width'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 20, "max_range"=> 200)))) ? $input['aiv_thumb_width'] : '140';
    $sanitizedinputs['aiv_thumb_height'] = (filter_var($input['aiv_thumb_height'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 20, "max_range"=> 200)))) ? $input['aiv_thumb_height'] : '140';
    
    $sanitizedinputs['aiv_medium_width'] = (filter_var($input['aiv_medium_width'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 200, "max_range"=> 800)))) ? $input['aiv_medium_width'] : '500';
    $sanitizedinputs['aiv_medium_height'] = (filter_var($input['aiv_medium_height'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 200, "max_range"=> 800)))) ? $input['aiv_medium_height'] : '500';
    
    $sanitizedinputs['aiv_large_width'] = (filter_var($input['aiv_large_width'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 800, "max_range"=> 1800)))) ? $input['aiv_large_width'] : '1200';
    $sanitizedinputs['aiv_large_height'] = (filter_var($input['aiv_large_height'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 800, "max_range"=> 1800)))) ? $input['aiv_large_height'] : '1200';

    // RECUPERO L'ELENCO DEI FORMATI ALLEGATO AMMESSI E LO CONVERTO IN ARRAY
    $options = get_option('aiv_options');
    $allowedfiletypes = $options['aiv_extensions_list'];
    $allowedfiletypes = unserialize($allowedfiletypes);
    
    // SE HO UN FORMATO DA AGGIUNGERE
    if ($input['aiv_extensions_list_add']) {
        $addfiletype = $input['aiv_extensions_list_add'];
        
        // SEPARO L'ESTENSIONE DAL MIME TYPE
        $addfiletype = explode('(',$addfiletype);
        $addextension = trim($addfiletype[0]);
        $addmimetype = trim(rtrim($addfiletype[1],')'));
        
        // AGGIUNGO L'ELEMENTO ALLA LISTA DEI FORMATI AMMESSI, SE NON PRESENTE
        $newfiletype = array('ext' => $addextension, 'mime' => $addmimetype);
        if (!in_array($newfiletype,$allowedfiletypes)) {
            $allowedfiletypes[] = $newfiletype;
        }
    }
    
    // SE HO UN FORMATO DA RIMUOVERE
    if ($input['aiv_extensions_list']) {
        $delfiletype = $input['aiv_extensions_list'];
        
        // SEPARO L'ESTENSIONE DAL MIME TYPE
        $delfiletype = explode('(',$delfiletype);
        $delextension = trim($delfiletype[0]);
        $delmimetype = trim(rtrim($delfiletype[1],')'));
        
        // RIMUOVO L'ELEMENTO DALLA LISTA DEI FORMATI AMMESSI
        foreach($allowedfiletypes as $index => $filetype) {
            if (($filetype['ext'] == $delextension) AND ($filetype['mime'] == $delmimetype)) {
                unset($allowedfiletypes[$index]);
            }
        }
    }
    
    // SERIALIZZO NUOVAMENTE L'ELENCO DEI FORMATI
    $allowedfiletypes = serialize($allowedfiletypes);
    $sanitizedinputs['aiv_extensions_list'] = $allowedfiletypes;
    
    // VERIFICO IL VALORE DEL NUMERO DI IMMAGINI CORRELATE
    $sanitizedinputs['aiv_related'] = (filter_var($input['aiv_related'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range"=> 100)))) ? $input['aiv_related'] : '10';

    // RESTITUISCO I VALORI DA SALVARE NEL DATABASE
    return $sanitizedinputs;
}

// AGGIUNGO IL MENU AIV IN AMMINISTRAZIONE
add_action('admin_menu', 'aiv_main_menu');
function aiv_main_menu() {
    add_menu_page(
        'Advanced Image Viewer', // TITOLO DELLA PAGINA NEL TAG TITLE E NELL'H1
        'AIV', // NOME MOSTRATO NEL MENU AMMINISTRATIVO GENERALE
        'manage_options', // PERMESSI NECESSARI PER VISUALIZZARE IL MENU
        'aiv-menu', // SLUG DEL MENU
        'aiv_show_menu' // FUNZIONE CHE MOSTRA IL MENU
    );
}

// FUNZIONE CHE MOSTRA IL MENU
function aiv_show_menu() {
    
    // SE L'UTENTE NON HA I PERMESSI PER VISUALIZZARLO ESCO
    if (!current_user_can('manage_options')) {
        return;
    }

    // SE SONO STATE SALVATE LE OPZIONI
    if (isset($_GET['settings-updated'])) {        
        
        // CREO IL MESSAGGIO DI CONFERMA
        add_settings_error('aiv_options', 'aiv-message', __('Settings saved', 'advanced-image-viewer'), 'updated');        
    }

    // MOSTRO I MESSAGGI
    settings_errors('aiv_options');

    // MOSTRO IL CONTENITORE E IL FORM
    echo '
    <div class="wrap">
        <h1>' . esc_html(get_admin_page_title()) . '</h1>
        <form action="options.php" method="post">';

            // GENERO I CAMPI NONCE E ALTRI ELEMENTI NECESSARI AL FORM
            settings_fields('aiv');
            
            // MOSTRO IL BLOCCO DI IMPOSTAZIONI PRECEDENTEMENTE GENERATO
            do_settings_sections('aiv_settings');
    
            // MOSTRO IL PULSANTE SALVA
            submit_button(__('Save Settings', 'advanced-image-viewer'));
            echo '
        </form>
        <a rel="license" title="A work by Francesco Rega, licensed with Creative Commons Attribution-ShareAlike 4.0 International" href="http://creativecommons.org/licenses/by-sa/4.0/" target="_blank">
            <img alt="Creative Commons Attribution-ShareAlike 4.0 International" title="A work by Francesco Rega, licensed with Creative Commons Attribution-ShareAlike 4.0 International" src="https://i.creativecommons.org/l/by-sa/4.0/80x15.png">
        </a>
    </div>';
}
?>