<?php

namespace App\Controller;

use App\Entity\Smartphone;
use App\Entity\Society;
use App\Repository\SmartphoneRepository;
use App\Repository\SocietyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SmartphoneController extends AbstractController
{
    #[Route('/api/smartphones', name: 'app_smartphone', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllSmartphones(SmartphoneRepository $smartphoneRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        //Syteme de pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        
        $smartphoneList = $smartphoneRepository->findAllWithPagination($page, $limit);

        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonSmartphoneList = $serializer->serialize($smartphoneList, 'json', ['groups' => 'getSmartphones']);
        return new JsonResponse($jsonSmartphoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_detail', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getDetailSmartphone(Smartphone $smartphone, SerializerInterface $serializer): JsonResponse
    {
        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonSmartphone = $serializer->serialize($smartphone, 'json', ['groups' => 'getSmartphones']);
        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_delete', methods: ['DELETE'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function deleteSmartphone(Smartphone $smartphone, EntityManagerInterface $em): JsonResponse
    {
        //Supression du smartphone avec l'id lié
        $em->remove($smartphone);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/smartphones', name: 'app_smartphone_add', methods: ['POST'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function createSmartphone(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        SocietyRepository $society
    ): JsonResponse {
        $smartphone = $serializer->deserialize($request->getContent(), Smartphone::class, 'json');

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idSociety. S'il n'est pas défini, alors on met -1 par défaut.
        $idSociety = $content['idSociety'] ?? -1;

        //Je le stock dans mon setSociety
        $smartphone->setSociety($society->find($idSociety));

        //On vérifie les erreurs
        $errors = $validator->Validate($smartphone);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($smartphone);
        $em->flush();

        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonSmartphone = $serializer->serialize($smartphone, 'json', ['groups' => 'getSmartphones']);


        //On génère une url qui sera retourné pour avoir acces au nouveau smartphone rajouté
        $location = $urlGenerator->generate('app_smartphone_detail', ['id' => $smartphone->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        //Je renvoi ma variable location dans ma jsonResponse
        return new JsonResponse($jsonSmartphone, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_update', methods: ['PUT'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function updateSmartphone(Request $request, Smartphone $currentSmartphone, SerializerInterface $serializer, EntityManagerInterface $em, SocietyRepository $society): JsonResponse
    {
        //AbstractNormalizer fonction pour ecrire dans la donnée récupérée
        $updateSmartphone = $serializer->deserialize($request->getContent(), Smartphone::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentSmartphone]);


        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idAuthor. S'il n'est pas défini, alors on met -1 par défaut.
        $idSociety = $content['idSociety'] ?? -1;

        //Je le stock dans mon setSociety
        $updateSmartphone->setSociety($society->find($idSociety));

        $em->persist($updateSmartphone);
        $em->flush();

        //Je renvoi ma variable location dans ma jsonResponse
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
