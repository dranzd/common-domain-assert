export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)

DOCKER = docker compose run --rm php

.PHONY: help install test coverage phpstan cs cs-fix check shell
.DEFAULT_GOAL := help

help: ## Show this help
	@awk 'BEGIN {FS = ":.*## "} /^[a-z-]+:.*## / {printf "  %-10s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	$(DOCKER) composer install

test: ## Run the test suite
	$(DOCKER) composer test

coverage: ## Run the test suite with a coverage report
	$(DOCKER) composer test:coverage

phpstan: ## Run static analysis
	$(DOCKER) composer phpstan

cs: ## Check coding standards
	$(DOCKER) composer cs-check

cs-fix: ## Fix coding standards
	$(DOCKER) composer cs-fix

check: ## Run every quality gate
	$(DOCKER) composer check

shell: ## Open a shell in the container
	$(DOCKER) bash
