<?php

namespace App\Test\Fonctional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Category;

class CategoryTest extends KernelTestCase
{
    public function testCategoryGetLabel(): void
    {
        $kernel = self::bootKernel();
        $category = static::getContainer()->get('doctrine.orm.entity_manager')->find(Category::class, 1);

        $this->assertTrue('Fruits & légumes' === $category->getLabel());
    }
}
