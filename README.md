
## Installation

1. Cloner le repo dans le dossier wp-content/plugins de votre projet local.
2. Remplacer le fichier functions.php de votre theme enfant par le fichier theme.functions.php (à renommer en "functions.php")
3. Activer le plugin dans votre console d'administration

## Feature

**I - Mode de fonctionnement de la feature**
J’ai pris la décision d’intégrer cette feature dans un endpoint spécifique de
l’API REST wordpress à l’intérieur d’un plugin afin qu’elle soit callable n’importe
ou dans le projet.
Le plugin est assez simple, il ajoute le bulk action d’impression sur le
custom post type “intervention”, avec un callback qui va se charger d’appeler le
endpoint en question pour génération du planning en pdf, avec le paramètre
post_ids qui spécifie les id à extraire (si ce paramètre est vide, l’api retourne toutes
les occurrences).
Pour ce qui est du planning en version calendrier, le bouton d’impression
se contente d’appeler l’api sans paramètre afin que celle-ci retourne l’ensemble
des occurrences.

**II - Librairies utilisées**
Utilisation de la librairie DOMPDF pour son utilisation simple et sa prise en
charge de css complète (Au contraire de TCPDF par exemple).

**III - Possibles améliorations**
Au vu du temps imparti et de mon envie de livrer ce mini projet
rapidement, il y aurait plusieurs axes sur lesquels nous pourrions travailler :
- Prise en compte de la vue du calendrier afin de n’exporter que les
interventions qui sont présentes dans la vue actuelle.
- Refacto du code de templating du pdf en utilisant le moteur Twig.
- Renommer certaines fonctions / path