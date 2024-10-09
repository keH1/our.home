FROM nginx:alpine

RUN rm /etc/nginx/conf.d/default.conf

COPY .docker/production/web/etc/default.conf /etc/nginx/conf.d/default.conf

COPY .docker/production/web/build /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
