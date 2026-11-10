# fabienclement.dev

Ce dépôt est le thème de blocs `fabienclement-dev`, écrit de zéro pour le site fabienclement.dev. Local, l'application qui fait tourner les sites WordPress de Fabien, fait tourner ce site sous `~/Sites/fabienclement-dev`. Le fichier `.claude/vault` nomme le dossier du projet dans le vault.

## Ce que le code du thème respecte

Les ADR et `decisions-mineures.md`, dans le dossier du vault, portent les décisions du thème. Les décisions suivantes règlent l'écriture du code :

-   Le thème emploie les seuls blocs du cœur de WordPress. La page d'accueil doit rester sous 200 Ko compressés.
-   Le thème se livre tel qu'il est écrit, sans étape de build. Fabien envoie le thème par FTP, fichier par fichier.
-   Un gabarit se modifie dans `templates/` ou `parts/`, puis entre dans git.

## Composer sert au seul développement

WordPress charge le PHP du thème sans passer par Composer. Les dossiers `vendor/`, `bin/` et `tests/` portent les outils de développement. Le serveur ne reçoit pas ces dossiers.

> POURQUOI : le 15/10/2026, une recherche de Claude a trouvé trois pannes d'un `vendor/` chargé à l'exécution. Chacune de ces pannes bloque tout le site. Le `CLAUDE.md` du starter de thème enfant d'Astra, `astra-child-starter-theme`, détaille ces pannes.

## Ce qui fait vert

`composer lint` lance PHPCS, qui contrôle les standards de WordPress sur la configuration de `phpcs.xml.dist`. Composer lance ensuite PHPStan, qui contrôle les types. `composer format` corrige ce qui se corrige seul. `composer test` lance PHPUnit sur `tests/`. Le sous-agent `verify-theme` lance ces contrôles avant un commit.

`tests/avant-mise-en-ligne.test.sh` éprouve le script d'avant mise en ligne sur le site Local, qui doit tourner. Ce test exporte la base dans `app/sql/`. Il crée ensuite une copie du gabarit `index`, puis il la supprime. Lance ce test avant de merger une pull request qui touche `bin/`.

`tests/stubs/wp-cli.php` déclare à PHPStan les méthodes de `WP_CLI` que le thème appelle. Une méthode de `WP_CLI` que le thème appelle pour la première fois s'ajoute à ce fichier.

> POURQUOI : le 10/11/2026, Composer a refusé le paquet `php-stubs/wp-cli-stubs`. Toutes ses versions exigent les stubs de WordPress 6 au plus. Le thème déclare ceux de WordPress 7.

## Git

-   Conventional Commits, sujet en anglais, à l'impératif, 72 caractères au plus.
-   Le travail part de `main` sur une branche `feature/<nom-court>`. Le travail revient sur `main` par une pull request.
-   **Une pull request se merge avec ses commits**, jamais en squash : l'historique est fait de commits de merge.
