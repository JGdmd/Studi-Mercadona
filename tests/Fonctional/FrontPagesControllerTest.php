<?php

namespace App\Tests\Fonctional;

use App\Repository\SellerRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontPagesControllerTest extends WebTestCase
{

    public function testLandingPage(): void
    {
        $client = static::createClient();

        //  test the admin/new-admin page
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testCatalogPage(): void
    {
        $client = static::createClient();

        //  test the admin/catalog page
        $client->request('GET', '/catalog/1');
        $this->assertResponseIsSuccessful();
    }

    public function testFilter(): void
    {
        $client = static::createClient();

        //  test the admin/get-products-by-category page
        $client->request('GET', '/get-products-by-category/1/1');
        $this->assertResponseIsSuccessful();
    }

    public function testFilterAjax(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/get-products-by-category-ajax/1',
            ['HTTP_Content-Type' => 'application/json']
        );

        $this->assertResponseIsSuccessful(200);
    }

}