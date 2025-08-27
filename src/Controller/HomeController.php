<?php

namespace App\Controller;

use App\Repository\DatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(DatasetRepository $datasetRepository): Response
    {
        $datasets = $datasetRepository->findBy([], ['uploadedAt' => 'DESC'], 5);
        $totalDatasets = $datasetRepository->count([]);

        return $this->render('home/index.html.twig', [
            'recent_datasets' => $datasets,
            'total_datasets' => $totalDatasets,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }
}
