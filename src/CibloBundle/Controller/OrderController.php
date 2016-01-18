<?php
/**
 * Created by JetBrains PhpStorm.
 * User: moabidi
 * Date: 17/01/16
 * Time: 21:13
 * To change this template use File | Settings | File Templates.
 */

namespace CibloBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller{

    /**
     * @Route("/orders")
     */
    public function getOrdersDistribuationAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $orderRepository = $this->get('ciblo.customer_repository.order');
        $result = $orderRepository->getOrdersDistribution(15);
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

    /**
     * @Route("/ca")
     */
    public function getCADistribuationAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $visitRepository = $this->get('ciblo.customer_repository.order');
        $result = $visitRepository->getCADistribuation(15);
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

    /**
     * @Route("/taux_orders")
     * @param Request $request
     * @return mixed
     */
    public function getTauxAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $visitRepository = $this->get('ciblo.customer_repository.visit');
        $result = $visitRepository->getTauxEvolution();
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

    /**
     * @Route("/taux_ca")
     * @param Request $request
     * @return mixed
     */
    public function getCATauxAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $visitRepository = $this->get('ciblo.customer_repository.order');
        $result = $visitRepository->getCATaux();
        var_dump($result);die();
        return $this->render('CibloBundle:Default:index.html.twig');
    }

}