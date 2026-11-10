# fabienclement.dev

Ce dépôt est le thème de blocs `fabienclement-dev`, écrit de zéro pour le site fabienclement.dev. Local, l'application qui fait tourner les sites WordPress de Fabien, fait tourner ce site sous `~/Sites/fabienclement-dev`. Le fichier `.claude/vault` nomme le dossier du projet dans le vault.

## Ce que le code du thème respecte

Les ADR et `decisions-mineures.md`, dans le dossier du vault, portent les décisions du thème. Les décisions suivantes règlent l'écriture du code :

-   Le thème emploie les seuls blocs du cœur de WordPress. La page d'accueil doit rester sous 200 Ko compressés.
-   Le thème se livre tel qu'il est écrit, sans étape de build. Fabien envoie le thème par FTP, fichier par fichier.
-   Un gabarit se modifie dans `templates/` ou `parts/`, puis entre dans git.

## Composer sert au seul développement

WordPress charge le PHP du thème sans passer par Composer. Le dossier `vendor/` porte les outils de développement. Le serveur ne reçoit pas ce dossier.

> POURQUOI : le 15/10/2026, une recherche de Claude a trouvé trois pannes d'un `vendor/` chargé à l'exécution. Chacune de ces pannes bloque tout le site. Le `CLAUDE.md` du starter de thème enfant d'Astra, `astra-child-starter-theme`, détaille ces pannes.

## Ce qui fait vert

`composer lint` lance PHPCS, qui contrôle les standards de WordPress sur la configuration de `phpcs.xml.dist`. `composer format` corrige ce qui se corrige seul. Le sous-agent `verify-theme` lance tous les contrôles avant un commit.

`composer lint` ne lance pas encore PHPStan, qui contrôle les types. La pull request qui ajoute le premier fichier PHP remet `phpstan analyse` dans le script `lint` de `composer.json`.

> POURQUOI : mesuré le 10/11/2026 sur ce thème, qui n'avait encore aucun fichier PHP. PHPStan a rendu « No files found to analyse ». Sa sortie en 1 a rendu `composer lint` rouge. PHPStan n'a pas d'option pour accepter une liste vide.

## Git

-   Conventional Commits, sujet en anglais, à l'impératif, 72 caractères au plus.
-   Le travail part de `main` sur une branche `feature/<nom-court>`. Le travail revient sur `main` par une pull request.
-   **Une pull request se merge avec ses commits**, jamais en squash : l'historique est fait de commits de merge.
