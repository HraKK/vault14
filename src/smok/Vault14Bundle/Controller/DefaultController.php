<?php

namespace smok\Vault14Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use smok\Vault14Bundle\Entity\Document;
use smok\Vault14Bundle\Entity\Folder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('Vault14Bundle:Default:index.html.twig', array('name' => NULL));
    }
    
    private function getCurrentUser() {
        return $this->get('security.context')->getToken()->getUser();
    }

    private function getDocumentUploadForm($folder = NULL) {
        $document = new Document();
        $document->name = 'untitled';
        $document->setUser($this->getCurrentUser());
        $document->setIsPublic(0);
        if (!is_null($folder)) 
            $document->setFolder ($folder);
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
    
    private function getFolderCreateForm($parent_folder = NULL) {
        $folder = new Folder();
        $folder->setUser($this->getCurrentUser());
        if (!is_null($parent_folder)) 
            $folder->setParentFolder($parent_folder);
        $folder_create_form = $this->createFormBuilder($folder)
            ->add('name')
            ->add('Create', 'submit')
            ->setAction($this->generateUrl('vault_createfolder'))
            ->getForm();
        return array(
            'folder' => $folder,
            'folder_create_form' => $folder_create_form
        );
    }
    
    public function vaultAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $current_folder = FALSE;
        if ($request->get('folder', FALSE)) {
            $current_folder_q = $em->createQuery(
                'SELECT f '
                    . 'FROM Vault14Bundle:Folder f '
                    . 'LEFT JOIN f.user u '
                    . 'WHERE f.id = :id '
                    . 'AND u.id = :user '
                )
                ->setParameter('id', (int)$request->get('folder', 0))
                ->setParameter('user', $this->getCurrentUser()->getId());
            
            $current_folder = $current_folder_q->getSingleResult();
            if (!$current_folder)
                throw new NotFoundHttpException('Folder not found');
            
            $folders_q = $em->createQuery(
                'SELECT f '
                    . 'FROM Vault14Bundle:Folder f '
                    . 'LEFT JOIN f.parent_folder p '
                    . 'LEFT JOIN f.user u '
                    . 'WHERE p.id = :parent '
                    . 'AND u.id = :user '
                )
                ->setParameter('user', $this->getCurrentUser()->getId())
                ->setParameter('parent', $current_folder->getId());

            $documents_q = $em->createQuery(
                'SELECT d '
                    . 'FROM Vault14Bundle:Document d '
                    . 'LEFT JOIN d.folder f '
                    . 'LEFT JOIN d.user u '
                    . 'WHERE f.id = :parent '
                    . 'AND u.id = :user '
                )
                ->setParameter('user', $this->getCurrentUser()->getId())
                ->setParameter('parent', $current_folder->getId());
            
        } else {
            $folders_q = $em->createQuery(
                'SELECT f '
                    . 'FROM Vault14Bundle:Folder f '
                    . 'LEFT JOIN f.parent_folder p '
                    . 'LEFT JOIN f.user u '
                    . 'WHERE p.id IS NULL '
                    . 'AND u.id = :user '
                )
                ->setParameter('user', $this->getCurrentUser()->getId());

            $documents_q = $em->createQuery(
                'SELECT d '
                    . 'FROM Vault14Bundle:Document d '
                    . 'LEFT JOIN d.folder f '
                    . 'LEFT JOIN d.user u '
                    . 'WHERE f.id IS NULL '
                    . 'AND u.id = :user '
                )
                ->setParameter('user', $this->getCurrentUser()->getId());
        }

        extract($this->getDocumentUploadForm());
        extract($this->getFolderCreateForm());
                    
        return $this->render('Vault14Bundle:Default:vault.html.twig', array(
            'uploadform' => $form->createView(),
            'folders' => $folders_q->getResult(),
            'documents' => $documents_q->getResult(),
            'folder_create_form' => $folder_create_form->createView(),
            'show_root_folder_link' => ($current_folder && !$current_folder->getParentFolder()),
            'parent_folder_id' => (($current_folder && $current_folder->getParentFolder())? $current_folder->getParentFolder()->getId() : NULL)
        ));
    }
    
    public function viewAction($file_id) {
        $em = $this->getDoctrine()->getManager();
        $document_q = $em->createQuery(
            'SELECT d '
                . 'FROM Vault14Bundle:Document d '
                . 'LEFT JOIN d.user u '
                . 'WHERE d.id = :id '
                . 'AND u.id = :user '    
            )
            ->setParameter('id', (int)$file_id)
            ->setParameter('user', $this->getCurrentUser()->getId());
        
        $document = $document_q->getSingleResult();
        
        if (!$document)
            throw new NotFoundHttpException('Document not found');
        
        return $this->render('Vault14Bundle:Default:view.html.twig', array(
            'document' => $document
        ));
    }
    
    public function downloadAction($file_id) {
        $em = $this->getDoctrine()->getManager();
        $document_q = $em->createQuery(
            'SELECT d '
                . 'FROM Vault14Bundle:Document d '
                . 'WHERE d.id = :id '
            )
            ->setParameter('id', (int)$file_id);
        
        $document = $document_q->getSingleResult();
        
        if (!$document)
            throw new NotFoundHttpException('File not found');
        
        $response = new Response(
            NULL,
            Response::HTTP_OK,
            array(
                'content-type' => $document->getMimetype(),
                'X-Accel-Redirect' => '/documents/'.$document->getPath()
            )
        );
        $response->send();
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
    
    public function createformAction(Request $request) {
        extract($this->getFolderCreateForm());
        
        $folder_create_form->handleRequest($request);
        
        if ($folder_create_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($folder);
            $em->flush();
            
            return $this->redirect(
                $this->generateUrl(
                    'vault', 
                    array('folder'=>$folder->getId())
                )
            );
        }
        
        return $this->render(
            'Vault14Bundle:Default:error.html.twig', 
            array('error' => 'Can\'t create your folder')
        );

    }
}
