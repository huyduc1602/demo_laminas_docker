FROM mariadb:10.10.2

MAINTAINER "mariadb_server" <nesv006@gmail.com>

RUN apt-get update
RUN apt-get install mecab mecab-ipadic mecab-ipadic-utf8 -y
RUN apt-get install mariadb-plugin-mroonga
RUN apt install -y -V groonga-tokenizer-mecab

RUN rm -f /etc/localtime && ln -s /usr/share/zoneinfo/Asia/Ho_Chi_Minh /etc/localtime
RUN rm -f /etc/mecabrc
RUN touch /etc/mysql/empty_stopword.txt

COPY ./mecabrc /etc/mecabrc
COPY ./70-mecab.cnf /etc/mysql/mariadb.conf.d
#
# Run this inside docker container:
# mysql -uroot -N -e"INSTALL SONAME 'ha_mroonga'; CREATE FUNCTION last_insert_grn_id RETURNS INTEGER SONAME 'ha_mroonga.so';SET GLOBAL mroonga_default_tokenizer = TokenMecab;"
