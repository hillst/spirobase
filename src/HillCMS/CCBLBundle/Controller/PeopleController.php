<?php

namespace HillCMS\CCBLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use HillCMS\ManageBundle\Controller\CMSController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HillCMS\ManageBundle\Entity\CmsPage;
use HillCMS\ManageBundle\Entity\CmsPageThings; 


class PeopleController extends CMSController
{
	private $pid = 2;
	public function indexAction()
	{		
		$em = $this->getDoctrine()->getManager();
    	$repo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$pagethings = $repo->findBy(array("pageid" => $this->pid)); //our people page id
    	if (sizeof($pagethings) === 0){
    		//empty page
    		return new Response("Error", 404);
    	}
    	$biogroups = $this->buildPageGroups($pagethings);
    	/*
    	 * Uncomment to see structure of arrays.
    	 * $fd = fopen("/scratch/groups", "w");
    	 * fwrite($fd, print_r($biogroups, TRUE));
    	 * fclose($fd);
    	 */
    	return $this->render('HillCMSCCBLBundle:Default:people.html.twig', array( "bios" => $biogroups["Bio"]));
	}
	
}
