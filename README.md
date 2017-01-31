# demos.tf api

Backend code for [demos.tf](https://demos.tf)

## WIP

This repo is still somewhat wip and might not be in the state yet where it can deployed outside of the existing demos.tf environment.

## Database

The api requires a PostgreSQL database to function, the database schema required can be found in `demos_schema.sql`.

Note that the `pg_trgrm` extension in required.

## Docker image

A prebuild docker image exists in the [docker hub](https://hub.docker.com/r/demostf/api/) which contains nginx, php and this code.

A seperate PostgreSQL database is required to run the image, the database details need to be configured with the following environment variables:

- DB_TYPE=pgsql
- DB_HOST=$database_host
- DB_DATABASE=$database_name
- DB_USERNAME=$database_user
- DB_PASSWORD=$database_password

## Installing

To install the project composer is required.

```
composer install
```

## Deploying

Deploying the api requires php5.6 or later, the webserver needs to be configured to server all requests to public/index.php

The database details need to be configured with the same environment variables as are described for the docker image above. The environment variables can also be be put in a `.env` file in the root of the project.
