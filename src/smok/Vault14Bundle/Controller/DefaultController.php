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
        
        $em = $this->getDoctrine()->getManager();
        $folders_q = $em->createQuery(
            'SELECT f'
                . 'FROM Vault14Bundle:Folder f'
                . 'WHERE f.parent_folder_id IS NULL'
                . 'AND f.user_id = :user'
            )
            ->setParameter('user', $this->getCurrentUser()->getId());
        
        $documents_q = $em->createQuery(
            'SELECT d'
                . 'FROM Vault14Bundle:Document d'
                . 'WHERE d.folder_id IS NULL'
                . 'AND d.user_id = :user'    
            )
            ->setParameter('user', $this->getCurrentUser()->getId());
                    
        return $this->render('Vault14Bundle:Default:vault.html.twig', array(
            'uploadform' => $form->createView(),
            'folders' => $folders_q->getResult(),
            'documents' => $documents_q->getResult()
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
