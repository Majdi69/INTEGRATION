<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;


use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    /**
     * @Route("/afficherrec", name="rec_liste")
     */
    public function index(PaginatorInterface  $paginator,Request $request): Response
    {
        $article=$this->getDoctrine()->getManager()->getRepository(Reclamation::class)->findAll();

        $arb=$paginator->paginate(
            $article,
            $request->query->getInt('page',1),
            4


        ) ;

        return $this->render('reclamation/list.html.twig', [
            'b'=>$arb
        ]);


    }

    /**
     * @Route("/addrecl", name="addrecl")
     */
    public function addreclamation(Request $request): Response
    {
        $foot = new Reclamation();
        $form=$this->createForm(ReclamationType::class,$foot);
        $form->HandleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em= $this->getDoctrine()->getManager();
            $em->persist($foot);
            $em->flush();
            return $this->redirectToRoute('rec_liste');
        }
        return $this->render('reclamation/index.html.twig',['b'=>$form->createView()]);

    }

    /**
     * @Route("/supprimerrec/{id}", name="supprec")
     */
    public function deleterec(Request $request) {
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();
        $delivery = $em->getRepository(Reclamation::class)->find($id);
        $em->remove($delivery);
        $em->flush();
        return $this->redirectToRoute('rec_liste');
    }

    /**
     * @Route("/modifierrec/{id}", name="modifierrec")
     */
    public function modifierrec(Request $request,$id): Response
    {
        $foot = $this->getDoctrine()->getManager()->getRepository(Reclamation::class)->find($id);
        $form=$this->createForm(ReclamationType::class,$foot);
        $form->HandleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em= $this->getDoctrine()->getManager();

            $em->flush();

            return $this->redirectToRoute('rec_liste');
        }
        return $this->render('reclamation/update.html.twig',['b'=>$form->createView()]);

    }

    /**
     * @Route("/afficherrecadm", name="rec_listeadm")
     */
    public function listadm(PaginatorInterface  $paginator,Request $request): Response
    {
        $article=$this->getDoctrine()->getManager()->getRepository(Reclamation::class)->findAll();

        $arb=$paginator->paginate(
            $article,
            $request->query->getInt('page',1),
            2


        ) ;

        return $this->render('reclamation/listAdmin.html.twig', [
            'b'=>$arb
        ]);

    }

    /**
     * @Route("/frt", name="frt")
     */
    public function frt(): Response
    {
        $article=$this->getDoctrine()->getManager()->getRepository(Reclamation::class)->findAll();
        return $this->render('reclamation/frt.html.twig', [
            'b'=>$article
        ]);
    }

}
