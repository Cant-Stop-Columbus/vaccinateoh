# Vaccinate Ohio Website

## Getting Started

### First-Time Install

1. [Install Docker](https://docs.docker.com/get-docker/)
1. [Install docker-compose](https://docs.docker.com/compose/install/)
1. Clone this repo `git clone git@github.com:Cant-Stop-Columbus/vaccinateoh.git`
1. `cd build`
1. Using the `build/env.mk.example` as a template, create a `build/env.mk` file with at least a value for your local domain (ensure the hostname you use is pointed to your machine) and avoid pulling images from a docker repo:
```
export LOCAL_DOMAIN=vaccinateoh.local
```
1. Follow the instructions in Starting Your Environment.

### Starting Your Environment

Follow these steps to start your dev environment:
1. `make nginx-proxy` before accessing the site to start `nginx-proxy`.
1. `make up` to start your local web, db, mailhog, and pgadmin containers
1. `make setup-environment` if this is the first time that you are starting your dev environment. This step will install dependencies, run migrations and seeders, and build assets.
1. `make watch`. Run this step while doing development to run the app build process inside the web container while coding/testing/designing.
1. Visit the site in your browser at [https://vaccinateoh.local](https://vaccinateoh.local) (or the domain and port you selected above in step 5 of First-Time Install).

### Stopping Your Dev Environment
1. Ctrl-C the running `make watch`.
1. `make down` to stop the local web, db, mailhog and pgadmin containers.
1. `make nginx-proxy-down` to stop the `nginx-proxy` container.

## Notes

- `make down` will stop all containers _except the `nginx-proxy` container_.
- `make nginx-proxy-down` will stop the `nginx-proxy` container.
