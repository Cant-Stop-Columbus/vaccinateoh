FROM buckii/web-laravel

RUN npm install -g n && n stable

ADD app.tar /app

RUN webpack-app-build.sh && laravel-app-build.sh
