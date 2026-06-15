<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductImages;
use App\Entity\ProductTranslation;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Mobilité'    => $this->createCategoryWithTranslations('Mobilité',    'Mobility',       $manager),
            'Diagnostic'  => $this->createCategoryWithTranslations('Diagnostic',  'Diagnostic',     $manager),
            'Hygiène'     => $this->createCategoryWithTranslations('Hygiène',     'Hygiene',        $manager),
            'Rééducation' => $this->createCategoryWithTranslations('Rééducation', 'Rehabilitation', $manager),
            'Optique'     => $this->createCategoryWithTranslations('Optique',     'Optics',         $manager),
            'Confort'     => $this->createCategoryWithTranslations('Confort',     'Comfort',        $manager),
        ];

        $productsData = [

            // ── MOBILITÉ (6) ──────────────────────────────────────────────────────
            [
                'sku'         => 'MOB-001',
                'thumbnail'   => 'https://www.materielmedical.fr/49865-home_default/deambulateur-rollator-pliant-boston.jpg',
                'price'       => 189.99,
                'promo_price' => 159.99,
                'stock'       => 15,
                'priority'    => 1,
                'weight'      => 8.5,
                'height'      => 85.0,
                'length'      => 60.0,
                'categories'  => ['Mobilité'],
                'images'      => [
                    'https://www.materielmedical.fr/53877-home_default/deambulateur-rollator-londres-light.jpg',
                    'https://www.materielmedical.fr/54856-home_default/deambulateur-pliant-miami.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Déambulateur Pliable Premium',
                        'subtitle'    => 'Avec siège et panier',
                        'description' => 'Déambulateur léger et robuste avec freins de sécurité',
                        'composition' => 'Aluminium, plastique renforcé',
                    ],
                    'en' => [
                        'title'       => 'Premium Foldable Walker',
                        'subtitle'    => 'With seat and basket',
                        'description' => 'Lightweight and sturdy walker with safety brakes',
                        'composition' => 'Aluminum, reinforced plastic',
                    ],
                ],
            ],
            [
                'sku'         => 'MOB-002',
                'thumbnail'   => 'https://www.materielmedical.fr/27513-home_default/fauteuil-roulant-en-aluminium-queen-assise-46-cm-.jpg',
                'price'       => 249.99,
                'promo_price' => null,
                'stock'       => 8,
                'priority'    => 2,
                'weight'      => 12.0,
                'height'      => 90.0,
                'length'      => 65.0,
                'categories'  => ['Mobilité'],
                'images'      => [
                    'https://www.materielmedical.fr/24228-home_default/fauteuil-roulant-assie-50-cm-standard-chrome.jpg',
                    'https://www.materielmedical.fr/24206-home_default/fauteuil-roulant-extra-large-assise-55-cm.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Fauteuil Roulant Manuel Confort',
                        'subtitle'    => 'Léger et maniable',
                        'description' => 'Fauteuil roulant pliable avec accoudoirs amovibles',
                        'composition' => 'Acier chromé, coussin en mousse',
                    ],
                    'en' => [
                        'title'       => 'Comfort Manual Wheelchair',
                        'subtitle'    => 'Lightweight and maneuverable',
                        'description' => 'Foldable wheelchair with removable armrests',
                        'composition' => 'Chrome steel, foam cushion',
                    ],
                ],
            ],
            [
                'sku'         => 'MOB-003',
                'thumbnail'   => 'https://www.materielmedical.fr/51239-home_default/cannes-de-marche-alu-reglables.jpg',
                'price'       => 89.99,
                'promo_price' => 69.99,
                'stock'       => 25,
                'priority'    => 3,
                'weight'      => 1.2,
                'height'      => 85.0,
                'length'      => 25.0,
                'categories'  => ['Mobilité'],
                'images'      => [
                    'https://www.materielmedical.fr/54838-home_default/canne-de-marche-reglable-4-pop.jpg',
                    'https://www.materielmedical.fr/54857-home_default/canne-de-marche-quadripode-quadra.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Canne de Marche Ergonomique',
                        'subtitle'    => 'Poignée antidérapante',
                        'description' => 'Canne ajustable en hauteur avec embout caoutchouc',
                        'composition' => 'Aluminium anodisé, caoutchouc',
                    ],
                    'en' => [
                        'title'       => 'Ergonomic Walking Cane',
                        'subtitle'    => 'Non-slip handle',
                        'description' => 'Height-adjustable cane with rubber tip',
                        'composition' => 'Anodized aluminum, rubber',
                    ],
                ],
            ],
            [
                'sku'         => 'MOB-004',
                'thumbnail'   => 'https://www.materielmedical.fr/27479-home_default/fauteuil-roulant-gima-deluxe.jpg',
                'price'       => 349.99,
                'promo_price' => 299.99,
                'stock'       => 5,
                'priority'    => 4,
                'weight'      => 22.0,
                'height'      => 92.0,
                'length'      => 70.0,
                'categories'  => ['Mobilité'],
                'images'      => [
                    'https://www.materielmedical.fr/57677-home_default/fauteuil-de-transfert-esculape.jpg',
                    'https://www.materielmedical.fr/54423-home_default/fauteuil-de-transfert-pliant-stan-up.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Fauteuil Roulant Électrique',
                        'subtitle'    => 'Joystick ergonomique',
                        'description' => 'Fauteuil motorisé avec autonomie 20 km et chargeur intégré',
                        'composition' => 'Acier renforcé, moteur 24V, batterie lithium-ion',
                    ],
                    'en' => [
                        'title'       => 'Electric Wheelchair',
                        'subtitle'    => 'Ergonomic joystick',
                        'description' => 'Motorized wheelchair with 20 km range and built-in charger',
                        'composition' => 'Reinforced steel, 24V motor, lithium-ion battery',
                    ],
                ],
            ],
            [
                'sku'         => 'MOB-005',
                'thumbnail'   => 'https://www.materielmedical.fr/57781-home_default/bequille-axilliaire-universelles-et-extra-reglables.jpg',
                'price'       => 129.99,
                'promo_price' => null,
                'stock'       => 18,
                'priority'    => 5,
                'weight'      => 3.5,
                'height'      => 88.0,
                'length'      => 30.0,
                'categories'  => ['Mobilité'],
                'images'      => [
                    'https://www.materielmedical.fr/57729-home_default/paire-de-bequilles-herdegen-evolution.jpg',
                    'https://www.materielmedical.fr/57725-home_default/bequille-legere-en-aluminium.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Béquilles Axillaires Réglables',
                        'subtitle'    => 'Paire, rembourrage gel',
                        'description' => 'Béquilles légères en aluminium avec coussinets gel anti-escarres',
                        'composition' => 'Aluminium, mousse gel haute densité',
                    ],
                    'en' => [
                        'title'       => 'Adjustable Axillary Crutches',
                        'subtitle'    => 'Pair, gel padding',
                        'description' => 'Lightweight aluminum crutches with gel anti-pressure pads',
                        'composition' => 'Aluminum, high-density gel foam',
                    ],
                ],
            ],
            [
                'sku'         => 'MOB-006',
                'thumbnail'   => 'https://www.materielmedical.fr/54848-home_default/canne-de-marche-tripode-reglable-herdegen.jpg',
                'price'       => 59.99,
                'promo_price' => 49.99,
                'stock'       => 30,
                'priority'    => 6,
                'weight'      => 0.8,
                'height'      => 100.0,
                'length'      => 12.0,
                'categories'  => ['Mobilité'],
                'images'      => [
                    'https://www.materielmedical.fr/54857-home_default/canne-de-marche-quadripode-quadra.jpg',
                    'https://www.materielmedical.fr/54838-home_default/canne-de-marche-reglable-4-pop.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Canne Tripode Antidérapante',
                        'subtitle'    => 'Base large stabilisatrice',
                        'description' => 'Canne à trois pieds pour une stabilité maximale en intérieur',
                        'composition' => 'Aluminium anodisé, plastique ABS, caoutchouc',
                    ],
                    'en' => [
                        'title'       => 'Non-Slip Tripod Cane',
                        'subtitle'    => 'Wide stabilizing base',
                        'description' => 'Three-legged cane for maximum indoor stability',
                        'composition' => 'Anodized aluminum, ABS plastic, rubber',
                    ],
                ],
            ],

            // ── DIAGNOSTIC (7) ────────────────────────────────────────────────────
            [
                'sku'         => 'DIAG-001',
                'thumbnail'   => 'https://www.materielmedical.fr/56544-home_default/thermometre-infrarouge-gima-a200.jpg',
                'price'       => 45.99,
                'promo_price' => 39.99,
                'stock'       => 50,
                'priority'    => 1,
                'weight'      => 0.3,
                'height'      => 15.0,
                'length'      => 12.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/54415-home_default/couvre-sonde-pour-thermometre-braun.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Thermomètre Digital Infrarouge',
                        'subtitle'    => 'Sans contact',
                        'description' => 'Mesure rapide et précise de la température',
                        'composition' => 'Plastique ABS, capteur infrarouge',
                    ],
                    'en' => [
                        'title'       => 'Digital Infrared Thermometer',
                        'subtitle'    => 'Contactless',
                        'description' => 'Fast and accurate temperature measurement',
                        'composition' => 'ABS plastic, infrared sensor',
                    ],
                ],
            ],
            [
                'sku'         => 'DIAG-002',
                'thumbnail'   => 'https://www.materielmedical.fr/34014-home_default/tensiometre-electronique-au-bras-spengler-autotensio-spg-440.jpg',
                'price'       => 79.99,
                'promo_price' => null,
                'stock'       => 30,
                'priority'    => 2,
                'weight'      => 0.5,
                'height'      => 8.0,
                'length'      => 15.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/30970-home_default/tensiometre-electronique-de-poignet-omron-rs7-automatique.jpg',
                    'https://www.materielmedical.fr/38433-home_default/brassard-tensiometres-spengler-lian-nano-nm-metal.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Tensiomètre Automatique',
                        'subtitle'    => 'Brassard ajustable',
                        'description' => 'Mesure automatique de la pression artérielle',
                        'composition' => 'Plastique médical, écran LCD',
                    ],
                    'en' => [
                        'title'       => 'Automatic Blood Pressure Monitor',
                        'subtitle'    => 'Adjustable cuff',
                        'description' => 'Automatic blood pressure measurement',
                        'composition' => 'Medical plastic, LCD screen',
                    ],
                ],
            ],
            [
                'sku'         => 'DIAG-003',
                'thumbnail'   => 'https://www.materielmedical.fr/43985-home_default/stethoscope-3m-littmann-classic-iii.jpg',
                'price'       => 129.99,
                'promo_price' => 99.99,
                'stock'       => 12,
                'priority'    => 3,
                'weight'      => 1.0,
                'height'      => 10.0,
                'length'      => 20.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/53175-home_default/peluche-cache-stethoscope.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Stéthoscope Professionnel',
                        'subtitle'    => 'Double pavillon',
                        'description' => 'Stéthoscope acoustique haute qualité',
                        'composition' => 'Acier inoxydable, membrane en latex',
                    ],
                    'en' => [
                        'title'       => 'Professional Stethoscope',
                        'subtitle'    => 'Dual-head',
                        'description' => 'High-quality acoustic stethoscope',
                        'composition' => 'Stainless steel, latex membrane',
                    ],
                ],
            ],
            [
                'sku'         => 'DIAG-004',
                'thumbnail'   => 'https://www.materielmedical.fr/30673-home_default/oxymetre-de-pouls-spengler-oxystart.jpg',
                'price'       => 34.99,
                'promo_price' => 29.99,
                'stock'       => 60,
                'priority'    => 4,
                'weight'      => 0.15,
                'height'      => 6.0,
                'length'      => 10.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/30673-home_default/oxymetre-de-pouls-spengler-oxystart.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Oxymètre de Pouls Digital',
                        'subtitle'    => 'Affichage SPO2 et fréquence',
                        'description' => 'Mesure rapide de la saturation en oxygène du sang',
                        'composition' => 'Plastique médical, capteur LED infrarouge',
                    ],
                    'en' => [
                        'title'       => 'Digital Pulse Oximeter',
                        'subtitle'    => 'SPO2 and heart rate display',
                        'description' => 'Fast blood oxygen saturation measurement',
                        'composition' => 'Medical plastic, infrared LED sensor',
                    ],
                ],
            ],
            [
                'sku'         => 'DIAG-005',
                'thumbnail'   => 'https://www.materielmedical.fr/43851-home_default/electrocardiographe-ecg-kardiamobile-6l-omron.jpg',
                'price'       => 199.99,
                'promo_price' => null,
                'stock'       => 10,
                'priority'    => 5,
                'weight'      => 1.8,
                'height'      => 12.0,
                'length'      => 25.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/39272-home_default/lot-de-600-electrodes-ecg-ambu-white-sensor-ws-00-s-gel-solide-a-pression.jpg',
                    'https://www.materielmedical.fr/52590-home_default/papier-pour-ecg-edan-10-rouleaux-ou-liasses.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Électrocardiographe Portable',
                        'subtitle'    => '12 dérivations, Bluetooth',
                        'description' => 'ECG de poche avec synchronisation application mobile',
                        'composition' => 'Plastique ABS, électrodes Ag/AgCl',
                    ],
                    'en' => [
                        'title'       => 'Portable ECG Monitor',
                        'subtitle'    => '12-lead, Bluetooth',
                        'description' => 'Pocket ECG with mobile app synchronization',
                        'composition' => 'ABS plastic, Ag/AgCl electrodes',
                    ],
                ],
            ],
            [
                'sku'         => 'DIAG-006',
                'thumbnail'   => 'https://www.materielmedical.fr/55783-home_default/kit-complet-glucometre-jt-100-spengler.jpg',
                'price'       => 24.99,
                'promo_price' => 19.99,
                'stock'       => 80,
                'priority'    => 6,
                'weight'      => 0.2,
                'height'      => 4.0,
                'length'      => 9.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/55790-home_default/bandelettes-pour-glucometre-jt-100-spengler-boite-de-50.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Glucomètre Auto-Codant',
                        'subtitle'    => 'Résultat en 5 secondes',
                        'description' => 'Lecteur de glycémie compact avec mémoire 500 mesures',
                        'composition' => 'Plastique ABS médical, capteur électrochimique',
                    ],
                    'en' => [
                        'title'       => 'Auto-Coding Glucometer',
                        'subtitle'    => 'Result in 5 seconds',
                        'description' => 'Compact blood glucose meter with 500-reading memory',
                        'composition' => 'Medical ABS plastic, electrochemical sensor',
                    ],
                ],
            ],
            [
                'sku'         => 'DIAG-007',
                'thumbnail'   => 'https://www.materielmedical.fr/55055-home_default/otoscope-smartled-spengler-x-luxamed-edition-anneau-led.jpg',
                'price'       => 59.99,
                'promo_price' => 49.99,
                'stock'       => 25,
                'priority'    => 7,
                'weight'      => 0.4,
                'height'      => 7.0,
                'length'      => 14.0,
                'categories'  => ['Diagnostic'],
                'images'      => [
                    'https://www.materielmedical.fr/59544-home_default/speculums-auriculaires-jetables-spengler-boite-de-250.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Otoscope LED Professionnel',
                        'subtitle'    => 'Spéculums inclus',
                        'description' => 'Examen du conduit auditif avec éclairage LED blanc froid',
                        'composition' => 'Corps aluminium, lentille grossissante x3, LED 3W',
                    ],
                    'en' => [
                        'title'       => 'Professional LED Otoscope',
                        'subtitle'    => 'Specula included',
                        'description' => 'Ear canal examination with cold white LED lighting',
                        'composition' => 'Aluminum body, x3 magnifying lens, 3W LED',
                    ],
                ],
            ],

            // ── HYGIÈNE (7) ───────────────────────────────────────────────────────
            [
                'sku'         => 'HYG-001',
                'thumbnail'   => 'https://www.materielmedical.fr/34542-home_default/gel-hydroalcoolique-aniosgel-85-npc.jpg',
                'price'       => 29.99,
                'promo_price' => 24.99,
                'stock'       => 100,
                'priority'    => 1,
                'weight'      => 0.2,
                'height'      => 10.0,
                'length'      => 8.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/37123-home_default/savon-doux-anios-haute-frequence-ph-neutre.jpg',
                    'https://www.materielmedical.fr/57994-home_default/alcool-modifie-a-70-laboratoires-gilbert-ethylique.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Gel Hydroalcoolique 500ml',
                        'subtitle'    => 'Avec pompe distributrice',
                        'description' => 'Solution antiseptique pour les mains',
                        'composition' => 'Alcool 70%, glycérine, aloe vera',
                    ],
                    'en' => [
                        'title'       => 'Hand Sanitizer Gel 500ml',
                        'subtitle'    => 'With pump dispenser',
                        'description' => 'Antiseptic solution for hands',
                        'composition' => 'Alcohol 70%, glycerin, aloe vera',
                    ],
                ],
            ],
            [
                'sku'         => 'HYG-002',
                'thumbnail'   => 'https://www.materielmedical.fr/36591-home_default/masque-de-protection-type-2r-bleu-3-plis.jpg',
                'price'       => 15.99,
                'promo_price' => null,
                'stock'       => 200,
                'priority'    => 2,
                'weight'      => 0.05,
                'height'      => 2.0,
                'length'      => 18.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/51868-home_default/masque-3-plis-chirurgien-type-iir-bleu-fonce-avec-elastiques.jpg',
                    'https://www.materielmedical.fr/54427-home_default/masques-de-protection-type-ii-jaune-3-plis-special-irm-boite-de-50.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Masques Chirurgicaux Type II',
                        'subtitle'    => 'Boîte de 50',
                        'description' => 'Masques jetables à haute filtration',
                        'composition' => 'Non-tissé polypropylène 3 plis',
                    ],
                    'en' => [
                        'title'       => 'Type II Surgical Masks',
                        'subtitle'    => 'Box of 50',
                        'description' => 'High filtration disposable masks',
                        'composition' => 'Non-woven polypropylene 3-ply',
                    ],
                ],
            ],
            [
                'sku'         => 'HYG-003',
                'thumbnail'   => 'https://www.materielmedical.fr/43075-home_default/gants-d-examen-latex-non-steriles-non-poudres-boite-de-100.jpg',
                'price'       => 12.99,
                'promo_price' => 9.99,
                'stock'       => 150,
                'priority'    => 3,
                'weight'      => 0.1,
                'height'      => 3.0,
                'length'      => 15.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/52114-home_default/gants-latex-non-poudres-non-steriles-boite-de-100-gants.jpg',
                    'https://www.materielmedical.fr/55528-home_default/gants-dexamen-latex-non-poudres-non-steriles-avec-aloe-vera.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Gants Latex Non Poudrés',
                        'subtitle'    => 'Boîte de 100',
                        'description' => "Gants d'examen ambidextres",
                        'composition' => 'Latex naturel, sans poudre',
                    ],
                    'en' => [
                        'title'       => 'Powder-Free Latex Gloves',
                        'subtitle'    => 'Box of 100',
                        'description' => 'Ambidextrous examination gloves',
                        'composition' => 'Natural latex, powder-free',
                    ],
                ],
            ],
            [
                'sku'         => 'HYG-004',
                'thumbnail'   => 'https://www.materielmedical.fr/39132-home_default/distributeur-automatique-de-savon-ou-gel-hydroalcoolique-joleti.jpg',
                'price'       => 49.99,
                'promo_price' => 39.99,
                'stock'       => 45,
                'priority'    => 4,
                'weight'      => 2.5,
                'height'      => 35.0,
                'length'      => 28.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/43976-home_default/distributeur-pour-essuie-main-en-rouleau-maxi.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Distributeur Mural de Savon',
                        'subtitle'    => 'Capacité 1 L, sans contact',
                        'description' => 'Distributeur automatique infrarouge pour milieu médical',
                        'composition' => 'ABS résistant, capteur infrarouge, pile AA',
                    ],
                    'en' => [
                        'title'       => 'Wall-Mounted Soap Dispenser',
                        'subtitle'    => '1 L capacity, touchless',
                        'description' => 'Automatic infrared dispenser for medical environments',
                        'composition' => 'Resistant ABS, infrared sensor, AA battery',
                    ],
                ],
            ],
            [
                'sku'         => 'HYG-005',
                'thumbnail'   => 'https://www.materielmedical.fr/39934-home_default/blouses-visiteurs-non-tissees-bleues-taille-unique-sachet-de-10.jpg',
                'price'       => 8.99,
                'promo_price' => null,
                'stock'       => 500,
                'priority'    => 5,
                'weight'      => 0.03,
                'height'      => 1.5,
                'length'      => 20.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/39934-home_default/blouses-visiteurs-non-tissees-bleues-taille-unique-sachet-de-10.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Surblouses Jetables Non Tissées',
                        'subtitle'    => 'Paquet de 10',
                        'description' => 'Surblouses de protection à usage unique, taille universelle',
                        'composition' => 'Polypropylène non tissé SMS 40g/m2',
                    ],
                    'en' => [
                        'title'       => 'Disposable Non-Woven Gowns',
                        'subtitle'    => 'Pack of 10',
                        'description' => 'Single-use protective gowns, universal size',
                        'composition' => 'Non-woven polypropylene SMS 40g/m2',
                    ],
                ],
            ],
            [
                'sku'         => 'HYG-006',
                'thumbnail'   => 'https://www.materielmedical.fr/47085-home_default/charlottes-a-clip-non-tissees-blanches-sachet-de-100.jpg',
                'price'       => 22.99,
                'promo_price' => 17.99,
                'stock'       => 120,
                'priority'    => 6,
                'weight'      => 0.4,
                'height'      => 5.0,
                'length'      => 22.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/39864-home_default/sur-chaussures-sans-semelles-bleues-sachet-de-100.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Charlotte et Couvre-Chaussures',
                        'subtitle'    => 'Kit 50 paires + 50 charlottes',
                        'description' => 'Accessoires de protection jetables en plastique PE',
                        'composition' => 'Polyéthylène basse densité, élastique latex',
                    ],
                    'en' => [
                        'title'       => 'Caps and Shoe Covers Kit',
                        'subtitle'    => '50 pairs + 50 caps',
                        'description' => 'Disposable PE plastic protection accessories',
                        'composition' => 'Low-density polyethylene, latex elastic',
                    ],
                ],
            ],
            [
                'sku'         => 'HYG-007',
                'thumbnail'   => 'https://www.materielmedical.fr/43066-home_default/gants-d-examen-nitrile-nitriskin-blue-evolution-non-poudres-boite-de-100.jpg',
                'price'       => 18.99,
                'promo_price' => 14.99,
                'stock'       => 300,
                'priority'    => 7,
                'weight'      => 0.08,
                'height'      => 4.0,
                'length'      => 20.0,
                'categories'  => ['Hygiène'],
                'images'      => [
                    'https://www.materielmedical.fr/43077-home_default/doigtiers-1-doigts-latex-roules-poudres-non-steriles-sachet-de-100.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Gants Nitrile Non Poudrés',
                        'subtitle'    => 'Boîte de 100, bleus',
                        'description' => "Gants d'examen haute résistance sans latex ni poudre",
                        'composition' => 'Nitrile synthétique, sans poudre, sans latex',
                    ],
                    'en' => [
                        'title'       => 'Powder-Free Nitrile Gloves',
                        'subtitle'    => 'Box of 100, blue',
                        'description' => 'High-resistance examination gloves, latex and powder free',
                        'composition' => 'Synthetic nitrile, powder-free, latex-free',
                    ],
                ],
            ],

            // ── RÉÉDUCATION (7) ───────────────────────────────────────────────────
            [
                'sku'         => 'REED-001',
                'thumbnail'   => 'https://www.materielmedical.fr/40146-home_default/bande-adhesive-elastique-k-tape.jpg',
                'price'       => 39.99,
                'promo_price' => 34.99,
                'stock'       => 40,
                'priority'    => 1,
                'weight'      => 0.6,
                'height'      => 5.0,
                'length'      => 60.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/53345-home_default/bande-de-taping-pour-kinesiologie-3b-scientific-3btape.jpg',
                    'https://www.materielmedical.fr/54534-home_default/bande-adhesive-elastique-3m-adheban.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Bande Élastique de Résistance',
                        'subtitle'    => 'Lot de 5 niveaux',
                        'description' => 'Bandes en latex pour exercices de kinésithérapie progressive',
                        'composition' => 'Latex naturel, sans poudre',
                    ],
                    'en' => [
                        'title'       => 'Resistance Exercise Bands',
                        'subtitle'    => 'Set of 5 levels',
                        'description' => 'Latex bands for progressive physiotherapy exercises',
                        'composition' => 'Natural latex, powder-free',
                    ],
                ],
            ],
            [
                'sku'         => 'REED-002',
                'thumbnail'   => 'https://www.materielmedical.fr/32518-home_default/electrostimulateur-cefar-rehab-x2.jpg',
                'price'       => 289.99,
                'promo_price' => 249.99,
                'stock'       => 7,
                'priority'    => 2,
                'weight'      => 6.0,
                'height'      => 40.0,
                'length'      => 50.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/41411-home_default/electrodes-pour-electrostimulateurs-cefar.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Appareil TENS/EMS Professionnel',
                        'subtitle'    => '8 canaux, 150 programmes',
                        'description' => 'Électrostimulateur pour douleur chronique et renforcement musculaire',
                        'composition' => 'Boîtier ABS médical, électrodes en silicone',
                    ],
                    'en' => [
                        'title'       => 'Professional TENS/EMS Device',
                        'subtitle'    => '8 channels, 150 programs',
                        'description' => 'Electrostimulator for chronic pain and muscle strengthening',
                        'composition' => 'Medical ABS housing, silicone electrodes',
                    ],
                ],
            ],
            [
                'sku'         => 'REED-003',
                'thumbnail'   => 'https://www.materielmedical.fr/56261-home_default/orthese-poignet-et-pouce-lanaform.jpg',
                'price'       => 74.99,
                'promo_price' => null,
                'stock'       => 22,
                'priority'    => 3,
                'weight'      => 1.5,
                'height'      => 12.0,
                'length'      => 30.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/36137-home_default/echarpe-multiusage-actimove-sling.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Orthèse de Poignet Articulée',
                        'subtitle'    => 'Taille S/M/L disponibles',
                        'description' => 'Maintien rigide du poignet avec articulation réglable',
                        'composition' => 'Néoprène, armature aluminium, velcro',
                    ],
                    'en' => [
                        'title'       => 'Articulated Wrist Orthosis',
                        'subtitle'    => 'Sizes S/M/L available',
                        'description' => 'Rigid wrist support with adjustable articulation',
                        'composition' => 'Neoprene, aluminum frame, velcro',
                    ],
                ],
            ],
            [
                'sku'         => 'REED-004',
                'thumbnail'   => 'https://www.materielmedical.fr/37322-home_default/plateau-de-freeman-winelec-rond.jpg',
                'price'       => 54.99,
                'promo_price' => 44.99,
                'stock'       => 15,
                'priority'    => 4,
                'weight'      => 0.9,
                'height'      => 10.0,
                'length'      => 25.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/37317-home_default/planche-d-equilibre-winelec-rectangulaire.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Coussin de Proprioception',
                        'subtitle'    => 'Gonflage réglable',
                        'description' => 'Plateau instable en PVC pour rééducation de la cheville et du genou',
                        'composition' => 'PVC souple anti-dérapant, valve de gonflage',
                    ],
                    'en' => [
                        'title'       => 'Proprioception Balance Cushion',
                        'subtitle'    => 'Adjustable inflation',
                        'description' => 'Unstable PVC platform for ankle and knee rehabilitation',
                        'composition' => 'Non-slip soft PVC, inflation valve',
                    ],
                ],
            ],
            [
                'sku'         => 'REED-005',
                'thumbnail'   => 'https://www.materielmedical.fr/53717-home_default/pedalier-d-exercice-pliant-euromedis.jpg',
                'price'       => 119.99,
                'promo_price' => 99.99,
                'stock'       => 10,
                'priority'    => 5,
                'weight'      => 3.0,
                'height'      => 20.0,
                'length'      => 45.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/60098-home_default/pedalier-d-exercice-elliptique-cubii-go.jpg',
                    'https://www.materielmedical.fr/49906-home_default/velo-pliable-care-striale-sv-317.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Vélo Pédales Bras et Jambes',
                        'subtitle'    => 'Résistance magnétique',
                        'description' => 'Pédalier de rééducation pour membres supérieurs et inférieurs',
                        'composition' => 'Acier époxy, plastique ABS, pédalier aluminium',
                    ],
                    'en' => [
                        'title'       => 'Arm and Leg Pedal Exerciser',
                        'subtitle'    => 'Magnetic resistance',
                        'description' => 'Rehabilitation pedaler for upper and lower limbs',
                        'composition' => 'Epoxy steel, ABS plastic, aluminum crank',
                    ],
                ],
            ],
            [
                'sku'         => 'REED-006',
                'thumbnail'   => 'https://www.materielmedical.fr/49700-home_default/appareil-de-reeducation-sissel-handgrip-pour-la-main.jpg',
                'price'       => 34.99,
                'promo_price' => null,
                'stock'       => 50,
                'priority'    => 6,
                'weight'      => 0.3,
                'height'      => 3.0,
                'length'      => 20.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/32422-home_default/dynamometre-de-collin-adulte.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Balle de Rééducation Main',
                        'subtitle'    => 'Set de 3 résistances',
                        'description' => 'Balles en gel pour renforcement de la préhension et rééducation digitale',
                        'composition' => 'Gel silicone médical, enveloppe TPE',
                    ],
                    'en' => [
                        'title'       => 'Hand Rehabilitation Ball',
                        'subtitle'    => 'Set of 3 resistances',
                        'description' => 'Gel balls for grip strengthening and finger rehabilitation',
                        'composition' => 'Medical silicone gel, TPE shell',
                    ],
                ],
            ],
            [
                'sku'         => 'REED-007',
                'thumbnail'   => 'https://www.materielmedical.fr/51689-home_default/attelle-a-depression-cir-medical-bras-complet-adulte-ou-jambe-enfant.jpg',
                'price'       => 179.99,
                'promo_price' => 149.99,
                'stock'       => 6,
                'priority'    => 7,
                'weight'      => 4.5,
                'height'      => 30.0,
                'length'      => 55.0,
                'categories'  => ['Rééducation'],
                'images'      => [
                    'https://www.materielmedical.fr/51754-home_default/attelle-a-depression-cir-medical-avant-bras-adulte-ou-bras-enfant.jpg',
                    'https://www.materielmedical.fr/56441-home_default/attelle-modelable-sam-splint-1-rouleau.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Attelle Genou Articulée CPM',
                        'subtitle'    => 'Réglable 0-120 degrés',
                        'description' => 'Attelle de mobilisation passive continue du genou post-opératoire',
                        'composition' => 'Aluminium aéronautique, sangles néoprène, moteur 12V',
                    ],
                    'en' => [
                        'title'       => 'CPM Articulated Knee Brace',
                        'subtitle'    => 'Adjustable 0-120 degrees',
                        'description' => 'Continuous passive motion knee brace for post-operative use',
                        'composition' => 'Aerospace aluminum, neoprene straps, 12V motor',
                    ],
                ],
            ],

            // ── OPTIQUE (5) ───────────────────────────────────────────────────────
            [
                'sku'         => 'OPT-001',
                'thumbnail'   => 'https://www.materielmedical.fr/42825-home_default/ophtalmoscope-heine-mini-3000-led.jpg',
                'price'       => 89.99,
                'promo_price' => 74.99,
                'stock'       => 20,
                'priority'    => 1,
                'weight'      => 0.4,
                'height'      => 8.0,
                'length'      => 18.0,
                'categories'  => ['Optique'],
                'images'      => [
                    'https://www.materielmedical.fr/40200-home_default/ophtalmoscope-gima-led.jpg',
                    'https://www.materielmedical.fr/37508-home_default/ophtalmoscope-heine-mini-3000-led-avec-poignee-rechargeable.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Ophtalmoscope Direct LED',
                        'subtitle'    => 'Tête rotative, 5 ouvertures',
                        'description' => "Examen du fond d'oeil avec éclairage LED haute intensité",
                        'composition' => 'Corps en aluminium anodisé, optique en verre borosilicaté',
                    ],
                    'en' => [
                        'title'       => 'Direct LED Ophthalmoscope',
                        'subtitle'    => 'Rotating head, 5 apertures',
                        'description' => 'Fundus examination with high-intensity LED lighting',
                        'composition' => 'Anodized aluminum body, borosilicate glass optics',
                    ],
                ],
            ],
            [
                'sku'         => 'OPT-002',
                'thumbnail'   => 'https://www.materielmedical.fr/16218-home_default/lunettes-dr-frenzel-recherche-de-nystagmus.jpg',
                'price'       => 19.99,
                'promo_price' => null,
                'stock'       => 90,
                'priority'    => 2,
                'weight'      => 0.08,
                'height'      => 3.0,
                'length'      => 14.0,
                'categories'  => ['Optique'],
                'images'      => [
                    'https://www.materielmedical.fr/34060-home_default/lunettes-de-frenzel-pour-nystagmus-avec-batterie-integree.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Lunettes de Lecture Légères',
                        'subtitle'    => '+1.5 à +3.5 dioptries',
                        'description' => 'Lunettes de presbytie légères avec monture flexible',
                        'composition' => 'Monture TR90, verres polycarbonate anti-reflet',
                    ],
                    'en' => [
                        'title'       => 'Lightweight Reading Glasses',
                        'subtitle'    => '+1.5 to +3.5 diopters',
                        'description' => 'Lightweight presbyopia glasses with flexible frame',
                        'composition' => 'TR90 frame, anti-reflective polycarbonate lenses',
                    ],
                ],
            ],
            [
                'sku'         => 'OPT-003',
                'thumbnail'   => 'https://www.materielmedical.fr/57070-home_default/coffret-otoscope-et-ophtalmoscope-omni-3000-fibre-optique.jpg',
                'price'       => 149.99,
                'promo_price' => 119.99,
                'stock'       => 12,
                'priority'    => 3,
                'weight'      => 2.0,
                'height'      => 20.0,
                'length'      => 35.0,
                'categories'  => ['Optique'],
                'images'      => [
                    'https://www.materielmedical.fr/55043-home_default/ophtalmoscope-devascope-fibre-optique-led-25v.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Lampe à Fente Portable',
                        'subtitle'    => 'Grossissement x10 et x16',
                        'description' => 'Biomicroscope portable pour examen de la cornée',
                        'composition' => 'Aluminium, lentilles en verre optique, LED 5W',
                    ],
                    'en' => [
                        'title'       => 'Portable Slit Lamp',
                        'subtitle'    => 'x10 and x16 magnification',
                        'description' => 'Portable biomicroscope for corneal examination',
                        'composition' => 'Aluminum, optical glass lenses, 5W LED',
                    ],
                ],
            ],
            [
                'sku'         => 'OPT-004',
                'thumbnail'   => 'https://www.materielmedical.fr/48125-home_default/flacon-de-solution-lave-oeil.jpg',
                'price'       => 29.99,
                'promo_price' => 24.99,
                'stock'       => 60,
                'priority'    => 4,
                'weight'      => 0.05,
                'height'      => 2.0,
                'length'      => 8.0,
                'categories'  => ['Optique'],
                'images'      => [
                    'https://www.materielmedical.fr/34922-home_default/solution-saline-sterile-pour-le-kit-pour-lavage-oculaire-500-ml.jpg',
                    'https://www.materielmedical.fr/28198-home_default/kit-pour-lavage-oculaire.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Collyre Lubrifiant Yeux Secs',
                        'subtitle'    => 'Unidoses 0.4 ml, boîte 30',
                        'description' => 'Larmes artificielles sans conservateur pour yeux secs',
                        'composition' => 'Acide hyaluronique 0.2%, NaCl, eau purifiée',
                    ],
                    'en' => [
                        'title'       => 'Dry Eye Lubricating Drops',
                        'subtitle'    => '0.4 ml unit doses, box of 30',
                        'description' => 'Preservative-free artificial tears for dry eyes',
                        'composition' => 'Hyaluronic acid 0.2%, NaCl, purified water',
                    ],
                ],
            ],
            [
                'sku'         => 'OPT-005',
                'thumbnail'   => 'https://www.materielmedical.fr/42613-home_default/tonometre-de-schioetz-cadran-incline.jpg',
                'price'       => 249.99,
                'promo_price' => null,
                'stock'       => 4,
                'priority'    => 5,
                'weight'      => 5.0,
                'height'      => 30.0,
                'length'      => 40.0,
                'categories'  => ['Optique'],
                'images'      => [
                    'https://www.materielmedical.fr/42615-home_default/cache-oeil-a-trous-pour-echelle-optometrique.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Tonomètre à Aplanation',
                        'subtitle'    => 'Mesure pression intra-oculaire',
                        'description' => 'Tonomètre de Goldmann portable pour dépistage du glaucome',
                        'composition' => 'Laiton chromé, prisme en verre optique, monture aluminium',
                    ],
                    'en' => [
                        'title'       => 'Applanation Tonometer',
                        'subtitle'    => 'Intraocular pressure measure',
                        'description' => 'Portable Goldmann tonometer for glaucoma screening',
                        'composition' => 'Chrome brass, optical glass prism, aluminum mount',
                    ],
                ],
            ],

            // ── CONFORT (8) ───────────────────────────────────────────────────────
            [
                'sku'         => 'CONF-001',
                'thumbnail'   => 'https://www.materielmedical.fr/50154-home_default/fauteuil-releveur-easy-ii-2-moteurs.jpg',
                'price'       => 319.99,
                'promo_price' => 279.99,
                'stock'       => 8,
                'priority'    => 1,
                'weight'      => 18.0,
                'height'      => 110.0,
                'length'      => 80.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/50168-home_default/fauteuil-releveur-thelma.jpg',
                    'https://www.materielmedical.fr/31221-home_default/fauteuil-de-repos-electrique-vog-medical-cerisy.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Fauteuil de Repos Releveur',
                        'subtitle'    => 'Motorisé, 2 positions',
                        'description' => 'Fauteuil confort avec système releveur motorisé pour aide au lever',
                        'composition' => 'Structure acier, mousse HR35, tissu microfibre lavable',
                    ],
                    'en' => [
                        'title'       => 'Electric Lift Recliner Chair',
                        'subtitle'    => 'Motorized, 2 positions',
                        'description' => 'Comfort chair with motorized lift system for standing assistance',
                        'composition' => 'Steel frame, HR35 foam, washable microfiber fabric',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-002',
                'thumbnail'   => 'https://www.materielmedical.fr/51317-home_default/coussin-d-assise-coccygien-orthia.jpg',
                'price'       => 69.99,
                'promo_price' => 59.99,
                'stock'       => 35,
                'priority'    => 2,
                'weight'      => 1.2,
                'height'      => 8.0,
                'length'      => 60.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/51313-home_default/coussin-d-assise-prostatique-orthia.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Coussin Anti-Escarres Gel',
                        'subtitle'    => 'Cellules indépendantes',
                        'description' => 'Coussin à cellules gel pour prévention des escarres en position assise',
                        'composition' => 'Gel de silicone médical, housse polyester lavable 60°C',
                    ],
                    'en' => [
                        'title'       => 'Anti-Pressure Gel Cushion',
                        'subtitle'    => 'Independent cells',
                        'description' => 'Gel cell cushion for pressure sore prevention in seated position',
                        'composition' => 'Medical silicone gel, polyester cover washable at 60°C',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-003',
                'thumbnail'   => 'https://www.materielmedical.fr/51764-home_default/oreiller-a-memoire-de-forme-orthia-evolution.jpg',
                'price'       => 44.99,
                'promo_price' => null,
                'stock'       => 50,
                'priority'    => 3,
                'weight'      => 0.7,
                'height'      => 6.0,
                'length'      => 50.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/50131-home_default/oreiller-ergonomique-tempur-symphony-smartcool.jpg',
                    'https://www.materielmedical.fr/55522-home_default/oreiller-ergonomique-memocervical-biosynex-double-vague.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Traversin Ergonomique Cervical',
                        'subtitle'    => 'Mousse à mémoire de forme',
                        'description' => 'Oreiller orthopédique pour soulager les douleurs cervicales',
                        'composition' => 'Mousse viscoélastique CertiPUR, housse bambou',
                    ],
                    'en' => [
                        'title'       => 'Ergonomic Cervical Bolster',
                        'subtitle'    => 'Memory foam',
                        'description' => 'Orthopedic pillow to relieve cervical pain',
                        'composition' => 'CertiPUR viscoelastic foam, bamboo cover',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-004',
                'thumbnail'   => 'https://www.materielmedical.fr/48409-home_default/chauffe-matelas-thermique-modele-ub30-130-x-75-cm.jpg',
                'price'       => 89.99,
                'promo_price' => 74.99,
                'stock'       => 14,
                'priority'    => 4,
                'weight'      => 2.8,
                'height'      => 15.0,
                'length'      => 40.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/58533-home_default/coussin-chauffant-polaire-hk-126-xxl.jpg',
                    'https://www.materielmedical.fr/58194-home_default/coussin-chauffant-shk-28-sanitas.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Matelas Chauffant Thérapeutique',
                        'subtitle'    => '3 niveaux de chaleur',
                        'description' => 'Sous-couverture chauffante infrarouge pour soulager les douleurs musculaires',
                        'composition' => 'Tissu peluche polyester, résistance carbone, thermostat automatique',
                    ],
                    'en' => [
                        'title'       => 'Therapeutic Heating Mattress Pad',
                        'subtitle'    => '3 heat levels',
                        'description' => 'Infrared heating under-blanket to relieve muscle pain',
                        'composition' => 'Polyester fleece, carbon heating element, auto thermostat',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-005',
                'thumbnail'   => 'https://www.materielmedical.fr/39579-home_default/lit-medical-a-1-articulation.jpg',
                'price'       => 159.99,
                'promo_price' => 129.99,
                'stock'       => 9,
                'priority'    => 5,
                'weight'      => 5.5,
                'height'      => 45.0,
                'length'      => 70.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/48310-home_default/sur-matelas-a-air-invacare-alternating-avec-compresseur.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Lit Médicalisé Électrique',
                        'subtitle'    => 'Dossier et hauteur réglables',
                        'description' => 'Lit de confort à hauteur variable avec télécommande filaire',
                        'composition' => 'Cadre acier époxy, sommier lattes, moteurs 24V silencieux',
                    ],
                    'en' => [
                        'title'       => 'Electric Adjustable Medical Bed',
                        'subtitle'    => 'Backrest and height adjustable',
                        'description' => 'Variable height comfort bed with wired remote control',
                        'composition' => 'Epoxy steel frame, slatted base, silent 24V motors',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-006',
                'thumbnail'   => 'https://www.materielmedical.fr/49647-home_default/bande-de-compression-hartmann-extensa-plus.jpg',
                'price'       => 24.99,
                'promo_price' => 19.99,
                'stock'       => 70,
                'priority'    => 6,
                'weight'      => 0.3,
                'height'      => 5.0,
                'length'      => 30.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/54546-home_default/bande-de-compression-elastique-lr-velpeau-press.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Genouillère Compressive Neoprene',
                        'subtitle'    => 'Taille S/M/L/XL',
                        'description' => 'Maintien articulaire thermique pour douleurs du genou',
                        'composition' => 'Néoprène 3mm, tissu jersey intérieur, velcro ajustable',
                    ],
                    'en' => [
                        'title'       => 'Neoprene Compression Knee Brace',
                        'subtitle'    => 'Sizes S/M/L/XL',
                        'description' => 'Thermal joint support for knee pain relief',
                        'composition' => '3mm neoprene, inner jersey fabric, adjustable velcro',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-007',
                'thumbnail'   => 'https://www.materielmedical.fr/59504-home_default/bande-de-compression-gel-chaud-froid-lombaire.jpg',
                'price'       => 49.99,
                'promo_price' => null,
                'stock'       => 28,
                'priority'    => 7,
                'weight'      => 0.8,
                'height'      => 6.0,
                'length'      => 35.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/15676-home_default/ceinture-de-massage-chauffante-dos-et-ventre-terraillon.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Ceinture Lombaire de Soutien',
                        'subtitle'    => 'Baleines rigides amovibles',
                        'description' => 'Ceinture orthopédique pour lombalgie et hernie discale',
                        'composition' => 'Élastique médical respirant, baleines polypropylène, velcro',
                    ],
                    'en' => [
                        'title'       => 'Lumbar Support Belt',
                        'subtitle'    => 'Removable rigid stays',
                        'description' => 'Orthopedic belt for lower back pain and herniated disc',
                        'composition' => 'Breathable medical elastic, polypropylene stays, velcro',
                    ],
                ],
            ],
            [
                'sku'         => 'CONF-008',
                'thumbnail'   => 'https://www.materielmedical.fr/53978-home_default/siege-de-massage-shiatsu-beurer-mg-202.jpg',
                'price'       => 199.99,
                'promo_price' => 169.99,
                'stock'       => 11,
                'priority'    => 8,
                'weight'      => 7.0,
                'height'      => 50.0,
                'length'      => 60.0,
                'categories'  => ['Confort'],
                'images'      => [
                    'https://www.materielmedical.fr/58155-home_default/appareil-de-massage-a-infrarouge-beurer-mg-40.jpg',
                    'https://www.materielmedical.fr/55207-home_default/pistolet-de-massage-beurer-mg-79-sensitive.jpg',
                ],
                'translations' => [
                    'fr' => [
                        'title'       => 'Bain de Pieds Massant Shiatsu',
                        'subtitle'    => 'Chaleur, bulles et vibrations',
                        'description' => 'Bain de pieds thérapeutique avec galets de massage rotatifs',
                        'composition' => 'Plastique ABS alimentaire, résistance chauffante, moteur 40W',
                    ],
                    'en' => [
                        'title'       => 'Shiatsu Foot Massage Bath',
                        'subtitle'    => 'Heat, bubbles and vibrations',
                        'description' => 'Therapeutic foot bath with rotating massage nodes',
                        'composition' => 'Food-grade ABS plastic, heating element, 40W motor',
                    ],
                ],
            ],
        ];

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

            foreach ($data['images'] ?? [] as $imageUrl) {
                $img = new ProductImages();
                $img->setImageUrl($imageUrl);
                $product->addProductImage($img);
                $manager->persist($img);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function createCategoryWithTranslations(string $nameFr, string $nameEn, ObjectManager $manager): ProductCategory
    {
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
}
