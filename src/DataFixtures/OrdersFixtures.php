<?php

namespace App\DataFixtures;

use App\Entity\Addresses;
use App\Entity\ItemsOrder;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private ProductRepository $productRepo,
        private UserRepository $userRepo,
    ) {}

    public function getDependencies(): array
    {
        return [ProductFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $products = $this->productRepo->findAll();
        if (empty($products)) {
            return;
        }

        // Créer un user de test si aucun n'existe
        $user = $this->userRepo->findOneBy([]);
        if (!$user) {
            $user = new User();
            $user->setEmail('test@althea.com');
            $user->setPassword('dummy');
            $manager->persist($user);
        }

        // Créer une adresse de test
        $address = new Addresses();
        $address->setStreet('12 rue de la Santé');
        $address->setCity('Paris');
        $address->setZipCode('75013');
        $address->setCountry('France');
        $address->setUser($user);
        $manager->persist($address);

        // Générer des commandes sur les 12 derniers mois
        $statuses = ['paid', 'shipped', 'delivered'];

        for ($i = 0; $i < 80; $i++) {
            // Date aléatoire dans les 12 derniers mois
            $daysAgo   = random_int(0, 365);
            $createdAt = new \DateTime("-$daysAgo days");

            $order = new Order();
            $order->setStatus($statuses[array_rand($statuses)]);
            $order->setCreatedAt($createdAt);
            $order->setUser($user);
            $order->setAddresses($address);

            // Ajouter 1 à 4 produits par commande
            $total        = 0.0;
            $itemCount    = random_int(1, 4);
            $pickedIndexes = array_rand($products, min($itemCount, count($products)));
            if (!is_array($pickedIndexes)) {
                $pickedIndexes = [$pickedIndexes];
            }

            foreach ($pickedIndexes as $idx) {
                $product  = $products[$idx];
                $quantity = random_int(1, 3);
                $price    = $product->getPromoPrice() ?? $product->getPrice();

                $item = new ItemsOrder();
                $item->setProduct($product);
                $item->setQuantity($quantity);
                $item->setPrice($price);
                $item->setOrder($order);

                $total += $price * $quantity;
                $manager->persist($item);
            }

            $order->setTotalPrice(round($total, 2));
            $manager->persist($order);
        }

        $manager->flush();
    }
}