<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\Evenement;
use App\Form\ParticipationType;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\ParticipationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use App\Service\UploaderService;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\PdfService;
use MercurySeries\FlashyBundle\FlashyNotifier;
use App\Entity\PdfGeneratorService;



class ParticipationController extends AbstractController
{
    #[Route('/home', name: 'app_participation')]
    public function index(): Response
    {
        return $this->render('back.html.twig', [
            'controller_name' => 'ParticipationController',
        ]);
    }
    #[Route('/addpartic/{id}', name: 'add_Participation')]
    public function addpartic(Request  $request,ManagerRegistry $doctrine,$id,EvenementRepository $rep,FlashyNotifier $flashy/*,VerifyEmailHelperInterface $verifyEmailHelper, UploaderService $uploaderService, MailerService $mailer*/)
    {
        $event= $rep->find($id);
        $nbrPer=$event->getNbrPersonnes();
        $participation= new Participation();
        $form= $this->createForm(ParticipationType::class,$participation);
        $form->handleRequest($request) ;
        $nbrPerR = $form["nbrPersonnes"]->getData();
        if($form->isSubmitted()&& $form->isValid()) {
            if ($nbrPer >= $nbrPerR) {
                $participation->setEvenement($event);
                $em = $this->getDoctrine()->getManager();
                $nbrPer = $nbrPer - $nbrPerR;
                $event->setNbrPersonnes($nbrPer);
                $em->persist($participation);
                $em->persist($event);
                $em->flush();

                return $this->redirectToRoute("listeventFront");
            }
        }
        return $this->render('participation/add.html.twig',
            [
                'tabPartic' => $participation,
                'tabEvent' => $event,

                'form'=>$form->createView()]);

    }




    #[Route('/listPartic', name: 'listPartic')]
    public function listPartic(ParticipationRepository $repository)
    {
        $participation= $repository->findAll();
        return $this->render("Participation/list.html.twig",array("tabPartic"=>$participation));
    }
    #[Route('/removePartic/{id}', name: 'remove_Partic')]
    public function remove(ManagerRegistry $doctrine,$id,ParticipationRepository $repository)
    {
        $event= $repository->find($id);
        $em= $doctrine->getManager();
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute("listPartic");
    }
    #[Route('/updatePartic/{id}', name: 'update_Partic')]
    public function updateParticForm($id,ParticipationType  $repository,Request  $request,ManagerRegistry $doctrine)
    {
        $participation=$this->getDoctrine()->getRepository(Participation::class)->find($id);
        $form= $this->createForm(ParticipationType::class,$participation);
        $form->handleRequest($request) ;
        if($form->isSubmitted()){
            $em= $doctrine->getManager();
            $em->flush();
            return  $this->redirectToRoute("listPartic");
        }
        return $this->renderForm("participation/update.html.twig",array("FormPartic"=>$form));
    }
    #[Route('/showEvenement/{id}', name: 'showEvenement')]
    public function showEvenement(ParticipationRepository $repo,$id,EvenementRepository $repository)
    {
        $event= $repository->find($id);
        $participation= $repo->getParticipationByEvenement($id);
        return $this->render("participation/showEvenement.html.twig",
            array("tabEvent"=>$event,
                "tabPartic"=>$participation));
    }

   /* #[Route('/listPartic/pdf', name: 'participation.pdf')]
    public function generatePdfParticipation(Participation $participation = null, ParticipationRepository $repository,PdfService $pdf) {
        $participation= $repository->findAll();
        $html = $this->render('participation/pdfs.html.twig', ['tabPartic' => $participation]);
        $pdf->showPdfFile($html);
    }*/
    #[Route('/pdf/Participants', name: 'pdf')]
    public function pdfServices(): Response
    {
        $participation= $this->getDoctrine()
            ->getRepository(Participation::class)
            ->findAll();



        $html =$this->renderView('participation/pdfs.html.twig', ['tabPartic' => $participation]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);


    }



}
