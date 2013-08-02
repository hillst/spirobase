<?php

namespace HillCMS\CCBLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('HillCMSCCBLBundle:Default:index.html.twig');
    }
}
