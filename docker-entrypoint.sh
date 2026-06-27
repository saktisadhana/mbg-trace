#!/bin/sh
set -e

# Render injects PORT (default 10000); Fly/local default to 80.
# Bind Apache to whatever port the platform expects.
: "${PORT:=80}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
