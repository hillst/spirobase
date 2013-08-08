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
				if (! key_exists($thinggroups->getGroupnum(), $page_arr["page_things"])){
					$page_arr["page_things"][$thinggroups->getGroupnum()] = array();
				} 
				array_push($page_arr["page_things"][$thinggroups->getGroupnum()], $thinggroups);
			}
			$page_arr["supergroups"] = $this->createAddNewThingForm($page_arr["page_things"]);
			//permission to edit all users
			$allowed_pages[$i] = $page_arr;
			$i++;
			//create add new thing forms, append to end of allowed_things
		}   
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
    	return new Response("invalid action");
    }
    
    function addThingAction(){
    	/*
    	 * Get POST data
    	 * make sure user can edit this page (from form)
    	 * create thing objects
    	 * submit thing objects to database
    	 * redirect
    	 */
    	$request = $this->getRequest();
    	if ($request->getMethod() != 'POST'){
    		return new Response("Not permitted", 403);
    	}
    	$perm = 0;//some value from the form
    	$pageid = $request->request->get('pageid');
    	//do lookup
    	$em = $this->getDoctrine()->getManager();
    	$repo = $em->getRepository("HillCMSManageBundle:CmsPage");
    	$page = $repo->findBy(array("pid"=>$pageid));
    	$page = $page[0]; //should work or error w/e
    	$perm = $page->getRoleAllowed();
    	if ($this->get('security.context')->isGranted($perm) === FALSE){
    		return new Response("Not permitted", 403);
    	}
    	$fd = fopen("/scratch/postdata", "w");
    	fwrite($fd, print_r($request->request->all(), TRUE));
    	fclose($fd);
    	
    	$thingsrepo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$maxgroup = $thingsrepo->findBy(array("groupnum" => "ASC"));
    	$maxgroup = $maxgroup[0]->getGroupnum();
    	$maxgroup += 1;
    	
    	$pagerepo = $em->getRepository("HillCMSManageBundle:CmsPage");
    	$page = $pagerepo->findBy(array("pid"=>$pageid));
    	if (sizeof($page) < 1){
    		
    	} else{
    		$pageid = $page[0]; 
    	}
    	foreach($request->request->all() as $key => $superfield){
    		if($key !== "pageid"){
    			$thing = new CmsPageThings();
    			$thing->setGroupnum($maxgroup);
    			$thing->setThingname($key);
    			$thing->setContent($superfield);
    			$thing->setPageid($pageid);
    			$em->persist($thing);
    			$em->flush();
    		}
    	}
    	
    	
    	return $this->redirect($this->get('router')->generate('manage'));
    	
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
    /**
     * Takes list of things, goes through each group, finds the supergroups, and creates a form to add a new element in that supergroup.
     * returns list of forms.
     * 
     * Expects an input that is an array where the key is the group number, and the values are the page_things that have that group number
     * 
     * [2] (group number) => array
                        (
                            [0] => HillCMS\ManageBundle\Entity\CmsPageThings Object
                                (
                                    [content:HillCMS\ManageBundle\Entity\CmsPageThings:private] => some text
                                    [thingname:HillCMS\ManageBundle\Entity\CmsPageThings:private] => Bio-Name
                                    [group:HillCMS\ManageBundle\Entity\CmsPageThings:private] => 2
                                    [thingid:HillCMS\ManageBundle\Entity\CmsPageThings:private] => 7
                                    [pageid:HillCMS\ManageBundle\Entity\CmsPageThings:private] => HillCMS\Manage
                                    ....
     * 
     * For a page that has Main-Body, Leader-Image, Leader-Name, Leader-Title, the output will look as follows:
     * 
     * Array
	 *	(
			    [Leader] => Array
			        (
			            [0] => Image
			            [1] => Name
			            [2] => Title
			        )
			
			    [Main] => Array
			        (
			            [0] => Body
			        )
			
			)
     * 
     * @param array $thing_array
     */
    function createAddNewThingForm($thing_array)
    {
    	$supergroups = array(); //"supername-subname for field name"
    	foreach($thing_array as $group){
    		//each group has thing
    		foreach($group as $groupthings){
    			$name_split = explode("-", $groupthings->getThingname());
    			if( !array_key_exists($name_split[0], $supergroups)){
    				$supergroups[$name_split[0]] = array();
    			}
    			if (array_search($name_split[1], $supergroups[$name_split[0]]) === FALSE){
    				array_push($supergroups[$name_split[0]],$name_split[1]);
    			}
    		}	
    	}
    	return $supergroups;
    	
    }
    
    
}
