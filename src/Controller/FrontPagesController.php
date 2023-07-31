<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    public function catalogDisplay(ProductRepository $productRepository, PromotionRepository $discountRepository, CategoryRepository $categoryRepository)
    {
        $products = $productRepository->findAll();
        $categories = $categoryRepository->findAll();
        $productsFormat = [];
        foreach($products as $product) {
            $discount = $discountRepository->discountAvailable($product->getId());
            if(empty($discount)) {
                $discount = null;
            } else {
                $discount = ($product->getPrice()) - ($product->getPrice() * $discount[0]->getDiscount() / 100);
            }
        $categoriesFormat = [];
        $i = 0;
        foreach($categories as $category) {
            $categoriesFormat[$i] = ['label' => $category->getLabel(), 'id' => $category->getId()];
            $i++;
        }
            $productsFormat[$product->getId()] = [
                'label' => $product->getLabel(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
                'unit' => $product->getUnit()->getLabel(),
                'price' => $product->getPrice(),
                'discount' => is_null($discount) ? null : round($discount,2)
            ];
        }

        return $this->render('catalog.html.twig',
        [
            'title' => 'Catalogue',
            'products' => $productsFormat,
            'categories' => $categoriesFormat
        ]);
    }

    #[Route('/get-product-by-category/{id}', name:'getProductByCategory')]
    public function getProduct($id, ProductRepository $productRepository, PromotionRepository $discountRepository):JsonResponse {
        $products = $productRepository->findBy(['category' => $id]);
        if(!$products) { return $this->json(['succes' => false]); }
        $productsFormat = [];
        $i = 0;
        foreach($products as $product) {
            $discount = $discountRepository->discountAvailable($product->getId());
            if(empty($discount)) {
                $discount = null;
            } else {
                $discount = ($product->getPrice()) - ($product->getPrice() * $discount[0]->getDiscount() / 100);
            }
            $productsFormat[$i] = [
                'label' => $product->getLabel(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
                'unit' => $product->getUnit()->getLabel(),
                'price' => $product->getPrice(),
                'discount' => is_null($discount) ? null : round($discount,2)
            ];
            $i++;
        }
        return $this->json([
             'succes' => true,
             'products' => $productsFormat,
        ]);
    }

}