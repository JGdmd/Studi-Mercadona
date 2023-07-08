<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController {

    #[Route('/', name:'landingPage')]
    public function landingPageDisplay()
    {
        return $this->render('landingPage.html.twig',
        [
            'title' => 'Accueil',
        ]);
    }

    #[Route('/catalog', name:'catalog')]
    public function catalogDisplay()
    {
        return $this->render('landingPage.html.twig',
        [
            'title' => 'Catalogue',
        ]);
    }

}