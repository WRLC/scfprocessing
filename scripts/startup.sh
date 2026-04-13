#!/bin/bash
set -euo pipefail

cat > /etc/nginx/sites-available/default << 'EOF'
server {
    listen 8080 default_server;
    listen [::]:8080 default_server;

    root /home/site/wwwroot/public;
    index index.php index.html index.htm;

    server_name _;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

echo "startup.sh applied custom nginx config"
cat /etc/nginx/sites-available/default
