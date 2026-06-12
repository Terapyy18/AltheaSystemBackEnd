<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Addresses;
use App\Entity\ItemsOrder;
use App\Entity\Order;
use App\Entity\Support;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Filtre automatiquement les collections API Platform pour que chaque utilisateur
 * ne voie que ses propres ressources. Les admins voient tout.
 */
final class CurrentUserExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly Security $security) {}

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $user = $this->security->getUser();

        // Pas connecté ou admin : pas de filtre (les security: des opérations gèrent le reste)
        if (!$user || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        match ($resourceClass) {
            Order::class, Addresses::class, Support::class => $queryBuilder
                ->andWhere("$rootAlias.user = :current_user")
                ->setParameter('current_user', $user),

            // Un user ne se voit que lui-même dans la collection /users
            User::class => $queryBuilder
                ->andWhere("$rootAlias = :current_user")
                ->setParameter('current_user', $user),

            // ItemsOrder n'a pas de user direct : on passe par la relation order
            ItemsOrder::class => $queryBuilder
                ->join("$rootAlias.order", 'o_cu_filter')
                ->andWhere('o_cu_filter.user = :current_user')
                ->setParameter('current_user', $user),

            default => null,
        };
    }
}
