<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController {

    #[Route('/admin', name:'adminDashboard')]
    public function dashboardDisplay()
    {
        dump('Dashboard');
        die();
    }

}