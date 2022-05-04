# base
FROM alpine:3.15.4
LABEL maintainer="Pablo A. Vargas <pablo@pampa.cloud>"

RUN apk update && apk add php8 php8-mysqli php8-mbstring php8-curl fping curl

COPY files/ /var/www/html
COPY script/entry.sh /usr/bin/entry.sh
COPY script/cron-o2ipam /tmp/cron-o2ipam

RUN crontab /tmp/cron-o2ipam && \    
    touch /var/log/cron.log && \
    chmod 755  /usr/bin/entry.sh

CMD ["/usr/bin/entry.sh"]