<?php

namespace HillCMS\CCBLBundle\Controller;

use HillCMS\ManageBundle\Controller\CMSController;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends CMSController
{
	private $pid;
    public function indexAction()
    {
    	$this->pid = 1;
    	$em = $this->getDoctrine()->getManager();
    	$repo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$pagethings = $repo->findBy(array("pageid" => $this->pid)); //our people page id
    	if (sizeof($pagethings) === 0){
    		//empty page
    		return new Response("Error", 404);
    	}
    	$homegroups = $this->buildPageGroups($pagethings);
    	//use main zero as it is a one-of
        return $this->render('HillCMSCCBLBundle:Default:index.html.twig', array("main" => $homegroups['Main'][0], "slides" => $homegroups["Slide"]));
    }
}
