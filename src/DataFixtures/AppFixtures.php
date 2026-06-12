<?php

namespace App\DataFixtures;

use App\Entity\Addresses;
use App\Entity\ItemsOrder;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly ProductRepository $productRepository,
    ) {}

    public function getDependencies(): array
    {
        return [ProductFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $products  = $this->productRepository->findAll();
        $users     = $this->createUsers($manager);
        $addresses = $this->createAddresses($users, $manager);

        $manager->flush();

        $this->createOrders($products, $users, $addresses, $manager);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): array
    {
        $usersData = [
            ['admin@demo.fr',  'Alice',  'Admin',   '0600000001', 123456789, ['ROLE_ADMIN']],
            ['bob@demo.fr',    'Bob',    'Martin',  '0600000002', 234567890, []],
            ['claire@demo.fr', 'Claire', 'Dupont',  '0600000003', 345678901, []],
            ['david@demo.fr',  'David',  'Leroy',   '0600000004', 456789012, []],
            ['emma@demo.fr',   'Emma',   'Bernard', '0600000005', 567890123, []],
            ['felix@demo.fr',  'Félix',  'Moreau',  '0600000006', 678901234, []],
        ];

        $users = [];

        foreach ($usersData as [$email, $first, $last, $phone, $siren, $roles]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($first);
            $user->setLastName($last);
            $user->setPhone($phone);
            $user->setSirenNumber($siren);
            $user->setRoles($roles);
            $user->setPassword($this->hasher->hashPassword($user, 'demo1234'));
            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createAddresses(array $users, ObjectManager $manager): array
    {
        $addressesData = [
            ['12 rue de la Paix',       'Paris',     75001, 'Île-de-France',      'FR'],
            ['5 avenue des Fleurs',     'Lyon',      69001, 'Auvergne-Rhône',     'FR'],
            ['8 boulevard du Littoral', 'Marseille', 13001, 'Provence-PACA',      'FR'],
            ['22 allée des Pins',       'Bordeaux',  33000, 'Nouvelle-Aquitaine', 'FR'],
            ['3 place Graslin',         'Nantes',    44000, 'Pays de la Loire',   'FR'],
            ['17 rue du Taur',          'Toulouse',  31000, 'Occitanie',          'FR'],
        ];

        $addresses = [];

        foreach ($users as $i => $user) {
            [$street, $city, $zip, $province, $code] = $addressesData[$i % count($addressesData)];

            $address = new Addresses();
            $address->setAddress($street);
            $address->setCity($city);
            $address->setPostalCode($zip);
            $address->setProvince($province);
            $address->setCountryCode($code);
            $address->setUser($user);
            $manager->persist($address);
            $addresses[] = $address;
        }

        return $addresses;
    }

    private function createOrders(
        array $products,
        array $users,
        array $addresses,
        ObjectManager $manager
    ): void {
        $now   = new \DateTime();
        $month = (int) $now->format('n');
        $year  = (int) $now->format('Y');

        for ($ago = 11; $ago >= 1; $ago--) {
            $m = $month - $ago;
            $y = $year;
            if ($m <= 0) { $m += 12; $y -= 1; }

            $count = random_int(8 + $ago, 18 + $ago);
            for ($o = 0; $o < $count; $o++) {
                $day  = random_int(1, 28);
                $date = new \DateTime(sprintf('%d-%02d-%02d %02d:00:00', $y, $m, $day, random_int(8, 20)));
                $this->buildOrder($products, $users, $addresses, $date, 'delivered', $manager);
            }
        }

        $cutoff = (int) $now->format('j') - 7;
        if ($cutoff >= 1) {
            for ($d = 1; $d <= $cutoff; $d++) {
                $count = random_int(2, 5);
                for ($o = 0; $o < $count; $o++) {
                    $date = new \DateTime(sprintf('%d-%02d-%02d %02d:00:00', $year, $month, $d, random_int(8, 20)));
                    $this->buildOrder($products, $users, $addresses, $date, 'paid', $manager);
                }
            }
        }

        for ($i = 6; $i >= 0; $i--) {
            $base  = new \DateTime("-$i days midnight");
            $count = random_int(3, 8);
            for ($o = 0; $o < $count; $o++) {
                $date = clone $base;
                $date->modify('+' . random_int(0, 86399) . ' seconds');
                $this->buildOrder($products, $users, $addresses, $date, 'paid', $manager);
            }
        }
    }

    private function buildOrder(
        array $products,
        array $users,
        array $addresses,
        \DateTime $date,
        string $status,
        ObjectManager $manager
    ): void {
        $user    = $users[array_rand($users)];
        $address = $addresses[array_rand($addresses)];

        $order = new Order();
        $order->setStatus($status);
        $order->setCreatedAt($date);
        $order->setUser($user);
        $order->setAddresses($address);

        if (in_array($status, ['paid', 'delivered'], true)) {
            $paidAt = clone $date;
            $paidAt->modify('+' . random_int(1, 30) . ' minutes');
            $order->setPayedAt($paidAt);
        }

        if ($status === 'delivered') {
            $shippedAt = clone $date;
            $shippedAt->modify('+1 day');
            $order->setShippedAt($shippedAt);

            $receivedAt = clone $shippedAt;
            $receivedAt->modify('+' . random_int(2, 5) . ' days');
            $order->setReceivedAt($receivedAt);
        }

        $lineCount = random_int(1, 3);
        $total     = 0.0;
        $picked    = [];

        for ($l = 0; $l < $lineCount; $l++) {
            $product = $products[array_rand($products)];
            if (in_array($product, $picked, true)) {
                continue;
            }
            $picked[] = $product;

            $qty = random_int(1, 3);

            $item = new ItemsOrder();
            $item->setOrder($order);
            $item->setProduct($product);
            $item->setQuantity($qty);
            $item->setPrice($product->getPrice());
            $manager->persist($item);

            $total += $product->getPrice() * $qty;
        }

        $order->setTotalPrice(round($total, 2));
        $manager->persist($order);
    }
}
