<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 */
class AlbumRepository extends ServiceEntityRepository
{
    protected EntityManagerInterface $entityManager;
    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Album::class);
        $this->entityManager = $entityManager;
    }

    public function findAlbumsByFilters(array $filters = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb->innerJoin('a.genre', 'g')
            ->innerJoin('a.artist', 'ar');

        // Build the AND conditions dynamically
        $andConditions = $qb->expr()->andX();
        if (isset($filters['name'])) {
            $andConditions->add($qb->expr()->like('a.name', ':name'));
            $qb->setParameter('name', '%'.$filters['name'].'%');
        }

        if (isset($filters['genre'])) {
            $andConditions->add($qb->expr()->like('g.name', ':genreName'));
            $qb->setParameter('genreName', '%'.$filters['genre'].'%');
        }

        if (isset($filters['artist'])) {
            $andConditions->add($qb->expr()->like('ar.name', ':artistName'));
            $qb->setParameter('artistName', '%'.$filters['artist'].'%');
        }

        // Only add the and conditions if at least one parameter is provided
        if (
            isset($filters['name'])
            || isset($filters['genre'])
            || isset($filters['artist'])
            ) {
            $qb->where($andConditions);
        }

        // Add sorting if specified
        if (isset($filters['sortBy'])) {
            $sortBy = $filters['sortBy'];
            $orderDirection = strtoupper($filters['sortOrder']);
            if (!in_array($orderDirection, ['ASC', 'DESC'])) {
                $orderDirection = 'ASC';
            }
            if ($this->isValidSortField($sortBy)) {
                $queryAlias = 'a.';
                //If sorting by genre or artist restrict to using name field
                if ($sortBy == 'genre' || $sortBy == 'artist') {
                    $queryAlias = $sortBy == 'genre' ? 'g.' : 'ar.';
                    $sortBy = 'name';
                }
                $qb->orderBy($queryAlias . $sortBy, $orderDirection);
            } else {
                $qb->orderBy('a.name', 'ASC'); // Default sort by name
            }
        }


        return $qb->getQuery()->getResult();
    }

    private function isValidSortField(string $field): bool
    {
        // Add all Album entity fields that are valid for sorting here and include Genre and Artist entity fields too.
        // This is EXTREMELY important for security to prevent SQL injection!
        $allowedFields = ['name', 'songCount', 'rights', 'title', 'releaseDate', 'price', 'currency','genre', 'artist'];
        return in_array($field, $allowedFields, true);
    }
}
