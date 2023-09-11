<?php

namespace App\Tests\Fonctional;

use App\Repository\SellerRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{

    static function loginUser()
    {
        $sellerRepository = static::getContainer()->get(SellerRepository::class);

        // retrieve the test seller
        $testSeller = $sellerRepository->findOneBy(['code' => 'MER-12345-123']);

        return $testSeller;
    }

    public function testVisitingWhileLoggedIn(): void
    {
        $client = static::createClient();

        // simulate $testSeller being logged in
        $client->loginUser(static::loginUser());

        // test the app_login page
        $client->request('GET', '/auth');
        $this->assertResponseIsSuccessful();
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        // simulate $testSeller being logged in
        $client->loginUser(static::loginUser());

        // test the app_login page
        $client->request('GET', '/logout');
        $this->assertResponseStatusCodeSame(302);
        // We check who the redirect go to /auth after /logout
        $targetUrl = $client->getResponse()->headers->get('Location');
        $path = parse_url($targetUrl, PHP_URL_PATH);
        $this->assertSame('/auth', $path);
    }
}