<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 20:33
 */

namespace OivBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OivController extends Controller
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
        $selectedYear = '2016';
        $selectedCodeCountry = 'FRA';
        $aCriteria = ['countryCode' => $selectedCodeCountry, 'year' => $selectedYear];
        $aParams['stats'] = $this->getStatsCountry($aCriteria);
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
        $aParams['globalResult'] = $this->getResultGLobalSearch('EducationData', $aCriteria,'tab1');
        $aParams['globalStatResult'] = $this->getResultGLobalSearch('EducationData', $aCriteria,'tab2');
        $aParams['selectedCountry'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findOneBy(['iso3' => 'FRA']);
        $aParams['isMemberShip'] = $this->getDoctrine()->getRepository('OivBundle:OivMemberShip')->isMemberShip($aCriteria);
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
        if ($request->request->has('countryCode')) {
            $aCriteria['countryCode'] = $request->request->get('countryCode');
        }
        if ($request->request->has('year')) {
            $aCriteria['year'] = $request->request->get('year');
        }
        $table = $request->request->get('dbType');
        $view = $request->request->get('view');
        $result = $this->getResultGLobalSearch($table, $aCriteria, $view);
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
        if ($request->request->has('countryCode')) {
            $aCriteria['countryCode'] = $request->request->get('countryCode');
        }
        if ($request->request->has('year')) {
            $aCriteria['year'] = $request->request->get('year');
        }
        $table = $request->request->get('dbType');
        $view = $request->request->get('view');
        $result = $this->getResultGLobalSearch($table, $aCriteria, $view);
        $labelfields = [];
        if (count($result)) {
            $translator = $this->get('translator');
            foreach($result[0] as $field => $val) {
                $labelfields[] = $translator->trans($field);
            }
        }
        return new JsonResponse(['data' => $result, 'labelfields'=>$labelfields]);
    }

    /**
     * @param string $table
     * @param array $aCriteria
     * @return array
     */
    private function getResultGLobalSearch($table, $aCriteria = [], $view=false)
    {
        $result = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getGlobalResult($aCriteria);
        if (in_array($view, ['tab1','tab2'])) {
            $selectedFields = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getTaggedFields($view);
            array_walk($result, function (&$v, $k) use ($selectedFields) {
                $selectedData = [];
                foreach ($selectedFields as $field) {
                    $selectedData[$field] = $v[$field];
                }
                $v = $selectedData;
            });
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getFiltredFiled()
    {
        $aTableType = ['StatData', 'EducationData', 'NamingData', 'VarietyData'];
        foreach ($aTableType as $table) {
            $repository = $this->getDoctrine()->getRepository('OivBundle:' . $table);
            $aFiltredFields[$table] = $repository->getTaggedFields('filter');
            foreach ($aFiltredFields[$table] as $field => &$field) {
                $aValues = $repository->getDistinctValueField($field);
                $field = ['label' => $field, 'values' => $aValues];
            }
        }
        return $aFiltredFields;
    }

    /**
     * @param array $aCriteria
     * @return array
     */
    private function getStatsCountry($aCriteria = [], $minDate = false, $maxDate = false)
    {
        $repository = $this->get('oiv.stat_repository');
        $minData = '1990';
        $maxDate = '2018';
        $allStats = $this->getStatProducts(array_merge($aCriteria, ['minDate' => $minData, 'maxDate' => $maxDate]));
        return [
            'products' => $this->getStatProducts($aCriteria,true),
            'graphProducts' => $this->formatDataGraph($allStats,$minData,$maxDate),
            'globalArea' => $repository->getSingleValueStatType('A_SURFACE', $aCriteria),
            'usedArea' => $repository->getSingleValueStatType('C_PROD_GRP', $aCriteria),
            'nbVariety' => $this->get('oiv.variety_repository')->getCountVariety($aCriteria),
            'nbEducation' => $this->get('oiv.education_repository')->getCountEducation($aCriteria),
            'nbNaming' => $this->get('oiv.naming_repository')->getCountNaming($aCriteria)
        ];
    }

    /**
     * @param $aCriteria
     * @return []
     */
    private function getStatProducts($aCriteria = [], $single = false)
    {
        $aProducts = [
            [
                'label' => 'Raisin frais',
                'name' => 'rfresh',
                'stat' => [
                    'prod' => 'C_PROD_GRP',
                    'consumption' => '',
                    'export' => 'I_EXPORT_GRP',
                    'import' => 'H_IMPORT_GRP'
                ]

            ],
            [
                'label' => 'Vin',
                'name' => 'rin',
                'stat' => [
                    'prod' => 'P_PRODUCTION_WINE',
                    'consumption' => 'S_CONSUMPTION_WINE',
                    'export' => 'R_EXPORT_WINE',
                    'import' => 'Q_IMPORT_WINE'
                ]

            ],
            [
                'label' => 'Raisin de tables',
                'name' => 'rtable',
                'stat' => [
                    'prod' => '',
                    'consumption' => 'L_COMSUMPTION_TABLE_GRP',
                    'export' => '',
                    'import' => ''
                ]

            ],
            [
                'label' => 'Raisin sec',
                'name' => 'rsec',
                'stat' => [
                    'prod' => 'G_PROD_DRIED_GRP',
                    'consumption' => 'N_CONSUMPTION_DRIED_GRP',
                    'export' => 'K_EXPORT_DRIED_GRP',
                    'import' => 'J_IMPORT_DRIED_GRP',
                ]

            ]
        ];
        $repository = $this->get('oiv.stat_repository');
        foreach ($aProducts as &$product) {
            foreach ($product['stat'] as $key => &$statType) {
                if ($single) {
                    $result = $repository->getSingleValueStatType($statType, $aCriteria);
                    $product['stat'][$key] = $result['val'];
                    if ($result['measure']) {
                        $product['measure'] = $result['measure'];
                    }
                }else{
                    $result = $repository->getMultiValueStatType($statType, $aCriteria);
                    $product['stat'][$key] = $result;
                }
            }
        }
        //var_dump($aProducts);die;
        return $aProducts;
    }

    private function formatDataGraph($aData, $minDate, $maxDate)
    {
        $formattedData = ['xAxis'=>[]];
        for($y = $minDate; $y<=$maxDate; $y++) {
            $formattedData['xAxis'][]= $y;
        }

        foreach ($aData as $product) {
            $productName = $product['name'];
            $formattedData[$productName] = [];
            array_walk($product['stat'], function ($value, $key) use (&$formattedData, $productName, $minDate, $maxDate) {
                $typeStat = ['name' => $key, 'data' => []];
                for($y = $minDate; $y<=$maxDate; $y++) {
                    $typeStat['data'][$y] = 0;
                }
                if ($value) {
                    foreach ($value as $stat) {
                        $typeStat['data'][$stat['year']] = in_array($stat['year'],$formattedData['xAxis']) ? floatval($stat['value']):0;
                    }
                }
                $typeStat['data'] = array_values($typeStat['data']);
                //var_dump($typeStat);die;
                $formattedData[$productName][] = $typeStat;
            });
            //var_dump($formattedData );die;
        }
        return $formattedData;
    }
}