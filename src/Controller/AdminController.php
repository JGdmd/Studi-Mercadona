<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Seller;
use App\Form\CategoryType;
use App\Form\SellerType;
use App\Functions;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\Flasher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
    public function addCategoryDisplay(Request $request, EntityManagerInterface $entityManager, Flasher $flasher)
    {
        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            try {
                $entityManager->persist($category);
                $entityManager->flush();
                $flasher->addSuccess('La catégorie a bien été ajoutée');
            } catch(UniqueConstraintViolationException $e)
            {

            }
        } elseif ($categoryForm->isSubmitted() && $categoryForm->isValid() == false) {
            $flasher->addError('La catégorie n\'a pas été ajouté. Recommencez.');
        }

        return $this->render('admin/addCategory.html.twig', [
            'categoryForm' => $categoryForm->createView(),
            'title' => 'Ajout Catégorie'
        ]);
    }

    #[Route('/admin/add-product', name:'addProduct')]
    public function addProductDisplay()
    {
        return $this->render('admin/addCategory.html.twig', [
            'title' => 'Ajout Produit'
        ]);
    }

    #[Route('/admin/add-discount', name:'addDiscount')]
    public function addDiscounttDisplay()
    {
        return $this->render('admin/addCategory.html.twig', [
            'title' => 'Ajout Promotion'
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