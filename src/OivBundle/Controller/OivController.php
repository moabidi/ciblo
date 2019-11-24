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
        $selectedYear = $this->getLastStatYear();
        $selectedCountryCode = $request->query->get('countryCode','oiv');
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => $selectedYear];
        $aCriteria['countryName'] = 'countryName'.ucfirst($this->get('translator')->getLocale());
        $aParams['stats'] = $this->getStatsCountry($aCriteria);
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getCountries($aCriteria['countryName']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $selectedCountry = $this->getDoctrine()->getRepository('OivBundle:Country')->findOneBy(['iso3' => $selectedCountryCode]);
        $aParams['selectedCountry'] = null;
        if($selectedCountry) {
            $aParams['selectedCountry'] = [
                'iso3' => $selectedCountry->getIso3(),
                'iso2' => $selectedCountry->getIso2(),
                'countryNameEn' => $selectedCountry->getCountryNameEn(),
                'countryNameFr' => $selectedCountry->getCountryNameFr(),
                'countryNameIt' => $selectedCountry->getCountryNameIt(),
                'countryNameEs' => $selectedCountry->getCountryNameEs(),
                'countryNameDe' => $selectedCountry->getCountryNameDe(),
                'tradeBloc' => $selectedCountry->getTradeBloc(),
            ];
        }
        //var_dump($aParams['selectedCountry']);die;
        if (!$aParams['selectedCountry'] ) {
            foreach ($aParams['tradeBlocs'] as $trade) {
                if ($selectedCountryCode == $trade['tradeBloc'] ) {
                    $aParams['selectedCountry'] = ['iso3' => $selectedCountryCode,'iso2' => $selectedCountryCode, 'countryNameDe' => $selectedCountryCode, 'countryNameEn' => $selectedCountryCode,'countryNameEs' => $selectedCountryCode,'countryNameIt' => $selectedCountryCode,'countryNameFr' => $selectedCountryCode];
                    break;
                }
            }
        }
        //$aParams['isMemberShip'] = $this->getDoctrine()->getRepository('OivBundle:OivMemberShip')->isMemberShip($aCriteria);
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['selectedYear'] = $selectedYear;
        $aParams['lastStatYear'] = $this->getLastStatYear();
        $oTranslator = $this->get('translator');
        $aParams['transData'] = [
            'infoCodeVivc'=>$oTranslator->trans('infoCodeVivc'),
            'data_not_available'=> $oTranslator->trans('Data not available'),
            'no_result_search'=> $oTranslator->trans('No results found for your search'),
            'no_result_found'=> $oTranslator->trans('No results found'),
            'error_response'=> $oTranslator->trans('error response'),

        ];
        $host = $request->getScheme().'://127.0.0.1:1987/'.$request->getLocale();
        $aParams['header'] = file_get_contents($host.'/header');
        $aParams['footer'] = file_get_contents($host.'/footer');
        $aParams['navMobile'] = file_get_contents($host.'/nav-mobile');
        $aParams['popupCookies'] = file_get_contents($host.'/popup-cookies');
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
        $result = [];
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table)) {
            $offset =  $request->request->get('offset',0);
            $limit  =  $request->request->get('limit',20);
            $sort   =  $request->request->get('sort');
            $order  =  $request->request->get('order');
            $view  =  $request->request->get('view');

            $aCriteria = $this->getCriteriaRequest($request);
            if($request->request->has('bo')){
                $aCriteria['bo'] = 1;
            }else{
                $aCriteria['isMainVariety'] = 1;
                if ($request->request->has('yearMax') && !$request->request->get('yearMax')) {
                    $aCriteria['yearMax'] = $this->getMaxYear(date('Y')-2);
                }
                $aCriteria['countryName'] = 'countryName'.ucfirst($this->get('translator')->getLocale());
            }

            $count = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getTotalResult($aCriteria);
            if ($count  && ($count>$offset)) {
                $result = $this->getResultGLobalSearch($table, $aCriteria, $view, $offset, $limit,$sort,$order);
                $result = $this->formatDataTable($result);
                $result = $this->getParamsPagination($result, $count, $offset, $limit);
                if ($request->request->has('bo') && $table == 'NamingData') {
                    $namingRepository = $this->getDoctrine()->getRepository('OivBundle:NamingData');
                    array_walk($result['data'], function (&$val) use ($namingRepository) {
                        $aListBase = $namingRepository->getInfoNaming($val['appellationName'],$val['appellationCode'],false);
                        $val['listReferenceName'] = array_map(function($row){
                            return $row['referenceName'];
                        }, $aListBase);
                        $val['listReferenceName'] = implode('</br>',$val['listReferenceName']);
                    });
                }
                $result['dbType'] = $request->request->get('dbType');
                $result['textViewMore'] = $this->get('translator')->trans('View more');
                $result['textView'] = $this->get('translator')->trans('View');
            }
        }
        return new JsonResponse($result);
    }

    /**
     * @Route("/global-country", name="global-country-search")
     * @param Request $request
     * @return JsonResponse
     */
    public function globalStatSearchAction(Request $request)
    {
        $result = [];
        $aCriteria = $this->getCriteriaRequest($request);
        $aCriteria['isMainVariety'] = 1;
        $aCriteria['countryName'] = 'countryName'.ucfirst($this->get('translator')->getLocale());
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table)) {
            $count = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getTotalResult($aCriteria);
            if ($count) {
                $view = $request->request->get('view');
                $offset =  $request->request->get('offset',0);
                $limit  =  $request->request->get('limit',20);
                $sort   =  $request->request->get('sort');
                $order  =  $request->request->get('order');
                $result = $this->getResultGLobalSearch($table, $aCriteria, $view, $offset, $limit,$sort,$order);
                $result = $this->formatDataTable($result);
                $result = $this->getParamsPagination($result, $count, $offset, $limit);
                $result['dbType'] = $request->request->get('dbType');
                $result['textViewMore'] = $this->get('translator')->trans('View more');
                $result['textView'] = $this->get('translator')->trans('View');
            }
        }
        return new JsonResponse($result);
    }

    /**
     * @Route("/info-naming", name="info-naming-search")
     * @param Request $request
     * @return JsonResponse
     */
    public function infoNamingSearchAction(Request $request)
    {
        $result = [];
        if ($appellationName = $request->request->get('appellationName')) {
            $appellationCode = $request->request->get('appellationCode');
            $isCtg = $request->request->get('isCtg', true);
            $result['data'] =  $this->getDoctrine()->getRepository('OivBundle:NamingData')->getInfoNaming($appellationName, $appellationCode, $isCtg,false);
            $otranslator = $this->get('translator');
            array_walk($result['data'], function(&$v)use($otranslator){
                if(isset($v['productType'])) {
                    $v['productType'] = $otranslator->trans($v['productType']);
                }
            });
            $result['isCtg'] = $isCtg;
            $result['appellationName'] = $appellationName;
        }
        //var_dump($result);die;
        return new JsonResponse($result);
    }


    /**
     * @param $result
     * @param $count
     * @param $offset
     * @param $limit
     * @return mixed
     */
    private function getParamsPagination($result, $count, $offset, $limit)
    {
        $result['total'] = $count%$limit == 0 ? floor($count/$limit): floor($count/$limit)+1;
        $result['next'] = $count>=($offset+$limit) ? ($offset+$limit):$offset;
        $result['current'] = floor($offset/$limit)+1;
        $result['prev'] = $offset > 0 ? $offset-$limit:0;
        $result['last'] = floor($count/$limit)*$limit == $count ? (floor($count/$limit)-1)*$limit:floor($count/$limit)*$limit;
        $result['count'] = $count;
        return $result;
    }

}