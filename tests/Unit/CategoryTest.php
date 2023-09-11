<?php

namespace App\Test\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Category;

class CategoryTest extends KernelTestCase
{

    public function testCategoryWithLabel(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $category = new Category();
        $category->setLabel('Label #1');
        $errors = $container->get('validator')->validate($category);

        $this->assertCount(0, $errors);
    }
    public function testCategoryWithoutLabel(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $category = new Category();
        $category->setLabel('');
        $errors = $container->get('validator')->validate($category);

        $this->assertCount(1, $errors);
    }
    public function testCategoryWithManyWord(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $category = new Category();
        $category->setLabel('Label contenant plus de 50 caractères pour tester le validator');
        $errors = $container->get('validator')->validate($category);

        $this->assertCount(1, $errors);
    }
}
