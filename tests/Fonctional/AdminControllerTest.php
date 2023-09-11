<?php

namespace App\Tests\Fonctional;

use App\Repository\SellerRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
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

        // test the admin page
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
    }

    public function testVisitingWhileBadPassword(): void
    {
        $client = static::createClient();

        // simulate $testSeller being logged in
        $client->loginUser(static::loginUser(), 'Password123!');

        // test the admin page
        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(302);
    }
    
    public function testVisitingWithoutLoggedIn(): void
    {
        $client = static::createClient();
        
        //  test the admin page
        $client->request('GET', '/admin');
        $this->assertResponseRedirects();
        $this->assertResponseStatusCodeSame(302);
    }
    
    public function testProductPage(): void
    {
        $client = static::createClient();

        $client->loginUser(static::loginUser());

        //  test the admin/add-product page
        $client->request('GET', '/admin/add-product');
        $this->assertResponseIsSuccessful();
    }

    public function testCategoryPage(): void
    {
        $client = static::createClient();

        $client->loginUser(static::loginUser());

        //  test the admin/add-category page
        $client->request('GET', '/admin/add-category');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateCategory(): void
    {
        $client = static::createClient();
        $client->loginUser(static::loginUser());
        $crawler = $client->request('GET', '/admin/add-category');
        
        // select the button
        $form = $crawler->selectButton('category[submit]')->form();
        
        // retrieve the Form object for the form belonging to this button
        
        // set values on a form object
        $form['category[label]'] = 'Label Category';
        
        // submit the Form object
        $crawler = $client->submit($form);
        $this->assertResponseIsSuccessful();
    }

    public function testDiscountPage(): void
    {
        $client = static::createClient();

        $client->loginUser(static::loginUser());

        //  test the admin/add-discount page
        $client->request('GET', '/admin/add-discount');
        $this->assertResponseIsSuccessful();
    }
    public function testSellerPage(): void
    {
        $client = static::createClient();

        $client->loginUser(static::loginUser());

        //  test the admin/new-admin page
        $client->request('GET', '/admin/new-admin');
        $this->assertResponseIsSuccessful();
    }

    public function getProductAjax(): void
    {
        $client = static::createClient();
        $client->loginUser(static::loginUser());

        $client->request(
            'GET',
            '/admin/get-product/11',
            ['HTTP_Content-Type' => 'application/json']
        );

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful(200);

        // Get the crawler from the response
        $crawler = $client->getCrawler();

        // Find the query parameter 'product_id'
        $productId = $crawler->filter('')->extract(['product_id'])[0];
    }

}