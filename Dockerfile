FROM php:7.4-apache

MAINTAINER Tom Hansen "tomh@uwm.edu"

# COPY ./.my.cnf /root/
COPY . /var/www/html/neeskay

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install mysqli

RUN echo deb http://http.us.debian.org/debian bullseye main contrib non-free >> /etc/apt/sources.list
RUN apt-get update && apt-get install -y ttf-mscorefonts-installer



###  TIMEZONE FIX  (AIN'T IT PURRDY?) ###
RUN ln -fs /usr/share/zoneinfo/America/Chicago /etc/localtime
RUN dpkg-reconfigure --frontend noninteractive tzdata
COPY ./mkphptz.sh .
RUN ./mkphptz.sh



RUN ln -fs /usr/share/zoneinfo/America/Chicago /etc/localtime
RUN dpkg-reconfigure --frontend noninteractive tzdata
RUN /var/www/html/neeskay/mkphptz.sh
RUN echo '[mysql]' > /root/.my.cnf
RUN echo 'host=waterdata.glwi.uwm.edu' >> /root/.my.cnf

