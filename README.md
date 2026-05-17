# Tautological Test

Projet Symfony d'exercices PHPUnit centrés sur les **tests tautologiques** et **anti-patterns** qui font passer des tests au vert alors qu'ils devraient être rouges (et inversement).

Chaque exercice reproduit un anti-pattern courant vu dans la formation (J2 — bonnes pratiques de test, section « ce qu'il ne faut pas faire »).

## Prérequis

- PHP ≥ 8.2 avec extensions `pdo_sqlite`, `mbstring`
- Composer

## Installation

```bash
make install        # composer install + génération de la DB SQLite
```

## Lancer les tests

```bash
make tests                 # tous les tests
make test-controller       # exercice 1
make test-service          # exercice 2
make test-repository       # exercice 3
make test-processor        # exercice 4
```

---

## Les 4 exercices

### Exercice 1 — Le test est vert. Il ne devrait pas l'être.

**Fichier** : `tests/Controller/OrderControllerTest.php`

Le test du contrôleur passe. Pourtant le contrat API attendu par les consommateurs (`status: "CONFIRMED"`) n'est plus respecté.

**Mission** : lancer le test, comprendre pourquoi il est vert, corriger.

```bash
make test-controller
```

> Indice : regardez ce que compare l'assertion finale.

---

### Exercice 2 — Le test est vert. La désérialisation est cassée.

**Fichier** : `tests/Service/OrderImporterTest.php`

L'API externe envoie un JSON avec `customerName`. L'entité s'attend à `customer`. En production, l'objet `Order` désérialisé a un `$customer` vide. Pourtant le test est vert.

**Mission** : trouver pourquoi le bug ne remonte pas dans le test, et corriger le test pour qu'il l'attrape.

```bash
make test-service
```

> Indice : que retourne le serializer dans le test ?

---

### Exercice 3 — Comprendre l'inutilité du mock de QueryBuilder

**Fichiers** :
- `tests/Repository/OrderRepositoryMockTest.php` — le test mocké, vert mais inutile
- `tests/Repository/OrderRepositoryIntegrationTest.php` — le test d'intégration, rouge à raison

Le test mocké vérifie que `QueryBuilder` est appelé avec certaines méthodes, mais ne lance jamais de requête SQL réelle. Le test d'intégration boote le kernel Symfony, branche une vraie base SQLite (versionnée dans `database/schema.sql`) et révèle un bug dans la requête.

**Mission** :
1. Lancer les deux tests.
2. Constater que le test mocké passe alors que la requête est cassée.
3. Comprendre pourquoi le test d'intégration échoue et corriger la requête.
4. Optionnel : supprimer le test mocké, devenu inutile.

```bash
make test-repository
```

---

### Exercice 4 — Test fragile : corriger le test, pas le code

**Fichier** : `tests/Processor/OrderProcessorTest.php`

Ce test échoue. **Le code de production est correct** — c'est le test qui est sur-contraint :

- `expects($this->once())` partout, mélange Arrange et Assert
- Comparaison stricte de l'objet `Email` complet (sur-spécification du transport)
- Attentes sur la structure exacte des arguments du logger (rigide à toute évolution)
- `setUp()` qui prépare les fixtures (anti-DAMP)
- Re-vérifications redondantes après l'act

**Mission** : refactoriser le test pour qu'il devienne **robuste**, **lisible**, et qu'il vérifie le **comportement** (la commande est confirmée, un email part au bon destinataire) plutôt que les détails d'implémentation.

```bash
make test-processor
```

Appliquer les bonnes pratiques vues en J2 :
- DAMP (pas de `setUp` fixtures)
- AAA (Arrange / Act / Assert visuellement séparés)
- Spy plutôt que Mock (`shouldHaveBeenCalled` après l'act)
- Permissive Arrange / Strict Assert (`Argument::cetera()` en stub, `Argument::that(fn => assertEquals)` en spy)
- Vérifier le **comportement**, pas l'implémentation

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
├── Repository/OrderRepositoryMockTest.php       # exo 3.a (à supprimer)
├── Repository/OrderRepositoryIntegrationTest.php # exo 3.b (à corriger)
└── Processor/OrderProcessorTest.php             # exo 4 (à refactor)

database/
├── schema.sql      # versionné dans le repo
└── data.db         # généré par bin/init-db.php (gitignored)
```

## Référence

Slides J2 — sections « Bonnes pratiques au niveau des tests » et « Ce qu'il ne faut PAS faire ».
