<?php

namespace HillCMS\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    public function indexAction()
    {
        return $this->render('HillCMSSecurityBundle:Security:login.html.twig');
    }
}
