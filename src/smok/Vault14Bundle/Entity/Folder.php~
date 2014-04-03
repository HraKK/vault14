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
    
}