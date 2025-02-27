<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\AlbumImage;
use App\Entity\Artist;
use App\Entity\Genre;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Sodium\add;

class AlbumUpdateType extends AbstractType
{
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
            'csrf_protection' => false,
        ]);
    }

}
