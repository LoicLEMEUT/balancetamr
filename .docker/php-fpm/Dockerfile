# docker/php-fpm/Dockerfile

FROM php:fpm-alpine
COPY wait-for-it.sh /usr/bin/wait-for-it
RUN chmod +x /usr/bin/wait-for-it
RUN apk --update --no-cache add git
RUN docker-php-ext-install pdo_mysql
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY crontab /etc/cron.d/cool-task
RUN chmod 0644 /etc/cron.d/cool-task
RUN crontab /etc/cron.d/cool-task
RUN touch /var/log/cron.log
WORKDIR /var/www
EXPOSE 9000