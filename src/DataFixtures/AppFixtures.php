<?php

namespace App\DataFixtures;

use Faker;
use DateTimeImmutable;
use App\Entity\Smartphone;
use App\Entity\Society;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');
        
        $society = new Society();
        $society->setSocietyName('MileBo');
        $society->setSocietyDescription("BileMo est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.
            Vous êtes en charge du développement de la vitrine de téléphones mobiles de l’entreprise BileMo. 
            Le business modèle de BileMo n’est pas de vendre directement ses produits sur le site web, mais de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue via une API (Application Programming Interface). 
            Il s’agit donc de vente exclusivement en B2B (business to business).");
        $manager->persist($society);    

        for ($gsm=0; $gsm < 50 ; $gsm++) { 
            $smarthone = new Smartphone();
            $smarthone->setPhoneBrand('Smartphone ' . $gsm);
            $smarthone->setPhoneModel('Android '. $gsm);
            $smarthone->setPhoneDescription($faker->text(400));

            $createdAt = $faker->dateTimeThisYear();
            $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);

            $smarthone->setPhoneCreatedAt($createdAtImmutable);
            $smarthone->setSociety($society);

            $manager->persist($smarthone);

        }

        $arrayCustomers = [
            'Apple',
            'Samsung',
            'Huawei',
            'Sony',
            'Google',
        ];

        for ($cts=0; $cts < 5 ; $cts++) { 
            $customers = new User();
            $customers->setEmail($faker->email());
        }
       

        $manager->flush();
    }
}
