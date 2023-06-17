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
}
