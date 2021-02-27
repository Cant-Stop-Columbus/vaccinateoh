# Basic Settings
# See https://docs.docker.com/compose/reference/envvars/
export COMPOSE_FILE=docker-compose.yml
export COMPOSE_PROJECT_NAME=example
export LOCAL_DOMAIN=$(COMPOSE_PROJECT_NAME).localhost

# Remote Server Parameters
PROD_WEB_SERVER=web1.$(SITE_DOMAIN)
PROD_SSH_USER=ubuntu

# Paths
export SRC_ROOT=../src

# Image Build Settings
export REGISTRY_URL=
export DOCKER_TAG=latest
export WEB_BUILD_PATH=$(COMPOSE_PROJECT_NAME)/web
export WEB_REPO_NAME=$(REGISTRY_URL)/$(WEB_BUILD_PATH)
export WEB_IMAGE_NAME=$(WEB_REPO_NAME):$(DOCKER_TAG)

# App Settings
export APP_NAME=example
export APP_ENV=local
export APP_KEY=base64:1cMRlzytI2i8fSPTEZtBQWceBn8T2L21RIRsrGC0v44=
export APP_DEBUG=true
export APP_LOG_LEVEL=debug
export APP_URL=https://$(LOCAL_DOMAIN)

# DB Settings
export DB_CONNECTION=pgsql
export DB_HOST=db
export DB_DATABASE=laravel
export DB_USERNAME=laravel
export DB_PASSWORD=laravel
export DB_PORT=5432

# Email Settings
export MAIL_DRIVER=smtp
export MAIL_HOST=mailhog
export MAIL_PORT=1025
export MAIL_USERNAME=null
export MAIL_PASSWORD=null
export MAIL_ENCRYPTION=null

# Cache settings
export MEMCACHED_HOST=memcached
export CACHE_DRIVER=memcached
export SESSION_DRIVER=memcached

# Container execs
DB_CONTAINER_NAME=docker ps --format '{{.Names}}' --filter "name=$(COMPOSE_PROJECT_NAME)_db | head -n1"
DOCKER_WEB_EXEC=docker-compose exec --user=application web
DOCKER_DB_EXEC=docker-compose exec db
DOCKER_DB_COMMAND=docker-compose exec -T db

# SQL related
PG_DUMP=pg_dump -Fc -U $$POSTGRES_USER $$POSTGRES_USER
PG_DUMP_SSH=pg_dump -Fc -U \$$POSTGRES_USER \$$POSTGRES_USER
PG_RESTORE=pg_restore -U $$POSTGRES_USER --single-transaction -d $$POSTGRES_USER
SQL_BACKUP_FILE=backup.psql

# App Variable Overrides
-include app.mk

# Environment Specific Variable Overrides
-include env.mk

up: ecr-login
ifndef NO_PULL
	docker-compose pull
endif
	docker-compose up -d

ecr-login:
	$$(aws ecr get-login --no-include-email --region us-east-2)

logs:
	docker-compose logs -f

down:
	docker-compose down
	docker-compose rm

web-shell:
	$(DOCKER_WEB_EXEC) bash

db-shell:
	$(DOCKER_DB_EXEC) psql -U $(DB_USERNAME)

setup-dev-environment: up fetch-db import-db

fetch-db:
	CNAME=$$(ssh $(PROD_SSH_USER)@$(PROD_WEB_SERVER) $(DB_CONTAINER_NAME)) && \
		ssh -C $(PROD_SSH_USER)@$(PROD_WEB_SERVER) "docker exec $$CNAME sh -c '$(PG_DUMP_SSH)'" > $(SQL_BACKUP_FILE)

drop-db: 
	$(DOCKER_DB_COMMAND) psql -U $(DB_USERNAME) -c "drop schema if exists public cascade; create schema if not exists public"

import-db: drop-db
	-$(DOCKER_DB_COMMAND) sh -c '$(PG_RESTORE)' < $(SQL_BACKUP_FILE)

export-db:
	$(DOCKER_DB_COMMAND) sh -c '$(PG_DUMP)' > $(SQL_BACKUP_FILE)

build: package-app build-web

package-app:
	cd ../src && git archive HEAD > ../build/images/app.tar

build-web:
	docker build -t $(WEB_REPO_NAME) -f images/web.Dockerfile --build-arg GOOGLE_MAPS_KEY=$(MIX_GOOGLE_MAPS_KEY) images

push: push-web

push-web: ecr-login
	docker push $(WEB_REPO_NAME)

release: build push

# App Target Overrides
-include app-targets.mk

# Environment Specific Target Overrides
-include env-targets.mk

create-new-app: install-new-app remove-defaults add-starter-files remove-starter-files initial-commit release up

install-new-app:
	cd .. && \
		composer create-project --prefer-dist laravel/laravel src

remove-defaults:
	cd ../src && \
		rm -rf resources/js resources/sass package.json webpack.mix.js

add-starter-files:
	cd custom-starter-files && \
		cp -r * ../../src/

remove-starter-files:
	rm -rf custom-starter-files

initial-commit:
	git add --all && git ci -m 'Initial Commit'
