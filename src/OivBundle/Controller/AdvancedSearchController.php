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

class AdvancedSearchController extends BaseController
{
    /**
     * @param Request $request
     * @Route("/recherche",name="index-adv-search")
     */
    public function indexAction(Request $request)
    {
        $selectedCountryCode = $request->query->get('countryCode','oiv');
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => '2016'];
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['globalResult'] = $this->getResultGLobalSearch('StatData', $aCriteria,'tab1');
        //var_dump($aParams['globalResult']);die;
        return $this->render('OivBundle:advancedSearch:index.html.twig',$aParams);
    }
}