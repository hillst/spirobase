<?php

namespace HillCMS\SpirodelaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use HillCMS\ManageBundle\Controller\CMSController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HillCMS\ManageBundle\Entity\CmsPage;
use HillCMS\ManageBundle\Entity\CmsPageThings; 

class JBrowseController extends CMSController
{
	private $pid = 3;
	
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$repo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$pagethings = $repo->findBy(array("pageid" => $this->pid)); //our people page id
    	if (sizeof($pagethings) === 0){
    			//empty page
    		return new Response("Error", 404);
    	}
    	$newsgroups = $this->buildPageGroups($pagethings);
    	
    	return $this->render('HillCMSSpirodelaBundle:Default:jbrowse.html.twig', array("contacts" => $newsgroups["Contact"], "resources" => $newsgroups["Resources"]));
    	
    }
}
