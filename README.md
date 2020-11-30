# demos.tf api

![Build Status](https://github.com/demostf/api/workflows/CI/badge.svg)

Backend code for [demos.tf](https://demos.tf)

## Database

The api requires a PostgreSQL database to function, the database schema required can be found in [demostf/db](https://github.com/demostf/api)/`demos_schema.sql`.

Note that the `pg_trgrm` extension in required.

## Docker image

A prebuild docker image exists in the [docker hub](https://hub.docker.com/r/demostf/api/) which contains nginx, php and this code.

A seperate PostgreSQL database is required to run the image, the database details need to be configured with the following environment variables:

- DB_TYPE=pgsql
- DB_HOST=$database_host
- DB_DATABASE=$database_name
- DB_USERNAME=$database_user
- DB_PASSWORD=$database_password
- BASE_HOST=$host // the domain the frontend site will be running on
- PARSER_URL=$parser_host // the full url for the demo parser's upload endpoint
- DEMO_ROOT=/demos // the folder uploaded demos will be stored in
- DEMO_HOST=static.$HOST // the hostname from which the uploaded demos will be served
- APP_ROOT=api.$HOST // the domain the api will be running on

## Installing

To install the project composer is required.

```
composer install
```

## Deploying

Deploying the api requires php7.1 or later, 
the webserver needs to be configured to server all requests to `public/index.php` execept
for request to `/upload` which needs to be handled by `public/upload.php`.

The database details need to be configured with the same environment variables as are described for the docker image above.

More information for hosting and a pre-configured docker based setup can be found at [demostf/setup](https://github.com/demostf/setup)
