<?php
/*
* Plugin Name: Advanced Image Viewer
* Description: Allows you to change the wordpress attachment page: shows the selected image or optionally a related video, lists the downloadable image sizes and allows you to upload more downloadable file versions in non-image format, shows the image's tags and other similar images sorted by number of common tags.
* Author: Francesco Rega
* Author URI: https://github.com/francescor93
* Version: 1.0.0
*/


// INCLUDO IL FILE CHE GESTISCE LE IMPOSTAZIONI
require __DIR__ . '/includes/options.php';


// INCLUDO IL FILE CHE GESTISCE I DOWNLOAD
require __DIR__ . '/includes/download.php';


// INCLUDO IL FILE CHE GESTISCE GLI UPLOAD
require __DIR__ . '/includes/upload.php';


// INCLUDO IL FILE CHE GESTISCE L'HEADER
require __DIR__ . '/includes/header.php';

// INCLUDO IL FILE CHE GESTISCE LE MODIFICHE
require __DIR__ . '/includes/edits.php';


// INCLUDO IL FILE CHE GENERA LA PAGINA DI VISUALIZZAZIONE FINALE
require __DIR__ . '/includes/mainview.php';


// INCLUDO IL FOGLIO DI STILE
add_action('wp_enqueue_scripts', 'loadAivStylesheet', 20);
function loadAivStylesheet() {
	wp_register_style('aivstyle', plugins_url('advanced-image-viewer/style.css'));
	wp_enqueue_style('aivstyle');
}


// INCLUDO LA TRADUZIONE
add_action('plugins_loaded', 'loadAivTranslation');
function loadAivTranslation() {
	load_plugin_textdomain('advanced-image-viewer', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}


// CREO IL LINK ALLE IMPOSTAZIONI
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'aivSettingsLink');
function aivSettingsLink($links) {
    $settings_link = '<a href="admin.php?page=aiv-menu">' . __('Settings') . '</a>';
    array_push($links, $settings_link);
  	return $links;
}


// ALL'ATTIVAZIONE DEL PLUGIN ESEGUO LA RELATIVA FUNZIONE DI INIZIALIZZAZIONE
register_activation_hook(__FILE__, 'initAiv');
function initAiv() {
    
    // SE IL PLUGIN IMAGE WATERMARK NON È ATTIVO
    if (!is_plugin_active('image-watermark/image-watermark.php')) {
        
        // MOSTRO UN MESSAGGIO DI AVVERTIMENTO
        load_plugin_textdomain('advanced-image-viewer', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        $message = sprintf(__('Advanced Image Viewer requires that <a href="%s">Image Watermark</a> plugin is installed and active. Enable it before proceeding.', 'advanced-image-viewer'), 'https://wordpress.org/plugins/image-watermark/');
        deactivate_plugins(plugin_basename(__FILE__));
        
        // E TERMINO, IMPEDENDO L'ATTIVAZIONE DEL PLUGIN
        exit($message);
    }
    
    // SE I PLUGIN EASY PRIMARY CATEGORY O RELEVANSSI NON SONO ATTIVI MOSTRO SOLO UN AVVISO, MA PROSEGUO
    if ((!is_plugin_active('easy-primary-category/easy-primary-category.php')) OR (!is_plugin_active('relevanssi/relevanssi.php'))) {
        load_plugin_textdomain('advanced-image-viewer', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        
        // CREO UN AVVISO E LO SALVO IN UNO SPAZIO TEMPORANEO
        $message = sprintf(__('This plugin works best when <a href="%s">Easy Primary Category</a> and <a href="%s">Relevanssi</a> are also installed: activate them for maximum functionality.<br>Note: this message will no longer be shown in the future. The absence of one or more of the plugins listed above will not cause problems.', 'advanced-image-viewer'), 'https://wordpress.org/plugins/easy-primary-category/', 'https://wordpress.org/plugins/relevanssi/');
        set_transient('aiv-activation-notice', $message, 5);
    }    
    
    // SE LE IMPOSTAZIONI DEL PLUGIN NON SONO ANCORA PRESENTI NEL DATABASE CREO E SALVO DELLE IMPOSTAZIONI DI DEFAULT
    if (!get_option('aiv_options')) {
        $default = array(
            'aiv_thumb_width' => '140',
            'aiv_thumb_height' => '140',
            'aiv_medium_width' => '500',
            'aiv_medium_height' => '500',
            'aiv_large_width' => '1200',
            'aiv_large_height' => '1200',
            'aiv_related' => '10'
        );
        add_option('aiv_options', $default);
    }
}

// QUANDO CARICO LE PAGINE AMMINISTRATIVE
add_action('admin_notices', 'aivCheckRequirement');
function aivCheckRequirement() {
    
    // SE IMAGE WATERMARK NON È ATTIVO MOSTRO SEMPRE UN AVVISO
    if (!is_plugin_active('image-watermark/image-watermark.php')) {
        $class = 'notice notice-error';
        $message = sprintf(__('Advanced Image Viewer requires that <a href="%s">Image Watermark</a> plugin is installed and active. Enable it before proceeding.', 'advanced-image-viewer'), 'https://wordpress.org/plugins/image-watermark/');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
    
    // SE ALL'ATTIVAZIONE DEL PLUGIN È STATO GENERATO UN AVVISO LO MOSTRO
    if (get_transient('aiv-activation-notice')) {
        $class = 'notice notice-info';
        $message = get_transient('aiv-activation-notice');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
}
?>