FROM php:8.4-fpm-bookworm

# Install system packages and PHP extensions to match Azure environment
RUN apt-get update && apt-get install -y \
  git \
  telnet \
  nginx \
  supervisor \
  libzip-dev \
  && docker-php-ext-install mysqli pdo pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

# Configure nginx to use public directory as document root
RUN echo 'server { \
    listen 8080; \
    root /home/site/wwwroot/public; \
    index index.php index.html index.htm; \
    \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    \
    location ~ /\.ht { \
        deny all; \
    } \
}' > /etc/nginx/sites-available/default

# Set the working directory
WORKDIR /home/site/wwwroot

# Copy application files
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /home/site/wwwroot

# Configure supervisor to run nginx and php-fpm
RUN echo '[supervisord]\n\
nodaemon=true\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autorestart=true\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Expose port 8080 (Azure Web Apps use port 8080, not 80)
EXPOSE 8080

# Start supervisor
CMD ["/usr/bin/supervisord"]