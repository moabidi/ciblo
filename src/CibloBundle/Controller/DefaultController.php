<?php

namespace CibloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        $visitRepository    = $this->get('ciblo.customer_repository.visit');
        $count              = $visitRepository->getCountVisit();
        $result             = $visitRepository->getResolutionDistribution(15);
        $title              = 'Répartitions des résolutions en Décembre 2015';
        return $this->getResponse($result,$count,$title);
    }

    /**
     * @Route("/device", name="device")
     */
    public function getDeviceDistribuationAction(Request $request){
        $visitRepository    = $this->get('ciblo.customer_repository.visit');
        $count              = $visitRepository->getCountVisit();
        $result             = $visitRepository->getDeviceDistribuation(15);
        $title              = 'Répartitions des péréphiriques en Décembre 2015';
        return $this->getResponse($result,$count,$title);
    }

    /**
     * @Route("/os", name="os")
     */
    public function getOsDistribuationAction(Request $request){
        $visitRepository    = $this->get('ciblo.customer_repository.visit');
        $count              = $visitRepository->getCountVisit();
        $result             = $visitRepository->getOsDistribuation(15);
        $title              = 'Répartitions des systemes en Décembre 2015';
        return $this->getResponse($result,$count,$title);
    }

    /**
     * @Route("/navigator", name="navigator")
     */
    public function getNavigatorDistribuationAction(Request $request){
        $visitRepository    = $this->get('ciblo.customer_repository.visit');
        $count              = $visitRepository->getCountVisit();
        $result             = $visitRepository->getNavigatorDistribuation(15);
        $title              = 'Répartitions des navigateurs en Décembre 2015';
        return $this->getResponse($result,$count,$title);
    }

    private function getResponse($result,$count,$title){
        foreach( $result as &$row){
            $row['y'] = $row['y']*100/$count['totalVisit'];
        }
        $data   = json_encode($result);
        return $this->render('CibloBundle:Default:index.html.twig',array('data'=>$data,'title'=>$title));
    }

}
