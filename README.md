# Vaccinate Ohio Website

## Getting Started

1. [Install Docker](https://docs.docker.com/get-docker/)
2. Clone this repo `git clone git@github.com:Cant-Stop-Columbus/vaccinateoh.git`
3. Create a `build/env.mk` file with at least a value for your local domain (ensure the hostname you use is pointed to your machine) and avoid pulling images from a docker repo:
```
export LOCAL_DOMAIN=vaccinateoh.local
```
4. Start nginx-proxy `docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy`
5. From the `build` directory (for the remaining steps), build the web image with `make build`
6. Start your local web, db, mailhog, and pgadmin containers with `make up`
7. Run migrations with `make web-shell` then `php artisan migrate && php artisan db:seed --class=LocationTypeSeeder`
8. Run your app assets build process by entering a shell on the web container with `make web-shell` and then `yarn watch`
9. Visit the site in your browser at https://vaccinateoh.local (or the domain you selected above).

## TODO

Add factories for faking location and availability data
