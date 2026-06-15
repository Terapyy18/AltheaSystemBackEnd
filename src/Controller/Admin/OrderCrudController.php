<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class OrderCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly OrderService $orderService,
    ) {
    }

    /**
     * Détecte le passage manuel du statut vers « shipped » ou « received » lors de
     * l'édition d'une commande dans EasyAdmin : on horodate l'évènement (si ce n'est
     * pas déjà fait) et on déclenche l'email correspondant (expédiée / livrée).
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $justShipped = false;
        $justDelivered = false;

        if ($entityInstance instanceof Order) {
            $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
            $previousStatus = $originalData['status'] ?? null;
            $newStatus = $entityInstance->getStatus();

            if ($newStatus === 'shipped' && $previousStatus !== 'shipped') {
                $justShipped = true;
                if ($entityInstance->getShippedAt() === null) {
                    $entityInstance->setShippedAt(new \DateTime());
                }
            } elseif ($newStatus === 'received' && $previousStatus !== 'received') {
                $justDelivered = true;
                if ($entityInstance->getReceivedAt() === null) {
                    $entityInstance->setReceivedAt(new \DateTime());
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);

        if ($entityInstance instanceof Order) {
            if ($justShipped) {
                $this->orderService->sendShippedEmail($entityInstance);
            } elseif ($justDelivered) {
                $this->orderService->sendDeliveredEmail($entityInstance);
            }
        }
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des Commandes')
            ->setPageTitle('edit', 'Modifier la Commande')
            ->setPageTitle('detail', 'Détail de la Commande');
    }

    public function configureActions(Actions $actions): Actions
    {
        $router = $this->router;

        $downloadInvoice = Action::new('downloadInvoice', 'Télécharger la facture', 'fa fa-file-pdf')
            ->linkToUrl(function (Order $order) use ($router): string {
                return $router->generate('admin_order_invoice_download', ['id' => $order->getId()]);
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->addCssClass('btn btn-sm btn-success')
            ->displayIf(static fn (Order $order): bool => $order->getInvoicePath() !== null);

        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $downloadInvoice)
            ->add(Crud::PAGE_INDEX, $downloadInvoice);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('user', 'Client'),
            AssociationField::new('addresses', 'Adresse de livraison'),

            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente'        => 'pending',
                    'Payée'             => 'paid',
                    'Expédiée'          => 'shipped',
                    'Livrée'            => 'received',
                    'Annulée'           => 'cancelled',
                    'Remboursée'        => 'refunded',
                    'Échec de paiement' => 'payment_failed',
                    'En vérification'   => 'suspicious',
                ])
                ->renderAsBadges([
                    'pending'        => 'warning',
                    'paid'           => 'info',
                    'shipped'        => 'primary',
                    'received'       => 'success',
                    'cancelled'      => 'danger',
                    'refunded'       => 'danger',
                    'payment_failed' => 'danger',
                    'suspicious'     => 'warning',
                ]),

            NumberField::new('totalPrice', 'Montant Total (€)')
                ->setNumDecimals(2)
                ->setFormTypeOption('disabled', true),

            IntegerField::new('shippingNumber', 'N° Suivi Colis'),

            TextField::new('invoiceNumber', 'N° Facture')
                ->setFormTypeOption('disabled', true)
                ->hideOnIndex(),

            TextField::new('invoicePath', 'Chemin Facture (PDF)')
                ->hideOnIndex()
                ->hideOnForm(),

            DateTimeField::new('createdAt', 'Créée le')
                ->hideOnForm(),
            DateTimeField::new('payedAt', 'Payée le')
                ->onlyOnDetail(),
            DateTimeField::new('shippedAt', 'Expédiée le')
                ->onlyOnDetail(),
            DateTimeField::new('receivedAt', 'Reçue le')
                ->onlyOnDetail(),

            CollectionField::new('itemsOrders', 'Produits commandés')
                ->useEntryCrudForm(ItemsOrderCrudController::class)
                ->onlyOnDetail(),
        ];
    }
}
