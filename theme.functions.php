<?php


function enqueue_style() {
    // to avoid cache problems
    $version = wp_get_theme()->get('Version');

    // Enqueue style.css
    wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css', array(), $version);
    wp_enqueue_script('font-awesome', 'https://kit.fontawesome.com/e144745512.js', array(), null, true);

}


function enqueue_fullcalendar_scripts() {
    if (is_admin()) { //
        wp_enqueue_script('fullcalendar-core', 'https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.9/index.global.min.js', array(), null, true);

    }
}

add_action('admin_enqueue_scripts', 'enqueue_style');
add_action('admin_enqueue_scripts', 'enqueue_fullcalendar_scripts');

function add_custom_admin_page() {
    add_menu_page(
        'Planning des Interventions',
        'Planning',
        'manage_options',
        'custom-interventions-calendar',
        'display_calendar_page',
        'dashicons-calendar',
        2
    );
}
add_action('admin_menu', 'add_custom_admin_page');

function display_calendar_page() {
    ?>
    <div class="wrap">
        <h1>Calendrier des Interventions</h1>
        <div class="actions">
            <?php if (get_option('print_rest_endpoint')) { ?>
            <button class="print_planning" onclick="print()">
                <i class="fa fa-print"></i>
                <span class="print_planning-label">Imprimer</span>
            </button>
            <?php } ?>
        </div>
        <div id='calendar'></div>
    </div>

    <script src="
    https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.9/index.global.min.js

"></script>

    <script>
        function print() {
            // ID du post à imprimer
            var postId = 1;

            // Effectuez une requête AJAX vers la route API personnalisée
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/wp-json/hop-place/v1/<?= get_option('print_rest_endpoint') ?>', true);
            xhr.responseType = 'blob';
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Créez un objet URL pour le blob
                    var url = window.URL.createObjectURL(xhr.response);

                    // Créez un élément <a> pour déclencher le téléchargement
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'hop-place-planning.pdf';  // Nom du fichier à télécharger

                    // Ajoutez l'élément <a> au corps du document
                    document.body.appendChild(a);

                    // Cliquez sur l'élément <a> pour déclencher le téléchargement
                    a.click();

                    // Retirez l'élément <a> du corps du document
                    document.body.removeChild(a);

                    // Libérez l'URL de l'objet blob
                    window.URL.revokeObjectURL(url);
                }
            };
            xhr.send();
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                firstDay: 1,
                weekNumbers: true,
                editable: true,
                droppable: true,
                headerToolbar: {
                    left: 'prev next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                },
                buttonText: {
                    today: 'Aujourd\'hui',
                    month: 'Mois',
                    week: 'Semaine',
                    day: 'Jour',
                    list: 'Liste'
                },
                events: <?php echo fetch_intervention_events(); ?>
            });
            calendar.render();

        });
    </script>
    <?php
}


function fetch_intervention_events() {
    $interventions = get_posts(array(
        'post_type' => 'intervention',
        'posts_per_page' => -1, // Récupère tous les posts
    ));

    $events = array();
    foreach ($interventions as $post) {
        $start_date = get_field('date_de_debut', $post->ID);
        $end_date = get_field('date_de_fin', $post->ID);
        $events[] = array(
            'title' => $post->post_title,
            'start' => $start_date,
            'end' => $end_date ? $end_date : $start_date
        );
    }

    return json_encode($events);
}

