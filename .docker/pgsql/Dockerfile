ARG POSTGRES_VERSION

FROM postgres:${POSTGRES_VERSION}

RUN apt-get install -y locales && sed -i '/ru_RU.UTF-8/s/^# //g' /etc/locale.gen && locale-gen
RUN localedef -i ru_RU -c -f UTF-8 -A /usr/share/locale/locale.alias ru_RU.utf8
ENV LANG ru_RU.utf8

CMD ["postgres"]

EXPOSE 5432
