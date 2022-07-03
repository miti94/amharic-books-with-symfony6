<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(['min' => 3])]
    private $title;

    #[ORM\Column(type: 'integer',)]
    #[Assert\NotBlank]
    private $published_year;

    #[ORM\Column(type: 'string', length: 255)]
    private $image_path;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private $description;

    #[ORM\ManyToMany(targetEntity: Author::class, inversedBy: 'books')]
    private $authors;

    #[ORM\OneToMany(mappedBy: 'books', targetEntity: UploadPdfFiles::class, orphanRemoval: true, cascade: ['persist'])]
    private $uploadPdfFiles;

//    #[ORM\Column(type: 'string', length: 255, nullable: true)]
//    private $book_pdf_file;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->uploadPdfFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublishedYear(): ?int
    {
        return $this->published_year;
    }

    public function setPublishedYear(?int $published_year): self
    {
        $this->published_year = $published_year;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(?string $image_path): self
    {
        $this->image_path = $image_path;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors[] = $author;
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        $this->authors->removeElement($author);

        return $this;
    }

    public function getBookPdfFile(): ?string
    {
        return $this->book_pdf_file;
    }

    public function setBookPdfFile(?string $book_pdf_file): self
    {
        $this->book_pdf_file = $book_pdf_file;

        return $this;
    }

    /**
     * @return Collection<int, UploadPdfFiles>
     */
    public function getUploadPdfFiles(): Collection
    {
        return $this->uploadPdfFiles;
    }

    public function addUploadPdfFile(UploadPdfFiles $uploadPdfFile): self
    {
        if (!$this->uploadPdfFiles->contains($uploadPdfFile)) {
            $this->uploadPdfFiles[] = $uploadPdfFile;
            $uploadPdfFile->setBooks($this);
        }

        return $this;
    }

    public function removeUploadPdfFile(UploadPdfFiles $uploadPdfFile): self
    {
        if ($this->uploadPdfFiles->removeElement($uploadPdfFile)) {
            // set the owning side to null (unless already changed)
            if ($uploadPdfFile->getBooks() === $this) {
                $uploadPdfFile->setBooks(null);
            }
        }

        return $this;
    }
}
