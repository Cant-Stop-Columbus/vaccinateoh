FROM buckii/web-laravel

ADD app.tar /app

RUN webpack-app-build.sh && laravel-app-build.sh
