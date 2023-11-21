<?php
class BulkAction{

  private $screen, $actions, $query_args_to_remove;

  function __construct($screen, $actions)
  {
    $this->screen = $screen;
    $this->actions = $actions;
    // sert à netoyer les urls si on utilise plusieurs actions à la suite
    $this->query_args_to_remove = [];
    foreach ($this->actions as $custom_action) {
      $this->query_args_to_remove[] = $custom_action['slug'];
    }

    // on ajoute les fonction à la liste dans le select
    add_filter('bulk_actions-'.$this->screen, function ($bulk_actions) {
      foreach ($this->actions as $action) {
        $bulk_actions[$action['slug']] = $action['name'];
      }
      return $bulk_actions;
    });

    // l'action à executé
    add_filter('handle_bulk_actions-'.$this->screen, function($redirect_url, $action, $post_ids) {
      $redirect_url = remove_query_arg($this->query_args_to_remove, false);
      foreach ($this->actions as $custom_action) {
        if ($action === $custom_action['slug']) {
          $custom_action['function']($post_ids);
          $redirect_url = add_query_arg($custom_action['slug'], count($post_ids), $redirect_url);

        }
      }
      return $redirect_url;
    }, 10, 3);

    // affichage du message
    add_action('admin_notices', function() {
      foreach ($this->actions as $key => $custom_action) {
        if (!empty($_REQUEST[$custom_action['slug']])) {
          $num_changed = (int) $_REQUEST[$custom_action['slug']];
          echo '<div class="notice notice-success is-dismissable"><p>Action '.$custom_action['name'].' sur '.$num_changed.' articles réussie.</p></div>';
        }
      }
    });
  }
}
