#!/bin/sh
# Instala WordPress local con Astra y el plugin de formularios. Correr una vez, después de `docker compose up -d`.
# Usuarios de prueba, solo para este entorno local:
#   admin        / yPbXpKNmmvS0CUFD   (administrador general)
#   responsable  / yPbXpKNmmvS0CUFD   (responsable de área: solo lee y exporta sus formularios)
set -e
cd "$(dirname "$0")"
WP="docker compose run --rm cli wp"
PASS=yPbXpKNmmvS0CUFD

until docker compose exec -T db mariadb-admin ping -uroot -proot-local --silent >/dev/null 2>&1; do sleep 2; done
$WP core install --url=http://localhost:8080 --title="Capital Cultural (local)" --admin_user=admin --admin_password=$PASS --admin_email=admin@example.test --skip-email
$WP language core install es_AR --activate || true
$WP option update timezone_string America/Argentina/Buenos_Aires
$WP rewrite structure '/%postname%/'
$WP theme activate astra
$WP plugin activate cc-forms
$WP user create responsable responsable@example.test --role=ccf_responsable --user_pass=$PASS --display_name="Responsable de prueba" || true
echo "Listo: http://localhost:8080/wp-admin  ·  correos en http://localhost:8025"
