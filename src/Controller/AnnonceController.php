<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination ;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\PdfGeneratorService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart; //stat
class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'app_annonce')] //, name: 'app_annonce'
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AnnonceController',
        ]);
    }

    #[Route('/addannonceForm', name: 'addannonceForm')]
    public function addForm(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger ,AnnonceRepository $repository,Security $security)
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $brochureFile */
            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('annonce_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $annonce->setImage($newFilename);
            }

            $em = $doctrine->getManager();
            $etat = $request->get("etat");
            $annonce->setEtat($etat);
            $cat = $request->get("categorie");
            $annonce->setCategorie($cat);

            $user = $security->getUser();
            $annonce->setUser($user);


            $em->persist($annonce);
            $em->flush();
            $repository->sms();
            $this->addFlash('danger', 'reponse envoyée avec succées');
            return $this->redirectToRoute('annonce_aff');
        }
        return $this->render("annonce/add.html.twig", ['f' => $form->createView()]);
    }

    #[Route('/updateannonce/{id}', name: 'annonce_upd')]
    public function updateForma($id, AnnonceRepository $repository, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger,Annonce $annonce)
    {
if($annonce->getUser() ==$this->getUser()){
    $annonce = $repository->find($id);
    $form = $this->createForm(AnnonceType::class, $annonce);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {

        //image
        /** @var UploadedFile $brochureFile */
        $photo = $form->get('photo')->getData();

        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($photo) {
            $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

            // Move the file to the directory where photos are stored
            try {
                $photo->move(
                    $this->getParameter('annonce_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $annonce->setImage($newFilename);
        }


        $em  =$doctrine->getManager();
        $etat  = $request->get("etat");
        $annonce->setEtat($etat);
        $cat  =$request->get("categorie");
        $annonce->setCategorie($cat);

        $em->flush();

        return $this->redirectToRoute('annonce_aff');
    }
    return $this->render("annonce/updatea.html.twig", ['f' => $form->createView()]);
}else
{
    $this->addFlash('error','you are not allowed to edit or delete this announcement');
}
    }

    #[Route('/removeannonce/{id}', name: 'annonce_rem')]
    public function removeann(ManagerRegistry $doctrine, $id, AnnonceRepository $repository)
    {
        $annonce = $repository->find($id);
        $em = $doctrine->getManager();
        $em->remove($annonce);
        $em->flush();
        return $this->redirectToRoute("annonce_aff");
    }

    #[Route('/annoncelist', name: 'annonce_aff')]
    public function list(AnnonceRepository $repository ,PaginatorInterface $paginator ,Request $request)
    {
        $annonce = $repository->findAll();

        $annonce = $paginator->paginate(
           $annonce,
            $request->query->getInt('page', 1),3);

        return $this->render("annonce/annonce.html.twig", array("f" => $annonce));
    }


    #[Route('/annoncesingle/{id}', name: 'annonce_single')]
    public function single(AnnonceRepository $repository, $id)
    {
        $annonce = $repository->find($id);
        return $this->render("annonce/oneannonce.html.twig", array("annonce" => $annonce));
    }


    #[Route('/pdf/annonce', name: 'generator_service')]
    public function pdfService(): Response
    {
        $annonce= $this->getDoctrine()
            ->getRepository(Annonce::class)
            ->findAll();



        $html =$this->renderView('pdf/index.html.twig', ['annonce' => $annonce]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);

    }


////Back
    #[Route('/annoncelist2', name: 'annonce_aff2')]
    public function list2(AnnonceRepository $repository ,Request $request)
    {
        $annonce = $repository->findAll();

        //tri+recherche
        $back = null;

        if($request->isMethod("POST")){
            if ( $request->request->get('optionsRadios')){
                $SortKey = $request->request->get('optionsRadios');
                switch ($SortKey){
                    case 'titre':
                        $annonce = $repository->SortBytitreAnnonce();
                        break;

                    case 'descreption':
                        $annonce = $repository->SortBydescriptionAnnonce();
                        break;

                }
            }
            else
            {
                $type = $request->request->get('optionsearch');
                $value = $request->request->get('Search');
                switch ($type){


                    case 'descreption':
                        $annonce = $repository->findBydescriptionAnnonce($value);
                        break;

                    case 'titre':
                        $annonce = $repository->findBytitreAnnonce($value);
                        break;

                }
            }

            if ( $annonce){
                $back = "success";
            }else{
                $back = "failure";
            }
        }

        /// fin tri+recherch

        return $this->render("aadmin/admin.html.twig", array("f" => $annonce));
    }

    #[Route('/statistique', name: 'statique')]
    public function stat()
    {

        $repository = $this->getDoctrine()->getRepository(Annonce::class);
        $annonce= $repository->findAll();

        $em = $this->getDoctrine()->getManager();


        $pr1 = 0;
        $pr2 = 0;


        foreach ($annonce as $annonce) {
            if ($annonce->getCategorie() == "Appareil médical")  :

                $pr1 += 1;
            else:

                $pr2 += 1;

            endif;

        }

        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(
            [['etat ', 'nom'],
                ['la catégorie de lannoce de type Appareil médical', $pr1],
                ['la catégorie de lannoce ne sont pas de type Appareil médical', $pr2],
            ]
        );
        $pieChart->getOptions()->setTitle('Catégories des annonces');
        $pieChart->getOptions()->setHeight(1000);
        $pieChart->getOptions()->setWidth(1400);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('green');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(30);



        return $this->render('aadmin/stat.html.twig', array('piechart' => $pieChart));
    }


    #[Route('/removeannonceback/{id}', name: 'annonce_remback')]
    public function removeannback(ManagerRegistry $doctrine, $id, AnnonceRepository $repository)
    {
        $annonce = $repository->find($id);
        $em = $doctrine->getManager();
        $em->remove($annonce);
        $em->flush();
        return $this->redirectToRoute("annonce_aff2");
    }

    ///Mobile
    #[Route('/alistjson', name: 'annonce_affjson')]
    public function getannonce( NormalizerInterface $normalizer)
    {
        $repository=$this->getDoctrine()->getRepository(Annonce::class);
        $annonces = $repository->findAll();
        $json=$normalizer->normalize($annonces,'json',['groups'=>'annonces']);
         return new Response("liste des annonces :".json_encode($json));

    }

    #[Route('/aaddjson', name: 'annonce_addjson')]
    public function addannonce(Request $request, SerializerInterface  $serializer,EntityManagerInterface $em)
    {
        $content=$request->getContent();
        $data=$serializer->deserialize($content,Annonce::class,'json' , [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
        ]);
        $em->persist($data);
        $em->flush();
        return new Response('annonce added successfully');
    }
    #[Route('/deljson', name: 'annonce_deljson')]
    public function delannonce(Request $request,NormalizerInterface $normalizer)
    {
        $id=$request->get("id");
        $em=$this->getDoctrine()->getManager();
        $annonce= $em->getRepository(Annonce::class)->find($id);
        if($annonce != null)
        {
            $em->remove($annonce);
            $em->flush();

            $formatted=$normalizer->normalize("Annonce supprimée avex success");
            return new JsonResponse($formatted);
        }
        return new JsonResponse('id annonce invalid');
    }
//    #[Route('/upjson', name: 'annonce_upjson')]
//    public function upannonce(Request $request,NormalizerInterface $normalizer)
//    {
//
//
//        $id=$request->get("id");
//
//        $em=$this->getDoctrine()->getManager();
//        $annonce= $em->getRepository(Annonce::class)->find($id);
//        var_dump($annonce); die();
//        $annonce->setNom($request->get("nom"));
//        $annonce->setImage($request->get("image"));
//        $annonce->setDescreption($request->get("descreption"));
//        $annonce->setTitre($request->get("titre"));
//        $annonce->setTag($request->get("tag"));
//        $annonce->setTel($request->get("tel"));
//        $annonce->setEmail($request->get("email"));
//        $annonce->setLocal($request->get("local"));
//        $annonce->setEtat($request->get("etat"));
//        $annonce->setCategorie($request->get("categorie"));
//        $em->persist($annonce);
//        $em->flush();
//        $formatted = $normalizer->normalize($annonce);
//
//        return new JsonResponse('Annonce modifiée avex success');
//    }



}
