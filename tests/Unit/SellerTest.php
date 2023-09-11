<?php

namespace App\Test\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Seller;
use App\Services\Functions;

class SellerTest extends KernelTestCase
{
    static function setSeller()
    {
        $seller = new Seller();
        $seller->setCode('MER-12345-123')
        ->setPassword('$2y$13$Rwo5NIN8wbQo1tfciCh2OOdneen.xfbxWFU3UTir/2M3SUxKrubGS')
        ->setEmail('test@localhost.com');
        return $seller;
    }

    // TODO : Change this function for test the password hash. 
    public function testPasswordGenerator(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $notError = true;
        for ($i=0; $i < 100 ; $i++) { 
            $plainPassword = Functions::generatePassword();
            $match = preg_match('#^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!$@*/&]).{12,}$#', $plainPassword);

            if(!$match) {
                echo "Mot de passe non conforme : $plainPassword\n";
                $notError = false;
            }
        }
        $this->assertTrue($notError === true);
    }

    public function testCreateSellerWithBadCode(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        for ($i=0; $i < 7 ; $i++) { 
            $seller = static::setSeller();
            switch($i) {
                case 0 :
                    // Code contenant une lettre dans le dernier tronçon
                    $seller->setCode('MER-01478-X00');
                    break;
                case 1 :
                    // Code contenant une lettre dans le premier tronçon
                    $seller->setCode('MJK-01234-123');
                    break;
                case 2 :
                    // Code contenant une lettre dans le second tronçon
                    $seller->setCode('MER-01AB8-000');
                    break;
                case 3 :
                    // Code contenant une suite de chiffre dans le premier tronçon
                    $seller->setCode('123-01234-123');
                    break;
                case 4 :
                    // Code avec chiffre manquant dans le second tronçon
                    $seller->setCode('MER-034-123');
                    break;
                case 5 :
                    // Code avec chiffre en trop (donc 2 erreurs) dans le second tronçon
                    $seller->setCode('MER-012345-123');
                    break;
                case 6 :
                    // Code bon
                    $seller->setCode('MER-12345-123');
                    break;
            }
            $errors[] = $container->get('validator')->validate($seller);
        }
        // Le nombre attendu est 7 et non 8 car il y a un code bon dans le lot.
        $this->assertCount(7, $errors);    
    }

    public function testCreateSellerWithBadEmail(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        for ($i=0; $i < 3 ; $i++) { 
            $seller = static::setSeller();
            switch($i) {
                case 0 :
                    // Email OK
                    $seller->setEmail('mercadona@localhost.com');
                    break;
                case 1 :
                    // Email sans @
                    $seller->setEmail('mercadona.localhost.com');
                    break;
                case 2 :
                    // Email sans extension
                    $seller->setEmail('mercadona@localhost');
                    break;
                case 3 :
                    // Email sans partie locale
                    $seller->setEmail('@localhost.com');
                    break;
                }
                $errors[] = $container->get('validator')->validate($seller);
                // Le nombre attendu est 3 et non 4 car il y a un mail bon dans le lot.
            }
            $this->assertCount(3, $errors);    
    }
}
