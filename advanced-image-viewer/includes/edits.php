<?php
// QUESTO FILE CONTIENE TUTTE LE ULTERIORI MODIFICHE MINORI APPORTATE AL CORE DI WORDPRESS NON RELATIVE AD UN FILE SPECIFICO


// OTTENGO L'ARRAY DEI TIPI DI FILE AMMESSI E LA LISTA SEMPLICE DELLE ESTENSIONI
$options = get_option('aiv_options');
$allowedfiletypes = $options['aiv_extensions_list'];
$allowedfiletypes = (unserialize($allowedfiletypes)) ?: array();
$aivAllowedFileTypes = array();
foreach($allowedfiletypes as $filetype) {
    $aivAllowedFileTypes[trim($filetype['ext'],'.')] = $filetype['mime'];
}
$allowedextensions = implode(', ', array_keys($aivAllowedFileTypes));


// AGGIUNGO TAG E CATEGORIE AGLI ALLEGATI
add_action('init', 'add_attachments_tags');
function add_attachments_tags() {
    register_taxonomy_for_object_type('post_tag', 'attachment');
}
add_action('init', 'add_attachments_categories');
function add_attachments_categories() {
    register_taxonomy_for_object_type('category', 'attachment');
}


// AGGIUNGO NUOVE DIMENSIONI PER LE IMMAGINI CON IL WATERMARK
add_action('init', 'add_watermarked_customsizes');
function add_watermarked_customsizes() {
    
    // LEGGO LE IMPOSTAZIONI DEL PLUGIN CHE CONTENGONO LE DIMENSIONI DELLE IMMAGINI
    $options = get_option('aiv_options');
    
    // FORMATO MINIATURA
    add_image_size('thumbnailWM', $options['aiv_thumb_width'], $options['aiv_thumb_height'], true);
    
    // FORMATO MEDIO
    add_image_size('mediumWM', $options['aiv_medium_width'], $options['aiv_medium_height'], false);
    
    // FORMATO GRANDE
    add_image_size('largeWM', $options['aiv_large_width'], $options['aiv_large_height'], false);
}


// AGGIUNGO IL CAMPO CATEGORIA AL FORM DI RICERCA
add_filter('get_search_form', 'add_searchform_category');
function add_searchform_category($form) {
    $form = str_replace('</form>',
        wp_dropdown_categories(array(
            'hide_empty' => false, 
            'show_option_all' => get_taxonomy('category')->labels->all_items,
            'echo' => 0,
        )) . '</form>',
        $form
    );
    return $form;
}


// AGGIUNGO L'ANTEPRIMA CLICCABILE DELL'IMMAGINE AL RISULTATO DELLA RICERCA SOTTO LA DESCRIZIONE
add_filter('the_excerpt', 'add_searchresult_thumb');
function add_searchresult_thumb($excerpt) {
    global $post;

    // SE SONO IN UN RISULTATO DI RICERCA DI TIPO ALLEGATO E NON SONO IN AMMINISTRAZIONE
    if ((is_search()) AND (!is_admin())) {

        // CREO IL CODICE DEL LINK
        $html = '';
        $html .= '<a href="' . get_attachment_link() . '">';

            // SE HO UN ALLEGATO DI TIPO IMMAGINE CREO LA SUA ANTEPRIMA
            if (($post->post_type == 'attachment') AND (wp_attachment_is_image(get_the_ID()))) {
                $url = wp_get_attachment_image_src(get_the_ID(), 'thumbnailWM');
                $url = $url[0];
                $html .= '<img src="' . $url . '" alt="Thumbnail">';
            }
        
            // ALTRIMENTI, SE HO UN POST CON UN'IMMAGINE IN EVIDENZA
            elseif (($post->post_type == 'post') AND (has_post_thumbnail(get_the_ID()))) {
                $html .= get_the_post_thumbnail($post, 'thumbnailWM');
            }
            
        // CHIUDO IL CODICE DEL LINK
        $html .= '</a>';
        
        // AGGIUNGO IL LINK ALLA DESCRIZIONE DALLA RICERCA
        $excerpt = $html . $excerpt;
    }

    // RESTITUISCO LA DESCRIZIONE MODIFICATA
    return $excerpt;
}


// AGGIUNGO UNO SHORTCODE PER VISUALIZZARE IL FORM DI RICERCA
add_shortcode('search_form', 'get_search_form');


// CAMBIO I LINK DEI TAG IN MODO CHE RIMANDINO AD UNA NUOVA RICERCA
add_filter('term_link', 'edit_tags_link', 10, 3);
function edit_tags_link($url, $term, $taxonomy) {
    return get_site_url() . "/?s=" . $term->name;
}


// PRIMA DI INVIARE GLI HEADER, SE SONO SU UNA PAGINA ALLEGATO, CAMBIO LE CLASSI DEL BODY RIMUOVENDO LA SIDEBAR
add_action('get_header', 'check_page_type');
function check_page_type() {
    if (is_attachment()) {
        add_filter('body_class', 'edit_body_sidebar_class');
    }
}
function edit_body_sidebar_class($wp_classes) {
    
    // ELENCO DELLE CLASSI DA RIMUOVERE
    $blacklist = array('has-sidebar');
    
    // CALCOLO LA DIFFERENZA FRA GLI ARRAY PER OTTENERE L'ELENCO DELLE CLASSI DA APPLICARE
    $wp_classes = array_diff($wp_classes,$blacklist);
    
    // RESTITUISCO LE CLASSI DA APPLICARE
    return $wp_classes;
}

// ALLA CANCELLAZIONE DI UN FILE RIMUOVO ANCHE LA SUA CARTELLA, SE PRESENTE
add_action('delete_attachment', 'remove_additional_filetypes');
function remove_additional_filetypes($fileid) {
    
    // RECUPERO IL PERCORSO DEL FILE
    $filepath = get_attached_file($fileid);
    
    // RIMUOVO L'ESTENSIONE DAL PERCORSO DEL FILE OTTENENDO IL PERCORSO DELLA CARTELLA CON LO STESSO NOME DEL FILE
    $dirpath = substr($filepath, 0, strrpos($filepath, "."));
    
    // SE LA CARTELLA CALCOLATA ESISTE
    if (is_dir($dirpath)) {
        
        // LA ELIMINO CON TUTTI I SUOI CONTENUTI
        foreach(glob($dirpath . '/*') as $file) {
            unlink($file);
        }
        rmdir($dirpath);
    }
}
?>