<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\CarouselSlide;
use Doctrine\ORM\QueryBuilder;

/**
 * Restreint la collection publique des slides du carrousel aux slides actifs.
 * Ne s'applique qu'aux opérations API Platform (EasyAdmin garde la visibilité
 * complète via ses propres requêtes).
 */
final class CarouselSlideExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (CarouselSlide::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.isActive = :carousel_active', $rootAlias))
            ->setParameter('carousel_active', true);
    }
}
