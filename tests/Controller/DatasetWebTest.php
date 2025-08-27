<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DatasetWebTest extends WebTestCase
{
    public function testDatasetIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/datasets/');

        self::assertResponseIsSuccessful();
    }
}
