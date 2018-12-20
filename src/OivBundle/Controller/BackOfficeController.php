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

    /**
     * @param Request $request
     * @Route("/backoffice/manager",name="manager-backoffice")
     */
    public function adminAction(Request $request)
    {
        $selectedCountryCode = $request->query->get('countryCode','oiv');
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => date('Y')-2];
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['globalResult'] = $this->getResultGLobalSearch('StatData', $aCriteria,'tab2');
        return $this->render('OivBundle:backOffice:index.html.twig',$aParams);
    }
}