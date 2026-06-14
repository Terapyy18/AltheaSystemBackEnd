<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $data) {
            $user = new User();

            $user->setEmail($data['email']);
            $user->setFirstName($data['first_name']);
            $user->setLastName($data['last_name']);
            $user->setPhone($data['phone']);
            $user->setSirenNumber($data['siren_number']);
            $user->setRoles($data['roles']);
            // Comptes de démo : déjà vérifiés pour ne pas bloquer la connexion.
            $user->setIsVerified(true);

            $hashedPassword = $this->hasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function getData(): array
    {
        return [
            // ── Admins ─────────────────────────────────────────────────────
            [
                'email'        => 'theodumontet.pro@gmail.com',
                'first_name'   => 'Theo',
                'last_name'    => 'Dumontet',
                'phone'        => '0600000000',
                'siren_number' => 0,
                'password'     => 'demo1234',
                'roles'        => ['ROLE_ADMIN'],
            ],
            [
                'email'        => 'admin@example.com',
                'first_name'   => 'admin',
                'last_name'    => 'System',
                'phone'        => '0600000001',
                'siren_number' => 123456789,
                'password'     => 'admin',
                'roles'        => ['ROLE_ADMIN'],
            ],

            // ── Users ──────────────────────────────────────────────────────
            [
                'email'        => 'alice.martin@example.com',
                'first_name'   => 'Alice',
                'last_name'    => 'Martin',
                'phone'        => '0611111111',
                'siren_number' => 111111111,
                'password'     => 'User1234!',
                'roles'        => ['ROLE_USER'],
            ],
            [
                'email'        => 'bob.dupont@example.com',
                'first_name'   => 'Bob',
                'last_name'    => 'Dupont',
                'phone'        => '0622222222',
                'siren_number' => 222222222,
                'password'     => 'User1234!',
                'roles'        => ['ROLE_USER'],
            ],
            [
                'email'        => 'charlie.leroy@example.com',
                'first_name'   => 'Charlie',
                'last_name'    => 'Leroy',
                'phone'        => '0633333333',
                'siren_number' => 333333333,
                'password'     => 'User1234!',
                'roles'        => ['ROLE_USER'],
            ],
        ];
    }
}