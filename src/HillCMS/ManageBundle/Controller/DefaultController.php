<?php

namespace HillCMS\ManageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HillCMS\ManageBundle\Entity\CmsPage;
use HillCMS\ManageBundle\Entity\CmsPageThings;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
	/**
	 * Default action should be to load all of the editable content
	 * 
	 * Get user's role, find pages that are editable by his role, place into page array. Find things that belong to that page, place in page array
	 * 
	 * pass to view for rendering.
	 */
    public function indexAction()
    {
    	$usr = $this->get('security.context')->getToken()->getUser();
    	$em = $this->getDoctrine()->getManager();
    	$pages = $em->getRepository("HillCMSManageBundle:CmsPage");
    	$pages = $pages->findAll();
    	$allowed_pages = array();
    	foreach ($pages as $page){
    		$perm = $page->getRoleAllowed();
    		if ($this->get('security.context')->isGranted($perm) === TRUE){
    			array_push($allowed_pages, $page);
    		}
    	}
    	$repo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
		$i = 0; //need a mutable array.
		foreach($allowed_pages as $page){				
			$srt_allowed_things = $repo->findBy(array('pageid' => $page->getPid()), array('thingname'=>"ASC"));
			$page_arr = array();
			$page_arr["page"] = $page;
			$page_arr["page_things"] = array();
			foreach ($srt_allowed_things as $thinggroups){
				if (! key_exists($thinggroups->getGroup(), $page_arr["page_things"])){
					$page_arr["page_things"][$thinggroups->getGroup()] = array();
				} 
				array_push($page_arr["page_things"][$thinggroups->getGroup()], $thinggroups);
			}
			//$page_arr["page_things"] = $srt_allowed_things; 
			$allowed_pages[$i] = $page_arr;
			$i++;
		}    	
		$fd = fopen("/scratch/pagesallowed", "w");
		fwrite($fd, print_r($allowed_pages, TRUE));
		fclose($fd);
        return $this->render('HillCMSManageBundle:Default:index.html.twig',array('allowed_pages' => $allowed_pages));
    }
    /**
     * Action responsible for saving things to the database that are edited.
     * Expects route to be a post, if not it will return a 403 error.
     * 
     * If the id is not found it will return a 404 error.
     * 
     * If a user is not permitted to edit a page 403 is returned.
     */
    public function saveAction()
    {
    	// POST id=elements_id&value=user_edited_content
		$request = $this->getRequest();
    	$data = $request->request->all();
    	if ($request->getMethod() === 'POST') {
    		$value = $request->request->get('value', NULL);
    		$id = $request->request->get('id', NULL);
    		if ($id === NULL || $value === NULL){
    			$value = "ERROR";
    		}
    	} else{
    		return new Response("", 403);
    	}
		//lookup the edited thing.
    	$em = $this->getDoctrine()->getManager();
    	$pagethings = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$page = $pagethings->findBy(array("thingid" => $id));
    	if (sizeof($page) <= 0){
    		return new Response("Error", 404);
    	}
    	$page = $page[0];
    	//check permissions
    	$em = $this->getDoctrine()->getManager();
    	$parent = $em->getRepository("HillCMSManageBundle:CmsPage");
    	$parent = $parent->findBy(array("pid" => $page->getPageid()));
    	if(sizeof($parent) <= 0){
    		return new Response("Error", 404);
    	}
    	$parent = $parent[0];
    	$perm = $parent->getRoleAllowed();
    	
    	if ($this->get('security.context')->isGranted($perm) === FALSE){
    		//yikes! probably log
    		return new Response("Not permitted", 403);
    	}
    	
    	//save content
    	$page->setContent($value);
    	$em->persist($page);
    	$em->flush();
    	return new Response($value);
    }
}
