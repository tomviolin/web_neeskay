FROM php:7.4-apache

MAINTAINER Tom Hansen "tomh@uwm.edu"

RUN echo deb http://http.us.debian.org/debian bullseye main contrib non-free >> /etc/apt/sources.list
# RUN apt-get update && apt-get install -y ttf-mscorefonts-installer

RUN apt-get update && apt-get install -y \
	ttf-mscorefonts-installer \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli


# COPY ./.my.cnf /root/
COPY . /var/www/html/neeskay


###  TIMEZONE FIX  (AIN'T IT PURRDY?) ###
#RUN ln -fs /usr/share/zoneinfo/America/Chicago /etc/localtime
#RUN dpkg-reconfigure --frontend noninteractive tzdata
#COPY ./mkphptz.sh .
#RUN ./mkphptz.sh



RUN ln -fs /usr/share/zoneinfo/America/Chicago /etc/localtime && \
    dpkg-reconfigure --frontend noninteractive tzdata && \
    /var/www/html/neeskay/mkphptz.sh
#RUN echo '[mysql]' > /root/.my.cnf && \
#    echo 'host=waterdata.glwi.uwm.edu' >> /root/.my.cnf

