FROM ubuntu:22.04

RUN apt update && \
    apt install -y python3 python3-pip php php-cli php-curl php-xml php-mbstring php-fpm nginx && \
    apt clean

COPY . /var/www/html

RUN rm /etc/nginx/sites-enabled/default
RUN echo "server {
    listen 8080;
    root /var/www/html;
    index index.php index.html;
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}" > /etc/nginx/sites-available/default

RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

EXPOSE 8080

CMD service php8.1-fpm start && nginx -g "daemon off;"
