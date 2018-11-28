<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 20:33
 */

namespace OivBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OivController extends BaseController
{

    /**
     * @param Request $request
     * @Route("/",name="result-search")
     */
    public function resultSearchAction(Request $request)
    {
        $repo = $this->get('oiv.variety_repository');
        //var_dump($repo->getCountVariety(['countryCode'=>'FRA']));die;

        //var_dump($this->getStatsCountry(['countryCode'=>'FRA','year'=>'2016']));die;
        $selectedYear = date('Y')-2;
        $selectedCountryCode = $request->query->get('countryCode','oiv');
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => $selectedYear];
        $aParams['stats'] = $this->getStatsCountry($aCriteria);
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
//        $aParams['globalResult'] = $this->getResultGLobalSearch('NamingData', $aCriteria,'tab1');
        $aParams['globalResult'] = [];
//        $aParams['globalStatResult'] = $this->getResultGLobalSearch('NamingData', $aCriteria,'tab2');
        $aParams['globalStatResult'] = [];
        $aParams['selectedCountry'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findOneBy(['iso3' => $selectedCountryCode]);
        $aParams['isMemberShip'] = $this->getDoctrine()->getRepository('OivBundle:OivMemberShip')->isMemberShip($aCriteria);
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['selectedYear'] = $selectedYear;
        //var_dump($aParams['globalResult']);die;
        return $this->render('OivBundle:search:result.html.twig', $aParams);
    }

    /**
     * @Route("/country", name="country-stat-search")
     * @param Request $request
     * @return JsonResponse
     */
    public function statCountryAction(Request $request)
    {
        $aCriteria = [];
        $result = $this->getStatsCountry($aCriteria);
        return new JsonResponse($result);
    }

    /**
     * @Route("/global", name="global-search")
     * @param Request $request
     * @return JsonResponse
     */
    public function globalSearchAction(Request $request)
    {
        $aCriteria = [];
        $result = [];
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table)) {
            $offset =  $request->request->get('offset',0);
            $limit  =  $request->request->get('limit',20);

            if ($request->request->has('countryCode')) {
                $aCriteria['countryCode'] = $request->request->get('countryCode');
            }
            if ($request->request->has('year')) {
                $aCriteria['year'] = $request->request->get('year');
            }else{
                if($request->request->get('yearMax')) {
                    $aCriteria['yearMax'] = $request->request->get('yearMax');
                }
                if($request->request->get('yearMin')) {
                    $aCriteria['yearMin'] = $request->request->get('yearMin');
                }
            }
            foreach($request->request->all() as $field => $val) {
                if (property_exists('OivBundle\\Entity\\'.$table, $field) && $val) {
                    $aCriteria[$field] = $val;
                }
            }
            $count = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getTotalResult($aCriteria);
            //var_dump($offset,$limit,$count);die();
            if ($count  && ($count>$offset)) {
                //$result['last'] = floor($count/$limit)*$limit;
                $result = $this->getResultGLobalSearch($table, $aCriteria, 'tab1', $offset, $limit);
                $result = $this->formatDataTable($result);
                $result['total'] = $count%$limit == 0 ? floor($count/$limit): floor($count/$limit)+1;
                $result['next'] = $count>=($offset+$limit) ? ($offset+$limit):$offset;
                $result['current'] = floor($offset/$limit)+1;
                $result['prev'] = $offset > 0 ? $offset-$limit:0;
                $result['last'] = floor($count/$limit)*$limit == $count ? (floor($count/$limit)-1)*$limit:floor($count/$limit)*$limit;
            }
        }
        //var_dump($result);
        return new JsonResponse($result);
    }

    /**
     * @Route("/global-country", name="global-country-search")
     * @param Request $request
     * @return JsonResponse
     */
    public function globalStatSearchAction(Request $request)
    {
        $aCriteria = [];
        $aCriteria['countryCode'] = $request->request->get('countryCode','oiv');
        if ($request->request->has('year')) {
            $aCriteria['year'] = $request->request->get('year');
        }
        $table = $request->request->get('dbType');
        $view = $request->request->get('view');
        $result = $this->getResultGLobalSearch($table, $aCriteria, $view);
        $result = $this->formatDataTable($result);
        return new JsonResponse($result);
    }

}