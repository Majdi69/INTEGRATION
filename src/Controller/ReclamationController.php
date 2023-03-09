<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Reclamation;
use App\Form\ArticlesType;
use App\Form\ReclamationType;
use App\Repository\ArticlesRepository;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\AnnonceRepository;
use App\Repository\CommentaireRepository;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;



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
            $foot->setDate(new \DateTime());
            $em= $this->getDoctrine()->getManager();
            $em->persist($foot);
            $em->flush();
            return $this->redirectToRoute('art_aff');
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
        return $this->redirectToRoute('rec_listeadm');
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




//    #[Route('/addcommForm/{idAnnonce}', name: 'addcommForm')]
//    public function addcommForm(Request $request, ManagerRegistry $doctrine, $idAnnonce, ArticlesRepository $annonceRepository)
//    {
//        $comm = new Articles();
//        $form = $this->createForm(ArticlesType::class, $comm);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $doctrine->getManager();
//            $annonce = $annonceRepository->find($idAnnonce);
//            $comm->setArticles($annonce);
//
//            die();
//            //$com->setUserid($userId);
//            $em->persist($comm);
//            $em->flush();
//            return $this->redirectToRoute('comm_affichage', array("idAnnonce" => $idAnnonce));
//        }
//        return $this->render("reclamation/add.html.twig", ['f' => $form->createView(), "idAnnonce" => $idAnnonce]);
//    }
//
//
//
//
//
//    #[Route('/commlist/{idAnnonce}', name: 'art_affichage')]
//    public function list(ReclamationRepository $repository, $idAnnonce)
//    {
//        $com = $repository->findBy(array('idAnnonce' => $idAnnonce), array('id' => 'DESC'));
//        return $this->render("reclamation/show.html.twig", array("f" => $com, "idAnnonce" => $idAnnonce));
//    }












}
