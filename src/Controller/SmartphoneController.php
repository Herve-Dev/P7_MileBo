<?php

namespace App\Controller;

use App\Entity\Smartphone;
use App\Repository\SmartphoneRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SmartphoneController extends AbstractController
{
    #[Route('/api/smartphones', name: 'app_smartphone', methods: ['GET'])]
    public function getAllSmartphones(SmartphoneRepository $smartphoneRepository, SerializerInterface $serializer): JsonResponse
    {
        $smartphoneList = $smartphoneRepository->findAll();

        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonSmartphoneList = $serializer->serialize($smartphoneList, 'json', ['groups' => 'getSmartphones']);
        return new JsonResponse($jsonSmartphoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/smartphone/{id}', name: 'app_smartphone_detail', methods: ['GET'])]
    public function getDetailSmartphone(Smartphone $smartphone, SerializerInterface $serializer)
    {
        //On précise le contexte avec la classe Groups serializer appelé dans mes entités
        $jsonSmartphone = $serializer->serialize($smartphone, 'json', ['groups' => 'getSmartphones']);
        return new JsonResponse($jsonSmartphone, Response::HTTP_OK, [], true);
    }
}
