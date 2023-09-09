<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Seller;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Form\CategoryType;
use App\Form\DiscountType;
use App\Form\ProductType;
use App\Form\SellerType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Repository\SellerRepository;
use App\Services\Functions;
use App\Services\FileUploader;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminController extends AbstractController {

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    // Page du dashboard Admin

    #[Route('/admin', name:'adminDashboard')]
    public function dashboardDisplay(ProductRepository $productRepository, PromotionRepository $discountRepository, CategoryRepository $categoryRepository)
    {
        // On récupère la date du jour et on affiche un compteur avec les informations pertinentes du catalogue
        $today = Date('Y-m-d');
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findAll();
        $discounts = $discountRepository->findDiscountAvailable($today);
        return $this->render('admin/admin.html.twig', [
            'title' => 'Dashboard',
            'categories' => count($categories),
            'products' => count($products),
            'discounts' => count($discounts),
        ]);
    }


    // Page d'ajout de catégorie

    #[Route('/admin/add-category', name:'addCategory')]
    public function addCategoryDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, CategoryRepository $categoryRepo, ValidatorInterface $validator)
    {
        $categories = $categoryRepo->findAll();
        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        $errors = $validator->validate($category);

        // Validation du formulaire

        try {
            if($categoryForm->isSubmitted() && $categoryForm->isValid()) {
    
                $entityManager->persist($category);
                $entityManager->flush();
                $flasher->addSuccess('La catégorie a bien été ajoutée');
                
                /* Si le formulaire n'a pas respecté les contraintes imposés par l'entity, 
                 on regarde pourquoi et on affiche un message en conséquence */

            } elseif ($categoryForm->isSubmitted() && $categoryForm->isValid() == false) {
                if(count($errors) > 0) {
                    $errorMessage = $errors->get(0)->getMessage();
                        $flasher->addError($errorMessage);
                } else {
                    $flasher->addError('Catégorie non enregistrée.');
                }
            }

        } catch(DriverException $e) {
            $flasher->addError('Une erreur s\'est produite. Catégorie non enregistrée.');
        }


        return $this->render('admin/addCategory.html.twig', [
            'categoryForm' => $categoryForm->createView(),
            'title' => 'Ajout Catégorie',
            'categories' => $categories,
        ]);
    }

#[Route('/admin/add-product', name:'addProduct')]
    public function addProductDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, ValidatorInterface $validator, FileUploader $fileUploader)
    {
        $product = new Product();

        $productForm = $this->createForm(ProductType::class, $product);

        $productForm->handleRequest($request);

        $errors = $validator->validate($product);

        // Validation du formulaire

        try {
            if($productForm->isSubmitted() && $productForm->isValid()) {
                // Si le formulaire est valide, on vient faire la validation de l'image
                
                $image = $productForm->get('image')->getData();
                $imageName = $fileUploader->upload($image, $product->getCategory());
                
                if($imageName !== null) {
                    $product->setImage($imageName);
                    $entityManager->persist($product);
                    $entityManager->flush();
                    $flasher->addSuccess('Le produit a bien été ajouté');
                } else {
                    $flasher->addError('Une erreur est survenue avec le fichier. Réessayez.');
                }
            } elseif ($productForm->isSubmitted() && $productForm->isValid() == false) {

                /* Si le formulaire n'a pas respecté les contraintes imposés par l'entity, 
                 on regarde pourquoi et on affiche un message en conséquence */

                if(count($errors) > 0) {
                    $errorMessage = $errors->get(0)->getMessage();
                        $flasher->addError($errorMessage);
                } else {
                    $errors = $productForm->getErrors(true);
                    if(count($errors) > 0) {
                        foreach($errors as $error) {
                        $flasher->addError($error->getMessage());
                        }
                    } else {
                        $flasher->addError('Produit non enregistré, une erreur s\'est produite.');
                    }
                }
            }
        } catch(DriverException $e) {
            dd($e);
            $flasher->addError('Une erreur s\'est produite. Produit non enregistrée.');
        }


        return $this->render('admin/addProduct.html.twig', [
            'productForm' => $productForm->createView(),
            'title' => 'Ajout Produit'
        ]);
    }

    #[Route('/admin/add-discount', name:'addDiscount')]
    public function addDiscountDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, ValidatorInterface $validator, PromotionRepository $promotionRepository)
    {
        $discount = new Promotion();

        $discountForm = $this->createForm(DiscountType::class, $discount);

        $discountForm->handleRequest($request);

        $errors = $validator->validate($discount);

        // Validation du formulaire

        try {

            if($discountForm->isSubmitted() && $discountForm->isValid()) {
                $isExist = $promotionRepository->validateDates($discount->getProduct()->getId(),$discount->getBegins(), $discount->getEnds());

                // On vérifie que la période de promotion n'est pas déjà existante.
                if(empty($isExist)) {
                    $entityManager->persist($discount);
                    $entityManager->flush();
                    $flasher->addSuccess('La promotion a bien été ajoutée');
                } else {
                    $existingBegins = $isExist[0]->getBegins();
                    $existingEnds = end($isExist)->getEnds();
                    $flasher->addError('Une promotion ou plusieurs sont programées : ' . date_format($existingBegins, 'd/m/Y') . ' => ' . date_format($existingEnds, 'd/m/Y'));
                }
            } elseif ($discountForm->isSubmitted() && $discountForm->isValid() == false) {
                if(count($errors) > 0) {
                    $errorMessage = $errors->get(0)->getMessage();
                        $flasher->addError($errorMessage);
                } else {
                    $flasher->addError('Promotion non enregistrée.');
                }
            }
        } catch(DriverException $e) {
            $flasher->addError('Une erreur s\'est produite. Promotion non enregistrée.');
        }

        return $this->render('admin/addDiscount.html.twig', [
            'discountForm' => $discountForm->createView(),
            'title' => 'Ajout Promotion',
        ]);
    }

    // Ajout d'un nouvel admin

    #[Route('/admin/new-admin', name:'newAdmin')]
    public function newAdminDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, UserPasswordHasherInterface $userPasswordHasherInterface, ValidatorInterface $validator, SellerRepository $sellerRepo)
    {
        $users = $sellerRepo->findAll();
        $usersExists = [];
        foreach($users as $user) {
            $usersExists[] = $user->getCode();
        }

        $seller = new Seller();

        $plainPassword = Functions::generatePassword();
        $hashpassword = $userPasswordHasherInterface->hashPassword($seller,$plainPassword);
        $seller->setPassword($hashpassword);
        
        $sellerForm = $this->createForm(SellerType::class, $seller);

        $sellerForm->handleRequest($request);

        $errors = $validator->validate($seller);

        try {
            if($sellerForm->isSubmitted() && $sellerForm->isValid()) {

                $entityManager->persist($seller);
                $entityManager->flush();
                $this->sendMail(mail: $seller->getEmail(), seller: $seller, password: $plainPassword);
                $flasher->addSuccess('Administrateur enregistré. Mot de passe envoyé par mail.');
    
            } elseif ($sellerForm->isSubmitted() && $sellerForm->isValid() == false) {
                if(count($errors) > 0) {
                    $errorMessage = $errors->get(0)->getMessage();
                        $flasher->addError($errorMessage);
                } else {
                    $flasher->addError('Administrateur non enregistré.');
                }
            }
        } catch(DriverException $e) {
            $flasher->addError('Une erreur s\'est produite. Administrateur non enregistré.');
        }


        return $this->render('admin/addAdmin.html.twig', [
            'sellerForm' => $sellerForm->createView(),
            'title' => 'Nouvel Admin',
            'users' => $usersExists
        ]);
    }

    // Fonction d'envoi de mail pour mot de passe

    private function sendMail(string $mail, Seller $seller, string $password)
    {
        $email = (new TemplatedEmail())
        ->from('mercadona@localhost.com')
        ->to($mail)
        ->subject('Bienvenue chez Mercadona !')
        ->htmlTemplate('mail.html.twig')
        ->context([
        'code' => $seller->getCode(),
        'password' => $password,
        ]);

        $this->mailer->send($email);
    }

    #[Route('/admin/get-product/{id}', name:'getProduct')]
    public function getProduct($id, ProductRepository $productRepository):JsonResponse {
        $product = $productRepository->find($id);
        if(!$product) { return $this->json(['succes' => false]); }
        $discounts = [];
        foreach ($product->getPromotions() as $discount) {
            $date = Date('Y-m-d');
            if(date_format($discount->getEnds(), 'Y-m-d') >= $date) {
                $discounts[] = [
                    'begins' => $discount->getBegins(),
                    'ends' => $discount->getEnds(),
                ];
            }
        }
        return $this->json([
             'name' => $product->getLabel(),
             'price' => $product->getPrice(),
             'discounts' => $discounts
        ]);
    }
}