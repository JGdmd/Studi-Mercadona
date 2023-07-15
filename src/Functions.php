<?php


namespace App;
use App\Entity\Seller;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Functions {
    
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
    
    static function generatePassword(Seller $seller) {
        $password = Functions::generateRandomString(12);
        for($i = 0; $i <= 3; $i++) {
            $randNumber = rand(1,14);
            $password = substr_replace($password, Functions::generateSpecialCharacter(), $randNumber, 0);
        }
        return $password;
    }
}

