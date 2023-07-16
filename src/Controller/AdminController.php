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
use App\Services\Functions;
use App\Services\FileUploader;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminController extends AbstractController {

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/admin', name:'adminDashboard')]
    public function dashboardDisplay()
    {
        return $this->render('admin/admin.html.twig', [
            'title' => 'Dashboard'
        ]);
    }

    #[Route('/admin/add-category', name:'addCategory')]
    public function addCategoryDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, CategoryRepository $categoryRepo, ValidatorInterface $validator)
    {
        $categories = $categoryRepo->findAll();
        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        $errors = $validator->validate($category);

        try {
            if($categoryForm->isSubmitted() && $categoryForm->isValid()) {
    
                $entityManager->persist($category);
                $entityManager->flush();
                $flasher->addSuccess('La catégorie a bien été ajoutée');
    
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

        if($productForm->isSubmitted() && $productForm->isValid()) {
            $image = $productForm->get('imageFile')->getData();
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

        return $this->render('admin/addProduct.html.twig', [
            'productForm' => $productForm->createView(),
            'title' => 'Ajout Produit'
        ]);
    }

    #[Route('/admin/add-discount', name:'addDiscount')]
    public function addDiscountDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, CategoryRepository $categoryRepo, ValidatorInterface $validator)
    {
        $discount = new Promotion();

        $discountForm = $this->createForm(DiscountType::class, $discount);

        $discountForm->handleRequest($request);

        $errors = $validator->validate($discount);

        if($discountForm->isSubmitted() && $discountForm->isValid()) {

            $entityManager->persist($discount);
            $entityManager->flush();
            $flasher->addSuccess('La promotion a bien été ajoutée');

        } elseif ($discountForm->isSubmitted() && $discountForm->isValid() == false) {
            if(count($errors) > 0) {
                $errorMessage = $errors->get(0)->getMessage();
                    $flasher->addError($errorMessage);
            } else {
                $flasher->addError('Promotion non enregistrée.');
            }
    }

        return $this->render('admin/addDiscount.html.twig', [
            'discountForm' => $discountForm->createView(),
            'title' => 'Ajout Promotion',
        ]);
    }

    #[Route('/admin/new-admin', name:'newAdmin')]
    public function newAdminDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher, UserPasswordHasherInterface $userPasswordHasherInterface, ValidatorInterface $validator)
    {
        $seller = new Seller();

        $sellerForm = $this->createForm(SellerType::class, $seller);

        $sellerForm->handleRequest($request);

        $errors = $validator->validate($seller);

        if($sellerForm->isSubmitted() && $sellerForm->isValid()) {
            $plainPassword = Functions::generatePassword($seller);
            $hashpassword = $userPasswordHasherInterface->hashPassword($seller,$plainPassword);
            $seller->setPassword($hashpassword);
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

        return $this->render('admin/addAdmin.html.twig', [
            'sellerForm' => $sellerForm->createView(),
            'title' => 'Nouvel Admin'
        ]);
    }

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

}