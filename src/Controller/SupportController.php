<?php

namespace App\Controller;

use App\Entity\Support;
use App\Repository\SupportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/admin/tickets', name: 'admin_tickets_')]
class SupportController extends AbstractController
{
    public function __construct(
        private SupportRepository $supportRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $ticketsOuverts = $this->supportRepository->findBy(
            ['status' => 'ouvert'],
            ['id' => 'DESC']
        );
        $ticketsEnCours = $this->supportRepository->findBy(
            ['status' => 'en_cours'],
            ['id' => 'DESC']
        );
        $ticketsTraites = $this->supportRepository->findBy(
            ['status' => ['resolu', 'ferme']],
            ['id' => 'DESC']
        );

        return $this->render('admin/tickets/index.html.twig', [
            'ticketsOuverts'  => $ticketsOuverts,
            'ticketsEnCours'  => $ticketsEnCours,
            'ticketsTraites'  => $ticketsTraites,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Support $support, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $reply  = $request->request->get('reply');
            $status = $request->request->get('status');

            if ($reply) {
                $support->setReply($reply);
            }
            if ($status) {
                $support->setStatus($status);
            }

            $this->em->flush();

            // Envoi du mail si l'user a un email
            if ($reply && $support->getUser() && $support->getUser()->getEmail()) {
                $this->sendReplyEmail($support, $reply);
            }

            $this->addFlash('success', 'Réponse envoyée et mail expédié au client.');

            return $this->redirectToRoute('admin_tickets_show', ['id' => $support->getId()]);
        }

        return $this->render('admin/tickets/show.html.twig', [
            'ticket' => $support,
        ]);
    }

    private function sendReplyEmail(Support $support, string $reply): void
    {
        $userEmail = $support->getUser()->getEmail();

        $email = (new Email())
            // ON UTILISE L'ADRESSE GMAIL EN DUR POUR ETRE SUR
            ->from('contact.altheasysteme@gmail.com') 
            ->to($userEmail)
            ->subject('Réponse à votre ticket #' . $support->getId())
            ->html($this->renderView('emails/support_reply.html.twig', [
                'ticket'   => $support,
                'reply'    => $reply,
                'userName' => $support->getUser()->getFirstName() ?? 'Client',
            ]));

        $this->mailer->send($email);
    }
}