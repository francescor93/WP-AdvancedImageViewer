<?php
// QUESTO FILE MODIFICA L'IMMAGINE DI INTESTAZIONE IN BASE ALLA CATEGORIA SULLA PAGINA DI VISUALIZZAZIONE DELL'ALLEGATO

add_filter('theme_mod_header_image', 'edit_header_image_from_category');

// CAMBIO L'IMMAGINE DI INTESTAZIONE
function edit_header_image_from_category($defaulturl) {
    
    // SE MI TROVO SULLA PAGINA DI UN ALLEGATO
    if (is_attachment()) {

        // IMPOSTO COME VUOTA LA VARIABILE DELLA CATEGORIA PRINCIPALE
        $primarycatid = '';

        // SE ESISTE LA CLASSE Easy_Primary_Term
        if (class_exists('Easy_Primary_Term')) {

            // LA RICHIAMO ED OTTENGO LA CATEGORIA PRIMARIA IMPOSTATA
            $easyprimary = new Easy_Primary_Term('category', get_the_ID());
            $primarycatid = $easyprimary->get_primary_term();
        }

        // A QUESTO PUNTO, SE NON HO ANCORA VALORIZZATO LA CATEGORIA PRINCIPALE, PER CLASSE MANCANTE O NESSUNA CATEGORIA IMPOSTATA
        if (!$primarycatid) {

            // OTTENGO L'ELENCO DI TUTTE LE CATEGORIE DEL POST
            $postcats = get_the_category();

            // E CONSIDERO COME PRIMARIA LA PRIMA VOCE DELL'ARRAY
            $primarycatid = $postcats[0]->term_id;
        }

        // SE ORA HO VALORIZZATO LA CATEGORIA PRINCIPALE
        if ($primarycatid) {

            // RECUPERO LO SLUG DALLA ID
            $catinfo = get_term_by('id', $primarycatid, 'category');
            $slug = $catinfo->slug;
            
            // SE ESISTE UN'IMMAGINE CORRISPONDENTE
            $file = dirname(dirname(__FILE__)) . '/images/' . $slug . '.jpg';
            if (file_exists($file)) {
                
                // IMPOSTO L'IMMAGINE DI HEADER PER QUELLA CATEGORIA
                $url = plugin_dir_url(dirname(__FILE__)) . 'images/' . $slug . '.jpg';
            }
            
            // SE NON ESISTE IMPOSTO L'IMMAGINE STANDARD
            else {
                $url = $defaulturl;
            }
        }

        // SE NON HO UN VALORE PER LA CATEGORIA PRINCIPALE IMPOSTO L'IMMAGINE STANDARD
        else {
            $url = $defaulturl;
        }
    }
    
    // SE NON SONO SULLA PAGINA DI UN ALLEGATO IMPOSTO IN OGNI CASO L'IMMAGINE STANDARD
    else {
        $url = $defaulturl;
    }
    
    // RESTITUISCO L'URL CALCOLATO COME RISPOSTA
    return $url;
}
?>