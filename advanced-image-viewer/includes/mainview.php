<?php
// QUESTO FILE GENERA LA PAGINA FINALE PER LA VISUALIZZAZIONE DELL'ALLEGATO. LO SHORTCODE VA INCOLLATO NEL FILE image.php DEL TEMPLATE

add_shortcode('aiv-view', 'aiv_template_load');

// GENERO LA PAGINA DI VISUALIZZAZIONE
function aiv_template_load($atts) {
    
    // INCLUDO LA VARIABILE CHE CONTIENE I FORMATI DI FILE CONSENTITI PER L'UPLOAD
    global $allowedextensions;
    
    // MOSTRO IL TITOLO ED I CONTENITORI
    echo
    '<div id="aiv-attachment" class="' . ((wp_attachment_is_image()) ? 'attachment-image' : 'attachment-file') . '">';
        the_title('<h1 class="entry-title attachment-title">', '</h1>');
        echo '
        <div id="attachment-area">
            <div id="imgdata">';

                // SE L'ELEMENTO È DI TIPO IMMAGINE MOSTRO L'ANTEPRIMA
                if (wp_attachment_is_image()) {

                    // SE HO UN VIDEO
                    if (has_category('video')) {

                        // LEGGO IL TESTO ALTERNATIVO, CHE DEVE CONTENERE L'URL YOUTUBE
                        $alt = get_post_meta( get_the_ID(), '_wp_attachment_image_alt', true);

                        // RICAVO L'ID DEL VIDEO
                        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $alt, $id)) {
                            $videoID = $id[1];
                        }
                        else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $alt, $id)) {
                            $videoID = $id[1];
                        }
                        else {
                            $videoID = '';
                        }

                        // CREO IL CODICE DI INCORPORAMENTO CON L'ID FORNITO
                        if ($videoID) {
                            echo '
                            <div class="videowrapper">
                                <iframe src="https://www.youtube-nocookie.com/embed/' . $videoID . '?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                            </div>';
                        }

                        // O MOSTRO UN AVVISO SE NON SONO RIUSCITO A CALCOLARE IL CODICE
                        else {
                            echo '<p><i>' . __('The video url has been defined in an incorrect format. Please double check the attachment settings', 'advanced-image-viewer') . '</i></p>';
                        }
                    }

                    // SE INVECE NON HO UN VIDEO MOSTRO L'IMMAGINE CON WATERMARK NEL FORMATO RICHIESTO
                    else {
                        $size = (isset($atts['size'])) ? $atts['size'] : 'largeWM';
                        $url = wp_get_attachment_image_src(get_the_ID(), $size);
                        $url = $url[0];
                        echo '<img src="' . $url . '" alt="Thumbnail">';
                    }
                }

                else {
                    echo wp_get_attachment_image(get_the_ID(), 'thumbnailWM', true, array('alt' => 'File'));
                }

                // MOSTRO LA DESCRIZIONE, SE PRESENTE E NON BLOCCATA
                $attdata = get_post(get_the_ID());
                $hidedescription = (isset($atts['hide-description'])) ? 1 : 0;
                if (($attdata->post_content) AND (!$hidedescription)) {
                    echo '<div class="entry-caption">';
                        echo nl2br($attdata->post_content);
                    echo '</div>';
                }

                // MOSTRO I TAG, SE PRESENTI E NON BLOCCATI
                $hidetags = (isset($atts['hide-tags'])) ? 1 : 0;
                if ((has_tag()) AND (!$hidetags)) {
                    echo '
                    <div class="tags-links">
                        <h3>' . __('Tags', 'advanced-image-viewer') . '</h3>';
                        the_tags('<p>','','</p>');
                    echo '</div>';
                }
            echo '</div>';

            // SE L'UTENTE HA IL PERMESSO DI CARICARE FILE
            if (current_user_can('upload_files')) {

                // ESEGUO LA FUNZIONE DI CONTROLLO E SALVATAGGIO UPLOAD
                aiv_start_upload();
            }

            // INIZIALIZZO UNA VARIABILE VUOTA PER CONTENERE LE OPZIONI DI DOWNLOAD
            $downloads = '';

            // SE HO UN ALLEGATO NON DI TIPO VIDEO
            if (!has_category('video')) {

                // CREO IL CONTENITORE DEI FORMATI IMMAGINE
                $downloads .= '
                <div id="sizeslist">
                    <h3>' . ((wp_attachment_is_image()) ? __('Image format', 'advanced-image-viewer') : __('Original file', 'advanced-image-viewer')) . '</h3>';

                    // SE HO UN FORMATO IMMAGINE
                    if (wp_attachment_is_image()) {

                        // LEGGO LE DIMENSIONI INTERMEDIE GENERATE DA WORDPRESS E PER OGNUNA GENERO UN'OPZIONE
                        $meta = wp_get_attachment_metadata(get_the_ID());
                        foreach ($meta['sizes'] as $name => $size) {

                            // MA SOLO SE SONO I FORMATI STANDARD
                            $standardsizes = array('thumbnail', 'medium', 'medium_large', 'large');
                            if (in_array(strtolower($name),$standardsizes)) {
                                $sizeinfo = ucfirst($name) . ' (' . $size['width'] . 'x' . $size['height'] . ')';
                                $downloads .= '<label>' . $sizeinfo . '
                                        <input type="radio" name="file" value="' . $name . '">
                                        <span></span>
                                    </label>';                        
                            }
                        }
                    }

                    // GENERO SEMPRE ANCHE L'OPZIONE PER IL FILE ORGINALE
                    if (wp_attachment_is_image()) {
                        $imgdata = wp_get_attachment_image_src( get_the_ID(), 'full');
                        $fileinfo = 'Original (' . $imgdata[1] . 'x' . $imgdata[2] . ')';
                    }
                    else {
                        $fileinfo = 'Original';
                    }
                    $downloads .= '<label>' . $fileinfo . '
                            <input type="radio" name="file" value="full" checked>
                            <span></span>
                        </label>';

                // CHIUDO IL CONTENITORE DEI FORMATI IMMAGINE
                $downloads .= '</div>';

            }

            // PER OGNI TIPOLOGIA CALCOLO IL NOME DELLA CARTELLA CORRISPONDENTE AL FILE ATTUALE
            $imgpath = get_attached_file(get_the_ID());
            $dirpath = substr($imgpath, 0, strrpos($imgpath, "."));

            // SE ESISTE
            if (file_exists($dirpath)) {

                // CREO IL CONTENITORE DEI FORMATI NON IMMAGINE
                $downloads .= '
                <div id="extensionslist">
                    <h3>' . __('Vector format', 'advanced-image-viewer') . '</h3>';

                    // ELENCO OGNI FILE CONTENUTO NELLA CARTELLA MOSTRANDONE IL NOME
                    $availablefiles = array_diff(scandir($dirpath), array('..', '.'));
                    foreach ($availablefiles as $file) {
                        $downloads .= '<label>' . $file . '
                                <input type="radio" name="file" value="' . $file . '">
                                <span></span>
                            </label>';
                    }

                // CHIUDO IL CONTENITORE DEI FORMATI NON IMMAGINE
                $downloads .= '</div>';
            }

            // MOSTRO IL CONTENITORE PER I DOWNLOAD
            echo '<div id="imgmanagement">';

                // SE HO DEL CONTENUTO DA PRESENTARE COME DOWNLOAD
                if ($downloads) {

                    // MOSTRO IL FORM PER LA SCELTA DEL FORMATO
                    echo '
                    <form id="downloadfile" method="post">
                        <h2>' . __('Download', 'advanced-image-viewer') . '</h2>';

                        // MOSTRO IL CONTENUTO SCARICABILE
                        echo $downloads;

                        // MOSTRO IL PULSANTE DI AVVIO DOWNLOAD E CHIUDO IL FORM
                        echo '
                        <input type="submit" name="startDownload" value="' . __('Download', 'advanced-image-viewer') . '">
                    </form>';            
                }

                // SE L'UTENTE HA IL PERMESSO DI CARICARE FILE
                if (current_user_can('upload_files')) {

                    // CREO L'AREA DI UPLOAD
                    echo '
                    <div id="uploadextension">
                        <hr>
                        <h2>' . __('Upload', 'advanced-image-viewer') . '</h2>';
                        // MOSTRO IL FORM
                        echo '<form method="post" enctype="multipart/form-data">';

                            // CREO IL NONCE
                            echo wp_nonce_field('aiv_upload_form', 'aiv_upload_form_submitted', true, false);

                            // MOSTRO I CAMPI
                            echo 
                            '<p>' . sprintf(__('Add to this element a non-image version - Max size: %d KB, allowed extensions: %s', 'advanced-image-viewer'), (wp_max_upload_size() / 1024), $allowedextensions) . '</p>
                            <label id="aiv_label" for="aiv_file">' . __('Choose file', 'advanced-image-viewer') . '</label>
                            <input type="file" name="aiv_file" id="aiv_file">
                            <br>
                            <input type="submit" id="aiv_submit" name="aiv_submit" value="' . __('Upload', 'advanced-image-viewer') . '">
                        </form>
                        <script>
                            jQuery("#aiv_file").on("change", function(e) {
                                if (jQuery("#aiv_file").get(0).files.length >= 1) {
                                    var filename = jQuery("#aiv_file")[0].files[0].name;
                                    jQuery("#aiv_label").html(filename);
                                }
                            });
                        </script>
                    </div>';
                }

                // CHIUDO TUTTI I CONTENITORI APERTI
                echo '
            </div>
        </div>';

        // SE NON NE HO RICHIESTO LA RIMOZIONE MOSTRO LE IMMAGINI CORRELATE
        $hiderelated = (isset($atts['hide-related'])) ? 1 : 0;
        if (!$hiderelated) {

            // RECUPERO UN ARRAY CON LE ID DI TUTTI I TAG DELL'ALLEGATO CHE STO VISUALIZZANDO
            $mainposttags = wp_get_post_tags(get_the_ID(),array('fields' => 'ids'));
            if ($mainposttags) {

                // LEGGO LE IMPOSTAZIONI DEL PLUGIN CHE CONTENGONO IL NUMERO MASSIMO DI IMMAGINI CORRELATE
                $options = get_option('aiv_options');
                $maxrelated = $options['aiv_related'];

                // CERCO UN MASSIMO DI DIECI POST CHE ABBIANO TAG IN COMUNE CON L'ALLEGATO ATTUALE
                $args = array(
                    'tag__in' => $mainposttags, // ALMENO UNO DEI TAG DELL'ALLEGATO DEVE ESSERE PRESENTE NEL RISULTATO
                    'post__not_in' => array(get_the_ID()), // IL RISULTATO NON DEVE ESSERE L'ALLEGATO STESSO
                    'post_type' => 'attachment', // IL RISULTATO DEVE ESSERE DI TIPO ALLEGATO
                    'post_status' => 'inherit', // NECESSARIO PER IDENTIFICARE GLI ALLEGATI
                    'posts_per_page' => $maxrelated, // MOSTRO AL MASSIMO "N" RISULTATI
                    'ignore_sticky_posts' => true, // EVITO L'IMPAGINAZIONE CHE PRIVILEGIA I POST CONTRASSEGNATI COME IMPORTANTI
                );
                $relatedquery = new WP_Query($args);

                // PER OGNI RISULTATO DI RICERCA
                foreach ($relatedquery->posts as $relatedpost) {

                    // OTTENGO LA LISTA DEI SUOI TAG
                    $tagobj = get_the_tags($relatedpost->ID);

                    // CREO UN ARRAY VUOTO PER RACCOGLIERE I TAG
                    $tags = array();

                    // PER OGNI OGGETTO TAG
                    foreach ($tagobj as $tag) {

                        // NE SALVO L'ID NELL'ARRAY
                        $tags[] = $tag->term_id;
                    }

                    // INFINE SALVO L'ARRAY DI TAG COME NUOVA PROPRIETÀ DEL POST
                    $relatedpost->tags = $tags;
                }

                // RICHIEDO DI ORDINARE L'ARRAY DEI RISULTATI CONFRONTANDOLI
                usort($relatedquery->posts, function($firstpost, $secondpost) use ($mainposttags) {

                    // CONTROLLO QUANTI TAG HA IN COMUNE IL PRIMO RISULTATO CHE STO PRENDENDO IN CONSIDERAZIONE CON IL POST PRINCIPALE
                    $firstcommontags = count(array_intersect($mainposttags,$firstpost->tags));

                    // CONTROLLO QUANTI TAG HA IN COMUNE IL SECONDO RISULTATO CHE STO PRENDENDO IN CONSIDERAZIONE CON IL POST PRINCIPALE
                    $secondcommontags = count(array_intersect($mainposttags,$secondpost->tags));                

                    // SE IL NUMERO DI POST IN COMUNE È LO STESSO RESTITUISCO UN RISULTATO NULLO
                    if ($firstcommontags == $secondcommontags) {
                        return 0;
                    }

                    // ALTRIMENTI, SE IL PRIMO POST HA MAGGIORI TAG IN COMUNE RISPETTO AL SECONDO LO POSIZIONO PRIMA, DIVERSAMENTE DOPO
                    return ($firstcommontags >$secondcommontags) ? -1 : 1;
                });

                // SE HO RISULTATI
                if ($relatedquery->have_posts()) {

                    // MOSTRO IL CONTENITORE
                    echo '<div id="attachmentrelated">';

                        // MOSTRO IL TITOLO
                        echo '<h3>' . __('Related images', 'advanced-image-viewer') . '</h3>';

                        // PER OGNUNO
                        while ($relatedquery->have_posts()) {
                            $relatedquery->the_post();

                            // CREO IL CONTENITORE
                            echo '<div class="related">';

                                // RECUPERO L'URL DELL'IMMAGINE
                                $url = wp_get_attachment_image_src(get_the_ID(), 'thumbnailWM');
                                $url = $url[0];

                                // MOSTRO L'IMMAGINE COME LINK CLICCABILE E IL SUO TITOLO COME CONTENUTO IN SOVRAIMPRESSIONE
                                echo '
                                <a href="' . get_attachment_link() . '">
                                    <img src="' . $url . '" alt="Thumbnail">
                                    <span>' . the_title('','',false) . '</span>
                                </a>
                            </div>';
                        }
                    echo '</div>';
                }

                // RIPRISTINO LA QUERY
                wp_reset_query();
            }
        }
    echo '</div>';
}
?>