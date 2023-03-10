<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\ParticipationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints\Json;
use symfony\Component\Serializer\Annotation\Groups;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\OptionsResolver\Options;
use Dompdf\Dompdf;
use MercurySeries\FlashyBundle\FlashyNotifier;





class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement')]
    public function index(): Response
    {
        return $this->render('back.html.twig', [
            'controller_name' => 'EvenementController',
        ]);
    }



    #[Route('/addevent', name: 'add_event')]
    public function addevent(Request $request, ManagerRegistry $doctrine , SluggerInterface $slugger, FlashyNotifier $flashy, \Symfony\Component\Security\Core\Security $security)
    {
        $event= new Evenement();
        $form= $this->createForm(EvenementType::class,$event);
        $form->handleRequest($request) ;
        if($form->isSubmitted()&& $form->isValid()){
            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('event_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $event->setImage($newFilename);
            }

            $em=$doctrine->getManager();
            $user = $security->getUser();
            $event->setUser($user);
            $em->persist($event);
            $em->flush();
            $flashy->success('Event created!');


            return  $this->redirectToRoute("listevent");
        }
        return $this->render("evenement/add.html.twig",['form'=> $form->createView()]);

    }

    #[Route('/listEvent', name: 'listevent')]
    public function listEvent(EvenementRepository $repository)
    {
        $event= $repository->findAll();
        return $this->render("evenement/list.html.twig",array("tabEvent"=>$event));
    }
    #[Route('/show/{id}', name: 'list')]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/listEventFront', name: 'listeventFront')]
    public function listEventFront(EvenementRepository $repository, PaginatorInterface $paginator ,Request  $request)
    {   $repository = $this->getDoctrine()->getRepository(Evenement::class);
        $event= $repository->findAll();
        $event = $paginator->paginate($event, $request->query->getInt('page', 1),2);
        return $this->render("evenement/listFront.html.twig",array("tabEvent"=>$event));
    }
    #[Route('/removeEvent/{id}', name: 'remove_Event')]
    public function remove(ManagerRegistry $doctrine,$id,EvenementRepository $repository)
    {
        $event= $repository->find($id);
        $em= $doctrine->getManager();
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute("listevent");
    }
    #[Route('/updateEvent/{id}', name: 'update_Event')]
    public function updateEventForm($id,EvenementType  $repository,Request  $request,ManagerRegistry $doctrine)
    {
        $event=$this->getDoctrine()->getRepository(Evenement::class)->find($id);
        $form= $this->createForm(EvenementType::class,$event);
        $form->handleRequest($request) ;
        if($form->isSubmitted()){
            $em= $doctrine->getManager();
            $em->flush();
            return  $this->redirectToRoute("listevent");
        }
        return $this->render("evenement/update.html.twig",['form'=> $form->createView()]);
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
    #[Route('/stats', name: 'stats')]
    public function stat(EvenementRepository $rep)
    {
        $event = $rep->findAll();

        $id = [];
        $nbrPersonnes = [];
        foreach($event as $evenement){
            $id[] = $evenement->getId();
            $nbrPersonnes[] = $evenement->getNbrPersonnes();
        }
        return $this->render('evenement/stats.html.twig', [
            'id' => json_encode($id),
            'nbrPersonnes' => json_encode($nbrPersonnes),
        ]);
    }


    #[Route('/filter', name: 'filter')]
    public function search(EvenementRepository $repository,Request $request,PaginatorInterface $paginator)
    {
        $data=$request->get('filter');
        $event=$repository->findBy(['nom'=>$data]);
        $event = $paginator->paginate($event, $request->query->getInt('page', 1),2);
        return $this->render("evenement/listFront.html.twig",['tabEvent'=>$event]);
    }
   /* #[Route('/listp', name: 'evenement_list')]
    public function listp(ParticipationRepository $repository)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('participation/pdf.html.twig', [
            'tabPartic' => $repository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render('participation/list.html.twig');

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);


        // Send some text response
    }*/



    ///////////////Mobile/////////////////

   #[Route('/addEventMobile',name :'addmobile')]
    public function ajouterevenement(Request $request,NormalizerInterface $Normalizer)
    {
        $evenement = new Evenement();
        $nom = $request->query->get("nom");
        $lieu = $request->query->get("lieu");
        $description = $request->query->get("description");
        $date =$request->query->get('date');
        $datefin =$request->query->get('date_fin');
        $image = $request->query->get('image');

        $em = $this->getDoctrine()->getManager();



        $evenement->setNom($nom);
        $evenement->setDescription($description);
        $evenement->setLieu($lieu);
        $evenement->setDate(new \DateTime($date));
        $evenement->setDatefin(new \DateTime($datefin));
        $evenement->setImage($image);
        $em->persist($evenement);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($evenement);
        //  return new JsonResponse($formatted);
        //return new JsonResponse("event ajoute");
        $jsonContent=$Normalizer->normalize($evenement,'json',['groups'=>'post:read']);
        return new Response("evenement ajoutee".json_encode( $jsonContent));
        //http://127.0.0.1:8000/addEventMobile?nom=atatatat&lieu=atatatat&description=ytitiutuit&date=2021-04-09&datefin=2021-04-09&image=tatata.jpg&datefin=8&categorie=hhhhh


    }
    #[Route('/deleteEventmobile',name :'deleteEventmobile')]
    public function deleteEventmobile(Request $request,NormalizerInterface $Normalizer) {
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository(Evenement::class)->find($id);
        if($evenement!=null ) {
            $em->remove($evenement);
            $em->flush();

            $serialize = new Serializer([new ObjectNormalizer()]);
            $formatted = $serialize->normalize($evenement);
            // return new JsonResponse($formatted);
            //return new JsonResponse("event deleted.");
            $jsonContent=$Normalizer->normalize($evenement,'json',['groups'=>'post:read']);
            return new Response("evenement supprimee".json_encode( $jsonContent));


        }
        return new JsonResponse("id event invalide.");
        //http://127.0.0.1:8000/deleteEventmobile?id=38


    }
    #[Route('/updateeventMobile',name :'updateeventMobile')]
    public function modifierevenement(Request $request,NormalizerInterface $Normalizer) {
        $em = $this->getDoctrine()->getManager();
        $evenement = $this->getDoctrine()->getManager()
            ->getRepository(Evenement::class)
            ->find($request->get("id"));
        $evenement->setNom($request->get("nom"));
        $evenement->setDescription($request->get("description"));
        $evenement->setLieu($request->get("lieu"));
        $evenement->setDate(new \DateTime($request->get("date")));
        $evenement->setDatefin(new \DateTime($request->get("datefin")));
        $evenement->setImage($request->get("image"));


        $em->persist($evenement);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($evenement);
        //return new JsonResponse("event a ete modifiee avec success.");
        $jsonContent = $Normalizer->normalize($evenement, 'json',['groups'=>'post:read']);
        return new Response("event a ete modifiee avec success".json_encode($jsonContent));
// http://127.0.0.1:8000/updateeventMobile?id=37&nom=mariem&lieu=mariem&description=ytitiutuit&date=2021-04-09&datefin=2021-04-09&image=tatata.jpg&nbrPersonnes=8&categorie=hhhhh
    }


    #[Route('/afficheeventMobile',name :'afficheeventMobile')]
    public function afficheevent(NormalizerInterface $normalizer)
    {

        $repository = $this->getDoctrine()->getRepository(Evenement::class);
        $evenement =$repository->findAll();
        $jsonContent = $normalizer->normalize($evenement, 'json',['groups'=>'post:read']);

        return new Response("Liste des evenements :".json_encode($jsonContent));

    }








}
