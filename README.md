## Plugin Apidae pour Wordpress

Ce plugin permet d'afficher des listes et le détail d'objets touristiques en provenance d'Apidae. Ce module utilise l'API d'Apidae, votre projet doit donc pouvoir l'utiliser. 

## Pourquoi

Ce projet a pour but de mettre à disposition un outil qui permet rapidement de créer un site internet avec de l'offre touristique et faire baisser le ticket d'entrée pour les petits offices de tourismes qui ont peu de budget.

## A qui s'adresse le Plugin

Ce plugin s'adresse à des prestataires techniques ou des personnes ayant des connaissances suffisantes. Le HTML est un prérequis, il faut ensuite savoir utiliser la documentation technique Apidae http://dev.apidae-tourisme.com/ il faudra également connaiter le JSONPath et plus particulièrement l'implémentation de Skyscanner https://github.com/Skyscanner/JsonPath-PHP

Les nombreux exemples de JSONPath disponibles dans la documentation, et l'outil de test JSONPath présent dans l'administration  du module devraientt permttre une appréhension rapide de cette implémentation.

## Installation

Cet outil a été développé avec php 5.6 et wordpress 4.7 et 4.8. Hors de ce contexte le plugin pourrait ne pas marcher.
Copiez le dossier wp84apidae dans le dossier wp-content/plugins de votre worpdress et installez l'extension. Attention l'utilisateur apache ou équivalent de votre serveur devra avoir des droits d'écriture. A l'installation de l'extension un dossier tmp avec les droits 0755 est créé pour la gestion du cache. Si après l'activation de l'extension ce dossier n'existe pas, c'est que vous avez un soucis de droits. Pour info, il est préférable que le wp-cron de votre wordpress soit activé car il effacera toutes les heures les fichiers de cache qui ne sont plus valides.

## Documentation

Dans le répertoire doc un fichier doc.pdf est disponible. Vous la trouverez également dans l'onglet documentation de l'administration du plugin. Dans chaque onglet de l'administration la partie correspondante de la doc s'affiche.

## Contributions

La contribution à ce projet sera disponible via la page github quand elle sera en place, ce plugin est en cours de test.

## Licence

La licence de ce plugin est en GNU GPLv3 voir LICENSE.txt pour plus d'information.

## Auteur

Plugin développé par Michel CHOUROT / Vaucluse Provence Attractivité http://vaucluseprovence-attractivite.com/