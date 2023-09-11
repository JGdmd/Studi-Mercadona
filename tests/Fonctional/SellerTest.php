<?php

namespace App\Test\Fonctional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Seller;

class SellerTest extends KernelTestCase
{
    public function testSellerGetInformations(): void
    {
        $kernel = self::bootKernel();
        $seller = static::getContainer()->get('doctrine.orm.entity_manager')->find(Seller::class, 2);

        $this->assertTrue('MER-12345-321' === $seller->getCode());
        $this->assertTrue('test@localhost.com' === $seller->getEmail());
        $this->assertTrue(2 === $seller->getId());
    }
}
