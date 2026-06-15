<?php

namespace App\DataFixtures;

use App\Entity\Addresses;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function getDependencies(): array
    {
        return [ProductFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->createUsers($manager);
        $this->createAddresses($users, $manager);

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
}
