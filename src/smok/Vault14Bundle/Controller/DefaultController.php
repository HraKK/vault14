<?php

namespace smok\Vault14Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use smok\Vault14Bundle\Entity\Document;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('Vault14Bundle:Default:index.html.twig', array('name' => NULL));
    }
    
    private function getDocumentUploadForm() {
        $document = new Document();
        $document->name = 'untitled';
        $form = $this->createFormBuilder($document)
            ->add('file')
            ->add('upload', 'submit')
            ->setAction($this->generateUrl('vault_upload'))
            ->getForm();
        return array(
            'document' => $document, 
            'form' => $form
        );
    }
    
    public function vaultAction() {
        extract($this->getDocumentUploadForm());
        return $this->render('Vault14Bundle:Default:vault.html.twig', array(
            'uploadform' => $form->createView()
            
        ));
    }
    
    public function uploadAction(Request $request) {
        extract($this->getDocumentUploadForm());
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $document->upload();

            $em->persist($document);
            $em->flush();

            return $this->redirect($this->generateUrl('vault'));
        }

        return $this->render(
            'Vault14Bundle:Default:error.html.twig', 
            array('error' => 'Invalid file')
        );
    }
}
