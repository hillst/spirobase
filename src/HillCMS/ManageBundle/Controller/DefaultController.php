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
			$users[0] = $this->get('security.context')->getToken()->getUser();
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
    
    function addUserAction()
    {
    	$request = $this->getRequest();
    	if ($request->getMethod() != "POST"){
    		return new Response("Not permitted", 403);
    	}
    	//not allowed!
    	if (!$this->get('security.context')->isGranted("ROLE_ADMIN")){
    		return new Response("Not permitted", 403);	
    	}
    	//get fields
    	$perm = 0;//some value from the form
    	$username = $request->request->get('username');
    	$password = $request->request->get('password');
    	$role = $request->request->get('role');
    	if ($username == "" || $password == "" || $role == ""){
    		return new Response("Bad form submission", 400);
    	}
    	$newuser = new User($username, $password, $role);
    	$factory = $this->get('security.encoder_factory');
    	$encoder = $factory->getEncoder($newuser);
    	$password = $encoder->encodePassword($password, $newuser->getSalt());
    	$newuser->setPassword($password);
    	$email = ""; //bad
    	$newuser->setEmail($email);
    	$em = $this->getDoctrine()->getEntityManager();
    	$em->persist($newuser);
    	$em->flush();
    	
    	return new Response("Successfully added user " . $username . " " . $role .". Reload your page to modify the user.");
    }
    
    function addThingAction()
    {
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
    	$thingsrepo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$maxgroup = $thingsrepo->findBy(array(), array("groupnum" => "DESC"));
    	
    	
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
    
    function changePassAction()
    {
    	$request = $this->getRequest();
    	$curuser = $this->get('security.context')->getToken()->getUser();
    	
    	//i dont remember the problem
    	if ($request->getMethod() != "POST"){
    		return new Response("Not permitted", 403);
    	}
    	//not allowed!
    	$oldpass = $request->get('oldpass'); //could be blank
    	$password = $request->get('password');
    	$mpassword = $request->get('mpassword');
    	$id = $request->get('id');
    	
    	//initial validation
    	if (!($this->get('security.context')->isGranted("ROLE_ADMIN")) && $curuser->getId() != $id ){
    		return new Response("Not permitted admin" . $curuser->getId() . " " . $id, 403);
    	}
    	
    	if ($password == "" || $id == ""){
    		return new Response("Bad form submission.", 400);
    	}
    	
    	$em = $this->getDoctrine()->getEntityManager();
    	$repo = $em->getRepository("HillCMSUserBundle:User");
    	$user = $repo->findBy(array("id" => $id));
    	$factory = $this->get('security.encoder_factory');
    	$encoder = $factory->getEncoder($user[0]);
    	
    	if (sizeof($user) < 1){
    		return new Response("Bad form submission.", 400);
    	}
    	
    	if ($password != $mpassword){
    		return new Response("Passwords do not match.", 400);
    	}
    	//normal user expect oldpass
    	if(!$this->get('security.context')->isGranted("ROLE_ADMIN")){
    		$hasoldpass = $encoder->encodePassword($oldpass, $user[0]->getSalt());
    		if( $hasoldpass != $user[0]->getPassword()){
    			return new Response("Invalid old password", 400 );
    		}
    	}
    	
    	$password = $encoder->encodePassword($password, $user[0]->getSalt());
    	$user[0]->setPassword($password);
    	$em->persist($user[0]);
    	$em->flush();
    	
    	return new Response("Success changing password for ". $user[0]->getUsername(). ".");	
    }
    

    function editUsernameAction()
    {
    	$request = $this->getRequest();
    	$curuser = $this->get('security.context')->getToken()->getUser();
    	
    	if ($request->getMethod() != "POST"){
    		return new Response("Not permitted", 403);
    	}
    	$id = $request->get('id');
    	$username = $request->get('username');
    	if ($id == "" || $username == ""){
    		return new Response("Bad form submission.", 400);
    	}
    	//not allowed!
    	if (!$this->get('security.context')->isGranted("ROLE_ADMIN")){
    		return new Response("Not permitted", 403);
    	}
    	
    	$em = $this->getDoctrine()->getEntityManager();
    	$repo = $em->getRepository("HillCMSUserBundle:User");
    	$user = $repo->findBy(array("id" => $id));
    	 
    	$user[0]->setUsername($username);
    	$em->persist($user[0]);
    	$em->flush();
    	return new Response("Success! Username changed to ". $username . ".");
    }
    
    function saveRoleAction()
    {
    	$request = $this->getRequest();
    	$curuser = $this->get('security.context')->getToken()->getUser();
    	
    	if ($request->getMethod() != "POST"){
    		return new Response("Not permitted", 403);
    	}
    	$id = $request->get('id');
    	$role = $request->get('role');
    	if ($id == "" || $role == ""){
    		return new Response("Bad form submission.", 400);
    	}
    	//not allowed!
    	if (!$this->get('security.context')->isGranted("ROLE_ADMIN")){
    		return new Response("Not permitted", 403);
    	}
    	
    	$em = $this->getDoctrine()->getEntityManager();
    	$repo = $em->getRepository("HillCMSUserBundle:User");
    	
    		
    	$user = $repo->findBy(array("id" => $id));
    	
    	$user[0]->setRole($role);
    	$em->persist($user[0]);
    	$em->flush();
    	return new Response("Success! Role for " . $user[0]->getUsername() . " changed to ". $role . ".");
    	 
    }
    
    function deleteThingAction()
    {
    	$request = $this->getRequest();
    	if($request->getMethod() != "POST"){
    		return new Response("Not permitted", 403);
    	}	
    	$groupnum = $request->get("groupnum");
    	if($groupnum == ""){
    		return new Response("Bad form submission." , 400);
    	}
    	//not exactly this.. but compare to the page permissions
    	$em = $this->getDoctrine()->getManager();
    	$repo = $em->getRepository("HillCMSManageBundle:CmsPageThings");
    	$things = $repo->findBy(array("groupnum" => $groupnum));
    	if (sizeof($things) < 1){
    		return new Response("Bad form submission.", 400);
    	}
    	
    	if( !$this->get("security.context")->isGranted("ROLE_ADMIN") ){
	    	foreach($things as $thing){
	    		if ($thing->getPageid()->getRoleAllowed() !== "ROLE_USER"){
	    			return new Response("Not permitted", 403);
	    		}
	    	}
    	}
    	//delete objects..
    	foreach($things as $thing){
    		$em->remove($thing);    		
    	}    	
    	$em->flush();
    	return $this->redirect($this->get('router')->generate('manage'));
   
    }
    
    
}
