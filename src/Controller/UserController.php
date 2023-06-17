<?php

namespace App\Controller;

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
    public function getAllCustomers(UserRepository $userRepository,SerializerInterface $serializer): JsonResponse
    {
        $customersList = $userRepository->findByRoleCustomers();

        $jsonCustomersList = $serializer->serialize($customersList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCustomersList, Response::HTTP_OK, [], true);
    }
}
