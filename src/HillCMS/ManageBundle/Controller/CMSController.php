<?php
namespace HillCMS\ManageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HillCMS\ManageBundle\Entity\CmsPage;
use HillCMS\ManageBundle\Entity\CmsPageThings;

class CMSController extends Controller
{
	/**
	 * Builds an array of each Group of things on the page. A group is defined by pagethings with the same group number.
	 * To further differentiate "things", they are expected to be titled based on "what" they do, followed by a hyphen. 
	 * The example here is for Bio-Text, Bio-Name, Bio-Title, Bio-Picture. This is done incase there are multiple sets of things
	 * on a page, and it allows for the twig page to differentiate between them. 
	 * 
	 * The function returns an array for each "Set" (thing before the hyphen), followed by a list of groups, where each contains one of the
	 * fields in that set. Following is an example. 
	 * 
	 * Example:
	 *
	 * [groupnum] => ( [Bio] =>
	 * 					 [0] =>
	 * 					 	[Text] => "content"
	 * 					 	[Name] => "name"
	 * 					 	[Title]=> "title"
	 * 					 	[Picture]=> "path to link"
	 * 					 [1] =>
	 * 						[Text] => "content"
	 * 					 	[Name] => "name"
	 * 					 	[Title]=> "title"
	 * 					 	[Picture]=> "path to link"
	 * @param array $options
	 */
	
	public function buildPageGroups($pagethings){
		$things = array();
	
		foreach($pagethings as $group){
			$exploded_group = explode("-", $group->getThingname());
			if (! key_exists($exploded_group[0], $things)){
				$things[$exploded_group[0]] = array();
			}
			if (! key_exists($group->getGroupnum(), $things[$exploded_group[0]])){
				$things[$exploded_group[0]][$group->getGroupnum()] = array();
			}
			$things[$exploded_group[0]][$group->getGroupnum()][$exploded_group[1]] = $group->getContent();
		}
		return $things;
	}
	
}
