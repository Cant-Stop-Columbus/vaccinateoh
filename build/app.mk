export REGISTRY_URL=cantstopcbus
export WEB_BUILD_PATH=vaccinate-oh
export WEB_REPO_NAME=$(REGISTRY_URL)/$(WEB_BUILD_PATH)
export COMPOSE_PROJECT_NAME=vaccinateoh
export APP_NAME=VaccinateOH
export MAIL_FROM_NAME=VaccinateOH
export MAIL_FROM_ADDRESS=info@vaccinateoh.org
export IMPORT_CRON="* 1 * * *"
export IMPORT_STORE_PREFIXES=kroger,riteaid
PROD_WEB_SERVER=web1.vaccinateoh.org
