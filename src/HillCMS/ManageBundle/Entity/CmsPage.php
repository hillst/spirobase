<?php

namespace HillCMS\ManageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CmsPage
 *
 * @ORM\Table(name="cms_page")
 * @ORM\Entity
 */
class CmsPage
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=25, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="role_allowed", type="string", length=20, nullable=false)
     */
    private $roleAllowed;

    /**
     * @var integer
     *
     * @ORM\Column(name="pid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pid;



    /**
     * Set name
     *
     * @param string $name
     * @return CmsPage
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
     * Set roleAllowed
     *
     * @param string $roleAllowed
     * @return CmsPage
     */
    public function setRoleAllowed($roleAllowed)
    {
        $this->roleAllowed = $roleAllowed;
    
        return $this;
    }

    /**
     * Get roleAllowed
     *
     * @return string 
     */
    public function getRoleAllowed()
    {
        return $this->roleAllowed;
    }

    /**
     * Get pid
     *
     * @return integer 
     */
    public function getPid()
    {
        return $this->pid;
    }
}