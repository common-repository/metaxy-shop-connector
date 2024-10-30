<?php
/*
Plugin Name:  Metaxy Shop Connector
Plugin URI:   https://reshark.eu/
Description:  Integra il tuo negozio WooCommerce con i servizi di Metaxy Shop Connector. 
Version:      1.2.0
Author:       Metaxy SRL
Author URI:   https://metaxy.eu/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

function msctor_modify_after_before_query( $args, $request ){
	$modified_after = $request->get_param('modified_after');
	$modified_before = $request->get_param('modified_before');

	if ($modified_after) {
		$args['date_query'][0]['column'] = 'post_modified';
		$args['date_query'][0]['after']  = $modified_after;
	}
	
	if ($modified_before) {
		$args['date_query'][1]['column'] = 'post_modified';
		$args['date_query'][1]['before']  = $modified_before;
	}
	

	return $args;
}

$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

if (
    in_array( $plugin_path, wp_get_active_and_valid_plugins() )
    || in_array( $plugin_path, wp_get_active_network_plugins() )
) {
	add_filter('woocommerce_rest_orders_prepare_object_query', 'msctor_modify_after_before_query', 10, 2);
	add_filter('woocommerce_rest_product_object_query', 'msctor_modify_after_before_query', 10, 2);
}

add_filter('wp_handle_sideload_prefilter', 'add_extension_if_none_exists');

function add_extension_if_none_exists($file){
    if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) {
        return $file;
    }
    $real_mime = wp_get_image_mime( $file['tmp_name'] );
    $mime_to_ext = apply_filters(
        'getimagesize_mimes_to_exts',
        array(
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/bmp'  => 'bmp',
            'image/tiff' => 'tif',
            'image/webp' => 'webp',
        )
    );
    if ( ! empty( $mime_to_ext[ $real_mime ] ) ) {
        $file['name'] .= '.' . $mime_to_ext[ $real_mime ];
    }
    return $file;
}

// Nuovo endpoint eliminazione Media
add_action('rest_api_init', function () {
    register_rest_route('wc', '/media/(?P<id>\d+)',
	[
        'methods'  => WP_REST_Server::DELETABLE ,
        'callback' => 'delete_media_rest',
		'permission_callback' => function () {
			return true;
		}
    ]);
});

// Nuovo endpoint creazione Media
add_action('rest_api_init', function () {
    register_rest_route('wc', '/media',
	[
        'methods'  => WP_REST_Server::CREATABLE ,
        'callback' => 'create_media_rest',
		'permission_callback' => function () {
			return true;
		}
    ]);
});

function delete_media($media_id) {
    // Ottieni le informazioni sull'elemento multimediale
    $media = get_post($media_id);
    
    // Verifica che l'elemento multimediale esista e sia un'immagine o un file multimediale
    if ($media && ($media->post_type == 'attachment' || $media->post_type == 'image')) {
        // Elimina l'elemento multimediale dal database
         wp_delete_attachment($media_id, true);
        
        // Elimina il file fisico dall'archivio
        $file_path = get_attached_file($media_id);
        if (file_exists($file_path)) {
			unlink($file_path);
        }
        
        // Restituisci true se l'eliminazione è riuscita
        return true;
    }
    
    // Restituisci false se l'eliminazione non è riuscita
    return false;
}

function delete_media_rest(WP_REST_Request $request)
{
    if (is_user_logged_in()) {
		
		$mediaId = $request->get_param('id');
		if(delete_media($mediaId)){
			return new WP_REST_Response(200);
		}	
		else{
			return new WP_Error('ArgumentException', 'Media not found', [ 'status' => 400 ]);
		}		
    }
    return new WP_Error('unauthorized', __('Not logged in'), [ 'status' => 401 ]); //can also use WP_REST_Response
}

function create_media_rest(WP_REST_Request $request)
{
    if (is_user_logged_in()) {
				
		$body = $request->get_body();
		$params = json_decode( $body, true );
		
		$mediaUrl = $params['url'];
		$mediaLabel = $params['label'];
		
		if($mediaUrl !== null && $mediaLabel !== null){
			$mediaId = upload_media_from_url($mediaUrl, $mediaLabel);	
			return new WP_REST_Response($mediaId, 200);
		}	
		else{
			return new WP_Error('ArgumentException', 'Url and label empty', [ 'status' => 400 ]);
		}		
    }
    return new WP_Error('unauthorized', __('Not logged in'), [ 'status' => 401 ]); //can also use WP_REST_Response
}

function upload_media_from_url( $url, $name ) {
  
  // Esegui il download del file dal URL
  $file_data = file_get_contents( $url );
  
  // Ottieni il percorso della cartella di caricamento dei Media
  $upload_dir = wp_upload_dir()['path'];

  // Aggiungi il nome del file alla cartella di caricamento dei Media
  $file_path = $upload_dir . '/' . $name;

  // Scrivi il file nella cartella di caricamento dei Media
  file_put_contents( $file_path, $file_data );

  // Imposta le informazioni del file per l'upload
  $file_info = array(
    'name' => $name,
    'type' => mime_content_type( $file_path ),
    'tmp_name' => $file_path,
    'error' => 0,
    'size' => filesize( $file_path )
  );

  // Carica il file nella libreria Media di WordPress
  $attachment_id = media_handle_sideload( $file_info, 0 );

  // Ritorna l'ID dell'attachment
  return $attachment_id;
}

?>