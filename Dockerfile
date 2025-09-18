# Dockerfile para deploy no Render
FROM php:8.1-apache

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Configurar Apache
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires
RUN a2enmod deflate
RUN a2enmod ssl

# Copiar arquivos do projeto
COPY . /var/www/html/

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Criar diretórios necessários
RUN mkdir -p /var/www/html/logs /var/www/html/data /var/www/html/docs
RUN chmod 755 /var/www/html/logs /var/www/html/data

# Configurar Apache para usar a porta do Render
RUN echo "Listen 80" > /etc/apache2/ports.conf
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.html index.php index.htm\n\
    </Directory>\n\
    # Configurar tipos MIME para arquivos estáticos\n\
    <Directory /var/www/html/links>\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.html index.php index.htm\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Expor a porta 80 (o Render mapeará para $PORT)
EXPOSE 80

# Script de inicialização
RUN echo '#!/bin/bash\n\
# Configurar porta do Render (se definida)\n\
if [ ! -z "$PORT" ]; then\n\
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf\n\
    sed -i "s/*:80/*:$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
fi\n\
# Iniciar Apache\n\
apache2-foreground' > /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

# Comando de inicialização
CMD ["/usr/local/bin/start.sh"]
