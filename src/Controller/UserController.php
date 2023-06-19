<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/api/clients', name: 'app_customers')]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllCustomers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $customersList = $userRepository->findByRoleCustomers();

        $jsonCustomersList = $serializer->serialize($customersList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCustomersList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/client/{id}', name: 'app_customers_detail')]
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

    #[Route('/api/utilisateurs/client/{id}', name: 'app_user_bycustomer')]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllUsersByCustomers(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findUsersByRoleAndParentId($id);
    
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/utilisateur/{id}', name: 'app_one_user')]
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
}
