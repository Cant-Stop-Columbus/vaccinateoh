export REGISTRY_URL=cantstopcbus
export WEB_BUILD_PATH=vaccinate-oh
export WEB_REPO_NAME=$(REGISTRY_URL)/$(WEB_BUILD_PATH)
export COMPOSE_PROJECT_NAME=vaccinateoh
export APP_NAME=Vaccinate Ohio
export MAIL_FROM_NAME=Vaccinate Ohio
export MAIL_FROM_ADDRESS=info@vaccinateoh.org
export KROGER_IMPORT_CRON="* 1 * * *"
PROD_WEB_SERVER=web1.vaccinateoh.org
