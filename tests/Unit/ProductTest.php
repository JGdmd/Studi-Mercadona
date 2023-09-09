<?php

namespace App\Test\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Product;
use App\Entity\Unit as UnitEntity;
use App\Entity\Category as CategoryEntity;

class ProductTest extends KernelTestCase
{

    static function setProduct()
    {
        $category = static::getContainer()->get('doctrine.orm.entity_manager')->find(CategoryEntity::class, 1);
        $unitProduct = static::getContainer()->get('doctrine.orm.entity_manager')->find(UnitEntity::class, 4);
        $product = new Product();
        $product->setLabel('Product #1')
        ->setDescription('A description with few words')
        ->setCategory($category)
        ->setPrice(2.25)
        ->setUnit($unitProduct)
        ->setImage('path_of_image_random');
        return $product;
    }
    
    public function testCreateProduct(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $product = static::setProduct();
        $errors = $container->get('validator')->validate($product);
        $this->assertCount(0, $errors);
    }
    
    public function testProductWithNoManyInformation(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $product = static::setProduct();
        $product->setLabel('');
        $product->setDescription('');
        $errors = $container->get('validator')->validate($product);
        
        $this->assertCount(2, $errors);
    }

    public function testProductFree(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $product = static::setProduct();
        $product->setPrice(0);
        $errors = $container->get('validator')->validate($product);
        $this->assertCount(1, $errors);
    }
}
