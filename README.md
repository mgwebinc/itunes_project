# iTunes Project

A project to showcase my abilities in back-end engineering!

## Background Information
1. This project is using blazing fast `frankenphp` which has native support for symfony and laravel!
2. Uses MariaDB because of it's superior query speed and scalability compared to MySQL
3. Uses the latest lts version of `Symfony` which is highly modular in its architecture (use only what you need!)

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

## AI LLM Prompt 
Given the album.title and album.name look for the designation from itunes (E for explicit) in the album.name and or album.title. Then given the album.album_images from the database, use OCR to find the Parental Advisory Explicit Content text like the one in this image: https://en.wikipedia.org/wiki/Parental_Advisory#/media/File:Parental_Advisory_label.svg. If either is found, update the database column album.nsfw and set it to true.

