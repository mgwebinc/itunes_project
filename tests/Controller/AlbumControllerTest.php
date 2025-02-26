<?php

namespace App\Tests\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use App\Service\AlbumService;
use App\Service\ExternalAlbumManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AlbumControllerTest extends WebTestCase
{
    public function testPopulateAlbumsWithNoAlbums(): void
    {
        $client = static::createClient();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $albumRepository = $this->createMock(AlbumRepository::class);
        $externalAlbumManager = $this->createMock(ExternalAlbumManager::class);
        $albumService = $this->createMock(AlbumService::class);


        // Mock the entity manager to use the mocked repository
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($albumRepository);

        // Set the mocked services on the container
        $client->getContainer()->set(ExternalAlbumManager::class, $externalAlbumManager);
        $client->getContainer()->set(AlbumService::class, $albumService);

        // Send the POST request
        $client->request(Request::METHOD_POST, '/albums/populate');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

        // Assert that the response contains the expected data
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['status']);
        $this->assertCount(count($responseData['data']), $responseData['data']);
    }

    public function testPopulateAlbumsRequestWithExistingAlbums(): void
    {
        $client = static::createClient();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $albumRepository = $this->createMock(AlbumRepository::class);

        // Mock the repository to return an existing album
        $albumRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(new Album()); // Return an Album object

        // Mock the entity manager to use the mocked repository
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($albumRepository);

        // Set the mocked service on the container
        $client->getContainer()->set(EntityManagerInterface::class, $entityManager);

        // Send the POST request
        $client->request(Request::METHOD_POST, '/albums/populate');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testIndexGetRequestWithFilters(): void
    {
        $client = static::createClient();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $albumRepository = $this->createMock(AlbumRepository::class);

        // Mock the repository's findAlbumsByFilters method
        $albumRepository->expects($this->once())
            ->method('findAlbumsByFilters')
            ->with([
                'name' => 'Test Album',
                'genre' => 'Rock',
                'artist' => 'Test Artist',
                'sortBy' => 'name',
                'sortOrder' => 'desc',
            ])
            ->willReturn([
                new Album(),
            ]);

        // Mock the entity manager to use the mocked repository
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($albumRepository);

        // Set the mocked service on the container
        $client->getContainer()->set(EntityManagerInterface::class, $entityManager);

        // Send the GET request with filters
        $client->request(Request::METHOD_GET, '/albums', [
            'name' => 'Test Album',
            'genre' => 'Rock',
            'artist' => 'Test Artist',
            'sortBy' => 'name',
            'sortOrder' => 'desc',
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

    }
}
