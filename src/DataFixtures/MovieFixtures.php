<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $book = new Book();
        $book->setTitle('ከአድማስ ባሻገር');
        $book->setDescription('በበዓሉ ግርማ');
        $book->setPublishedYear(2012);
        $book->setImagePath('https://i.gr-assets.com/images/S/compressed.photo.goodreads.com/books/1609552246i/56510195._UY1360_SS1360_.jpg');

        // Add Data to Pivot Table
        $book->addAuthor($this->getReference('author_1'));
        $book->addAuthor($this->getReference('author_2'));

        $manager->persist($book);

        $book2 = new Book();
        $book2->setTitle('ግራጫ ቃጭሎች');
        $book2->setDescription('በአዳም ረታ');
        $book2->setPublishedYear(2013);
        $book2->setImagePath('https://i.gr-assets.com/images/S/compressed.photo.goodreads.com/books/1439387781i/25955670._UY630_SR1200,630_.jpgረ');

        // Add Data to Pivot Table
        $book2->addAuthor($this->getReference('author_3'));
        $book2->addAuthor($this->getReference('author_4'));

        $manager->persist($book2);

        $manager->flush();
    }
}
