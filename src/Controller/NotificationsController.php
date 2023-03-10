<?php

namespace App\Controller;

use App\Entity\Notifications;
use App\Form\NotificationsType;
use App\Repository\NotificationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotificationsController extends AbstractController
{
    #[Route('/', name: 'app_notifications_index', methods: ['GET'])]
    public function index(NotificationsRepository $notificationsRepository): Response
    {
        return $this->render('notifications/index.html.twig', [
            'notifications' => $notificationsRepository->findAll(),
        ]);
    }
    #[Route('/user', name: 'app_notifications_user', methods: ['GET'])]
    public function user(NotificationsRepository $notificationsRepository): Response
    {
        return $this->render('notifications/user.html.twig', [
            'notifications' => $notificationsRepository->findAll(),
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/new', name: 'app_notifications_new', methods: ['GET', 'POST'])]
    public function new(Request $request, NotificationsRepository $notificationsRepository): Response
    {

        $notification = new Notifications();
        $form = $this->createForm(NotificationsType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notificationsRepository->save($notification, true);

            return $this->redirectToRoute('app_notifications_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('notifications/new.html.twig', [
            'notification' => $notification,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_notifications_show', methods: ['GET'])]
    public function show(Notifications $notification): Response
    {
        return $this->render('notifications/show.html.twig', [
            'notification' => $notification,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_notifications_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Notifications $notification, NotificationsRepository $notificationsRepository): Response
    {
        $new = new Notifications();
        $form = $this->createForm(NotificationsType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notificationsRepository->save($notification, true);
            if ($new) {
                $message = "a été ajouté avec succes";

            }

            return $this->redirectToRoute('app_notifications_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('notifications/edit.html.twig', [
            'notification' => $notification,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_notifications_delete', methods: ['POST'])]
    public function delete(Request $request, Notifications $notification, NotificationsRepository $notificationsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$notification->getId(), $request->request->get('_token'))) {
            $notificationsRepository->remove($notification, true);
        }

        return $this->redirectToRoute('app_notifications_index', [], Response::HTTP_SEE_OTHER);
    }
}
