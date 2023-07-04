<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController {

    #[Route('/', name:'landingpage')]
    public function landingPageDisplay()
    {
        dump('landingPage');
        die();
    }

    #[Route('/catalog', name:'catalog')]
    public function catalogDisplay()
    {
        dump('catalogPage');
        die();
    }

}