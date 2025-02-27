<?php

namespace App\Tests\Controller;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Genre;
use App\Repository\AlbumRepository;
use App\Service\AlbumService;
use App\Service\ExternalAlbumManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlbumControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private AlbumRepository $albumRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->albumRepository = static::getContainer()->get(AlbumRepository::class);
    }
    public function testPopulateAlbumsWithNoAlbums(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $albumRepository = $this->createMock(AlbumRepository::class);
        $externalAlbumManager = $this->createMock(ExternalAlbumManager::class);
        $albumService = $this->createMock(AlbumService::class);


        // Mock the entity manager to use the mocked repository
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($albumRepository);

        // Set the mocked services on the container
        $this->client->getContainer()->set(ExternalAlbumManager::class, $externalAlbumManager);
        $this->client->getContainer()->set(AlbumService::class, $albumService);

        // Send the POST request
        $this->client->request(Request::METHOD_POST, '/api/albums/populate');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());

        // Assert that the response contains the expected data
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['status']);
        $this->assertCount(count($responseData['data']), $responseData['data']);
    }

    public function testPopulateAlbumsRequestWithExistingAlbums(): void
    {
        // Send the POST request
        $this->client->request(Request::METHOD_POST, '/api/albums/populate');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testIndexGetRequestWithFilters(): void
    {
        // Send the GET request with filters
        $this->client->request(Request::METHOD_GET, '/api/albums', [
            'name' => 'Test Album',
            'genre' => 'Rock',
            'artist' => 'Test Artist',
            'sortBy' => 'name',
            'sortOrder' => 'desc',
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());

    }

    public function testCreateAlbum(): void
    {
        $this->client->request(Request::METHOD_POST, '/api/album',[],[],[], json_encode([
            'name' => 'Test Album',
            'songCount' => 10,
            'rights' => 'Copyright 2023',
            'title' => 'Test Title',
            'releaseDate' => '2023-10-26T12:00:00+00:00',
            'appleID' => '1234567890',
            'appleURL' => 'https://example.com/album/1234567890',
            'price' => 9.99,
            'currency' => 'USD',
            'artist' => [
                'name' => 'Test Artist',
            ],
            'genre' => [
                'name' => 'Test Genre',
                'category' => 'music',
            ],
            'albumImages' => [
                [
                    'imageURL' => 'https://example.com/image1.jpg',
                    'height' => 300
                ],
            ]
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $album = $this->albumRepository->findOneBy(['name' => 'Test Album']);
        $this->assertNotNull($album);
        $this->assertEquals('Test Album', $album->getName());
    }

    public function testUpdateAlbum(): void
    {
        $album = $this->createAlbum();

        $this->client->request(Request::METHOD_PATCH, '/api/album/' . $album->getId(),[],[],[], json_encode([
            'name' => 'Updated Album Name',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $updatedAlbum = $this->albumRepository->find($album->getId());
        $this->assertEquals('Updated Album Name', $updatedAlbum->getName());
    }

    public function testDeleteAlbum(): void
    {
        $album = $this->createAlbum();

        $this->client->request(Request::METHOD_DELETE, '/api/album/' . $album->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    private function createAlbum(): Album
    {
        $genre = new Genre();
        $genre->setName('Test Genre');
        $genre->setCategory('Rock');
        $album = new Album();
        $album->setName('Test Album');
        $album->setSongCount(10);
        $album->setRights('Copyright 2023');
        $album->setTitle('Test Title');
        $album->setReleaseDate(new \DateTime('2023-10-26T12:00:00+00:00'));
        $album->setAppleID('1234567890');
        $album->setAppleURL('https://example.com/album/1234567890');
        $album->setPrice(9.99);
        $album->setCurrency('USD');
        $album->setArtist((new Artist())->setName('Test Artist'));
        $album->setGenre($genre);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        return $album;
    }
}
