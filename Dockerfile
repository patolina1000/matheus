# Dockerfile para deploy no Render
FROM php:8.1-apache

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Configurar Apache
RUN a2enmod rewrite
RUN a2enmod headers

# Copiar arquivos do projeto
COPY . /var/www/html/

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Configurar Apache para usar a porta do Render
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf
RUN echo "<VirtualHost *:\${PORT}>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Expor a porta
EXPOSE $PORT

# Comando de inicialização
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf && \
    sed -i "s/80/$PORT/g" /etc/apache2/ports.conf && \
    apache2-foreground
