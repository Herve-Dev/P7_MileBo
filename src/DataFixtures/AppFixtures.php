<?php

namespace App\DataFixtures;

use Faker;
use DateTimeImmutable;
use App\Entity\Smartphone;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');
        

        for ($gsm=0; $gsm < 50 ; $gsm++) { 
            $smarthone = new Smartphone();
            $smarthone->setPhoneBrand('Smartphone ' . $gsm);
            $smarthone->setPhoneModel('Android '. $gsm);
            $smarthone->setPhoneDescription($faker->text(400));

            $createdAt = $faker->dateTimeThisYear();
            $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);

            $smarthone->setPhoneCreatedAt($createdAtImmutable);

            $manager->persist($smarthone);

        }

        $manager->flush();
    }
}
