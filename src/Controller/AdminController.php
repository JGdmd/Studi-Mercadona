<?php

namespace App\Controller;

use App\Entity\Seller;
use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\SellerType;
use App\Form\TicketType;
use App\Entity\Promotion;
use App\Form\ProductType;
use App\Form\CategoryType;
use App\Form\DiscountType;
use Flasher\Prime\Flasher;
use App\Services\Functions;
use App\Services\FileUploader;
use App\Repository\SellerRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

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
            $flasher->addError('Erreur MER-001. Catégorie non enregistrée.');
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
                        $flasher->addError('Produit non enregistré, erreur MER-002');
                    }
                }
            }
        } catch(DriverException $e) {
            dd($e);
            $flasher->addError('Erreur MER-001. Produit non enregistrée.');
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
                    $flasher->addError('Promotion non enregistrée. Erreur MER-002');
                }
            }
        } catch(DriverException $e) {
            $flasher->addError('Erreur MER-001 s\'est produite. Promotion non enregistrée.');
        }

        return $this->render('admin/addDiscount.html.twig', [
            'discountForm' => $discountForm->createView(),
            'title' => 'Ajout Promotion',
        ]);
    }

    // Ajout d'un nouvel admin

    #[Route('/admin/new-admin', name:'newAdmin')]
    public function newAdminDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, UserPasswordHasherInterface $userPasswordHasherInterface, ValidatorInterface $validator, SellerRepository $sellerRepo, GoogleAuthenticatorInterface $authenticator)
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

        $secret = $authenticator->generateSecret();
        
        $seller->setGoogleAuthenticatorSecret($secret);
        $seller->setEnable2FA(false);
        
        try {
            try {
                if($sellerForm->isSubmitted() && $sellerForm->isValid()) {
    
                    $entityManager->persist($seller);
                    $entityManager->flush();
                    $this->sendMail(mail: $seller->getEmail(), seller: $seller, password: $plainPassword);
                    $flasher->addWarning('Administrateur enregistré. Mot de passe envoyé par mail. Paramétrage du 2FA à votre prochaine connexion.');
        
                } elseif ($sellerForm->isSubmitted() && $sellerForm->isValid() == false) {
                    if(count($errors) > 0) {
                        $errorMessage = $errors->get(0)->getMessage();
                            $flasher->addError($errorMessage);
                    } else {
                        $flasher->addError('Administrateur non enregistré. Erreur MER-002');
                    }
                }
            } catch(DriverException $e) {
                $flasher->addError('Erreur MER-001 s\'est produite. Administrateur non enregistré.');
            }
        } catch(TransportException $t) {
            $flasher->addError('Erreur MER-003 s\'est produite. Administrateur non enregistré.');
        }


        return $this->render('admin/addAdmin.html.twig', [
            'sellerForm' => $sellerForm->createView(),
            'title' => 'Nouvel Admin',
            'users' => $usersExists
        ]);
    }

    // Paramètres

    #[Route('/admin/params', name:'params')]
    public function paramsDisplay(ValidatorInterface $validator, Request $request, Flasher $flasher, SellerRepository $sellerRepository)
    {

        $ticketForm = $this->createForm(TicketType::class);

        $ticketForm->handleRequest($request);

        $errors = $validator->validate($ticketForm);

        $user = $this->getUser()->getCode();
        $seller = $sellerRepository->findOneBy(['code' => $user]);

        if($ticketForm->isSubmitted() && $ticketForm->isValid()) {

            $content = filter_var($ticketForm->get('content')->getData(), FILTER_DEFAULT);

            $success = $this->sendMailToSupport($seller, $content);
            if($success) {
                $flasher->addSuccess('Ticket envoyé');
            } else {
                $flasher->addError('Ticket non envoyé. Contactez le support par téléhpone. Erreur MER-003');
            }
            $url = $this->generateUrl('params') . '#';
            return new RedirectResponse($url);

        } elseif ($ticketForm->isSubmitted() && $ticketForm->isValid() == false) {
            if(count($errors) > 0) {
                $errorMessage = $errors->get(0)->getMessage();
                $flasher->addError($errorMessage);
            } else {
                $flasher->addError('Erreur MER-002 s\'est produite. Contactez le support par téléhpone');
                 return $this->redirectToRoute('params');
            }
        }

        return $this->render('admin/params.html.twig', [
            'title' => 'Paramètres',
            'ticket' => $ticketForm
        ]);
    }


    #[Route('/admin/statistics', name:'stats')]
    public function statsDisplay(CategoryRepository $categoryRepository, ProductRepository $productRepository, PromotionRepository $discountRepository)
    {
        $categories = $categoryRepository->findAll();
        $productsByCategory = [];
        foreach ($categories as $category) {
            $products = $productRepository->findBy(['category' => $category->getId()]);
            $productsByCategory[$category->getLabel()] = $products;
        }
        
        $countsByPrice = ['2' => 0, '5' => 0, '10' => 0, '20' => 0, '50' => 0, '51' => 0];
        $count = [];
        foreach ($productsByCategory as $categoryTitle => $products) {
            $count[$categoryTitle] = count($products);
            foreach ($products as $product) {
                $price = $product->getPrice();
                switch($price) {
                    case $price >= 0 && $price < 2 :
                        $countsByPrice['2']++;
                        break;
                    case $price >= 2 && $price < 5 :
                        $countsByPrice['5']++;
                        break;
                    case $price >= 5 && $price < 10 :
                        $countsByPrice['10']++;
                        break;
                    case $price >= 10 && $price < 20 :
                        $countsByPrice['20']++;
                        break;
                    case $price >= 20 && $price < 50 :
                        $countsByPrice['50']++;
                        break;
                    case $price >= 50 : 
                        $countsByPrice['51']++;
                        break;
                }
            }
        }
        
        $discounts = $discountRepository->findAll();
        $countsByPercent= ['10' => 0, '20' => 0, '30' => 0, '40' => 0, '50' => 0, '60' => 0, '70' => 0, '80' => 0, '90' => 0];

        foreach ($discounts as $discount) {
            $percent = $discount->getDiscount();
            switch($percent) {
                case $percent > 0 && $percent < 10 :
                    $countsByPercent['0']++;
                    break;
                case $percent >= 10 && $percent < 20 :
                    $countsByPercent['10']++;
                    break;
                case $percent >= 20 && $percent < 30 :
                    $countsByPercent['20']++;
                    break;
                case $percent >= 30 && $percent < 40 :
                    $countsByPercent['30']++;
                    break;
                case $percent >= 40 && $percent < 50 :
                    $countsByPercent['40']++;
                    break;
                case $percent >= 50 && $percent < 60 :
                    $countsByPercent['50']++;
                    break;
                case $percent >= 60 && $percent < 70 :
                    $countsByPercent['60']++;
                    break;
                case $percent >= 70 && $percent < 80 :
                    $countsByPercent['70']++;
                    break;
                case $percent >= 80 && $percent < 90 :
                    $countsByPercent['80']++;
                    break;
                case $percent > 90 :
                    $countsByPercent['90']++;
                    break;
            }
        }

        return $this->render('admin/stats.html.twig', [
            'title' => 'Statistiques',
            'countByCategories' => $count,
            'percents' => $countsByPercent,
            'prices' => $countsByPrice
        ]);
    }

    #[Route('/admin/newpassword', name:'newPass')]
    public function newPassword(Request $request, SellerRepository $sellerRepository, Flasher $flasher, UserPasswordHasherInterface $userPasswordHasherInterface, EntityManagerInterface $entityManager)
    {
        try {
            
            try {

                $user = $this->getUser()->getCode();
                $seller = $sellerRepository->findOneBy(['code' => $user]);
                $plainPassword = Functions::generatePassword();
                $hashpassword = $userPasswordHasherInterface->hashPassword($seller,$plainPassword);
                $seller->setPassword($hashpassword);
                $this->sendMailNewPassword(mail: $seller->getEmail(), seller: $seller, password: $plainPassword);
                $entityManager->persist($seller);
                $entityManager->flush();

            } catch(DriverException $e) {
                $flasher->addError('UErreur MER-001 \'est produite. Mot de passe non changé');
                return $this->redirectToRoute('params');
            }
        } catch(TransportException $d) {
            $flasher->addError('Erreur MER-003 \'est produite. Mot de passe non changé');
            return $this->redirectToRoute('params');
        }
        $flasher->addSuccess('Mot de passe changé, envoyé par mail');
        return $this->redirectToRoute('params');
    }


    // Fonction d'envoi de mail pour mot de passe

    private function sendMail(string $mail, Seller $seller, string $password)
    {
        $email = (new TemplatedEmail())
        ->from('admin@studi-mercadona.devexploris.com')
        ->to($mail)
        ->subject('Bienvenue chez Mercadona !')
        ->htmlTemplate('mail.html.twig')
        ->context([
            'newPass' => false,
            'ticket' => false,
            'code' => $seller->getCode(),
            'password' => $password,
        ]);

        $this->mailer->send($email);
    }

    private function sendMailNewPassword(string $mail, Seller $seller, string $password)
    {
        $email = (new TemplatedEmail())
        ->from('admin@studi-mercadona.devexploris.com')
        ->to($mail)
        ->subject('Demande de nouveau mot de passe')
        ->htmlTemplate('mail.html.twig')
        ->context([
            'newPass' => true,
            'password' => $password,
            'ticket' => false,
        ]);

        $this->mailer->send($email);
    }
    
    private function sendMailToSupport(Seller $seller, $content)
    {
        $email = (new TemplatedEmail())
        ->from('admin@studi-mercadona.devexploris.com')
        ->to('admin@studi-mercadona.devexploris.com')
        ->cc($seller->getEmail())
        ->subject('Nouveau ticket de '.$seller->getCode())
        ->htmlTemplate('mail.html.twig')
        ->context([
            'ticket' => true,
            'content' => $content,
            'code' => $seller->getCode(),
        ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch(TransportException $e) {
            return false;
        }
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