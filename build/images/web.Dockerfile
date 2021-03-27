FROM buckii/web-laravel

RUN npm install -g n && n stable

ADD app.tar /app

COPY cron-env-setup.sh /opt/docker/provision/entrypoint.d/cron-env-setup.sh
COPY laravel-cron /opt/docker/etc/cron/laravel-cron

RUN webpack-app-build.sh && laravel-app-build.sh
