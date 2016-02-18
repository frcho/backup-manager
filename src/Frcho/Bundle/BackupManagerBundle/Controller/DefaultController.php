<?php

namespace Frcho\Bundle\BackupManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FrchoBackupManagerBundle:Default:index.html.twig');
    }
}
