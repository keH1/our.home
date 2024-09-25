#!/usr/bin/env make
# SHELL = sh -xv

DOCKER_COMPOSE_DIR := $(shell pwd)/.docker/production

ifneq ("$(wildcard ${DOCKER_COMPOSE_DIR}/.env)","")
	include $(DOCKER_COMPOSE_DIR)/.env
endif

DOCKER_COMPOSE := docker compose \
	--file="$(DOCKER_COMPOSE_DIR)/docker-compose.yml" \
	--project-name="$(PROJECT_NAME)" \
	--project-directory="$(shell pwd)" \
	--env-file="$(shell pwd)/.docker/production/.env" \
	--progress=tty

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

.PHONY: push
push: ## Push images for local development to GitLab registry
	${DOCKER_COMPOSE} push

.PHONY: pull
pull: ## Pull images for local development from GitLab registry
	${DOCKER_COMPOSE} pull

.PHONY: copy-envs
copy-envs:
	cp .docker/production/.env.example .docker/production/.env
	cp .env.example .env

.PHONY: shell
shell: ## Runs sh within php container
	${DOCKER_COMPOSE} exec php sh

.PHONY: up
up: ## Spins up containers
	${DOCKER_COMPOSE} up -d --remove-orphans
	sleep 3
	make ps
	#make npm-watch-logs

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
	#make composer-install
	#make npm-install
	make artisan-key-generate
	make artisan-migrate
	#make artisan-seed
	make artisan-storage-link
	make down

.PHONY: convert
convert: ## Shows rendered Docker Compose file
	${DOCKER_COMPOSE} convert

.PHONY: ground-zero
ground-zero: ## Removes logs, .env-files, folders: .data, vendor, node_modules
	rm -rf ./.data
	rm -rf ./node_modules
	rm -rf ./vendor
	if [ -f ./.docker/local/.env ]; then rm -f ./.docker/local/.env; fi
	rm -f ./.env
	rm -f ./storage/logs/*.log
	rm -f ./public/storage

########################################################################################################################
### PHP Commands
########################################################################################################################
.PHONY: composer-install
composer-install: ## Runs `composer install`
	${COMPOSER} install

.PHONY: composer-dumpautoload
composer-dumpautoload: ## Runs `composer dumpautoload`
	${COMPOSER} dumpautoload

.PHONY: test
test: ## Runs project tests
	${ARTISAN} test

########################################################################################################################
### PHP Artisan Commands
########################################################################################################################

.PHONY: artisan-tinker
tinker: ## Runs Tinker
	${ARTISAN} tinker

.PHONY: artisan-key-generate
artisan-key-generate:
	${ARTISAN} key:generate

.PHONY: artisan-seed
seed: ## Runs seeders to fill database for local development
	${ARTISAN} db:seed --class=LocalSeeder

.PHONY: artisan-ide-helper-models
ide-helper-models: ## Runs `php artisan ide-helper:models --write-mixin`
	${ARTISAN} ide-helper:models --write-mixin

.PHONY: artisan-migrate
artisan-migrate:
	${ARTISAN} migrate

.PHONY: artisan-migrate-fresh
artisan-migrate-fresh:
	${ARTISAN} migrate:fresh

.PHONY: artisan-cache-clear
artisan-cache-clear:
	${ARTISAN} cache:clear

.PHONY: artisan-storage-link
artisan-storage-link:
	@if [ ! -L "./public/storage" ]; then ${ARTISAN} storage:link; fi

########################################################################################################################
### PHP Artisan Macro Commands
########################################################################################################################

.PHONY: fresh
fresh: ## Clears cache and refreshes database
	make artisan-cache-clear
	make artisan-migrate-fresh

########################################################################################################################

.PHONY: build-production
build-production: ## Builds images for production
	docker compose \
    	--file "$(shell pwd)/.docker/production/docker-compose.yml" \
    	--project-directory="$(shell pwd)" \
    	--env-file="$(shell pwd)/.docker/production/.env" \
	build

FRONTEND_TAG := "registry.gitlab.com/keh192/snt.priborist/production/frontend"
.PHONY: build-production-frontend
build-production-frontend: ## Builds image for production
	docker build \
		--file="$(shell pwd)/.docker/production/frontend/Dockerfile" \
		--tag="${FRONTEND_TAG}" \
		.
	docker push ${FRONTEND_TAG}

.PHONY: node-shell
node-shell: ## Runs bash within node container
	${DOCKER_COMPOSE} run --rm node sh

.PHONY: npm-watch
npm-watch: ## Runs `npm run dev`
	${DOCKER_COMPOSE} run --rm --service-ports node npm run dev

.PHONY: npm-watch-logs
npm-watch-logs: ## Runs `npm run dev`
	${DOCKER_COMPOSE} logs -f node

.PHONY: npm-install
npm-install: ## Runs `npm install`
	${DOCKER_COMPOSE} run --rm node npm install

.PHONY: git-checkout
git-checkout:
	make composer-install
	make npm-install
	make artisan-migrate
