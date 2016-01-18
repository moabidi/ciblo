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
     * @Route("/orders", name="orders")
     */
    public function getOrdersDistribuationAction(Request $request){
        $orderRepository    = $this->get('ciblo.customer_repository.order');
        $count              = $orderRepository->getCountOrders();
        $result             = $orderRepository->getOrdersDistribution(15);
        $title              = 'Répartitions des ventes en Décembre 2015';
        return $this->getResponse($result,$count,$title);
    }

    /**
     * @Route("/ca", name="ca")
     * @param Request $request
     * @return mixed
     */
    public function getCADistribuationAction(Request $request){
        $orderRepository    = $this->get('ciblo.customer_repository.order');
        $count              = $orderRepository->getCountOrders();
        $result             = $orderRepository->getCADistribuation(15);
        $title              = 'Répartitions des chiffres affaires en Décembre 2015';
        return $this->getResponse($result,$count,$title);
    }

    /**
     * @Route("/taux_orders", name="taux_orders")
     * @param Request $request
     * @return mixed
     */
    public function getTauxAction(Request $request){
        $visitRepository    = $this->get('ciblo.customer_repository.visit');
        $result             = $visitRepository->getTauxEvolution();
        $aList['name']      = 'Taux de conversion en (%)';
        foreach($result as $row){
            /**
             * @var \DateTime $oDateTime
             */
            $oDateTime      = $row['date'];
            $aDays[]        = $oDateTime->format('Y-m-d');
            $aList['data'][]= $row['nbOrders']*100/$row['nbVisit'];

        }
        $category   = json_encode($aDays);
        $list       = json_encode(array($aList));
        $params     = array('title'     =>'Taux de conversion en Décembre 2015',
                            'axeName'   =>'Taux de conversion en (%)',
                            'list'      =>$list,
                            'days'      =>$category,
                            'max'       =>100,
                            'suffix'    => '%'
                            );
        return $this->render('CibloBundle:Default:index.html.twig',$params);
    }

    /**
     * @Route("/taux_ca", name="taux_ca")
     * @param Request $request
     * @return mixed
     */
    public function getCATauxAction(Request $request){
        $visitRepository    = $this->get('ciblo.customer_repository.order');
        $result             = $visitRepository->getCATaux();
        $aList      = array();
        foreach($result as $row){
            $aDays[]    = $row['day'];
            $aList['data'][$row['day']]= $row['total']/$row['nbOrders'];

        }
        // Set taux of all days of the month
        $aAllDays           = range(01,31,1);
        $aAllList['name']   = 'Taux de panier moyen en (€)';
        foreach($aAllDays as &$day){
            if( $day < 10 )
                $day = '2015-12-0'.$day;
            else
                $day = '2015-12-'.$day;
            if( in_array($day,$aDays) )
                $aAllList['data'][]= round($aList['data'][$day],2);
            else
                $aAllList['data'][]= 0;
        }
        $category   = json_encode($aAllDays);
        $list       = json_encode(array($aAllList));

        $params     = array('title'     =>'Taux de panier moyen en Décembre 2015',
                            'axeName'   =>'Taux de panier moyen en (€)',
                            'list'      =>$list,
                            'days'      =>$category,
                            'max'       =>100000,
                            'suffix'    => '€'
                        );
        return $this->render('CibloBundle:Default:index.html.twig',$params);
    }

    /**
     * @param $result
     * @param $count
     * @param $title
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getResponse($result,$count,$title){
        foreach( $result as &$row){
            $row['y'] = $row['y']*100/$count['totalOrders'];
        }
        $data   = json_encode($result);
        return $this->render('CibloBundle:Default:index.html.twig',array('data'=>$data,'title'=>$title));
    }

}