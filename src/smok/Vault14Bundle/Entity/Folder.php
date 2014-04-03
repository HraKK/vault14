<?php

namespace smok\Vault14Bundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Folder {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    public $name;
    
    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="folder")
     */
    protected $documents;
    
    public function __construct() {
        $this->documents = new ArrayCollection();
        $this->child_folders = new ArrayCollection();
    }
    
    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent_folder")
     */
    protected $child_folders;
    
    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="child_folders")
     * @ORM\JoinColumn(name="parent_folder_id", referencedColumnName="id")
     */
    protected $parent_folder;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="folders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Folder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add documents
     *
     * @param \smok\Vault14Bundle\Entity\Document $documents
     * @return Folder
     */
    public function addDocument(\smok\Vault14Bundle\Entity\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \smok\Vault14Bundle\Entity\Document $documents
     */
    public function removeDocument(\smok\Vault14Bundle\Entity\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add child_folders
     *
     * @param \smok\Vault14Bundle\Entity\Folder $childFolders
     * @return Folder
     */
    public function addChildFolder(\smok\Vault14Bundle\Entity\Folder $childFolders)
    {
        $this->child_folders[] = $childFolders;

        return $this;
    }

    /**
     * Remove child_folders
     *
     * @param \smok\Vault14Bundle\Entity\Folder $childFolders
     */
    public function removeChildFolder(\smok\Vault14Bundle\Entity\Folder $childFolders)
    {
        $this->child_folders->removeElement($childFolders);
    }

    /**
     * Get child_folders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildFolders()
    {
        return $this->child_folders;
    }

    /**
     * Set parent_folder
     *
     * @param \smok\Vault14Bundle\Entity\Folder $parentFolder
     * @return Folder
     */
    public function setParentFolder(\smok\Vault14Bundle\Entity\Folder $parentFolder = null)
    {
        $this->parent_folder = $parentFolder;

        return $this;
    }

    /**
     * Get parent_folder
     *
     * @return \smok\Vault14Bundle\Entity\Folder 
     */
    public function getParentFolder()
    {
        return $this->parent_folder;
    }

    /**
     * Set user
     *
     * @param \smok\Vault14Bundle\Entity\User $user
     * @return Folder
     */
    public function setUser(\smok\Vault14Bundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \smok\Vault14Bundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
