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
    
    private function getCurrentUser() {
        return $this->get('security.context')->getToken()->getUser();
    }

    private function getDocumentUploadForm() {
        $document = new Document();
        $document->name = 'untitled';
        $document->setUser($this->getCurrentUser());
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
        $folders = $this->getDoctrine()
            ->getRepository('Vault14Bundle:Folder')
            ->findBy(
                array('parent_folder_id'=>NULL),
                array('user_id', $this->getCurrentUser()->getId())
            );
        $documents = $this->getDoctrine()
            ->getRepository('Vault14Bundle:Document')
            ->findBy(
                array('folder_id'=>NULL),
                array('user_id', $this->getCurrentUser()->getId())
            );
        
        
        return $this->render('Vault14Bundle:Default:vault.html.twig', array(
            'uploadform' => $form->createView(),
            'folders' => $folders,
            'documents' => $documents
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
