<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 25/11/18
 * Time: 20:38
 */

namespace OivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends BaseController
{
    /**
     * @param Request $request
     * @Route("/backoffice/login",name="login-backoffice")
     */
    public function indexAction(Request $request)
    {
        return $this->render('OivBundle:backOffice:login.html.twig');
    }
}