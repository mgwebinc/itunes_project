<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $songCount = null;

    #[ORM\Column(length: 1000)]
    private ?string $rights = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'm/d/Y H:i:s O'])]
    private ?\DateTime $releaseDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appleID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appleURL = null;

    /**
     * @var Collection<int, AlbumImage>
     */
    #[ORM\OneToMany(targetEntity: AlbumImage::class, mappedBy: 'album', cascade: ['persist'], orphanRemoval: true)]
    private Collection $albumImages;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 10)]
    private ?string $currency = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'albums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    #[ORM\ManyToOne(cascade: ['persist'] ,inversedBy: 'albums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Genre $genre = null;

    public function __construct()
    {
        $this->albumImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSongCount(): ?int
    {
        return $this->songCount;
    }

    public function setSongCount(int $songCount): static
    {
        $this->songCount = $songCount;

        return $this;
    }

    public function getRights(): ?string
    {
        return $this->rights;
    }

    public function setRights(string $rights): static
    {
        $this->rights = $rights;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getAppleID(): ?string
    {
        return $this->appleID;
    }

    public function setAppleID(string $appleID): static
    {
        $this->appleID = $appleID;

        return $this;
    }

    public function getAppleURL(): ?string
    {
        return $this->appleURL;
    }

    public function setAppleURL(string $appleURL): static
    {
        $this->appleURL = $appleURL;

        return $this;
    }

    /**
     * @return Collection<int, AlbumImage>
     */
    public function getAlbumImages(): Collection
    {
        return $this->albumImages;
    }

    public function addAlbumImage(AlbumImage $albumImage): static
    {
        if (!$this->albumImages->contains($albumImage)) {
            $this->albumImages->add($albumImage);
            $albumImage->setAlbum($this);
        }

        return $this;
    }

    public function removeAlbumImage(AlbumImage $albumImage): static
    {
        if ($this->albumImages->removeElement($albumImage)) {
            // set the owning side to null (unless already changed)
            if ($albumImage->getAlbum() === $this) {
                $albumImage->setAlbum(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artistID): static
    {
        $this->artist = $artistID;

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genreID): static
    {
        $this->genre = $genreID;

        return $this;
    }
}
