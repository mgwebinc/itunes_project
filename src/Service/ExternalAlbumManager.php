<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExternalAlbumManager
{
    /**
     * Method that calls the external itunes endpoint.
     * Returns json and converts to array
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAlbums(): array
    {
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            'https://itunes.apple.com/us/rss/topalbums/limit=100/json'
        )->toArray();

        return $response['feed']['entry'];
    }
}
