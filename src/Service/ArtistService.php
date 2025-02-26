<?php

namespace App\Service;

use App\Entity\Artist;
use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class ArtistService
{
    private $entityManager;
    private Logger $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = new Logger();
    }

    public function getArtistByNameOrAppleID(array $artist): ?Artist
    {
        $appleArtistID = array_key_exists('attributes',$artist)
            ? UrlHelper::extractAppleMusicID($artist['attributes']['href'])
            : 0;
        return $this->entityManager->getRepository(Artist::class)
            ->findOneByNameOrAppleID($artist['label'], $appleArtistID);
    }

    public function createArtistFromArray(array $itunesArtist): Artist
    {
        $artist = new Artist();
        $artist->setName($itunesArtist['label']);
        $appleArtistID = array_key_exists('attributes',$itunesArtist)
            ? UrlHelper::extractAppleMusicID($itunesArtist['attributes']['href'])
            : 0;
        if ($appleArtistID !== 0) $artist->setAppleID($appleArtistID);
        if (array_key_exists('attributes',$itunesArtist))
            $artist->setAppleURL($itunesArtist['attributes']['href']);
        $this->entityManager->persist($artist);
        return $artist;
    }
}
