<?php

namespace Frcho\Bundle\BackupManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('FrchoBackupManagerBundle:Default:index.html.twig');
    }
}
