<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\EditProfilType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/profil/modifier', name: 'admin_profil_modifier')]
    public function editProfil(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfilType::class, $this->getUser());
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em= $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message','Profil mise à jour');
            return $this->redirectToRoute('app_admin');
        }
        return $this->render('admin/editprofil.html.twig',[
            'form' => $form->createView(),
        ]);

    }

    #[Route('/admin/pass/modifier', name: 'admin_pass_modifier')]
    public function editPass(Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {

        if($request->isMethod('POST'))
        {
            $em= $this->getDoctrine()->getManager();
            $user = $this->getUser();
            if($request->request->get('pass') == $request->request->get('pass2'))
            {
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass2')));
                $em->flush();
                $this->addFlash('message','Mot de passe mise à jours avec succès');

                return $this->redirectToRoute('app_admin');
            }
            else
            {
                $this->addFlash('error','les deux mot de passe ne sont pas identiques');
            }
        }
        return $this->render('admin/editpass.html.twig');

    }

    #[Route('/admin/listusers', name: 'listusers')]
    public function listUsers(UsersRepository $repository)
    {
        $users = $this->getDoctrine()->getRepository(Users::class)->findAll();

        return $this->render('admin/list.html.twig', [
            'users' => $users,]);
    }

    #[Route('/admin/listusers/{id}/remove', name: 'removeusers')]
    public function removeUser(Users $user): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User account has been removed successfully.');

        return $this->redirectToRoute("listusers");
    }
}
