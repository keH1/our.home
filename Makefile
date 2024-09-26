#!/usr/bin/env make
# SHELL = sh -xv

DOCKER_COMPOSE_DIR := $(shell pwd)/.docker/production

ifneq ("$(wildcard ${DOCKER_COMPOSE_DIR}/.env)","")
	include $(DOCKER_COMPOSE_DIR)/.env
endif

DOCKER_COMPOSE := docker compose \
	--file="$(DOCKER_COMPOSE_DIR)/docker-compose.yml" \
	--project-directory="$(shell pwd)" \
	--env-file="$(shell pwd)/.docker/production/.env"

PHP := ${DOCKER_COMPOSE} run --rm -e XDEBUG_MODE=off app

ARTISAN := ${PHP} php artisan

COMPOSER := ${PHP} composer

########################################################################################################################
### Generic Commands
########################################################################################################################

.PHONY: help
help:  ## Shows this help message
	@echo "\n  🦄 Project \033[35m${PROJECT_NAME}\033[0m"
	@echo "  Usage: make [target]\n\n  Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' "$(shell pwd)/Makefile" | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "   🔸 \033[36m%-30s\033[0m %s\n", $$1, $$2}'

version: ## Shows project version
	@echo "  🦄 Project name: \033[35m${PROJECT_NAME}\033[0m"
	@echo "  🦊 GitLab Project: \033[35mhttps://gitlab.com/${CI_PROJECT_PATH}\033[0m"
	@echo "  🐳 \033[35m$(shell docker-compose --version)\033[0m"
	@echo "  📜 \033[35m$(shell make --version | head -n 1)\033[0m\n"

########################################################################################################################
### Basic Docker Commands
########################################################################################################################

.PHONY: login
login: ## Login to GitLab registry
	docker login ${CI_REGISTRY}

.PHONY: build
build: ## Build images for local development
	${DOCKER_COMPOSE} build

.PHONY: pull
pull: ## Pull images for local development from GitLab registry
	${DOCKER_COMPOSE} pull

.PHONY: copy-envs
copy-envs:
	cp .docker/production/.env.example .docker/production/.env
	cp .env.example .env

.PHONY: shell
shell: ## Runs sh within php container
	${DOCKER_COMPOSE} exec app sh

.PHONY: logs
logs: ## Shows logs of a service
	$(eval SERVICE := $(filter-out $@,$(MAKECMDGOALS)))
	@if [ "${SERVICE}" = "" ]; then \
		echo "Please specify a service. Usage: make logs [service_name]"; \
		echo "Available services are:"; \
		${DOCKER_COMPOSE} config --services; \
	else \
		${DOCKER_COMPOSE} logs -f ${SERVICE}; \
	fi

%:
	@:

.PHONY: up
up: ## Spins up containers
	${DOCKER_COMPOSE} up -d --remove-orphans
	sleep 3
	make ps

.PHONY: down
down: ## Shuts down project's containers
	${DOCKER_COMPOSE} down --remove-orphans

.PHONY: restart
restart: ## Restarts containers
	make down
	make up

.PHONY: ps
ps: ## Shows containers status
	${DOCKER_COMPOSE} ps -a

.PHONY: init
init: ## Initialize project
	#make login
	#make pull
	make up
	make artisan-key-generate
	make artisan-migrate
	make artisan-storage-link
	make down

.PHONY: convert
convert: ## Shows rendered Docker Compose file
	${DOCKER_COMPOSE} convert

########################################################################################################################
### PHP Artisan Commands
########################################################################################################################

.PHONY: artisan-key-generate
artisan-key-generate:
	${ARTISAN} key:generate

.PHONY: artisan-migrate
artisan-migrate:
	${ARTISAN} migrate

.PHONY: artisan-cache-clear
artisan-cache-clear:
	${ARTISAN} cache:clear

.PHONY: artisan-storage-link
artisan-storage-link:
	@if [ ! -L "./public/storage" ]; then ${ARTISAN} storage:link; fi
