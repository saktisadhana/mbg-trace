# TLS certificates

Aiven's free MySQL **requires** a TLS connection. Download the CA certificate
from your Aiven service page (**Overview → Connection information → CA certificate**,
the `ca.pem` download) and save it here as:

```
certs/aiven-ca.pem
```

Commit it (a CA cert is public — not a secret). The Docker image copies it in, and
`render.yaml` points `MYSQL_ATTR_SSL_CA` at `/var/www/html/certs/aiven-ca.pem`,
which [config/database.php](../config/database.php) feeds to PDO.

You also use this same file for the one-time schema import:

```
mysql --ssl-ca=certs/aiven-ca.pem -h <AIVEN_HOST> -P <PORT> -u avnadmin -p \
      < db/sppg_database_lengkap.unix.sql
```
