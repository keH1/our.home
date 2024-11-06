FROM nginx:1-alpine

WORKDIR /var/www/html

COPY . /var/www/html

RUN if [ ! -L /var/www/html/public/storage ]; then \
        ln -s /var/www/html/storage/app/public /var/www/html/public/storage; \
    fi

COPY .docker/production/nginx/etc/default.conf /etc/nginx/templates/default.conf.template
COPY .docker/production/nginx/etc/nginx.conf /etc/nginx/nginx.conf
