<?php

namespace App\Controller;

use App\Entity\Dataset;
use App\Service\FileUploadService;
use App\Service\MktCalculatorService;
use App\Repository\DatasetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/datasets')]
class DatasetController extends AbstractController
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private MktCalculatorService $mktCalculator,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_dataset_index', methods: ['GET'])]
    public function index(DatasetRepository $datasetRepository): Response
    {
        $datasets = $datasetRepository->findAll();

        return $this->render('dataset/index.html.twig', [
            'datasets' => $datasets,
        ]);
    }

    #[Route('/upload', name: 'app_dataset_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            try {
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $request->files->get('file');
                $datasetName = $request->request->get('name');
                $description = $request->request->get('description');

                if (!$uploadedFile) {
                    throw new \InvalidArgumentException('No file uploaded');
                }

                if (empty($datasetName)) {
                    throw new \InvalidArgumentException('Dataset name is required');
                }

                $dataset = $this->fileUploadService->processUploadedFile(
                    $uploadedFile,
                    $datasetName,
                    $description
                );

                $this->addFlash('success', sprintf(
                    'Dataset "%s" uploaded successfully with %d temperature readings. MKT: %.4f°C',
                    $dataset->getName(),
                    $dataset->getTemperatureReadingsCount(),
                    $dataset->getMktValue()
                ));

                return $this->redirectToRoute('app_dataset_show', ['id' => $dataset->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Upload failed: ' . $e->getMessage());
            }
        }

        return $this->render('dataset/upload.html.twig');
    }

    #[Route('/{id}', name: 'app_dataset_show', methods: ['GET'])]
    public function show(Dataset $dataset): Response
    {
        $statistics = $this->mktCalculator->calculateStatistics($dataset);

        return $this->render('dataset/show.html.twig', [
            'dataset' => $dataset,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/{id}/calculate', name: 'app_dataset_calculate', methods: ['POST'])]
    public function recalculateMkt(Dataset $dataset): Response
    {
        try {
            $mktValue = $this->mktCalculator->calculateMkt($dataset);
            $dataset->setMktValue((string) $mktValue);

            $this->entityManager->flush();

            $this->addFlash('success', sprintf('MKT recalculated: %.4f°C', $mktValue));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Calculation failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_dataset_show', ['id' => $dataset->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_dataset_delete', methods: ['POST', 'DELETE'])]
    public function delete(Dataset $dataset): Response
    {
        try {
            $this->entityManager->remove($dataset);
            $this->entityManager->flush();

            $this->addFlash('success', 'Dataset deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Delete failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_dataset_index');
    }

    #[Route('/api/datasets', name: 'api_dataset_list', methods: ['GET'])]
    public function apiList(DatasetRepository $datasetRepository): JsonResponse
    {
        $datasets = $datasetRepository->findAll();
        $data = [];

        foreach ($datasets as $dataset) {
            $data[] = [
                'id' => $dataset->getId(),
                'name' => $dataset->getName(),
                'description' => $dataset->getDescription(),
                'filename' => $dataset->getFilename(),
                'fileType' => $dataset->getFileType(),
                'fileSize' => $dataset->getFileSize(),
                'uploadedAt' => $dataset->getUploadedAt()->format('Y-m-d H:i:s'),
                'mktValue' => $dataset->getMktValue(),
                'temperatureReadingsCount' => $dataset->getTemperatureReadingsCount(),
                'minTemperature' => $dataset->getMinTemperature(),
                'maxTemperature' => $dataset->getMaxTemperature(),
                'avgTemperature' => $dataset->getAvgTemperature(),
                'startTime' => $dataset->getStartTime()?->format('Y-m-d H:i:s'),
                'endTime' => $dataset->getEndTime()?->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/datasets/{id}', name: 'api_dataset_show', methods: ['GET'])]
    public function apiShow(Dataset $dataset): JsonResponse
    {
        $temperatureReadings = [];
        foreach ($dataset->getTemperatureReadings() as $reading) {
            $temperatureReadings[] = [
                'timestamp' => $reading->getTimestamp()->format('Y-m-d H:i:s'),
                'temperature' => $reading->getTemperature(),
                'temperatureKelvin' => $reading->getTemperatureKelvin(),
                'unixTimestamp' => $reading->getUnixTimestamp(),
            ];
        }

        $data = [
            'id' => $dataset->getId(),
            'name' => $dataset->getName(),
            'description' => $dataset->getDescription(),
            'filename' => $dataset->getFilename(),
            'fileType' => $dataset->getFileType(),
            'fileSize' => $dataset->getFileSize(),
            'uploadedAt' => $dataset->getUploadedAt()->format('Y-m-d H:i:s'),
            'mktValue' => $dataset->getMktValue(),
            'temperatureReadingsCount' => $dataset->getTemperatureReadingsCount(),
            'minTemperature' => $dataset->getMinTemperature(),
            'maxTemperature' => $dataset->getMaxTemperature(),
            'avgTemperature' => $dataset->getAvgTemperature(),
            'startTime' => $dataset->getStartTime()?->format('Y-m-d H:i:s'),
            'endTime' => $dataset->getEndTime()?->format('Y-m-d H:i:s'),
            'activationEnergy' => $dataset->getActivationEnergy(),
            'temperatureReadings' => $temperatureReadings,
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/calculate-mkt', name: 'api_calculate_mkt', methods: ['POST'])]
    public function apiCalculateMkt(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['temperatures']) || !is_array($data['temperatures'])) {
                throw new \InvalidArgumentException('temperatures array is required');
            }

            $temperatures = $data['temperatures'];
            $activationEnergy = $data['activationEnergy'] ?? 83.144;

            $mktValue = $this->mktCalculator->calculateMktFromArray($temperatures, $activationEnergy);

            return new JsonResponse([
                'mkt' => $mktValue,
                'activationEnergy' => $activationEnergy,
                'temperatureCount' => count($temperatures),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/datasets/{id}/export', name: 'api_dataset_export', methods: ['GET'])]
    public function apiExport(Dataset $dataset, Request $request): Response
    {
        $format = $request->query->get('format', 'csv');

        $temperatureReadings = [];
        foreach ($dataset->getTemperatureReadings() as $reading) {
            $temperatureReadings[] = [
                'timestamp' => $reading->getTimestamp()->format('Y-m-d H:i:s'),
                'temperature' => $reading->getTemperature(),
                'temperature_kelvin' => $reading->getTemperatureKelvin(),
                'unix_timestamp' => $reading->getUnixTimestamp(),
            ];
        }

        switch ($format) {
            case 'json':
                $content = json_encode([
                    'dataset' => [
                        'name' => $dataset->getName(),
                        'mkt_value' => $dataset->getMktValue(),
                        'activation_energy' => $dataset->getActivationEnergy(),
                    ],
                    'readings' => $temperatureReadings
                ], JSON_PRETTY_PRINT);
                $contentType = 'application/json';
                $extension = 'json';
                break;

            case 'csv':
            default:
                $content = "timestamp,temperature,temperature_kelvin,unix_timestamp\n";
                foreach ($temperatureReadings as $reading) {
                    $content .= implode(',', $reading) . "\n";
                }
                $contentType = 'text/csv';
                $extension = 'csv';
                break;
        }

        $filename = sprintf(
            '%s_export_%s.%s',
            preg_replace('/[^a-zA-Z0-9_-]/', '_', $dataset->getName()),
            date('Y-m-d_H-i-s'),
            $extension
        );

        $response = new Response($content);
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
