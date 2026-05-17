# Tautological Test

Projet Symfony d'exercices PHPUnit centrés sur les **tests tautologiques** et **anti-patterns** qui font passer des tests au vert alors qu'ils devraient être rouges (et inversement).

Chaque exercice reproduit un anti-pattern courant vu dans la formation (J2 — bonnes pratiques de test, section « ce qu'il ne faut pas faire »).

## Prérequis

- Docker + Docker Compose

(rien d'autre — PHP, Composer et toutes les extensions tournent dans un container basé sur `php:8.3-cli`).

## Installation

```bash
make install        # build de l'image, composer install, génération de la DB SQLite
```

## Lancer les tests

```bash
make tests                 # tous les tests
make test-controller       # exercice 1
make test-service          # exercice 2
make test-repository       # exercice 3
make test-processor        # exercice 4
make shell                 # bash interactif dans le container
```

---

## Les 4 exercices

### Exercice 1 — Le test du contrôleur passe. Devrait-il ?

**Fichier** : `tests/Controller/OrderControllerTest.php`

Le contrat de l'API avec ses consommateurs : quand une commande est confirmée, le champ `status` de la réponse JSON vaut `"CONFIRMED"`.

**Mission** : lancer le test, vérifier que le contrat est bien respecté.

```bash
make test-controller
```

---

### Exercice 2 — Le service importe-t-il vraiment la commande ?

**Fichier** : `tests/Service/OrderImporterTest.php`

`OrderImporter::importFromJson()` doit transformer le payload JSON reçu de l'API externe en entité `Order`.

**Mission** : lancer le test, vérifier qu'en production l'objet retourné est bien rempli.

```bash
make test-service
```

---

### Exercice 3 — Deux tests pour un repository

**Fichiers** :
- `tests/Repository/OrderRepositoryMockTest.php`
- `tests/Repository/OrderRepositoryIntegrationTest.php`

Le repository expose `findByStatus(string $status): array`. Deux tests vérifient ce comportement, l'un en mockant Doctrine, l'autre en lançant une vraie requête sur la base SQLite versionnée dans `database/schema.sql`.

**Mission** :
1. Lancer les deux tests, comparer les résultats.
2. Décider quel test apporte la vraie protection — et que faire de l'autre.

```bash
make test-repository
```

---

### Exercice 4 — Pourquoi ce test casse ?

**Fichier** : `tests/Processor/OrderProcessorTest.php`

`OrderProcessor::process()` confirme une commande, envoie un email, logue. Le test échoue.

**Mission** :
1. Lancer le test, identifier la cause de l'échec.
2. Décider s'il faut corriger le code ou le test.
3. Si c'est le test, le refactoriser pour qu'il devienne robuste, lisible, et qu'il vérifie le comportement plutôt que les détails d'implémentation (cf. bonnes pratiques vues en J2 : DAMP, AAA, Spy plutôt que Mock, Permissive Arrange / Strict Assert).

```bash
make test-processor
```

---

## Structure

```
src/
├── Controller/OrderController.php      # exo 1
├── Service/OrderImporter.php           # exo 2
├── Repository/OrderRepository.php      # exo 3
├── Processor/OrderProcessor.php        # exo 4
└── Entity/Order.php

tests/
├── Controller/OrderControllerTest.php
├── Service/OrderImporterTest.php
├── Repository/OrderRepositoryMockTest.php
├── Repository/OrderRepositoryIntegrationTest.php
└── Processor/OrderProcessorTest.php

database/
├── schema.sql      # versionné dans le repo
└── data.db         # généré par bin/init-db.php (gitignored)
```

## Référence

Slides J2 — sections « Bonnes pratiques au niveau des tests » et « Ce qu'il ne faut PAS faire ».
