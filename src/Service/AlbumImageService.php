<?php

namespace App\Service;

use App\Entity\Album;
use App\Entity\AlbumImage;
use Doctrine\ORM\EntityManagerInterface;

class AlbumImageService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createAlbumImageFromJson(Album $album, array $image): AlbumImage
    {
        $albumImage = new AlbumImage();
        $albumImage->setImageURL($image['label']);
        $albumImage->setHeight($image['attributes']['height']);
        $albumImage->setAlbum($album);
        $this->entityManager->persist($albumImage);
        return $albumImage;
    }
}
