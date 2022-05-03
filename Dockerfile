# base
FROM php:7.4-cli
LABEL maintainer="Pablo A. Vargas <pablo@pampa.cloud>"

# Environment
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && apt-get -y dist-upgrade && \
    apt-get -y install cron curl fping && \
    apt-get clean autoclean && apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/lib/cache/* /var/lib/log/* /var/lib/apt/lists/*

#VOLUME ["/var/www/html" ]

COPY files/cron-o2ipam /etc/cron.d/cron-o2ipam 
RUN  chmod 0644 /etc/cron.d/cron-o2ipam && \
     crontab /etc/cron.d/cron-o2ipam && \
     touch /var/log/cron.log

RUN docker-php-ext-install mysqli
COPY files/  /var/www/html

WORKDIR /etc
CMD ["cron", "-f"]
