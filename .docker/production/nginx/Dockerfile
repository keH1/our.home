FROM nginx:1-alpine

COPY . /var/www/html

COPY .docker/production/nginx/etc/default.conf /etc/nginx/templates/default.conf.template
COPY .docker/production/nginx/etc/nginx.conf /etc/nginx/nginx.conf
