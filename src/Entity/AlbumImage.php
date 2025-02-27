<?php

namespace App\Entity;

use App\Repository\AlbumImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumImageRepository::class)]
class AlbumImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'],inversedBy: 'albumImages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    #[ORM\Column(length: 255)]
    private ?string $imageURL = null;

    #[ORM\Column]
    private ?int $height = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $albumID): static
    {
        $this->album = $albumID;

        return $this;
    }

    public function getImageURL(): ?string
    {
        return $this->imageURL;
    }

    public function setImageURL(string $imageURL): static
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }
}
