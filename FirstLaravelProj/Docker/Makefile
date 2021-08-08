SHELL:=/bin/bash
WAIT_FOR_HOST:=localhost
WAIT_FOR_TIMEOUT:=180
UNAME_S:=$(shell uname -s)
silent:=false
ifeq ($(UNAME_S),Linux)
ifeq ($(silent),true)
    CONTAINER_EXEC_TERM:=docker-compose exec -T
else
	CONTAINER_EXEC_TERM:=docker-compose exec
endif
else
	CONTAINER_EXEC_TERM:=docker-compose exec
endif
TESTS_FOLDER=docker/dev/tests
MAKE_IN_TESTS=$(MAKE) -C $(TESTS_FOLDER)
NUXT_PORT=3000
NUXT_BUILD_WAIT_TIMEOUT=1500

#-----------------------------------------------------------
# Docker
#-----------------------------------------------------------

# Wake up docker containers
up:
	docker-compose up -d

# Shut down docker containers
down-containers:
	docker-compose down

down: down-tests-containers down-containers

# Show a status of each container
status:
	docker-compose ps

# Status alias
s: status

# Show logs of each container
logs:
	docker-compose logs

# Restart all containers
restart: down up

#Restart specified containers
up-specified:
	docker-compose up -d $(container)

down-specified:
	docker-compose stop $(container)
	docker-compose rm -f $(container)

restart-specified-full: down-specified up-specified

# Restart the api container
restart-php:
	docker-compose restart php

# Restart the client container alias
rphp: restart-php

# full restart client
restart-php-full:
	$(MAKE) down-specified container=php
	$(MAKE) up-specified container=php

# Restart the client container
restart-client:
	docker-compose restart client

# Restart the client container alias
rc: restart-client

# full restart client
restart-client-full:
	$(MAKE) down-specified container=client
	$(MAKE) up-specified container=client

# Show the client logs
logs-client:
	docker-compose logs client

# Show the client logs alias
lc: logs-client

# Build and up docker containers
build:
	docker-compose up -d --build

# Build containers with no cache option
build-no-cache:
	docker-compose build --no-cache

# Build and up docker containers
rebuild: down build

# Run terminal of the php container
php:
	$(CONTAINER_EXEC_TERM) php bash

# Run terminal of the client container
client:
	$(CONTAINER_EXEC_TERM) client bash

#-----------------------------------------------------------
# Logs
#-----------------------------------------------------------

# Clear file-based logs
logs-clear:
	sudo rm docker/dev/nginx/logs/*.log
	sudo rm docker/dev/supervisor/logs/*.log
	sudo rm api/storage/logs/*.log

#-----------------------------------------------------------
# Artisan
#-----------------------------------------------------------
php-artisan:
	$(CONTAINER_EXEC_TERM) php php artisan $(command)

#-----------------------------------------------------------
# Configs
#-----------------------------------------------------------
config-cache:
	$(CONTAINER_EXEC_TERM) php php artisan config:cache

config-cache-testing:
	$(CONTAINER_EXEC_TERM) php php artisan config:cache --env=testing

#-----------------------------------------------------------
# Database
#-----------------------------------------------------------

# Run database migrations
db-migrate:
	$(CONTAINER_EXEC_TERM) php php artisan migrate

# Migrate alias
migrate: db-migrate

# Run migrations rollback
db-rollback:
	$(CONTAINER_EXEC_TERM) php php artisan migrate:rollback

# Rollback alias
rollback: db-rollback

# Run seeders
db-seed:
	$(CONTAINER_EXEC_TERM) php php artisan db:seed

# Fresh all migrations
db-fresh:
	$(CONTAINER_EXEC_TERM) php php artisan migrate:fresh

# Dump database into file
db-dump:
	@if [ -f .env ]; \
	then \
		export $(grep -v '^#' .env | xargs); \
		if [[ ! -d "${PWD}/docker/dev/mysql/dumps" ]]; \
		then \
			echo "folder ${PWD}/docker/dev/mysql/dumps not exists. Create it"; \
			mkdir ${PWD}/docker/dev/mysql/dumps; \
		fi; \
		$(CONTAINER_EXEC_TERM) mysql bash -c 'exec mysqldump --all-databases -uroot -p"$$MYSQL_ROOT_PASSWORD"' > docker/dev/mysql/dumps/dump.sql; \
	else \
		echo "file .env not exists. Database username and password must be set inside."; \
	fi

restart-mysql-testing-full:
	$(MAKE) down-specified container=mysql-testing
	$(MAKE) up-specified container=mysql-testing

wait-test-db-up:
ifeq ($(restart_test_db),true)
	$(MAKE) restart-mysql-testing-full
endif
	@echo "Wait for mysql-testing database connection"
	$(CONTAINER_EXEC_TERM) php bash -c "/var/www/sync_containers/wait-for-it.sh mysql-testing:3306 -t 180"

#-----------------------------------------------------------
# Redis
#-----------------------------------------------------------

redis:
	$(CONTAINER_EXEC_TERM) redis redis-cli

redis-flush:
	$(CONTAINER_EXEC_TERM) redis redis-cli FLUSHALL

redis-install:
	$(CONTAINER_EXEC_TERM) php composer require predis/predis


#-----------------------------------------------------------
# Queue
#-----------------------------------------------------------

# Restart queue process
queue-restart:
	$(CONTAINER_EXEC_TERM) php php artisan queue:restart

#-----------------------------------------------------------
# Testing containers
#-----------------------------------------------------------
down-tests-containers:
	$(MAKE_IN_TESTS) down-containers

#-----------------------------------------------------------
# Browser docker containers
#-----------------------------------------------------------
browser-chrome-up:
	$(MAKE_IN_TESTS) up-specified container=selenium-chrome
# 	cd docker/dev/tests && docker-compose up -d selenium-chrome

browser-chrome-down:
	$(MAKE_IN_TESTS) down-specified container=selenium-chrome
# 	cd docker/dev/tests && docker-compose stop selenium-chrome && docker-compose rm -f selenium-chrome

#-----------------------------------------------------------
# Ngrok containers
#-----------------------------------------------------------
ngrok-up:
	$(MAKE_IN_TESTS) up-specified container=ngrok

ngrok-down:
	$(MAKE_IN_TESTS) down-specified container=ngrok

ngrok-restart: ngrok-down ngrok-up

#-----------------------------------------------------------
# Testing
#-----------------------------------------------------------

test-db-prepare:
	$(MAKE) wait-test-db-up
	$(CONTAINER_EXEC_TERM) php php artisan testdb:prepare

test-db-delete-stripe-customer:
	$(CONTAINER_EXEC_TERM) php php artisan testclient:deletecustomer

#-----------------------------------------------------------
# Testing api
#-----------------------------------------------------------

# Run phpunit tests
test:
	$(MAKE) wait-test-db-up
	$(CONTAINER_EXEC_TERM) php vendor/bin/phpunit --order-by=defects --stop-on-defect
	$(MAKE) config-cache

# Run all tests ignoring failures.
test-all:
	$(MAKE) wait-test-db-up
	$(CONTAINER_EXEC_TERM) php vendor/bin/phpunit --order-by=defects
	$(MAKE) config-cache

# Run all tests ignoring failures.
test-specified:
	$(MAKE) wait-test-db-up
	$(CONTAINER_EXEC_TERM) php vendor/bin/phpunit --filter $(testname)
	$(MAKE) config-cache

# Run phpunit tests with coverage
coverage:
	$(MAKE) wait-test-db-up
	$(CONTAINER_EXEC_TERM) php vendor/bin/phpunit --coverage-html tests/report
	$(MAKE) config-cache

# Generate metrics
metrics:
	$(MAKE) wait-test-db-up
	$(CONTAINER_EXEC_TERM) php vendor/bin/phpmetrics --report-html=api/tests/metrics api/app

#-----------------------------------------------------------
# Testing client
#-----------------------------------------------------------
client-test-exec-chrome:
	$(CONTAINER_EXEC_TERM) client bash -c "/var/www/sync_containers/wait-for-it.sh localhost:$(NUXT_PORT) -t $(NUXT_BUILD_WAIT_TIMEOUT)"
	$(CONTAINER_EXEC_TERM) client bash -c "/var/www/sync_containers/wait-for-it.sh selenium-chrome:4444 -t 180 -- yarn test:e2e"

client-test-start: config-cache-testing test-db-prepare browser-chrome-up client-test-exec-chrome browser-chrome-down test-db-delete-stripe-customer config-cache

client-test:
	$(MAKE) -k client-test-start

#-----------------------------------------------------------
# Code quality api
#-----------------------------------------------------------
code-quality-api:
	$(MAKE) php-artisan command="insights app"

code-quality-api-jsonfile:
	$(MAKE) php-artisan command="insights app --format=json > insights-test.json"

code-quality-api-fix:
	$(MAKE) php-artisan command="insights app --fix"

code-quality-api-github-actions:
	$(MAKE) php-artisan command="insights app --format=github-action"

#-----------------------------------------------------------
# Code quality client
#-----------------------------------------------------------
es-lint:
	$(CONTAINER_EXEC_TERM) client yarn run lint

es-lint-fix:
	$(CONTAINER_EXEC_TERM) client yarn run lintfix

#-----------------------------------------------------------
# Dependencies
#-----------------------------------------------------------

# Install composer dependencies
composer-install:
	$(CONTAINER_EXEC_TERM) php composer install

# Update composer dependencies
composer-update:
	$(CONTAINER_EXEC_TERM) php composer update

# Update yarn dependencies
yarn-update:
	$(CONTAINER_EXEC_TERM) client yarn

# Update all dependencies
dependencies-update: composer-update yarn-update

# Show composer outdated dependencies
composer-outdated:
	$(CONTAINER_EXEC_TERM) yarn outdated

# Show yarn outdated dependencies
yarn-outdated:
	$(CONTAINER_EXEC_TERM) yarn outdated

# Show all outdated dependencies
outdated: yarn-update composer-outdated


#-----------------------------------------------------------
# Tinker
#-----------------------------------------------------------

# Run tinker
tinker:
	$(CONTAINER_EXEC_TERM) php php artisan tinker


#-----------------------------------------------------------
# Installation
#-----------------------------------------------------------

# Copy the Laravel API environment file
env-api:
	cp .env.api.example api/.env

# Copy the NuxtJS environment file
env-client:
	cp .env.client.example client/.env

# Add permissions for Laravel cache and storage folders
permissions:
	sudo chmod -R 777 api/bootstrap/cache
	sudo chmod -R 777 api/storage

# Permissions alias
perm: permissions

# Generate a Laravel app key
key:
	$(CONTAINER_EXEC_TERM) php php artisan key:generate --ansi

# Generate a Laravel storage symlink
storage:
	$(CONTAINER_EXEC_TERM) php php artisan storage:link

# PHP composer autoload command
autoload:
	$(CONTAINER_EXEC_TERM) php composer dump-autoload

# Install the environment
install: build install-laravel env-api migrate install-nuxt env-client restart


#-----------------------------------------------------------
# Git commands
#-----------------------------------------------------------

# Undo the last commit
git-undo:
	git reset --soft HEAD~1

# Make a Work In Progress commit
git-wip:
	git add .
	git commit -m "WIP"

# Export the codebase as app.zip archive
git-export:
	git archive --format zip --output app.zip master


#-----------------------------------------------------------
# Frameworks installation
#-----------------------------------------------------------

# Laravel
install-laravel:
	docker-compose down
	sudo rm -rf api
	mkdir api
	$(MAKE) up
	$(CONTAINER_EXEC_TERM) php composer create-project --prefer-dist laravel/laravel .
	sudo chown -R ${USER}:$(id -gn) api
	sudo chmod -R 777 api/bootstrap/cache
	sudo chmod -R 777 api/storage
	sudo rm api/.env
	cp .env.api.example api/.env
	$(CONTAINER_EXEC_TERM) php php artisan key:generate --ansi
	$(CONTAINER_EXEC_TERM) php composer require predis/predis
	$(CONTAINER_EXEC_TERM) php composer require pda/pheanstalk
	$(CONTAINER_EXEC_TERM) php php artisan --version

# Nuxt
install-nuxt:
	docker-compose down
	sudo rm -rf client
	docker-compose run client yarn create nuxt-app ../client
	sudo chown -R ${USER}:$(id -gn) client
	cp .env.client.example client/.env
	sed -i "1i require('dotenv').config()" client/nuxt.config.js
	$(MAKE) up
	$(CONTAINER_EXEC_TERM) client yarn info nuxt version

#-----------------------------------------------------------
# Pulling existing projects
#-----------------------------------------------------------
pull-api:
	@echo "#####PULL AND CONFIGURE LARAVEL API#####"
ifneq ($(silent),true)
	@if [[ -d "${PWD}/api" ]]; \
	then \
		read -r -p "Folder api exists. All content inside will erased. Are you sure? [y/N] " response; \
		case "$$response" in \
		    [yY][eE][sS]|[yY]) \
				echo "Clear folder ${PWD}/api"; \
		        ;; \
		    *) \
		        echo "EXIT"; \
		        exit 1; \
		        ;; \
		esac \
	fi
endif
	docker-compose down
	sudo chown -R ${USER}:$(id -gn) api
	sudo chmod -R 777 api/bootstrap/cache
	sudo chmod -R 777 api/storage
	@if [[ -f "${PWD}/api/.env" ]]; then sudo rm api/.env; fi
	cp .env.api.example api/.env
	@if [[ -f "${PWD}/api/.env.testing" ]]; then sudo rm api/.env.testing; fi
	cp .env.api.testing.example api/.env.testing
	$(MAKE) up
ifdef x_oauth_token
	$(CONTAINER_EXEC_TERM) php composer config --global github-oauth.github.com $(x_oauth_token)
endif
	$(CONTAINER_EXEC_TERM) php composer install
	$(CONTAINER_EXEC_TERM) php php artisan key:generate --ansi
	$(CONTAINER_EXEC_TERM) php php artisan key:generate --ansi --env=testing
	$(CONTAINER_EXEC_TERM) php php artisan storage:link
	$(CONTAINER_EXEC_TERM) php php artisan config:cache
	@echo "Wait for DB initializing"
	$(CONTAINER_EXEC_TERM) php chmod 777 /var/www/sync_containers/wait-for-it.sh
	$(CONTAINER_EXEC_TERM) php bash -c "/var/www/sync_containers/wait-for-it.sh mysql:3306 -t 180"
	@echo "DB initialized succesfully"
	$(CONTAINER_EXEC_TERM) php php artisan migrate --force
	$(CONTAINER_EXEC_TERM) php php artisan db:seed --force
	$(CONTAINER_EXEC_TERM) php php artisan passport:install
	docker-compose restart supervisor
	@echo "#####LARAVEL API SUCCESFULLY PULLED AND CONFIGURED#####"

pull-client:
	@echo "#####PULL AND CONFIGURE NUXTJS CLIENT#####"
ifneq ($(silent),true)
	@if [[ -d "${PWD}/client" ]]; \
	then \
		read -r -p "Folder client exists. All content inside will erased. Are you sure? [y/N] " response; \
		case "$$response" in \
		    [yY][eE][sS]|[yY]) \
				echo "Clear folder ${PWD}/client"; \
		        ;; \
		    *) \
		        echo "EXIT"; \
		        exit 1; \
		        ;; \
		esac \
	fi
endif
	docker-compose down
	sudo chown -R ${USER}:$(id -gn) client
	@if [[ -f "${PWD}/client/.env" ]]; then sudo rm client/.env; fi
	cp .env.client.example client/.env
	$(MAKE) up
	$(CONTAINER_EXEC_TERM) client yarn info nuxt version
	@echo "#####NUXTJS CLIENT SUCCESFULLY PULLED AND CONFIGURED. WAIT FOR FRONTEND BUILDING. AFTER FRONTEND COMPILING YOU CAN CLOSE LOG MANUALLY WITH CTRL+C#####"
ifneq ($(silent),true)
	docker-compose logs -f client
endif

#-----------------------------------------------------------
# Configure and init project
#-----------------------------------------------------------
pull-all: pull-api pull-client

configure-all:
	@if [[ -f "${PWD}/.env" ]]; then sudo rm .env; fi
	cp .env.example .env
	sudo chown -R ${USER}:$(id -gn) docker
	sudo chmod -R 777 docker

init-all: configure-all pull-all permissions

#-----------------------------------------------------------
# Update code from github
#-----------------------------------------------------------
update-code:
	git pull origin master

pull-code-api:
	cd api && git pull origin master

update-code-api: pull-code-api composer-update config-cache db-migrate

pull-code-client:
	cd client && git pull origin master

update-code-client: pull-code-client restart-client-full

#-----------------------------------------------------------
# Clearing
#-----------------------------------------------------------

# Shut down and remove all volumes
remove-volumes:
	docker-compose down --volumes

# Remove all existing networks (useful if network already exists with the same attributes)
prune-networks:
	docker network prune

# Clear cache
prune-a:
	docker system prune -amake

#-----------------------------------------------------------
# Stripe
#-----------------------------------------------------------
stripe-plans-sync-command:
	$(CONTAINER_EXEC_TERM) php php artisan stripe:syncplans

stripe-webhooks-sync-command:
	$(CONTAINER_EXEC_TERM) php php artisan stripe:syncwebhooks

stripe-plans-sync: config-cache stripe-plans-sync-command

stripe-webhooks-sync: config-cache stripe-webhooks-sync-command

#-----------------------------------------------------------
# Notifications
#-----------------------------------------------------------
use-check-singup-mails:
	$(CONTAINER_EXEC_TERM) php php artisan users:checksingupmails $(options)

#-----------------------------------------------------------
# Custom commands 
#-----------------------------------------------------------
wait-for:
	./docker/sync_containers/wait-for-it.sh $(WAIT_FOR_HOST):$(PORT) -t $(WAIT_FOR_TIMEOUT)
