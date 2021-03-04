# Vaccinate Ohio Website

## Getting Started

### First-Time Install

1. [Install Docker](https://docs.docker.com/get-docker/)
1. [Install docker-compose](https://docs.docker.com/compose/install/)
1. Clone this repo `git clone git@github.com:Cant-Stop-Columbus/vaccinateoh.git`
1. `cd build`
1. Create a `build/env.mk` file with at least a value for your local domain (ensure the hostname you use is pointed to your machine) and avoid pulling images from a docker repo:
```
export LOCAL_DOMAIN=vaccinateoh.local
```
1. `make setup-environment` the first time you start your dev environment to install dependencies, run migrations and seeders, and build assets

### Starting your environment

1. `make nginx-proxy` before accessing the site to start `nginx-proxy`
1. `make up` to start your local web, db, mailhog, and pgadmin containers
1. `make watch` While doing development to run your app assets build process inside the web container
1. Visit the site in your browser at [https://vaccinateoh.local](https://vaccinateoh.local) (or the domain you selected above).

## Notes

- `make down` will stop all containers _except the `nginx-proxy` container_.
