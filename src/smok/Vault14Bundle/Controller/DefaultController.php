<?php

namespace smok\Vault14Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('Vault14Bundle:Default:index.html.twig', array('name' => NULL));
    }
    
    public function vaultAction() {
        return $this->render('Vault14Bundle:Default:vault.html.twig');
    }
}
