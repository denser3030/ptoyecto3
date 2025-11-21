FROM ubuntu:22.04

RUN apt update && \
    apt install -y python3 python3-pip php php-cli php-curl php-xml php-mbstring php-fpm nginx && \
    apt clean

COPY . /var/www/html

# Eliminar sitio default de nginx
RUN rm /etc/nginx/sites-enabled/default

# Crear config correcta de nginx
RUN printf "server {\n\
    listen 8080;\n\
    root /var/www/html;\n\
    index index.php index.html;\n\
\n\
    location / {\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
    }\n\
\n\
    location ~ \\.php\$ {\n\
        include snippets/fastcgi-php.conf;\n\
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;\n\
    }\n\
}\n" > /etc/nginx/sites-available/default

RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

EXPOSE 8080

CMD service php8.1-fpm start && nginx -g "daemon off;"
