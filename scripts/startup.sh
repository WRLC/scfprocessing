#!/bin/bash

# Azure Web App startup script to configure nginx for public directory

# Configure nginx to use public directory as document root
cat > /etc/nginx/sites-available/default << 'EOF'
server {
    listen 8080;
    root /home/site/wwwroot/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Restart nginx to apply the new configuration
service nginx restart