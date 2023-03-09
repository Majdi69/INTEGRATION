<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
use Knp\Component\Pager\PaginatorInterface;


namespace App\Controller;

use App\Entity\Article;
use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;

use Dompdf\Options;
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
use Knp\Component\Pager\PaginatorInterface;


class ArticlesController extends AbstractController
{







    #[Route('/articles', name: 'app_articles')]
    public function index(): Response
    {
        return $this->render('articles/index.html.twig', [
            'controller_name' => 'ArticlesController',
        ]);
    }

    #[Route('/articlesaff', name: 'art_aff')]
    public function list(ArticlesRepository $repository, Request $request)
    {
        $annonce = $repository->findAll();

        return $this->render("articles/articles.html.twig", array("f" => $annonce));
    }
    #[Route('/articlesaffback', name: 'art_aff_back')]
    public function lists(ArticlesRepository $repository, Request $request,PaginatorInterface $paginator)
    {
        $annonce = $repository->findAll();
        $annonce = $paginator->paginate($annonce, $request->query->getInt('page', 1),2);

        return $this->render("articles/articlesf.html.twig", array("f" => $annonce));
    }

    #[Route('/listp', name: 'listp')] //pdf
    public function listdf(ArticlesRepository $repository
    )
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFront','Arial');

        $dompdf = new Dompdf($pdfOptions);

        $annonce = $repository->findAll();







        $html = $this->render("articles/listp.html.twig", array("f" => $annonce));

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');

        $dompdf->render();



        $dompdf->stream("mypdf.pdf",[
            "Attachment"=> true
        ]);

    }


    #[Route('/addarts', name: 'addarts')]
    public function addForm(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger, ArticlesRepository $repository)
    {
        $annonce = new Articles();
        $form = $this->createForm(ArticlesType::class, $annonce);
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
                        $this->getParameter('art'),
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

            // var_dump($annonce); die();
            $em->persist($annonce);
            $em->flush();

            $this->addFlash(
                'info',
                'Added successfully!');


            return $this->redirectToRoute('art_aff_back');
        }
        return $this->render("articles/add.html.twig", ['f' => $form->createView()]);
    }

    #[Route('/removeartc/{id}', name: 'art_rem')]
    public function removeann(ManagerRegistry $doctrine, $id, ArticlesRepository $repository)
    {
        $annonce = $repository->find($id);
        $em = $doctrine->getManager();
        $em->remove($annonce);
        $em->flush();
        $this->addFlash(
            'info',
            'Removed successfully!');

        return $this->redirectToRoute("art_aff_back");
    }

    #[Route('/updateart/{id}', name: 'art_upd')]
    public function updateForm($id, ArticlesRepository $repository, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger)
    {
        $annonce = $repository->find($id);
        $form = $this->createForm(ArticlesType::class, $annonce);
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
                        $this->getParameter('art'),
                        $newFilename

                    );

                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
            }


            $em = $doctrine->getManager();


            $em->flush();

            $this->addFlash(
                'info',
                'Updated successfully!');

            return $this->redirectToRoute('art_aff_back');
        }
        return $this->render("articles/update.html.twig", ['f' => $form->createView()]);
    }




}

