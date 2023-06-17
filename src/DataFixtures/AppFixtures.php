<?php

namespace App\DataFixtures;

use Faker;
use DateTimeImmutable;
use App\Entity\Smartphone;
use App\Entity\Society;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder)
    {
    }


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

        $admin = new User();
        $admin->setEmail($faker->email());
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPseudo('MilBo');
        $admin->setPassword(
            $this->passwordEncoder->hashPassword($admin, 'PasswordAdmin123')
        );

        $manager->persist($admin);

        $arrayCustomers = [
            'Apple',
            'Samsung',
            'Huawei',
            'Sony',
            'Google',
        ];

        //Création des clients
        for ($cts=0; $cts < 5 ; $cts++) {
            $customers = new User();
            $customers->setEmail($faker->email());
            $customers->setRoles(['ROLE_CUSTOMERS']);
            $customers->setPseudo($arrayCustomers[$cts]);
            $customers->setPassword(
                $this->passwordEncoder->hashPassword($customers, 'PasswordCustomer123')
            );

            $manager->persist($customers);
            $parents[] = $customers;
        }

        //Création des users lié au clients
        for ($usr=0; $usr < 50 ; $usr++) {
            $user = new User();
            $user->setEmail($faker->email());
            $user->setRoles(['ROLE_USER']);
            $user->setPseudo($faker->userName());
            $user->setPassword(
                $this->passwordEncoder->hashPassword($user, 'PasswordUser123')
            );

            //Assigner un parent aléatoirement parmi les parents existants
            $randomIndex = array_rand($parents);
            $customer = $parents[$randomIndex];
            $user->setParent($customer);

            $manager->persist($user);
        }


        $manager->flush();
    }
}
