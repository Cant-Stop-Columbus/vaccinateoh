# Basic Settings
# See https://docs.docker.com/compose/reference/envvars/
export COMPOSE_FILE=docker-compose.yml
export COMPOSE_PROJECT_NAME=example
export LOCAL_DOMAIN=$(COMPOSE_PROJECT_NAME).localhost
export EXPOSE_PORT=3000

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

up:
ifndef NO_PULL
	docker-compose pull
endif
	docker-compose up -d

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
	docker build -t $(WEB_IMAGE_NAME) -f images/web.Dockerfile images

push: push-web

push-web:
	docker push $(WEB_IMAGE_NAME)

release: build push

# App Target Overrides
-include app-targets.mk

# Environment Specific Target Overrides
-include env-targets.mk

initial-commit:
	git add --all && git ci -m 'Initial Commit'

certs/default.key certs/default.crt:
	openssl req -new -newkey rsa:2048 -sha256 -days 3650 -nodes -x509 -subj "/C=US/ST=Ohio/L=New Albany/O=Buckeye Innovation, LLC/CN=buckeye-innovation" -keyout certs/default.key -out certs/default.crt

init-proxy-network:
	-docker network create -d bridge proxy-network

nginx-proxy: init-proxy-network certs/default.key certs/default.crt
	-docker rm nginx-proxy
	docker pull jwilder/nginx-proxy
	docker run -d --name nginx-proxy -p 80:80 -p 443:443 \
	-v /var/run/docker.sock:/tmp/docker.sock:ro \
	-v "$(PWD)/certs:/etc/nginx/certs" \
	-v "$(PWD)/conf/custom.conf:/etc/nginx/conf.d/custom.conf" \
	--restart=unless-stopped \
	--network=proxy-network jwilder/nginx-proxy

nginx-proxy-down:
	-docker stop nginx-proxy
	-docker rm nginx-proxy

composer-install:
	$(DOCKER_WEB_EXEC) composer install

setup-environment: install composer-install run-dev migrate seed seed-locations

migrate:
	$(DOCKER_WEB_EXEC) php artisan migrate

seed:
	$(DOCKER_WEB_EXEC) php artisan db:seed

seed-locations:
	$(DOCKER_WEB_EXEC) php artisan db:seed --class=LocationSeeder

tinker:
	$(DOCKER_WEB_EXEC) php artisan tinker

install:
	$(DOCKER_WEB_EXEC) yarn install

run-dev:
	$(DOCKER_WEB_EXEC) yarn dev

watch:
	$(DOCKER_WEB_EXEC) yarn watch
