<?php namespace InstaClone\Api;

class Auth{

    public function generateKey(){
        // TODO: figure out how to do api auth
        // what exactly does the client and server generate?!
        $publicKey = bin2hex(openssl_random_pseudo_bytes(16));
        $privateKey = crypt(bin2hex(openssl_random_pseudo_bytes(16)));
    }

    public function checkSecretKey($publicKey){
        if(crypt($publicKey) == crypt() ){
            return true;
        }

        return false;
    }
}