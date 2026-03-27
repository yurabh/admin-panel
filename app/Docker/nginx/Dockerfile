FROM nginx:alpine

COPY ./app/Docker/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN sed -i 's/app:9000/127.0.0.1:9000/g' /etc/nginx/conf.d/default.conf
