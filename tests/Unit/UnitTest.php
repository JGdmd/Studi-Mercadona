<?php

namespace App\Test\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Unit as UnitEntity;

class UnitTest extends KernelTestCase
{

    public function testUnitWithLabel(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $unit = new UnitEntity();
        $unit->setLabel('Label #1');
        $errors = $container->get('validator')->validate($unit);

        $this->assertCount(0, $errors);
    }
    public function testUnitWithoutLabel(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $unit = new UnitEntity();
        $unit->setLabel('');
        $errors = $container->get('validator')->validate($unit);

        $this->assertCount(1, $errors);
    }
    public function testUnitWithManyWord(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $unit = new UnitEntity();
        $unit->setLabel('Label contenant plus de 50 caractères pour tester le validator');
        $errors = $container->get('validator')->validate($unit);

        $this->assertCount(1, $errors);
    }
}
