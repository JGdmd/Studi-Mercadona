<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use function PHPUnit\Framework\isEmpty;

class FrontPagesController extends AbstractController {

    #[Route('/', name:'landingPage')]
    public function landingPageDisplay()
    {
        return $this->render('landingPage.html.twig',
        [
            'title' => 'Accueil',
        ]);
    }

    #[Route('/catalog', name:'catalog')]
    public function catalogDisplay(ProductRepository $productRepository, PromotionRepository $discountRepository)
    {
        $products = $productRepository->findAll();
        $productsFormat = [];
        foreach($products as $product) {
            $discount = $discountRepository->discountAvailable($product->getId());
            if(empty($discount)) {
                $discount = null;
            } else {
                $discount = ($product->getPrice()) - ($product->getPrice() * $discount[0]->getDiscount() / 100);
            }
            $productsFormat[$product->getId()] = [
                'label' => $product->getLabel(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
                'price' => $product->getPrice(),
                'discount' => is_null($discount) ? null : round($discount,2)
            ];
        }
        
        return $this->render('catalog.html.twig',
        [
            'title' => 'Catalogue',
            'products' => $productsFormat
        ]);
    }

}