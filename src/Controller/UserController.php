<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/api/clients",
     *     summary="Obtenir la liste des clients",
     *     tags={"Clients"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients récupérée avec succès"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     )
     * )
     * Méthode pour recevoir tout les clients 
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/clients', name: 'app_customers', methods: ['GET'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllCustomers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getCustomers"]);
        $customersList = $userRepository->findByRoleCustomers();

        $jsonCustomersList = $serializer->serialize($customersList, 'json', $context);
        return new JsonResponse($jsonCustomersList, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Get(
     *     path="/api/client/{id}",
     *     summary="Obtenir les détails d'un client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du client récupérés avec succès"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     * Méthode pour recevoir detail d'un client.
     */
    #[Route('/api/client/{id}', name: 'app_customers_detail', methods: ['GET'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getOneCustomers(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $userRepository->findCustomerById($id);
        $context = SerializationContext::create()->setGroups(["getCustomers"]);

        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Get(
     *     path="/api/utilisateurs/client/{id}",
     *     summary="Récupérer tous les utilisateurs par client",
     *     tags={"Utilisateurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     )
     * )
     * Méthode pour obtenir tout les utilisateurs d'un client bia son id
     */
    #[Route('/api/utilisateurs/client/{id}', name: 'app_user_bycustomer', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllUsersByCustomers(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findUsersByRoleAndParentId($id);
        $context = SerializationContext::create()->setGroups(["getUsers"]);

        $jsonUsers = $serializer->serialize($users, 'json', $context);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

     /**
     * @OA\Get(
     *     path="/api/utilisateur/{id}",
     *     summary="Obtenir les détails d'un utilisateur",
     *     tags={"Utilisateurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur récupérés avec succès"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     * Méthode pour recevoir detail d'un Utilisateur
     */
    #[Route('/api/utilisateur/{id}', name: 'app_one_user', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getOneUser(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $context = SerializationContext::create()->setGroups(["getOneUser"]);

        $userConnected = $this->getUser();

        //Condition pour verifier si l'user connecté a le droit d'acces à cette ressource
        if (!$this->isGranted('ROLE_ADMIN') && $userConnected !== $user->getParent()) {
            $responseData = [
                'message' => "Vous n'avez pas les droits requis pour cette ressource",
            ];
            return new JsonResponse($responseData, Response::HTTP_FORBIDDEN);
        }

        $jsonUser = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Post(
     *     path="/api/ajout_utilisateur",
     *     summary="Ajouter un nouvel utilisateur",
     *     tags={"Utilisateurs"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du nouvel utilisateur",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", example="user@test.fr"),
     *                 @OA\Property(property="pseudo", type="string", example="testUser"),
     *                 @OA\Property(property="password", type="string", example="PasswordUser123"),
     *                 @OA\Property(property="idParent", type="integer", example=2),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Nouvel utilisateur créé avec succès",
     *         @OA\Header(
     *             header="Location",
     *             description="URL de l'utilisateur créé",
     *             @OA\Schema(
     *                 type="string",
     *                 format="url"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     )
     * )
     * Méthode pour ajouter un Utilisateur
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @return JsonResponse
     */
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
        $context = SerializationContext::create()->setGroups(["getCustomers"]);

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
        $jsonNewUser = $serializer->serialize($newUser, 'json', $context);

        //On génère une url qui sera retourné pour avoir acces au nouveau utilisateur rajouté
        $location = $urlGenerator->generate('app_one_user', ['id' => $newUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        //Je renvoi ma variable location dans ma jsonResponse
        return new JsonResponse($jsonNewUser, Response::HTTP_CREATED, ['location' => $location], true);
    }

    /**
     * @OA\Delete(
     *     path="/api/delete_utilisateur/{id}",
     *     summary="Supprimer un utilisateur",
     *     tags={"Utilisateurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur à supprimer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     )
     * )
     * Méthode pour supprimer un utilisateur
     */
    #[Route('/api/delete_utilisateur/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function deleteUser(User $user, EntityManagerInterface $em) : JsonResponse
    {
        if (count($user->getRoles()) === 1 && in_array('ROLE_USER', $user->getRoles())) {
            $em->remove($user);
            $em->flush();
            return new JsonResponse(['message' => 'Suppression réussie'], Response::HTTP_OK);
        }

        $responseData = [
            'message' => "Cette action n'est pas possible",
        ];
        return new JsonResponse($responseData, Response::HTTP_FORBIDDEN);
        
    }

}
