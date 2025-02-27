<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumCreateType;
use App\Form\AlbumUpdateType;
use App\Helper\NormalizerHelper;
use App\Service\AlbumService;
use App\Service\ExternalAlbumManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    #[Route('/api/albums', name: 'albums', methods: ['GET'])]
    #[OA\Get(
        path: '/api/albums',
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
     *
     *
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */

    #[Route('/api/albums/populate', name: 'populate_albums', methods: ['POST'])]
    #[OA\Post(
        path: '/api/albums/populate',
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


    #[Route('/api/album', name: 'create_album', methods: ['POST'])]
    #[OA\Post(
        path: '/api/album',
        description: 'Creates a new album',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Album::class)
            )
        ),
        tags: ['create_album'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Returns the created album',
                content: new OA\JsonContent(
                    ref: new Model(type: Album::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Returns an error if the request body is invalid'
            ),
            new OA\Response(
                response: 401,
                description: 'Returns an error if the user is not authenticated'
            ),
            new OA\Response(
                response: 500,
                description: 'Returns an error if an internal error occurs'
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $errors = [];
        try {
            //Create a new Album and submit form data from request
            $album = new Album();
            $form = $this->createForm(AlbumCreateType::class, $album);
            $form->submit(json_decode($request->getContent(), true));

            if ($form->isValid()) {
                $entityManager->persist($album);
                $entityManager->flush();

                return $this->formAlbumsResponse($album, $errors, Response::HTTP_CREATED);
            } else {
                $errors = $this->getFormErrors($form);
                return $this->formAlbumsResponse(null, $errors, Response::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $errors= [$exception->getMessage()];
            return $this->formAlbumsResponse(null, $errors, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/album/{id}', name: 'update_album', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/album/{id}',
        description: 'Updates an existing album',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Album::class)
            )
        ),
        tags: ['update_album'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'The ID of the album to update',
                in: 'path',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the updated album',
                content: new OA\JsonContent(
                    ref: new Model(type: Album::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Returns an error if the request body is invalid'
            ),
            new OA\Response(
                response: 401,
                description: 'Returns an error if the user is not authenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Returns an error if the album is not found'
            ),
            new OA\Response(
                response: 500,
                description: 'Returns an error if an internal error occurs'
            )
        ]
    )]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $errors = [];
        try {
            //Find Album entity in db and update with form data
            $album = $entityManager->getRepository(Album::class)->find($id);
            if (null === $album) {
                $errors[] = 'Album not found.';
                return $this->formAlbumsResponse(null, $errors, Response::HTTP_NOT_FOUND);
            }

            $form = $this->createForm(AlbumUpdateType::class, $album, [
                'method' => 'PATCH',
            ]);
            //When updating DO NOT clear missing attributes from the entity
            $form->submit(json_decode($request->getContent(), true), false);

            if ($form->isValid()) {
                $entityManager->flush();

                return $this->formAlbumsResponse($album, $errors);
            } else {
                $errors[] = $this->getFormErrors($form);
                return $this->formAlbumsResponse(null, $errors, Response::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $errors[] = $exception->getMessage();
            return $this->formAlbumsResponse(null, $errors, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/album/{id}', name: 'delete_album', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/album/{id}',
        description: 'Deletes an existing album',
        tags: ['delete_album'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'The ID of the album to delete',
                in: 'path',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Returns no content if the album is deleted successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Returns an error if the user is not authenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Returns an error if the album is not found'
            ),
            new OA\Response(
                response: 500,
                description: 'Returns an error if an internal error occurs'
            )
        ]
    )]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $errors = [];
        try {
            $album = $entityManager->getRepository(Album::class)->find($id);
            if (null === $album) {
                $errors[] = 'Album not found.';
                return $this->formAlbumsResponse(null, $errors, Response::HTTP_NOT_FOUND);
            }

            $entityManager->remove($album);
            $entityManager->flush();

            return $this->formAlbumsResponse(null, $errors, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $errors[] = $exception->getMessage();
            return $this->formAlbumsResponse(null, $errors, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function formAlbumsResponse
    (
        mixed $albums,
        array $errors,
        int $statusCode = Response::HTTP_OK): JsonResponse
    {
        try {
            //Normalize the data
            $normalizedAlbums = null;
            if (null !== $albums) {
                $normalizedAlbums = $this->serializer->normalize(
                    $albums,
                    'json',
                    NormalizerHelper::createAlbumContextArray()
                );
            }
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
            ], $statusCode ?? Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            //Return success response with data
            $response = new JsonResponse([
                'data' => $normalizedAlbums ?? [],
                'status' => true,
            ], $statusCode);
        }
        return $response;
    }

    private function getFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $this->logger->error($error->getMessage());
            $errors[]= $error->getMessage();
        }
        return $errors;
    }
}
