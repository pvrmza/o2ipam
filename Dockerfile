# base
FROM alpine:3.15.4
LABEL maintainer="Pablo A. Vargas <pablo@pampa.cloud>"

RUN apk update && apk add php8 php8-mysqli php8-mbstring php8-curl fping curl

COPY files/ /var/www/html
COPY files/entry.sh /entry.sh

RUN crontab /var/www/html/cron-o2ipam && \    
    touch /var/log/cron.log && \
    chmod 755  /entry.sh

CMD ["/entry.sh"]