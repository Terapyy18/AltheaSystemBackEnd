<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des catégories avec traductions
        $categories = [
            'Mobilité' => $this->createCategoryWithTranslations('Mobilité', 'Mobility', $manager),
            'Diagnostic' => $this->createCategoryWithTranslations('Diagnostic', 'Diagnostic', $manager),
            'Hygiène' => $this->createCategoryWithTranslations('Hygiène', 'Hygiene', $manager),
        ];

        // Produits avec traductions FR et EN
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
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
                    ]
                ]
            ],
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setSku($data['sku']);
            $product->setThumbnail($data['thumbnail']);
            $product->setPrice($data['price']);
            $product->setPromoPrice($data['promo_price']); // null est maintenant accepté
            $product->setStock($data['stock']);
            $product->setPriority($data['priority']);
            $product->setWeight($data['weight']);
            $product->setHeight($data['height']);
            $product->setLength($data['length']);
            $product->setIsPublished(true);
            $product->setCreateAt(new \DateTime());

            // Ajouter les catégories
            foreach ($data['categories'] as $catName) {
                $product->addProductCategory($categories[$catName]);
            }

            // Créer les traductions
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
        }

        $manager->flush();
    }

    /**
     * Crée une catégorie avec ses traductions FR et EN
     */
    private function createCategoryWithTranslations(string $nameFr, string $nameEn, ObjectManager $manager): ProductCategory
    {
        $category = new ProductCategory();
        $category->setStatus(true); // ✅ Status obligatoire
        
        $manager->persist($category);
        
        // Traduction FR
        $translationFr = new ProductCategoryTranslation();
        $translationFr->setLanguage('fr');
        $translationFr->setTitle($nameFr); // ✅ Utilise title
        $translationFr->setDescription('Catégorie ' . $nameFr); // ✅ Description obligatoire
        $translationFr->setProductCategory($category);
        $manager->persist($translationFr);
        
        // Traduction EN
        $translationEn = new ProductCategoryTranslation();
        $translationEn->setLanguage('en');
        $translationEn->setTitle($nameEn); // ✅ Utilise title
        $translationEn->setDescription($nameEn . ' category'); // ✅ Description obligatoire
        $translationEn->setProductCategory($category);
        $manager->persist($translationEn);
        
        return $category;
    }
}