<?php

namespace App\Test\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Promotion;
use App\Entity\Unit as UnitEntity;
use App\Entity\Category as CategoryEntity;
use App\Test\Unit\ProductTest;
use DateTime;
use DateTimeImmutable;

class DiscountTest extends KernelTestCase
{

    static function setDiscount()
    {
        $product = ProductTest::setProduct();
        $product->setPrice(100);
        $discount = new Promotion();
        $discount->setProduct($product)
            ->setBegins(new DateTimeImmutable())
            ->setEnds(new DateTimeImmutable())
            ->setDiscount(25);
        return $discount;
    }
    
    public function testCreateDisount(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $discount = static::setDiscount();
        $errors = $container->get('validator')->validate($discount);
        $this->assertCount(0, $errors);
    }

    public function testDiscountCalc(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $discount = static::setDiscount();
        $product = $discount->getProduct();
        $priceProduct = $product->getPrice();
        $realPrice = $priceProduct - (($priceProduct * $discount->getDiscount()) / 100);
        $this->assertTrue(75 == $realPrice);
    }
}
