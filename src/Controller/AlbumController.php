<?php

namespace App\Controller;

use App\Entity\Album;
use App\Helper\NormalizerHelper;
use App\Service\AlbumService;
use App\Service\ExternalAlbumManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use OpenApi\Attributes as OA;

final class AlbumController extends AbstractController
{
    private array $encoders;
    private Logger $logger;
    private array $normalizers;
    private Serializer $serializer;
    public function __construct()
    {
        $this->encoders = [new JsonEncoder()];
        $this->logger = new Logger();
        $this->normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $this->serializer  = new Serializer($this->normalizers, $this->encoders);
    }

    /**
     * Lists Albums
     *
     * @param EntityManagerInterface $entityManager
     * @param string|null $name
     * @param string|null $genre
     * @param string|null $artist
     * @param string|null $sortBy
     * @param string|null $sortOrder
     * @return JsonResponse
     */
    #[Route('/albums', name: 'albums', methods: ['GET'])]
    #[OA\Get(
        path: '/albums',
        description: 'Lists all albums, allows for filtering via Name, Artist, or Genre and sorting',
        tags: [
            'get_albums',
        ],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: 'The name of the album.',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'genre',
                description: 'The genre of the album.',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'artist',
                description: 'The artist of the album.',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sortBy',
                description: 'The sort field of the album. Accepted values are: name, songCount, rights, title,
                releaseDate,price, currency, genre, and artist',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sortOrder',
                description: 'The sort field of the album. Accepted values are: asc, desc',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses:
        [
            new OA\Response(
                response: 200,
                description: 'Returns a list of Albums.',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Album::class))
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Returns an error with error bag',
            )
        ]
    )]
    public function index
    (
        EntityManagerInterface $entityManager,
        #[MapQueryParameter] ?string $name,
        #[MapQueryParameter] ?string $genre,
        #[MapQueryParameter] ?string $artist,
        #[MapQueryParameter] ?string $sortBy,
        #[MapQueryParameter] ?string $sortOrder,
    ): JsonResponse
    {
        $errors = [];
        // GET request allows for filtering and sorting
        $filters = [];
        if ($name) $filters['name'] = $name;
        if ($genre) $filters['genre'] = $genre;
        if ($artist) $filters['artist'] = $artist;
        if ($sortBy) {
            $filters['sortBy'] = $sortBy;
            $filters['sortOrder'] = $sortOrder ?: 'asc';
        }
        $albums = $entityManager->getRepository(Album::class)->findAlbumsByFilters($filters);
        //return the albums w/o filters & sort
        return $this->formAlbumsResponse($albums, $errors);
    }

    /**
     * Post API that populates the db from an external iTunes feed
     * uses:
     *
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */

    #[Route('/albums/populate', name: 'populate_albums', methods: ['POST'])]
    #[OA\Post(
        path: '/albums/populate',
        tags: [
            'populate_albums'
        ],
        responses:
        [
            new OA\Response(
                response: 200,
                description: 'Returns a list of Albums.',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Album::class))
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Returns an error with error bag',
            )
        ],
    )]
    public function populateAlbums(EntityManagerInterface $entityManager): JsonResponse
    {
        //Populate albums only fetches the albums from the external endpoint the first time.
        //With more time, I would add logic to change the top albums every time and sort them. Rather than just pulling
        //From the database once
        $errors = [];
        //Retrieve and save albums from external endpoint
        $album = $entityManager->getRepository(Album::class)->find(1);
        if (null === $album) {
            //No albums exist in the db, call to external endpoint to retrieve
            try {
                $itunesAlbums = (new ExternalAlbumManager)->getAlbums();
                foreach ($itunesAlbums as $itunesAlbum) {
                    try {
                        //Save each album to the db
                        (new AlbumService($entityManager))->createAlbumFromArray($itunesAlbum);
                    } catch (\Throwable $exception) {
                        //If exception is thrown, log it and continue saving the other albums
                        $this->logger->error($exception->getMessage());
                    }
                }
            } catch (\Throwable $exception) {
                //Exception was thrown retrieving data from external endpoint
                $this->logger->error($exception->getMessage());
                $errors[] = $exception->getMessage();
            }
        }
        //POST will always return all albums
        $albums = $entityManager->getRepository(Album::class)->findAll();
        return $this->formAlbumsResponse($albums, $errors);
    }

    private function formAlbumsResponse(array $albums, array $errors): JsonResponse
    {
        try {
            //Normalize the data
            $normalizedAlbums = $this->serializer->normalize
            (
                $albums,
                'json',
                NormalizerHelper::createAlbumContextArray()
            );
        } catch (\Throwable $exception) {
            //An error occurred normalizing the data
            $this->logger->error($exception->getMessage());
            $errors[] = $exception->getMessage();
        }
        if (count($errors) > 0) {
            //TODO: add more verbose error handling with different response codes not just 500.
            //There were errors, return fail response with the errorMessageBag
            $response = new JsonResponse([
                'data' => [],
                'status' => false,
                'errors' => $errors
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            //Return success response with data
            $response = new JsonResponse([
                'data' => $normalizedAlbums ?? [],
                'status' => true,
            ]);
        }
        return $response;
    }
}
