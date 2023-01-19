<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        for ($i=0;$i<20;$i++){
            $post = new Post();
            $post->setTitle($faker->words($faker->numberBetween(3,10),true))
                ->setContent($faker->paragraphs(3,true))
                ->setCreatedAt($faker->dateTimeBetween('-6 months'));
                $categoryRef = $faker->numberBetween(1,10);
                $post->setCategory($this->getReference("category_".$categoryRef));
            $manager->persist($post);

        }

        $manager->flush();
    }
}
