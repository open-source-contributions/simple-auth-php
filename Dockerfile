# Install required packages
FROM ubuntu:16.04
RUN apt-get update
RUN apt-get install -y software-properties-common
RUN apt-get install -y python-software-properties
RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
RUN apt-get update
RUN apt-get install -y apt-transport-https apt-utils curl php7.2-cli php7.2-mysql php7.2-curl php7.2-zip php7.2-mbstring
RUN apt-get install -y php7.2-xml php7.2-dom php7.2-xsl php7.2-json php7.2-fpm php7.2-gd nginx
RUN apt-get install -y libcurl3-openssl-dev vim

RUN mv /etc/localtime /etc/localtime.old
RUN ln -s /usr/share/zoneinfo/Asia/Taipei /etc/localtime

RUN apt-get update && apt-get install -y locales wget
RUN locale-gen "en_US.UTF-8"
RUN echo 'LC_ALL="en_US.UTF-8"' > /etc/default/locale

# Install Simple Auth API service
WORKDIR /var/www/html
COPY ./nginx-default.conf /etc/nginx/sites-available/default
COPY . ./
COPY ./.env.example ./.env
RUN curl -sS https://getcomposer.org/installer | php
RUN php composer.phar install -n

EXPOSE 5000
CMD ["bash", "-c", "service php7.2-fpm start && nginx -g 'daemon off;'"]
