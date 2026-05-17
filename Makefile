.PHONY: install init-db tests test-controller test-service test-repository test-processor shell help

.DEFAULT_GOAL := help

DC := docker compose run --rm php

help: ## Outputs this help screen.
	@grep -E '(^[a-zA-Z0-9_\/\-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-22s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

install: ## composer install + génération de la base SQLite
	$(DC) composer install
	$(DC) php bin/init-db.php

init-db: ## Régénère la base SQLite depuis database/schema.sql
	$(DC) php bin/init-db.php

tests: ## Lance tous les tests
	$(DC) vendor/bin/phpunit

test-controller: ## Exercice 1 — Controller
	$(DC) vendor/bin/phpunit --testsuite controller

test-service: ## Exercice 2 — Service
	$(DC) vendor/bin/phpunit --testsuite service

test-repository: ## Exercice 3 — Repository (mocké + intégration)
	$(DC) vendor/bin/phpunit --testsuite repository

test-processor: ## Exercice 4 — Processor (test fragile)
	$(DC) vendor/bin/phpunit --testsuite processor

test-calculator: ## Exercice 5 — Calculator (test tautologique pur)
	$(DC) vendor/bin/phpunit --testsuite calculator

shell: ## Ouvre un shell dans le container PHP
	$(DC) bash
