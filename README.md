# iTunes Project

A project to showcase my abilities in back-end engineering!


## Getting Started
1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Using the API!
1. Read the API DOCS!!! - The documentation for the api is hosted on `https://localhost/api/doc`
2. (if for some reason there are no docs -- they should already exist!) Run `docker ps` and find the `app-php` container id
3. Run `docker exec -it containerid bash`
4. Run `php bin/console nelmio:apidoc:dump`

## Running PHPUnit Tests (The exciting part)
1. Run `docker ps` and find the `app-php` container id
2. Run `docker exec -it containerid bash`
3. Run `php bin/phpunit tests/Controller/AlbumControllerTest`

## Running the API
1. Call the POST `/api/albums/populate` endpoint to populate albums from iTunes library.
2. Try the other endpoints in the documentation

