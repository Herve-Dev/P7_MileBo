<?php

namespace App\Controller;

use App\Repository\SmartphoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SmartphoneController extends AbstractController
{
    #[Route('/api/smartphones', name: 'app_smartphone', methods: ['GET'])]
    public function getAllSmartphones(SmartphoneRepository $smartphoneRepository): JsonResponse
    {
        $smartphoneList = $smartphoneRepository->findAll();

        return new JsonResponse([
            'smartphones' => $smartphoneList,
        ]);
    }
}
