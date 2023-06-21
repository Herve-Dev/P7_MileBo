<?php

namespace App\Controller;

use App\Entity\Smartphone;
use App\Entity\Society;
use App\Repository\SmartphoneRepository;
use App\Repository\SocietyRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\serializer;

class SmartphoneController extends AbstractController
{
    #[Route('/api/smartphones', name: 'app_smartphone', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getAllSmartphones(SmartphoneRepository $smartphoneRepository, 
        SerializerInterface $serializer, 
        Request $request,
        TagAwareCacheInterface $cache): JsonResponse
    {
        //Methode pour utilisé JMS serializer 
        $context = SerializationContext::create()->setGroups(["getSmartphones"]);

        //Syteme de pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = "getAllSmartphone-" . $page . "-" .$limit;

        $jsonSmartphoneList = $cache->get($idCache, function (ItemInterface $item) use ($smartphoneRepository, $page, $limit, $serializer, $context){
            echo ("L'element n'est pas encore en cache \n");
            $item->tag("smartphonesCache");
            //Je met en place une expiration de mon cache de 60 secondes (securité pour ne pas avoir de données érroné en cas de manipulatio)
            $item->expiresAfter(60);
            $smartphoneList = $smartphoneRepository->findAllWithPagination($page, $limit);
            return $serializer->serialize($smartphoneList, 'json', $context);
        });

        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        //$jsonSmartphoneList = $serializer->serialize($smartphoneList, 'json', ['groups' => 'getSmartphones']);
        return new JsonResponse($jsonSmartphoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_detail', methods: ['GET'])]
    #[Security('is_granted("ROLE_CUSTOMERS") or is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function getDetailSmartphone(Smartphone $smartphone, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getSmartphones"]);
        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonSmartphone = $serializer->serialize($smartphone, 'json', $context);
        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_delete', methods: ['DELETE'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function deleteSmartphone(Smartphone $smartphone, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        //Suppression de la donnée en cache important pour ne pas récupérer les données alors qu'elle sont delete
        $cache->invalidateTags(["smartphonesCache"]);

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

        $context = SerializationContext::create()->setGroups(["getSmartphones"]);
        
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
        $jsonSmartphone = $serializer->serialize($smartphone, 'json', $context);

        //On génère une url qui sera retourné pour avoir acces au nouveau smartphone rajouté
        $location = $urlGenerator->generate('app_smartphone_detail', ['id' => $smartphone->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        //Je renvoi ma variable location dans ma jsonResponse
        return new JsonResponse($jsonSmartphone, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_update', methods: ['PUT'])]
    #[Security('is_granted("ROLE_ADMIN")', message: "Vous n'avez pas les droits suffisants pour accéder à cette ressource.")]
    public function updateSmartphone(Request $request, 
        Smartphone $currentSmartphone, 
        SerializerInterface $serializer,
        EntityManagerInterface $em, 
        SocietyRepository $society,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache): JsonResponse
    {
        $updateSmartphone = $serializer->deserialize($request->getContent(), Smartphone::class, 'json');
    
        $currentSmartphone->setPhoneBrand($updateSmartphone->getPhoneBrand());
        $currentSmartphone->setPhoneModel($updateSmartphone->getPhoneModel());
        $currentSmartphone->setPhoneDescription($updateSmartphone->getPhoneDescription());
        $currentSmartphone->setPhoneCreatedAt($updateSmartphone->getPhoneCreatedAt());

        $errors = $validator->validate($currentSmartphone);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idSociety. S'il n'est pas défini, alors on met -1 par défaut.
        $idSociety = $content['idSociety'] ?? -1;

        //Je le stock dans mon setSociety
        $currentSmartphone->setSociety($society->find($idSociety));

        $em->persist($currentSmartphone);
        $em->flush();

        $cache->invalidateTags(["smartphonesCache"]);

        //Je renvoi ma variable location dans ma jsonResponse
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
