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

    // Page d'accueil
    #[Route('/', name:'landingPage')]
    public function landingPageDisplay()
    {
        return $this->render('landingPage.html.twig',
        [
            'title' => 'Accueil',
        ]);
    }

    // Page d'accueil
    #[Route('/legalNotices', name:'legalNotices')]
    public function legalNoticesDisplay()
    {
        return $this->render('legalNotices.html.twig',
        [
            'title' => 'Mentions Légales',
        ]);
    }

    // Catalogue avec 12 produits par page
    #[Route('/catalog/{page}', name:'catalog',
    condition: "params['page'] > 0")]
    public function catalogDisplay($page, ProductRepository $productRepository, PromotionRepository $discountRepository, CategoryRepository $categoryRepository)
    {
        // On récupère l'ensemble du catalogue
        $totalProduct = $productRepository->findAll();
        $productsPerPage = 12;
        // On calcul le nombre max de pages du catalogue
        $maxPage = count($totalProduct) / $productsPerPage + 1;

        if($page > round($maxPage)) {
            $page = 1;
        }
        if($page + 1 <= round($maxPage)) {
            $next = $page + 1;
        } else {
            $next = null;
        }

        // On calcul la page suivante et la page précédente
        $next = ($page < $maxPage) ? $page + 1 : null;
        $previous = ($page > 1) ? $page - 1 : null;

        $offset = ($page - 1) * $productsPerPage;

        // On récupère uniquement les produits de la page concernée
        $products = $productRepository->findBy([], null, $productsPerPage, $offset);

        // On récupère les catégories de produits
        $categories = $categoryRepository->findAll();
        $productsFormat = [];

        // Pour chaque produits, on vient récupérer la promotion en cours
        foreach($products as $product) {
            $discount = $discountRepository->discountAvailable($product->getId());
            if(empty($discount)) {
                $discount = null;
            } else {
                $discount = ($product->getPrice()) - ($product->getPrice() * $discount[0]->getDiscount() / 100);
            }

            // On formatte les produits pour l'affichage en TWIG
            $productsFormat[$product->getId()] = $this->formatProduct($product, $discount);
        }

        // Pour chaque catégorie, on récupère le label et l'id
        $categoriesFormat = [];
        $i = 0;
        foreach($categories as $category) {
            $categoriesFormat[$i] = ['label' => ucfirst(strtolower($category->getLabel())), 'id' => $category->getId()];
            $i++;
        }
        
        return $this->render('catalog.html.twig',
        [
            'title' => 'Catalogue',
            'products' => $productsFormat,
            'categories' => $categoriesFormat,
            'next' => $next,
            'previous' => $previous
        ]);
    }

    // Pages des produits par catégorie
    #[Route('/get-products-by-category/{id}/{page}', name:"getProductsByCategory",
    condition: "params['page'] > 0")]
    public function getProductsByCategory($id, $page, ProductRepository $productRepository, PromotionRepository $discountRepository) {
        
        //On récupère l'ensemble des produits de la catégorie
        $totalProduct = $productRepository->findBy(['category' => $id]);
        $productsPerPage = 12;

        // On calcul le nombre max de pages du catalogue
        $maxPage = count($totalProduct) / $productsPerPage + 1;
        if($page > round($maxPage)) {
            $page = 1;
        }
        if($page + 1 <= round($maxPage)) {
            $next = $page + 1;
        } else {
            $next = null;
        }

        // On calcul la page suivante et la page précédente
        $next = ($page < $maxPage) ? $page + 1 : null;
        $previous = ($page > 1) ? $page - 1 : null;
        
        $offset = ($page - 1) * $productsPerPage;

        // On récupère uniquement les produits de la page concernée
        $products = $productRepository->findBy(['category' => $id], null, $productsPerPage, $offset);
        $productsFormat = [];

        // Pour chaque produits, on vient récupérer la promotion en cours
        foreach($products as $product) {
            $discount = $discountRepository->discountAvailable($product->getId());
            if(empty($discount)) {
                $discount = null;
            } else {
                $discount = ($product->getPrice()) - ($product->getPrice() * $discount[0]->getDiscount() / 100);
            }

            // On formatte les produits pour l'affichage en TWIG
            $productsFormat[$product->getId()] = $this->formatProduct($product, $discount);
        }
        return $this->render('catalog_category.html.twig',
        [
            'title' => 'Catalogue',
            'products' => $productsFormat,
            'next' => $next,
            'previous' => $previous,
            'category' => $id
        ]);
    }

    // Appel Ajax pour le filtre des 12 premiers produits par catégorie
    #[Route('/get-products-by-category-ajax/{id}', name:'getProductsByCategoryAjax')]
    public function getProductsAjax($id, ProductRepository $productRepository, PromotionRepository $discountRepository):JsonResponse {

        //On récupère l'ensemble des produits de la catégorie
        $totalProducts = $productRepository->findBy(['category' => $id]);
        
        // On retourne false si aucun produit dans la catégorie
        if(!$totalProducts) { return $this->json(['succes' => false]); }
        
        // On prévoit l'affichage d'un bouton next si plus que 12 produits
        if(count($totalProducts) > 12) {
            $products = $productRepository->findBy(['category' => $id],null,12);
            $nextPage = true;
        } else {
            $products = $totalProducts;
            $nextPage = false;
        }

        // Pour chaque produits, on vient récupérer la promotion en cours
        $productsFormat = [];
        $i = 0;
        foreach($products as $product) {
            $discount = $discountRepository->discountAvailable($product->getId());
            if(empty($discount)) {
                $discount = null;
            } else {
                $discount = ($product->getPrice()) - ($product->getPrice() * $discount[0]->getDiscount() / 100);
            }

            // On formatte les produits pour l'affichage en TWIG
            $productsFormat[$i] = $this->formatProduct($product, $discount);
            $i++;
        }
        return $this->json([
             'succes' => true,
             'nextPage' => $nextPage,
             'products' => $productsFormat
        ]);
    }

    // Fonction de formattage des produits pour le TWIG
    private function formatProduct($product, $discount) {
        return [
            'label' => ucfirst(strtolower($product->getLabel())),
            'description' => ucfirst(strtolower($product->getDescription())),
            'image' => $product->getImage(),
            'unit' => ucfirst(strtolower($product->getUnit()->getLabel())),
            'price' => $product->getPrice(),
            'discount' => is_null($discount) ? null : round($discount, 2)
            ];
    }

}