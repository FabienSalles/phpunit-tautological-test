.PHONY: install init-db tests help

.DEFAULT_GOAL := help

help: ## Outputs this help screen.
	@grep -E '(^[a-zA-Z0-9_\/\-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-20s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

install: ## Install dependencies and init the SQLite DB
	composer install
	php bin/init-db.php

init-db: ## (Re)generate the SQLite DB from database/schema.sql
	php bin/init-db.php

tests: ## Run all tests
	vendor/bin/phpunit

test-controller: ## Run exercise 1 (controller)
	vendor/bin/phpunit --testsuite controller

test-service: ## Run exercise 2 (service)
	vendor/bin/phpunit --testsuite service

test-repository: ## Run exercise 3 (repository)
	vendor/bin/phpunit --testsuite repository

test-processor: ## Run exercise 4 (processor — fragile test)
	vendor/bin/phpunit --testsuite processor
