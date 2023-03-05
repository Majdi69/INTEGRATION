<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/afficherart", name="display_liste")
     */
    public function index(PaginatorInterface  $paginator,Request $request): Response
    {
        $article=$this->getDoctrine()->getManager()->getRepository(Article::class)->findAll();

        $arb=$paginator->paginate(
            $article,
            $request->query->getInt('page',1),
            2


        ) ;
        return $this->render('article/list.html.twig', [
            'b'=>$arb
        ]);
    }

    /**
     * @Route("/addarticle", name="addarticle")
     */
    public function addart(Request $request): Response
    {
        $foot = new Article();
        $form=$this->createForm(ArticleType::class,$foot);
        $form->HandleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em= $this->getDoctrine()->getManager();
            $em->persist($foot);
            $em->flush();


            $this->addFlash(
                'info',
                'Added successfully!'
            );

            return $this->redirectToRoute('display_liste');
        }
        return $this->render('article/index.html.twig',['b'=>$form->createView()]);

    }

    /**
     * @Route("/supprimerart/{id}", name="supp")
     */
    public function deleteart(Request $request) {
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();
        $delivery = $em->getRepository(Article::class)->find($id);
        $em->remove($delivery);
        $em->flush();

        $this->addFlash(
            'info',
            'Removed successfully!'
        );

        return $this->redirectToRoute('display_liste');
    }

    /**
     * @Route("/modifierarticle/{id}", name="modifierarticle")
     */
    public function modifierarticle(Request $request,$id): Response
    {
        $foot = $this->getDoctrine()->getManager()->getRepository(Article::class)->find($id);
        $form=$this->createForm(ArticleType::class,$foot);
        $form->HandleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em= $this->getDoctrine()->getManager();

            $em->flush();

            $this->addFlash(
                'info',
                'Updated successfully!'
            );

            return $this->redirectToRoute('display_liste');
        }
        return $this->render('article/update.html.twig',['b'=>$form->createView()]);

    }



    /**
     * @Route("/", name="front")
     */
    public function base(): Response
    {
        $article=$this->getDoctrine()->getManager()->getRepository(Article::class)->findAll();
        return $this->render('base.html.twig', [
            'b'=>$article
        ]);
    }




}
