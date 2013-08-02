<?php

namespace HillCMS\ManageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CmsPageThings
 *
 * @ORM\Table(name="cms_page_things")
 * @ORM\Entity
 */
class CmsPageThings
{
    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="thingname", type="string", length=25, nullable=false)
     */
    private $thingname;

    /**
     * @var integer
     *
     * @ORM\Column(name="group", type="integer", nullable=false)
     */
    private $group;

    /**
     * @var integer
     *
     * @ORM\Column(name="thingid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $thingid;

    /**
     * @var \HillCMS\ManageBundle\Entity\CmsPage
     *
     * @ORM\ManyToOne(targetEntity="HillCMS\ManageBundle\Entity\CmsPage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pageid", referencedColumnName="pid")
     * })
     */
    private $pageid;



    /**
     * Set content
     *
     * @param string $content
     * @return CmsPageThings
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set thingname
     *
     * @param string $thingname
     * @return CmsPageThings
     */
    public function setThingname($thingname)
    {
        $this->thingname = $thingname;
    
        return $this;
    }

    /**
     * Get thingname
     *
     * @return string 
     */
    public function getThingname()
    {
        return $this->thingname;
    }

    /**
     * Set group
     *
     * @param integer $group
     * @return CmsPageThings
     */
    public function setGroup($group)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return integer 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get thingid
     *
     * @return integer 
     */
    public function getThingid()
    {
        return $this->thingid;
    }

    /**
     * Set pageid
     *
     * @param \HillCMS\ManageBundle\Entity\CmsPage $pageid
     * @return CmsPageThings
     */
    public function setPageid(\HillCMS\ManageBundle\Entity\CmsPage $pageid = null)
    {
        $this->pageid = $pageid;
    
        return $this;
    }

    /**
     * Get pageid
     *
     * @return \HillCMS\ManageBundle\Entity\CmsPage 
     */
    public function getPageid()
    {
        return $this->pageid;
    }
}