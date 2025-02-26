<?php

namespace App\Helper;

use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class NormalizerHelper
{
    public static function createAlbumContextArray(): array
    {$context = [
            //Catch potential circular references
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (object $object) {
                $object->getName();
            },
            //Do not return genre/artist albums
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['albums']
        ];
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($context)
            ->withFormat('m/d/Y H:i:s T');
        return $contextBuilder->toArray();
    }

}
