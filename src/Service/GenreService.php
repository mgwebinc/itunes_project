<?php

namespace App\Service;

use App\Entity\Genre;
use Doctrine\ORM\EntityManagerInterface;

class GenreService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function getGenreByName(String $genreName): ?Genre
    {
        return $this->entityManager->getRepository(Genre::class)->findOneBy(['name' => $genreName]);
    }

    public function createGenreFromArray(array $itunesGenre): Genre
    {
        $genre = new Genre();
        $genre->setName($itunesGenre['attributes']['term']);
        $genre->setAppleID($itunesGenre['attributes']['im:id']);
        $genre->setAppleURL($itunesGenre['attributes']['scheme']);
        $genre->setCategory($itunesGenre['attributes']['label']);
        $this->entityManager->persist($genre);
        return $genre;
    }
}
