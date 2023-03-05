<?php

namespace App\Controller;


use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\AnnonceRepository;
use App\Repository\CommentaireRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire', name: 'app_commentaire')]
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'CommentaireController',
        ]);
    }

    #[Route('/addcommForm/{idAnnonce}', name: 'addcommForm')]
    public function addcommForm(Request $request, ManagerRegistry $doctrine, $idAnnonce, AnnonceRepository $annonceRepository)
    {
        $comm = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $comm);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $annonce = $annonceRepository->find($idAnnonce);
            $comm->setAnnonce($annonce);
            $comm->setDate(new \DateTime('now'));
            $userId=$request->get('userid');
            var_dump($userId);
            die();
            //$com->setUserid($userId);
            $em->persist($comm);
            $em->flush();
            return $this->redirectToRoute('comm_affichage', array("idAnnonce" => $idAnnonce));
        }
        return $this->render("commentaire/add.html.twig", ['f' => $form->createView(), "idAnnonce" => $idAnnonce]);
    }

    #[Route('/updatecomm/{id}', name: 'comm_upd')]
    public function updateForm($id, CommentaireRepository $commentaireRepository, Request $request, ManagerRegistry $doctrine)
    {
        $com = $commentaireRepository->find($id);
        $form = $this->createForm(CommentaireType::class, $com);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $doctrine->getManager();
            $idAnnonce = $com->getAnnonce()->getId();
            $em->flush();
            return $this->redirectToRoute('comm_affichage', array("idAnnonce" => $idAnnonce));
        }
        return $this->render("commentaire/update.html.twig", ['f' => $form->createView()]);
    }

    #[Route('/removecommm/{id}', name: 'comm_remove')]
    public function remove(ManagerRegistry $doctrine, $id, CommentaireRepository $repository)
    {
        $com = $repository->find($id);
        $em = $doctrine->getManager();
        $idAnnonce = $com->getAnnonce()->getId();
        $em->remove($com);
        $em->flush();
        return $this->redirectToRoute('comm_affichage', array("idAnnonce" => $idAnnonce));
    }

    #[Route('/commlist/{idAnnonce}', name: 'comm_affichage')]
    public function list(CommentaireRepository $repository, $idAnnonce)
    {
        $com = $repository->findBy(array('idAnnonce' => $idAnnonce), array('id' => 'DESC'));
        return $this->render("commentaire/show.html.twig", array("f" => $com, "idAnnonce" => $idAnnonce));
    }

    ////Back
    #[Route('/removscommback/{id}', name: 'remove_commback')]
    public function removeback(ManagerRegistry $doctrine,$id,CommentaireRepository $repository)
    {
        $com= $repository->find($id);
        $em= $doctrine->getManager();
        $idAnnonce = $com->getAnnonce()->getId();
        $em->remove($com);
        $em->flush();
        return $this->redirectToRoute("affcomback", array("idAnnonce" => $idAnnonce));
    }

    #[Route('/comments', name: 'affcomback')]
    public function listcommback(CommentaireRepository $repository)
    {   $com=$repository->findAll();
        return $this->render("admin/commentaireback.html.twig",array("Comm"=>$com));
    }
}
