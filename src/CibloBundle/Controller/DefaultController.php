<?php

namespace CibloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $visitRepository    = $this->get('ciblo.customer_repository.visit');
        $result             = $visitRepository->getResolutionDistribution(15);
        $data               = json_encode($result);
        //echo $data; die();
        return $this->render('CibloBundle:Default:index.html.twig',array('data'=>$data));
    }

    /**
     * @Route("/device")
     */
    public function getDeviceDistribuationAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $visitRepository = $this->get('ciblo.customer_repository.visit');
        $result = $visitRepository->getDeviceDistribuation(15);
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

    /**
     * @Route("/os")
     */
    public function getOsDistribuationAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $visitRepository = $this->get('ciblo.customer_repository.visit');
        $result = $visitRepository->getOsDistribuation(15);
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

    /**
     * @Route("/navigator")
     */
    public function getNavigatorDistribuationAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $visitRepository = $this->get('ciblo.customer_repository.visit');
        $result = $visitRepository->getNavigatorDistribuation(15);
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

}
