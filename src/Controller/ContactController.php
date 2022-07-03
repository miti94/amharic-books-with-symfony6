<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\UploadPdfFiles;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ContactController extends AbstractController
{
    /**
     * @var ContactRepository
     */
    private ContactRepository $contactRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ContactRepository $contactRepository, EntityManagerInterface $em) {

        $this->contactRepository = $contactRepository;
        $this->em = $em;
    }
    #[Route('/contact', name: 'contact.index'), IsGranted('ROLE_ADMIN')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $contacts = $this->contactRepository->findAll();
        $pagination_contacts = $paginator->paginate(
            $contacts,
            $request->query->getInt('page', 1),
            3
        );
        return $this->render('contact/index.html.twig', compact('pagination_contacts'));
    }

    #[Route('/contact/create', name: 'contact.create')]
    public function createContact(Request $request, SluggerInterface $slugger): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $bookPdfFiles = $form->get('pdfFiles')->getData();
            if ($bookPdfFiles) {
                foreach ($bookPdfFiles as $bookPdfFile) {
                    // Generate a new file name
                    $originalBookPdfFileName = pathinfo($bookPdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalBookPdfFileName);
                    $newBookPdfFileName = $safeFilename . '_' .uniqid() . '.' . $bookPdfFile->guessExtension();


                    // Copy the file to the uploads folder
                    $bookPdfFile->move(
                        $this->getParameter('uploads_pdf_directory'),
                        $newBookPdfFileName
                    );

                    $uploadPdfFiles = new UploadPdfFiles();
                    $uploadPdfFiles->setName($newBookPdfFileName);
                    $contact->addUploadPdfFile($uploadPdfFiles);
                }

            }
            $contact->setCreatedAt(new \DateTime('now'));
            $contact->setUpdatedAt(new \DateTime('now'));
            $this->em->persist($contact);
            $this->em->flush();
            $this->addFlash(
                'success',
                "The email <strong>{$contact->getEmail()}</strong> has been registered !"
            );
            return $this->redirectToRoute('contact.index');
        }
        return $this->render('contact/create.html.twig', [
//            'contact_email' => $this->getParameter('app.contact.email'),
            'form' => $form->createView()
        ]);
    }

    #[Route('/contact/delete/{id}', name: 'contact.delete'), IsGranted('ROLE_ADMIN')]
    public function deleteContact(Contact $contact, EntityManagerInterface $em): Response
    {
        $uploadPdfFiles = $contact->getUploadPdfFiles();
        if ($uploadPdfFiles) {
            foreach ($uploadPdfFiles as $uploadPdfFile) {
                $pdfName = $this->getParameter('uploads_pdf_directory'). '/' . $uploadPdfFile->getName();

                if (file_exists($pdfName)) {
                    unlink($pdfName);
                }
            }
        }

        $em->remove($contact);
        $em->flush();
        $this->addFlash(
            'success',
            'The email <strong>{$contact->getEmail()}</strong> has been successfully deleted!'
        );
        return $this->redirectToRoute('contact.index');
    }

}
