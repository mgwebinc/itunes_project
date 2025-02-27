<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\AlbumImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AlbumCreateType extends AbstractType
{
    private EntityManagerInterface $entityManager;
    private array $normalizers;
    private Serializer $serializer;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($this->normalizers);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('songCount')
            ->add('rights')
            ->add('title')
            ->add('releaseDate')
            ->add('appleID')
            ->add('appleURL')
            ->add('price')
            ->add('currency')
            ->add('artist', ArtistType::class)
            ->add('genre', GenreType::class)
            ->add('albumImages', CollectionType::class, [
                'entry_type' => AlbumImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ]);

        $builder->get('albumImages')
            ->addModelTransformer(new CallbackTransformer(
                function ($albumImages) {
                    // Transform Collection of AlbumImage to array of IDs or null
                    return $albumImages->map(function ($albumImage) {
                        return $albumImage->getId();
                    })->toArray();
                },
                function ($albumImageData) {
                    $albumImages = new ArrayCollection();
                    if (is_array($albumImageData)) {
                        foreach ($albumImageData as $image) {
                            $image = $this->serializer->normalize($image, AlbumImage::class);
                            if (isset($image['id'])) {
                                $albumImage = $this->entityManager->getRepository(AlbumImage::class)->find($image['id']);
                                if (!$albumImage) {
                                    // Handle the case where the AlbumImage with the given ID is not found
                                    continue;
                                }
                            } else {
                                $albumImage = new AlbumImage();
                                if (isset($image['imageURL'])) {
                                    $albumImage->setImageURL($image['imageURL']);
                                }
                                if (isset($image['height'])) {
                                    $albumImage->setHeight($image['height']);
                                }
                            }
                            $albumImages->add($albumImage);
                        }
                    }
                    return $albumImages;
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
            'csrf_protection' => false,
        ]);
    }

}
