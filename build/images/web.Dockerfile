FROM buckii/web-laravel

RUN npm install -g n && n stable

ADD app.tar /app

ARG GOOGLE_MAPS_KEY
ENV MIX_GOOGLE_MAPS_KEY=$GOOGLE_MAPS_KEY

RUN webpack-app-build.sh && laravel-app-build.sh
