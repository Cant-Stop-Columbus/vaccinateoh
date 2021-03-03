# Vaccinate Ohio Website

## Getting Started

1. [Install Docker](https://docs.docker.com/get-docker/)
1. [Install docker-compose](https://docs.docker.com/compose/install/)
1. Clone this repo `git clone git@github.com:Cant-Stop-Columbus/vaccinateoh.git`
1. Create a `build/env.mk` file with at least a value for your local domain (ensure the hostname you use is pointed to your machine) and avoid pulling images from a docker repo:
```
export LOCAL_DOMAIN=vaccinateoh.local
```
5. Start `nginx-proxy`:
```
docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy
```
6. `cd build`
1. Create a proxy network:
```
docker network create proxy-network
```
8. `make up` to start your local web, db, mailhog, and pgadmin containers
1. `make migrate seed-locations` to run migrations
1. `make watch` to run your app assets build process by entering a shell on the web container
1. Visit the site in your browser at [https://vaccinateoh.local](https://vaccinateoh.local) (or the domain you selected above).

## Notes

- `make down` will stop all containers _except the `nginx-proxy` container_.

## TODO

1. Add factories for faking location and availability data
