<?php


namespace App\Services;
use App\Entity\Seller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class Functions {
    
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    static function getRandomBytes($nbBytes = 32)
    {
        $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
        if (false !== $bytes && true === $strong) {
            return $bytes;
        }
        else {
            throw new \Exception("Unable to generate secure token from OpenSSL.");
        }
    }
    
    static function generateSpecialCharacter() {
        $characters = '!$@*/&';
        $charactersLength = strlen($characters);
        $randomString = $characters[random_int(0, $charactersLength - 1)];
        return $randomString;
    }
    
    static function generateRandomString($length) {
        return substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(Functions::getRandomBytes($length+1))),0,$length);
    }
    
    static function generatePassword() {
        // On génére automatiquement le mot de passe
        // Etape 1 : On obtient un string aléatoire à l'aide d'une chaine de 32 bytes.
        $password = Functions::generateRandomString(12);
        // Etape 2 : On remplace aléatoirement 3 caractères par des caractères spéciaux.
        for($i = 0; $i <= 3; $i++) {
            $randNumber = rand(1,14);
            $password = substr_replace($password, Functions::generateSpecialCharacter(), $randNumber, 0);
        }
        $match = preg_match('#^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!$@*/&]).{12,}$#', $password);
        if(!$match) {
            $password = static::generatePassword();
        }

        // On retourne le mot de passe pour l'insérer dans le mail.
        return $password;
    }

}

