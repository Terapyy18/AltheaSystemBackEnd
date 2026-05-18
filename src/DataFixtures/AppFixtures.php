<?php

namespace App\DataFixtures;

use App\Entity\Addresses;
use App\Entity\ItemsOrder;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use App\Entity\ProductTranslation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    //  Poids de popularité (same order as $productsData below)
    // ─────────────────────────────────────────────────────────────────
    private const WEIGHTS = [5, 3, 8, 9, 6, 4, 7, 10, 8];

    public function load(ObjectManager $manager): void
    {
        // 1. Catégories
        $categories = [
            'Mobilité'    => $this->createCategoryWithTranslations('Mobilité',    'Mobility',    $manager),
            'Diagnostic'  => $this->createCategoryWithTranslations('Diagnostic',  'Diagnostic',  $manager),
            'Hygiène'     => $this->createCategoryWithTranslations('Hygiène',     'Hygiene',     $manager),
        ];

        // 2. Produits
        $products = $this->createProducts($categories, $manager);

        // 3. Utilisateurs + adresses
        $users     = $this->createUsers($manager);
        $addresses = $this->createAddresses($users, $manager);

        $manager->flush(); // IDs nécessaires avant les commandes

        // 4. Commandes (12 mois d'historique + 7 derniers jours denses)
        $this->createOrders($products, $users, $addresses, $manager);

        $manager->flush();
    }

    // ─────────────────────────────────────────────────────────────────
    //  Catégories
    // ─────────────────────────────────────────────────────────────────
    private function createCategoryWithTranslations(
        string $nameFr,
        string $nameEn,
        ObjectManager $manager
    ): ProductCategory {
        $category = new ProductCategory();
        $category->setStatus(true);
        $manager->persist($category);

        $translationFr = new ProductCategoryTranslation();
        $translationFr->setLanguage('fr');
        $translationFr->setTitle($nameFr);
        $translationFr->setDescription('Catégorie ' . $nameFr);
        $translationFr->setProductCategory($category);
        $manager->persist($translationFr);

        $translationEn = new ProductCategoryTranslation();
        $translationEn->setLanguage('en');
        $translationEn->setTitle($nameEn);
        $translationEn->setDescription($nameEn . ' category');
        $translationEn->setProductCategory($category);
        $manager->persist($translationEn);

        return $category;
    }

    // ─────────────────────────────────────────────────────────────────
    //  Produits (repris de ProductFixtures, style identique)
    // ─────────────────────────────────────────────────────────────────
    private function createProducts(array $categories, ObjectManager $manager): array
    {
        $productsData = [
            [
                'sku' => 'MOB-001',
                'thumbnail' => 'https://images.unsplash.com/photo-1587224251899-0c0c49f5283c?w=400',
                'price' => 189.99,
                'promo_price' => 159.99,
                'stock' => 15,
                'priority' => 1,
                'weight' => 8.5,
                'height' => 85.0,
                'length' => 60.0,
                'categories' => ['Mobilité'],
                'translations' => [
                    'fr' => [
                        'title' => 'Déambulateur Pliable Premium',
                        'subtitle' => 'Avec siège et panier',
                        'description' => 'Déambulateur léger et robuste avec freins de sécurité',
                        'composition' => 'Aluminium, plastique renforcé',
                    ],
                    'en' => [
                        'title' => 'Premium Foldable Walker',
                        'subtitle' => 'With seat and basket',
                        'description' => 'Lightweight and sturdy walker with safety brakes',
                        'composition' => 'Aluminum, reinforced plastic',
                    ],
                ],
            ],
            [
                'sku' => 'MOB-002',
                'thumbnail' => 'https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?w=400',
                'price' => 249.99,
                'promo_price' => null,
                'stock' => 8,
                'priority' => 2,
                'weight' => 12.0,
                'height' => 90.0,
                'length' => 65.0,
                'categories' => ['Mobilité'],
                'translations' => [
                    'fr' => [
                        'title' => 'Fauteuil Roulant Manuel Confort',
                        'subtitle' => 'Léger et maniable',
                        'description' => 'Fauteuil roulant pliable avec accoudoirs amovibles',
                        'composition' => 'Acier chromé, coussin en mousse',
                    ],
                    'en' => [
                        'title' => 'Comfort Manual Wheelchair',
                        'subtitle' => 'Lightweight and maneuverable',
                        'description' => 'Foldable wheelchair with removable armrests',
                        'composition' => 'Chrome steel, foam cushion',
                    ],
                ],
            ],
            [
                'sku' => 'MOB-003',
                'thumbnail' => 'https://images.unsplash.com/photo-1631679706909-1844bbd07221?w=400',
                'price' => 89.99,
                'promo_price' => 69.99,
                'stock' => 25,
                'priority' => 3,
                'weight' => 1.2,
                'height' => 85.0,
                'length' => 25.0,
                'categories' => ['Mobilité'],
                'translations' => [
                    'fr' => [
                        'title' => 'Canne de Marche Ergonomique',
                        'subtitle' => 'Poignée antidérapante',
                        'description' => 'Canne ajustable en hauteur avec embout caoutchouc',
                        'composition' => 'Aluminium anodisé, caoutchouc',
                    ],
                    'en' => [
                        'title' => 'Ergonomic Walking Cane',
                        'subtitle' => 'Non-slip handle',
                        'description' => 'Height-adjustable cane with rubber tip',
                        'composition' => 'Anodized aluminum, rubber',
                    ],
                ],
            ],
            [
                'sku' => 'DIAG-001',
                'thumbnail' => 'https://images.unsplash.com/photo-1584982751601-97dcc096659c?w=400',
                'price' => 45.99,
                'promo_price' => 39.99,
                'stock' => 50,
                'priority' => 1,
                'weight' => 0.3,
                'height' => 15.0,
                'length' => 12.0,
                'categories' => ['Diagnostic'],
                'translations' => [
                    'fr' => [
                        'title' => 'Thermomètre Digital Infrarouge',
                        'subtitle' => 'Sans contact',
                        'description' => 'Mesure rapide et précise de la température',
                        'composition' => 'Plastique ABS, capteur infrarouge',
                    ],
                    'en' => [
                        'title' => 'Digital Infrared Thermometer',
                        'subtitle' => 'Contactless',
                        'description' => 'Fast and accurate temperature measurement',
                        'composition' => 'ABS plastic, infrared sensor',
                    ],
                ],
            ],
            [
                'sku' => 'DIAG-002',
                'thumbnail' => 'https://images.unsplash.com/photo-1615486511484-92e172cc4fe0?w=400',
                'price' => 79.99,
                'promo_price' => null,
                'stock' => 30,
                'priority' => 2,
                'weight' => 0.5,
                'height' => 8.0,
                'length' => 15.0,
                'categories' => ['Diagnostic'],
                'translations' => [
                    'fr' => [
                        'title' => 'Tensiomètre Automatique',
                        'subtitle' => 'Brassard ajustable',
                        'description' => 'Mesure automatique de la pression artérielle',
                        'composition' => 'Plastique médical, écran LCD',
                    ],
                    'en' => [
                        'title' => 'Automatic Blood Pressure Monitor',
                        'subtitle' => 'Adjustable cuff',
                        'description' => 'Automatic blood pressure measurement',
                        'composition' => 'Medical plastic, LCD screen',
                    ],
                ],
            ],
            [
                'sku' => 'DIAG-003',
                'thumbnail' => 'https://images.unsplash.com/photo-1603398938378-e54eab446dde?w=400',
                'price' => 129.99,
                'promo_price' => 99.99,
                'stock' => 12,
                'priority' => 3,
                'weight' => 1.0,
                'height' => 10.0,
                'length' => 20.0,
                'categories' => ['Diagnostic'],
                'translations' => [
                    'fr' => [
                        'title' => 'Stéthoscope Professionnel',
                        'subtitle' => 'Double pavillon',
                        'description' => 'Stéthoscope acoustique haute qualité',
                        'composition' => 'Acier inoxydable, membrane en latex',
                    ],
                    'en' => [
                        'title' => 'Professional Stethoscope',
                        'subtitle' => 'Dual-head',
                        'description' => 'High-quality acoustic stethoscope',
                        'composition' => 'Stainless steel, latex membrane',
                    ],
                ],
            ],
            [
                'sku' => 'HYG-001',
                'thumbnail' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=400',
                'price' => 29.99,
                'promo_price' => 24.99,
                'stock' => 100,
                'priority' => 1,
                'weight' => 0.2,
                'height' => 10.0,
                'length' => 8.0,
                'categories' => ['Hygiène'],
                'translations' => [
                    'fr' => [
                        'title' => 'Gel Hydroalcoolique 500ml',
                        'subtitle' => 'Avec pompe distributrice',
                        'description' => 'Solution antiseptique pour les mains',
                        'composition' => 'Alcool 70%, glycérine, aloe vera',
                    ],
                    'en' => [
                        'title' => 'Hand Sanitizer Gel 500ml',
                        'subtitle' => 'With pump dispenser',
                        'description' => 'Antiseptic solution for hands',
                        'composition' => 'Alcohol 70%, glycerin, aloe vera',
                    ],
                ],
            ],
            [
                'sku' => 'HYG-002',
                'thumbnail' => 'https://images.unsplash.com/photo-1584308972272-9e4e7685e80f?w=400',
                'price' => 15.99,
                'promo_price' => null,
                'stock' => 200,
                'priority' => 2,
                'weight' => 0.05,
                'height' => 2.0,
                'length' => 18.0,
                'categories' => ['Hygiène'],
                'translations' => [
                    'fr' => [
                        'title' => 'Masques Chirurgicaux Type II',
                        'subtitle' => 'Boîte de 50',
                        'description' => 'Masques jetables à haute filtration',
                        'composition' => 'Non-tissé polypropylène 3 plis',
                    ],
                    'en' => [
                        'title' => 'Type II Surgical Masks',
                        'subtitle' => 'Box of 50',
                        'description' => 'High filtration disposable masks',
                        'composition' => 'Non-woven polypropylene 3-ply',
                    ],
                ],
            ],
            [
                'sku' => 'HYG-003',
                'thumbnail' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400',
                'price' => 12.99,
                'promo_price' => 9.99,
                'stock' => 150,
                'priority' => 3,
                'weight' => 0.1,
                'height' => 3.0,
                'length' => 15.0,
                'categories' => ['Hygiène'],
                'translations' => [
                    'fr' => [
                        'title' => 'Gants Latex Non Poudrés',
                        'subtitle' => 'Boîte de 100',
                        'description' => 'Gants d\'examen ambidextres',
                        'composition' => 'Latex naturel, sans poudre',
                    ],
                    'en' => [
                        'title' => 'Powder-Free Latex Gloves',
                        'subtitle' => 'Box of 100',
                        'description' => 'Ambidextrous examination gloves',
                        'composition' => 'Natural latex, powder-free',
                    ],
                ],
            ],
        ];

        $products = [];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setSku($data['sku']);
            $product->setThumbnail($data['thumbnail']);
            $product->setPrice($data['price']);
            $product->setPromoPrice($data['promo_price']);
            $product->setStock($data['stock']);
            $product->setPriority($data['priority']);
            $product->setWeight($data['weight']);
            $product->setHeight($data['height']);
            $product->setLength($data['length']);
            $product->setIsPublished(true);
            $product->setCreateAt(new \DateTimeImmutable());

            foreach ($data['categories'] as $catName) {
                $product->addProductCategory($categories[$catName]);
            }

            foreach ($data['translations'] as $lang => $translation) {
                $productTranslation = new ProductTranslation();
                $productTranslation->setLanguage($lang);
                $productTranslation->setTitle($translation['title']);
                $productTranslation->setSubtitle($translation['subtitle']);
                $productTranslation->setDescription($translation['description']);
                $productTranslation->setComposition($translation['composition']);
                $productTranslation->setProduct($product);
                $manager->persist($productTranslation);
            }

            $manager->persist($product);
            $products[] = $product;
        }

        return $products;
    }

    // ─────────────────────────────────────────────────────────────────
    //  Utilisateurs
    // ─────────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────
    //  Adresses
    // ─────────────────────────────────────────────────────────────────
    private function createAddresses(array $users, ObjectManager $manager): array
    {
        $addressesData = [
            ['12 rue de la Paix',       'Paris',     75001, 'Île-de-France',     'FR'],
            ['5 avenue des Fleurs',     'Lyon',      69001, 'Auvergne-Rhône',    'FR'],
            ['8 boulevard du Littoral', 'Marseille', 13001, 'Provence-PACA',     'FR'],
            ['22 allée des Pins',       'Bordeaux',  33000, 'Nouvelle-Aquitaine','FR'],
            ['3 place Graslin',         'Nantes',    44000, 'Pays de la Loire',  'FR'],
            ['17 rue du Taur',          'Toulouse',  31000, 'Occitanie',         'FR'],
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

    // ─────────────────────────────────────────────────────────────────
    //  Commandes : 12 mois d'historique + 7 derniers jours denses
    // ─────────────────────────────────────────────────────────────────
    private function createOrders(
        array $products,
        array $users,
        array $addresses,
        ObjectManager $manager
    ): void {
        $now   = new \DateTime();
        $month = (int) $now->format('n');
        $year  = (int) $now->format('Y');

        // Historique 11 mois glissants (volume croissant vers le présent)
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

        // Mois courant (jours antérieurs aux 7 derniers)
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

        // 7 derniers jours : 3-8 commandes/jour
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

    // ─────────────────────────────────────────────────────────────────
    //  Construction d'une commande + ses lignes ItemsOrder
    // ─────────────────────────────────────────────────────────────────
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
            $product = $this->weightedPick($products);
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

    // ─────────────────────────────────────────────────────────────────
    //  Tirage pondéré (simule des best-sellers naturels)
    // ─────────────────────────────────────────────────────────────────
    private function weightedPick(array $products): Product
    {
        $totalWeight = array_sum(self::WEIGHTS);
        $rand        = random_int(1, $totalWeight);
        $cumul       = 0;

        foreach ($products as $i => $product) {
            $cumul += self::WEIGHTS[$i] ?? 1;
            if ($rand <= $cumul) {
                return $product;
            }
        }

        return $products[0];
    }
}