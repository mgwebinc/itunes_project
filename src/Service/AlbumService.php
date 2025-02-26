<?php

namespace App\Service;

use App\Entity\Album;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class AlbumService
{
    protected EntityManagerInterface $entityManager;
    private Logger $logger;
    public function __construct(EntityManagerInterface $entityManager)
    {
       $this->entityManager = $entityManager;
       $this->logger = new Logger();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function createAlbumFromArray(array $itunesAlbum): void
    {
        $album = $this->entityManager->getRepository(Album::class)->findOneBy([
            'appleID' => $itunesAlbum['id']['attributes']['im:id'],
        ]);
        if (null === $album) $album = new Album();
        try {
            $album = $this->setAlbumBaseAttributes($album, $itunesAlbum);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $album = $this->setAlbumImages($album, $itunesAlbum['im:image']);
        $album = $this->setGenre($album, $itunesAlbum['category']);
        $album = $this->setArtist($album, $itunesAlbum['im:artist']);
        $this->entityManager->persist($album);
        $this->entityManager->flush();
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function setAlbumBaseAttributes(Album $album, array $itunesAlbum): Album
    {
        $album->setName($itunesAlbum['im:name']['label']);
        $album->setTitle($itunesAlbum['title']['label']);
        $album->setSongCount($itunesAlbum['im:itemCount']['label']);
        $album->setAppleID($itunesAlbum['id']['attributes']['im:id']);
        $album->setAppleURL($itunesAlbum['link']['attributes']['href']);
        $album->setRights($itunesAlbum['rights']['label']);
        $album->setPrice($itunesAlbum['im:price']['attributes']['amount']);
        $album->setCurrency($itunesAlbum['im:price']['attributes']['currency']);
        $album->setReleaseDate(new DateTime($itunesAlbum['im:releaseDate']['label']));
        return $album;
    }

    private function setAlbumImages(Album $album, array $itunesAlbumImages): Album
    {
        foreach ($itunesAlbumImages as $image) {
            $albumImage = (new AlbumImageService($this->entityManager))->createAlbumImageFromJson($album, $image);
            $album->addAlbumImage($albumImage);
        }
        return $album;
    }

    private function setGenre(Album $album, array $itunesGenre): Album
    {
        $genreService = new GenreService($this->entityManager);
        $genre = $genreService->getGenreByName($itunesGenre['attributes']['term']);
        if (empty($genre)) {
            $genre = $genreService->createGenreFromArray($itunesGenre);
        }
        $album->setGenre($genre);
        return $album;
    }

    private function setArtist(Album $album, array $itunesArtist): Album
    {
        $artistService = new ArtistService($this->entityManager);
        $artist = $artistService->getArtistByNameOrAppleID($itunesArtist);
        if (empty($artist)) {
            $artist = $artistService->createArtistFromArray($itunesArtist);
        }
        $album->setArtist($artist);
        return $album;
    }


}
