FROM debian:bullseye-slim

RUN apt-get update && \
    apt-get install -y supervisor

RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]