<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatasetControllerApiTest extends WebTestCase
{
    public function testApiCalculateMkt(): void
    {
        $client = static::createClient();

        $data = [
            'temperatures' => [20.0, 25.0, 30.0],
            'activationEnergy' => 83.144
        ];

        $client->request(
            'POST',
            '/datasets/api/calculate-mkt',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('mkt', $responseData);
        $this->assertArrayHasKey('activationEnergy', $responseData);
        $this->assertArrayHasKey('temperatureCount', $responseData);

        $this->assertEquals(83.144, $responseData['activationEnergy']);
        $this->assertEquals(3, $responseData['temperatureCount']);
        $this->assertGreaterThan(20.0, $responseData['mkt']);
        $this->assertLessThan(35.0, $responseData['mkt']);
    }

    public function testApiCalculateMktWithInvalidData(): void
    {
        $client = static::createClient();

        $data = [
            'temperatures' => [], // Empty array should cause error
            'activationEnergy' => 83.144
        ];

        $client->request(
            'POST',
            '/datasets/api/calculate-mkt',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testApiDatasetsList(): void
    {
        $client = static::createClient();

        $client->request('GET', '/datasets/api/datasets');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testHomePage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mean Kinetic Temperature Calculator');
    }

    public function testDatasetsPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/datasets/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2');
    }

    public function testUploadPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/datasets/upload');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[enctype="multipart/form-data"]');
    }

    public function testAboutPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mean Kinetic Temperature');
    }
}
