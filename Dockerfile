FROM php:7.4-apache

# 필요한 PHP 확장 기능 설치
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache 설정 (필요 시)
COPY ./custom-apache.conf /etc/apache2/sites-available/000-default.conf
# RUN a2enmod rewrite

# 웹 애플리케이션 코드를 컨테이너로 복사
COPY ./web /var/www/html/

# uploads 디렉토리 권한 설정
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 755 /var/www/html/uploads
