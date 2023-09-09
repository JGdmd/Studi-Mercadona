<?php

namespace App\Test\Fonctional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Product;
use App\Test\Unit\DiscountTest;

class ProductTest extends KernelTestCase
{
    public function testProductGetLabelAndPrice(): void
    {
        $kernel = self::bootKernel();
        $product = static::getContainer()->get('doctrine.orm.entity_manager')->find(Product::class, 15);

        $this->assertTrue('Wrap Sodebo' === $product->getLabel());
        $this->assertTrue(3.25 === $product->getPrice());
    }

    public function testGetPromotion(): void
    {
        $kernel = self::bootKernel();
        $product = static::getContainer()->get('doctrine.orm.entity_manager')->find(Product::class, 15);

        $this->assertTrue(!empty($product->getPromotions()));
    }

    public function testSetPromotionAndRemove(): void
    {
        $kernel = self::bootKernel();
        $discount = DiscountTest::setDiscount();
        $product = static::getContainer()->get('doctrine.orm.entity_manager')->find(Product::class, 15);
        $discountsOfProduct = $product->getPromotions();
        foreach($discountsOfProduct as $discountOfProduct) {
            $product->removePromotion($discountOfProduct);
        }
        $product->addPromotion($discount);
        $this->assertContains($discount, $product->getPromotions());
        
        $discount = DiscountTest::setDiscount();
        $product->removePromotion($discount);
        $this->assertNotContains($discount, $product->getPromotions());
    }
}
