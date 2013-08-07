<?php

namespace HillCMS\ManageBundle\Controller;

use Symfony\Component\Debug\Exception\ContextErrorException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HillCMS\ManageBundle\Entity\CmsPage;
use HillCMS\ManageBundle\Entity\CmsPageThings;
use HillCMS\UserBundle\Entity\User;
use HillCMS\UserBundle\Entity\Role;
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
		//permission to edit all users 	
		if($this->get('security.context')->isGranted("ROLE_ADMIN") === TRUE){
			$isAdmin = 1;
			$repo = $em->getRepository("HillCMSUserBundle:User");	
			$users = $repo->findAll();
		} else{
			$users[0] = $usr= $this->get('security.context')->getToken()->getUser();
			$isAdmin = -1;
		}
	
        return $this->render('HillCMSManageBundle:Default:index.html.twig',array('allowed_pages' => $allowed_pages, "users"=> $users, "isAdmin"=> $isAdmin));
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
    /*ERROR POSTING IDGI..... thats okay also gotta figure out why it thinks i'm not an admin ???????? */
    function addUserAction()
    {
    	$request = $this->getRequest();
    	$data = $request->request->all();
    	if ($request->getMethod() === 'POST') {
    		$username = $request->request->get('username', NULL);
    		$password = $request->request->get('password', NULL);
    		$email = $request->request->get('email', NULL);
    		$role = $request->request->get('role', NULL);
    		if ($username === NULL || $password === NULL || $email === NULL || $role === NULL){
    			$value = "ERROR";
    		}
    	} else{
    		return new Response("", 403);
    	}
    	//lookup the edited thing.
    	$em = $this->getDoctrine()->getManager();
    	$rolerepo = $em->getRepository("HillCMSUserBundle:Role");
    	$roleobj = $rolerepo->findBy(array("role" => $role));
    	if (sizeof($roleobj) <= 0){
    		return new Response("Error", 404);
    	}
    	$roleobj = $roleobj[0];
    	//check permissions
    	$em = $this->getDoctrine()->getManager();	   	 
    	if ($this->get('security.context')->isGranted("ROLE_ADMIN") === FALSE){
    		//yikes! probably log
    		return new Response("Not permitted", 403);
    	}
    	$user = new User();
    	$user->setPassword($password);
    	$user->setEmail($email);
    	$user->setUsername($username);
    	$value = "Successfully added ". $username;
    	$em->persist($user);
    	$em->flush();
    	return new Response($value);
    }
    
    function saveRoleAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$repo = $em->getRepository("HillCMSUserBundle:User");
    	$user = $repo->findBy(array("id" => $this->getRequest()->get('id')));
    	$user[0]->addRole($this->getRequest()->get('role'));
    	$em->persist($user[0]);
    	$em->flush();
    	return new Response(":)");
    	
    }
    
    
}
