<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AuthorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $author = new Author();
        $author->setName('በዓሉ ግርማ');
        $manager->persist($author);

        $author2 = new Author();
        $author2->setName('አዳም ረታ');
        $manager->persist($author2);

        $author3 = new Author();
        $author3->setName('ብርቅርቅታ');
        $manager->persist($author3);

        $author4 = new Author();
        $author4->setName('ፍቅር እስከ መቃብር');
        $manager->persist($author4);

        $manager->flush();

        $this->addReference('author_1', $author);
        $this->addReference('author_2', $author2);
        $this->addReference('author_3', $author3);
        $this->addReference('author_4', $author4);
    }
}
