<?php
require_once('tcpdf/tcpdf.php');
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

class PdfGenerator {

  private $posts;

  private const STATUS_COLOR = [
    "lightgreen",
    "#FED8B1",
    "#F1807E"
  ];

  private const MOIS = [
    '01' => 'Janvier', '02' => 'Février', '03' => 'Mars',
    '04' => 'Avril', '05' => 'Mai', '06' => 'Juin',
    '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre',
    '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
  ];

  // Tableau de correspondance des noms de jours de la semaine en français
  private const JOURS = [
      'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
      'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche',
  ];


  public function __construct(array $data) {
    $this->posts = $data;
    $this->mapByDate();
  }

  private function mapByDate() {
    $mappedPosts = [];
    foreach($this->posts as $post) {
      $dateTime = new DateTime($post["date_de_debut"]);
      $formattedDate = $dateTime->format('d/m/Y');
      $mappedPosts[$formattedDate][] = $post;
    }
    uksort($mappedPosts, array($this, 'compareDates'));

    $this->posts = $mappedPosts;
  }

  private function compareDates($a, $b) {
    $dateA = DateTime::createFromFormat('d/m/Y', $a);
    $dateB = DateTime::createFromFormat('d/m/Y', $b);

    if ($dateA == $dateB) {
        return 0;
    }
    return ($dateA < $dateB) ? -1 : 1;
  }

  public function generatePdf() {

    $dompdf = new Dompdf();
    $separator = '<div style="width: 100%; height: 1.5px; background-color:#F6F6F6;margin-bottom:20px;"></div>';
    $html = '<div style="font-family: Arial, sans-serif;color:#003366;">
    <h1 style="font-weight:bold;">Planning</h1>';
    $html .= $separator;
    foreach($this->posts as $key => $datePosts) {
      $date = DateTime::createFromFormat('d/m/Y', $key);

      $jour = $date->format('d');
      $monthStr = mb_substr(self::MOIS[$date->format('m')], 0, 3);
      $dayStr = self::JOURS[$date->format('l')];
      $dateFormat = "$jour $monthStr. $dayStr";
      // general date
      //$html .= '<p style="text-transform: uppercase;">'.$dateFormat . '</p>';

      $html .= '<p style="font-weight:bold;padding-bottom:10px;font-size:1.2em;">'.$dateFormat.'</p>';
      $html .= '<table  style="margin-bottom:20px;width:100%;border-collapse:separate;">';
      foreach($datePosts as $post) {
        $start = !empty($post['date_de_debut']) ? (new DateTime($post['date_de_debut']))->format('H:i') : 'NC';
        $end = !empty($post['date_de_fin']) ? (new DateTime($post['date_de_fin']))->format('H:i') : (new DateTime($post['date_de_debut']))->format('H:i');
        $state = $post['etat'] == 0 ? 'A Faire' : 'Faite';
        $priority = isset(self::STATUS_COLOR[$post['priorite']]) ? self::STATUS_COLOR[$post['priorite']] : 'red';
        $html .= '
        <tr style="font-size:1em;">
            <td style="font-weight:bold;width:200px;">'. $start .' - '. $end .'</td>
            <td style="width:80px;">'. $state .'</td>
            <td>
              <div style="width:15px; height:15px; border-radius: 50%; background-color:'.$priority.';"></div>
            </td>
            <td style="width: 400px;display: flex; align-items:center;text-align:start;">'. $post['title'] .'</td>
            <td>'. $post['localisation'] .'</td>
            <td style="font-weight:bold;">'. $post['techniciens_affectes']['display_name'] .'</td>
        </tr>';
      }
      $html .= '</table>';
      $html .= $separator;
    }
    $html .= '</div>';

    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    return $dompdf->stream();
  }
}