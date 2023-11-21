<?php
/**
 * Plugin Name: HopPlace Planning Print API
 * Version: 0.0.1
 */

// BULK ACTIONS HOOK
require_once 'bulk_action.php';
require_once 'pdfGenerator.php';


function activate_hook() {
    add_option('print_rest_endpoint', 'print_posts');
}

function deactivation() {
    delete_option('print_rest_endpoint');
}

register_activation_hook(__FILE__, 'activate_hook');
register_deactivation_hook(__FILE__, 'deactivation');

$actions = [
    [
        'slug' => 'print',
        'name' => 'Imprimer',
        'function' => function($ids) {
            $api_url = sprintf('http://hopplace-challenge.local/wp-json/hop-place/v1/'.get_option('print_rest_endpoint').'/?post_ids=%s', json_encode($ids));

            // Effectuez la requête vers l'API REST
            $response = wp_remote_request($api_url);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                // Traitement de la réponse
                $data = json_decode(wp_remote_retrieve_body($response), true);
                // Faites quelque chose avec les données, par exemple, enregistrez-les dans le journal
                error_log(print_r($data, true));
            }

            $json_res = json_decode(wp_remote_retrieve_body($response));
            // Envoi des en-têtes pour forcer le téléchargement du fichier
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="hop-place-planning.pdf"');
            header('Content-Length: ' . strlen($json_res));

            // Envoyer le contenu du PDF
            echo wp_remote_retrieve_body($response);
            exit;
        },
    ]
];

$ba = new BulkAction('edit-intervention', $actions);

// Enregistrez la route API personnalisée
add_action('rest_api_init', 'print_posts_hp');
function print_posts_hp() {
    register_rest_route('hop-place/v1', '/'.get_option('print_rest_endpoint').'/', array(
        'methods'  => WP_REST_SERVER::READABLE,
        'callback' => 'get_posts_pdf',
        'args' => array(),
        'permission_callback' => function () {
          return true;
        }
    ));
}

// Callback pour la route API
function get_posts_pdf($data) {
    // Initialisez le tableau de données à retourner
    $result = array();

    $results = get_posts(array(
        'post_type' => 'intervention',
        'posts_per_page' => -1, // Récupère tous les posts
    ));

    if (isset($data['post_ids']) && !empty($data['post_ids'])) {
        // Récupérez les ID de post du tableau
        $post_ids = json_decode($data['post_ids'], true);

        $results = array_filter($results, function($value) use ($post_ids) {
            return in_array($value->ID, $post_ids); // Filtrer les nombres pairs
        });
    }

    foreach($results as $post) {
        if(isset($post->ID)) {
            $acf_data = get_fields($post->ID); // Utilisez la fonction get_fields d'ACF pour récupérer les données
            if ($acf_data) {
                $title = [
                    "title" => $post->post_title
                ];
                $result[] = $acf_data + $title;
            }
        }
    }


    $pdf = (new PdfGenerator($result))->generatePdf();
    echo $pdf;
    exit;

}