<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\UploadPdfFiles;
use App\Form\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('books')]
class BooksController extends AbstractController
{
//    #[Route('/books/{name}', name: 'app_books', defaults: ['name' => 'null'], methods: ['GET', 'HEAD'])]
//    public function index($name): Response
//    {
//        return $this->json([
//            "message" => $name,
//            'path' => 'src/controller/BooksController.php'
//        ]);
//    }

//    #[Route('/books', name: 'app_books')]
//    public function index(): Response
//    {
//        $books = ['ከአድማስ ባሻገር', 'ትኩሳት', 'ፍቅር እስከ መቃብር', 'የተቆለፈበት ቁልፍ', 'እመጓ '];
//        $controllerName = 'Books Controller';
//        return $this->render('books/index.html.twig', array(
//            'books' => $books
//        ));
//    }
//    private $em;
//
//    public function __construct(EntityManagerInterface $em) {
//        $this->em = $em;
//    }
//
//    #[Route('/books', name: 'app_books')]
//    public function index(): Response
//    {
//        $repository = $this->em->getRepository(Book::class);
//
//        // findAll() - SELECT * FROM books;
//            //$books = $repository->findAll();
//        // find() - SELECT * FROM books WHERE id = 5;
//            //$books = $repository->find(5);
//        // findBy() - SELECT * FROM books ORDER BY id DESC;
//            // $books = $repository->findBy([], ['id' => 'DESC']);
//        // findOneBy() - SELECT * FROM books WHERE id = 6 AND title = "ከአድማስ ባሻገር" ORDER BY id DESC;
//            //$books = $repository->findOneBy(['id' => 6], ['id' => 'DESC']);
//        // count - SELECT count() FROM books WHERE id = 5;
//        $books = $repository->count(['id' => 5]);
//
//        // get class name -> "App\Entity\Book"
//       // $books = $repository->getClassName();
//        //dd($books);
//        return $this->render('books/index.html.twig');
//    }

    /**
     * @var BookRepository
     */
    private BookRepository $bookRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(BookRepository $bookRepository, EntityManagerInterface $em)
    {
        $this->bookRepository = $bookRepository;
        $this->em = $em;
    }

    #[Route('/', name: 'books', methods: 'GET')]
    public function index(Request $request, PaginatorInterface $paginator) : Response
    {
        $books = $this->bookRepository->findAll();
        $pagination_books = $paginator->paginate(
            $books,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('books/index.html.twig', compact('pagination_books'));
    }

    #[Route('/create', name: 'book.create'), IsGranted('ROLE_ADMIN')]
    public function create(Request $request, SluggerInterface $slugger) : Response
    {
        $book = new Book();
        $form = $this->createForm(BookFormType::class, $book);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newBook = $form->getData();

            $imagePath = $form->get('image_path')->getData();
            $bookPdfFiles = $form->get('pdfFiles')->getData();

            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();
                try {
                    $imagePath->move(
                        $this->getParameter('uploads_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
                $newBook->setImagePath('/uploads/' . $newFileName);
            }

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
                    $book->addUploadPdfFile($uploadPdfFiles);
                }

            }
            $this->em->persist($book);
            $this->em->flush();

            return $this->redirectToRoute('books');
        }

        return $this->render('books/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'book.show', methods: 'GET')]
    public function show($id) : Response
    {
        $book = $this->bookRepository->find($id);

        return $this->render('books/show.html.twig', [
            'book' => $book
        ]);
    }

    #[Route('/edit/{id?0}', name: 'book.edit'), IsGranted('ROLE_ADMIN')]
    public function edit($id, Request $request, SluggerInterface $slugger): Response
    {

        $book = $this->bookRepository->find($id);

        $form = $this->createForm(BookFormType::class, $book);

        $form->handleRequest($request);
        $imagePath = $form->get('image_path')->getData();
        $bookPdfFiles = $form->get('pdfFiles')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                if ($book->getImagePath() !== null) {
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . $book->getImagePath()
                    )) {
                        $this->GetParameter('kernel.project_dir') . $book->getImagePath();
                    }
                    $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                    try {
                        $imagePath->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads',
                            $newFileName
                        );
                    } catch (FileException $e) {
                        return new Response($e->getMessage());
                    }

                    $book->setImagePath('/uploads/' . $newFileName);
                    $this->em->flush();

                    return $this->redirectToRoute('books');
                }
            }
            elseif ($bookPdfFiles) {
                if ($book->getImagePath() !== null) {
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . $book->getImagePath()
                    )) {
                        $this->GetParameter('kernel.project_dir') . $book->getImagePath();
                    }
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

//                    $newBook->setBookPdfFile('/upload_pdf_files/' . $newBookPdfFileName);
                        $uploadPdfFiles = new UploadPdfFiles();
                        $uploadPdfFiles->setName($newBookPdfFileName);
                        $book->addUploadPdfFile($uploadPdfFiles);
                    }
                    $this->em->flush();


                    return $this->redirectToRoute('books');
                }
            } else {
                $book->setTitle($form->get('title')->getData());
                $book->setPublishedYear($form->get('published_year')->getData());
                $book->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('books');
            }
        }

        return $this->render('books/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'book.delete', methods: ['GET', 'DELETE']), IsGranted('ROLE_ADMIN')]
    public function delete($id) : Response
    {
        $book = $this->bookRepository->find($id);
        $this->em->remove($book);
        $this->em->flush();



        return $this->redirectToRoute('books');
    }
}
