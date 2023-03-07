<?php

namespace App\Controller;

use App\Form\EditProfilType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(): Response
    {
        return $this->render('users/index.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }

    #[Route('/users/profil/modifier', name: 'users_profil_modifier')]
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
            return $this->redirectToRoute('app_users');
        }
        return $this->render('users/editprofil.html.twig',[
           'form' => $form->createView(),
        ]);

    }
    #[Route('/users/pass/modifier', name: 'users_pass_modifier')]
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

                return $this->redirectToRoute('app_users');
            }
            else
            {
                $this->addFlash('error','les deux mot de passe ne sont pas identiques');
            }
        }
        return $this->render('users/editpass.html.twig');

    }

}
