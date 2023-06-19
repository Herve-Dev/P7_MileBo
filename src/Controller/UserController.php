<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/clients', name: 'app_customers', methods: ['GET'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllCustomers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $customersList = $userRepository->findByRoleCustomers();

        $jsonCustomersList = $serializer->serialize($customersList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCustomersList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/client/{id}', name: 'app_customers_detail', methods: ['GET'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getOneCustomers(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $userRepository->findCustomerById($id);

        //Vérification Customer existe
        if (!$customer) {
            $responseData = [
                'message' => "La ressource demandée n'a pas été trouvée.",
            ];
            return new JsonResponse($responseData, Response::HTTP_NOT_FOUND);
        }

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }

    #[Route('/api/utilisateurs/client/{id}', name: 'app_user_bycustomer', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllUsersByCustomers(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findUsersByRoleAndParentId($id);

        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/utilisateur/{id}', name: 'app_one_user', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getOneUser(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findOneBy(['id' => $id]);

        $userConnected = $this->getUser();

        //Condition pour verifier si l'user connecté a le droit d'acces à cette ressource
        if (!$this->isGranted('ROLE_ADMIN') && $userConnected !== $user->getParent()) {
            $responseData = [
                'message' => "Vous n'avez pas les droits requis pour cette ressource",
            ];
            return new JsonResponse($responseData, Response::HTTP_FORBIDDEN);
        }


        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);

    }

    #[Route('/api/ajout_utilisateur', name: 'app_user_add', methods: ['POST'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function addNewUser(Request $request, 
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idParent. S'il n'est pas défini, alors on met -1 par défaut.
        $idParent = $content['idParent'] ?? -1;

        //Récupération du password (fait à des fin de démo à ne jamais faire en réel)
        $passNewUser = $content['password'];

        //hash du password
        $newUser->setPassword($passwordHasher->hashPassword($newUser, $passNewUser));

        //je cherche un user avec un ROLE_CUSTOMER
        $customer = $userRepository->findCustomerById($idParent);

        //Je verfie si le customer existe sinon erreur
        if (!$customer) {
            $responseData = [
                'message' => "Cette action n'est pas possible",
            ];
            return new JsonResponse($responseData, Response::HTTP_FORBIDDEN);
        }

        //Je le stock dans mon setParent
        $newUser->setParent($customer);

        //Je rajoute un ROLE_USER par default
        $newUser->setRoles(['ROLE_USER']);

        //On vérifie les erreurs
        $errors = $validator->Validate($newUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($newUser);
        $em->flush();

        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonNewUser = $serializer->serialize($newUser, 'json', ['groups' => 'getUsers']);

        //On génère une url qui sera retourné pour avoir acces au nouveau utilisateur rajouté
        $location = $urlGenerator->generate('app_one_user', ['id' => $newUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        //Je renvoi ma variable location dans ma jsonResponse
        return new JsonResponse($jsonNewUser, Response::HTTP_CREATED, ['location' => $location], true);
    }
}
