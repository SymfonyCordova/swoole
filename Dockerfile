FROM sfswoole:latest
MAINTAINER symfonycordova
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
RUN mkdir /etc/mysql/custom.conf.d \
    && sed -i '$a!includedir /etc/mysql/custom.conf.d/' /etc/mysql/my.cnf
VOLUME /etc/nginx/conf.d
VOLUME /var/log/nginx
VOLUME /var/www/html
VOLUME /etc/mysql/custom.conf.d
VOLUME /var/lib/mysql
VOLUME /var/log/mysql
EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]