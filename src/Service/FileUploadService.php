<?php

namespace App\Service;

use App\Entity\Dataset;
use App\Entity\TemperatureReading;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManagerInterface;

class FileUploadService
{
    private EntityManagerInterface $entityManager;
    private MktCalculatorService $mktCalculator;
    private string $uploadDirectory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MktCalculatorService $mktCalculator,
        string $uploadDirectory = 'uploads'
    ) {
        $this->entityManager = $entityManager;
        $this->mktCalculator = $mktCalculator;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * Process uploaded file and create dataset with temperature readings
     *
     * @param UploadedFile $file
     * @param string $datasetName
     * @param string|null $description
     * @return Dataset
     */
    public function processUploadedFile(UploadedFile $file, string $datasetName, ?string $description = null): Dataset
    {
        // Validate file
        $this->validateFile($file);

        // Store file size before moving the file
        $fileSize = $file->getSize();
        $fileExtension = $file->getClientOriginalExtension();

        // Create upload directory if it doesn't exist
        $uploadPath = $this->getUploadPath();
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Move file to upload directory
        $filename = $this->generateUniqueFilename($file);
        $file->move($uploadPath, $filename);

        // Parse file content
        $temperatureData = $this->parseFile($uploadPath . '/' . $filename, $fileExtension);

        // Create dataset
        $dataset = $this->createDataset($file, $datasetName, $description, $filename, $fileSize, $fileExtension);

        // Create temperature readings
        $this->createTemperatureReadings($dataset, $temperatureData);

        // Calculate statistics and MKT
        $this->updateDatasetStatistics($dataset);

        // Save to database
        $this->entityManager->persist($dataset);
        $this->entityManager->flush();

        return $dataset;
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @throws \InvalidArgumentException
     */
    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('File upload failed');
        }

        $allowedExtensions = ['csv', 'xml', 'yml', 'yaml', 'json'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('File type not supported. Allowed types: ' . implode(', ', $allowedExtensions));
        }

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('File size too large. Maximum size is 10MB');
        }
    }

    /**
     * Parse file content based on file type
     *
     * @param string $filePath
     * @param string $extension
     * @return array
     */
    private function parseFile(string $filePath, string $extension): array
    {
        switch (strtolower($extension)) {
            case 'csv':
                return $this->parseCsvFile($filePath);
            case 'xml':
                return $this->parseXmlFile($filePath);
            case 'yml':
            case 'yaml':
                return $this->parseYamlFile($filePath);
            case 'json':
                return $this->parseJsonFile($filePath);
            default:
                throw new \InvalidArgumentException('Unsupported file type: ' . $extension);
        }
    }

    /**
     * Parse CSV file
     *
     * @param string $filePath
     * @return array
     */
    private function parseCsvFile(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \InvalidArgumentException('Cannot read CSV file');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            throw new \InvalidArgumentException('CSV file is empty or invalid');
        }

        // Normalize header names
        $header = array_map('strtolower', $header);
        $timestampIndex = $this->findTimestampColumn($header);
        $temperatureIndex = $this->findTemperatureColumn($header);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= max($timestampIndex + 1, $temperatureIndex + 1)) {
                $timestamp = $this->parseTimestamp($row[$timestampIndex]);
                $temperature = $this->parseTemperature($row[$temperatureIndex]);

                if ($timestamp && $temperature !== null && $this->mktCalculator->isValidTemperature($temperature)) {
                    $data[] = [
                        'timestamp' => $timestamp,
                        'temperature' => $temperature,
                        'original_data' => implode(',', $row)
                    ];
                }
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Parse XML file
     *
     * @param string $filePath
     * @return array
     */
    private function parseXmlFile(string $filePath): array
    {
        $data = [];
        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            throw new \InvalidArgumentException('Cannot parse XML file');
        }

        // Look for temperature readings in various XML structures
        foreach ($xml->xpath('//reading | //measurement | //data') as $reading) {
            $timestamp = null;
            $temperature = null;

            // Try different attribute/element names for timestamp
            foreach (['timestamp', 'time', 'date', '@timestamp', '@time'] as $timestampField) {
                if (isset($reading->$timestampField)) {
                    $timestamp = $this->parseTimestamp((string)$reading->$timestampField);
                    break;
                }
            }

            // Try different attribute/element names for temperature
            foreach (['temperature', 'temp', 'value', '@temperature', '@temp'] as $tempField) {
                if (isset($reading->$tempField)) {
                    $temperature = $this->parseTemperature((string)$reading->$tempField);
                    break;
                }
            }

            if ($timestamp && $temperature !== null && $this->mktCalculator->isValidTemperature($temperature)) {
                $data[] = [
                    'timestamp' => $timestamp,
                    'temperature' => $temperature,
                    'original_data' => $reading->asXML()
                ];
            }
        }

        return $data;
    }

    /**
     * Parse YAML file
     *
     * @param string $filePath
     * @return array
     */
    private function parseYamlFile(string $filePath): array
    {
        $yamlData = Yaml::parseFile($filePath);
        return $this->parseArrayData($yamlData);
    }

    /**
     * Parse JSON file
     *
     * @param string $filePath
     * @return array
     */
    private function parseJsonFile(string $filePath): array
    {
        $jsonContent = file_get_contents($filePath);
        $jsonData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON file: ' . json_last_error_msg());
        }

        return $this->parseArrayData($jsonData);
    }

    /**
     * Parse array data (for JSON/YAML)
     *
     * @param array $data
     * @return array
     */
    private function parseArrayData(array $data): array
    {
        $result = [];

        // Handle different data structures
        if (isset($data['readings']) && is_array($data['readings'])) {
            $data = $data['readings'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $timestamp = null;
            $temperature = null;

            // Find timestamp
            foreach (['timestamp', 'time', 'date'] as $timestampField) {
                if (isset($item[$timestampField])) {
                    $timestamp = $this->parseTimestamp($item[$timestampField]);
                    break;
                }
            }

            // Find temperature
            foreach (['temperature', 'temp', 'value'] as $tempField) {
                if (isset($item[$tempField])) {
                    $temperature = $this->parseTemperature($item[$tempField]);
                    break;
                }
            }

            if ($timestamp && $temperature !== null && $this->mktCalculator->isValidTemperature($temperature)) {
                $result[] = [
                    'timestamp' => $timestamp,
                    'temperature' => $temperature,
                    'original_data' => json_encode($item)
                ];
            }
        }

        return $result;
    }

    /**
     * Find timestamp column in CSV header
     */
    private function findTimestampColumn(array $header): int
    {
        $timestampFields = ['timestamp', 'time', 'date', 'datetime'];
        foreach ($timestampFields as $field) {
            $index = array_search($field, $header);
            if ($index !== false) {
                return $index;
            }
        }

        // Default to first column if no timestamp field found
        return 0;
    }

    /**
     * Find temperature column in CSV header
     */
    private function findTemperatureColumn(array $header): int
    {
        $temperatureFields = ['temperature', 'temp', 'value'];
        foreach ($temperatureFields as $field) {
            $index = array_search($field, $header);
            if ($index !== false) {
                return $index;
            }
        }

        // Default to second column if no temperature field found
        return 1;
    }

    /**
     * Parse timestamp from various formats
     */
    private function parseTimestamp($timestampStr): ?\DateTimeImmutable
    {
        if (empty($timestampStr)) {
            return null;
        }

        // Try Unix timestamp first
        if (is_numeric($timestampStr)) {
            return new \DateTimeImmutable('@' . $timestampStr);
        }

        // Try common date formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i:s\Z',
            'Y-m-d',
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'm/d/Y H:i:s'
        ];

        foreach ($formats as $format) {
            try {
                $date = \DateTimeImmutable::createFromFormat($format, $timestampStr);
                if ($date !== false) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try generic parsing
        try {
            return new \DateTimeImmutable($timestampStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse temperature value
     */
    private function parseTemperature($temperatureStr): ?float
    {
        if (empty($temperatureStr)) {
            return null;
        }

        // Remove any non-numeric characters except decimal point and minus sign
        $cleaned = preg_replace('/[^0-9.\-]/', '', $temperatureStr);

        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        return null;
    }

    /**
     * Create dataset entity
     */
    private function createDataset(UploadedFile $file, string $name, ?string $description, string $filename, int $fileSize, string $fileExtension): Dataset
    {
        $dataset = new Dataset();
        $dataset->setName($name);
        $dataset->setDescription($description);
        $dataset->setFilename($filename);
        $dataset->setFileType($fileExtension);
        $dataset->setFileSize($fileSize);
        $dataset->setUploadedAt(new \DateTimeImmutable());

        return $dataset;
    }

    /**
     * Create temperature reading entities
     */
    private function createTemperatureReadings(Dataset $dataset, array $temperatureData): void
    {
        foreach ($temperatureData as $data) {
            $reading = new TemperatureReading();
            $reading->setDataset($dataset);
            $reading->setTimestamp($data['timestamp']);
            $reading->setTemperature((string) $data['temperature']);
            $reading->setTemperatureKelvin((string) ($data['temperature'] + 273.15));
            $reading->setUnixTimestamp($data['timestamp']->getTimestamp());
            $reading->setOriginalData($data['original_data']);

            $dataset->addTemperatureReading($reading);
            $this->entityManager->persist($reading);
        }
    }

    /**
     * Update dataset statistics
     */
    private function updateDatasetStatistics(Dataset $dataset): void
    {
        $statistics = $this->mktCalculator->calculateStatistics($dataset);
        $mktValue = $this->mktCalculator->calculateMkt($dataset);

        $dataset->setTemperatureReadingsCount($statistics['count']);
        $dataset->setMinTemperature((string) $statistics['min']);
        $dataset->setMaxTemperature((string) $statistics['max']);
        $dataset->setAvgTemperature((string) $statistics['average']);
        $dataset->setMktValue((string) $mktValue);

        // Set start and end times
        $readings = $dataset->getTemperatureReadings();
        if (!$readings->isEmpty()) {
            $timestamps = [];
            foreach ($readings as $reading) {
                $timestamps[] = $reading->getTimestamp();
            }
            $dataset->setStartTime(min($timestamps));
            $dataset->setEndTime(max($timestamps));
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Get upload path
     */
    private function getUploadPath(): string
    {
        return __DIR__ . '/../../public/' . $this->uploadDirectory;
    }
}
