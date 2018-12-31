<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 25/11/18
 * Time: 22:30
 */

namespace OivBundle\Controller;


use Monolog\Logger;
use OivBundle\Entity\Country;
use OivBundle\Repository\StatDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class BaseController extends Controller
{

    protected $_aTableType = ['StatData', 'EducationData', 'NamingData', 'VarietyData'];
    /**
     * @param $table
     * @param array $aCriteria
     * @param bool $view
     * @param int $offset
     * @param int $limit
     * @param $sort
     * @param $order
     * @return mixed
     */
    protected function getResultGLobalSearch($table, $aCriteria = [], $view=false, $offset=0, $limit =200, $sort= null, $order = null)
    {
        $result = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getGlobalResult($aCriteria,$offset, $limit, $sort, $order);
        if (in_array($view, ['tab1','tab2','tab3'])) {
            $selectedFields = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getTaggedFields($view);
            $translator = $this->get('translator');
            array_walk($result, function (&$v, $k) use ($selectedFields, $translator) {
                $selectedData = [];
                foreach ($selectedFields as $field) {
                    $selectedData[$field] = $translator->trans($v[$field]);
                }
                $v = $selectedData;
            });
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getFiltredFiled($view = 'public')
    {
        foreach ($this->_aTableType as $table) {
            $repository = $this->getDoctrine()->getRepository('OivBundle:' . $table);
            $aFiltredFields[$table] = $repository->getTaggedFields('filter');
            foreach ($aFiltredFields[$table] as $field => &$field) {
                $aValues = $repository->getDistinctValueField($field);
                $field = ['label' => $field, 'values' => $aValues];
            }
        }
        if (isset($aFiltredFields['StatData']['statType'])) {
            $aFiltredFields['StatData']['statType']['values'] = $this->getDoctrine()->getRepository('OivBundle:StatDataParameter')->getListProduct($view);
        }
        return $aFiltredFields;
    }

    /**
     * @param array $aCriteria
     * @return array
     */
    protected function getStatsCountry($aCriteria = [], $minDate = false, $maxDate = false)
    {
        $repository = $this->get('oiv.stat_repository');
        $minData = $minDate ? $minDate:'1990';
        $maxDate = $maxDate ? $maxDate:date('Y')-2;
        $allStats = $this->getStatProducts(array_merge($aCriteria, ['yearMin' => $minData, 'yearMax' => $maxDate]));
        //var_dump($aCriteria,$repository->getSingleValueStatType('A_SURFACE', $aCriteria));die;
        return [
            'products' => $this->getStatProducts($aCriteria,true),
            'graphProducts' => $this->formatDataGraph($allStats,$minData,$maxDate),
            'globalArea' => $repository->getSingleValueStatType('A_SURFACE', $aCriteria),
            'nbVariety' => $this->get('oiv.variety_repository')->getCountVariety($aCriteria),
            'nbNaming' => $this->get('oiv.naming_repository')->getCountNaming($aCriteria),
            'nbEducation' => $this->get('oiv.education_repository')->getCountEducation($aCriteria),
        ];
    }

    /**
     * @param $aCriteria
     * @return []
     */
    protected function getStatProducts($aCriteria = [], $single = false)
    {
        $translator = $this->get('translator');
        $aProducts = [
            [
                'label' => $translator->trans('rfresh'),
                'name' => 'rfresh',
                'stat' => [
                    'prod' => 'C_PROD_GRP',
                    'export' => 'I_EXPORT_GRP',
                    'import' => 'H_IMPORT_GRP',
                    'consumption' => '',
                    'indovcons' => '',
                ]

            ],
            [
                'label' => $translator->trans('rin'),
                'name' => 'rin',
                'stat' => [
                    'prod' => 'P_PRODUCTION_WINE',
                    'export' => 'R_EXPORT_WINE',
                    'import' => 'Q_IMPORT_WINE',
                    'consumption' => 'S_CONSUMPTION_WINE',
                    'indovcons' => '',
                ]

            ],
            [
                'label' => $translator->trans('rtable'),
                'name' => 'rtable',
                'stat' => [
                    'prod' => 'F_PROD_TABLE_GRP',
                    'export' => '',
                    'import' => '',
                    'consumption' => 'L_COMSUMPTION_TABLE_GRP',
                    'indovcons' => '',
                ]

            ],
            [
                'label' => $translator->trans('rsec'),
                'name' => 'rsec',
                'stat' => [
                    'prod' => 'G_PROD_DRIED_GRP',
                    'export' => 'K_EXPORT_DRIED_GRP',
                    'import' => 'J_IMPORT_DRIED_GRP',
                    'consumption' => 'N_CONSUMPTION_DRIED_GRP',
                    'indovcons' => 'M_COMSUMPTION_CAPITA_GRP',
                ]

            ],
            [
                'label' => $translator->trans('area'),
                'name' => 'area',
                'stat' => [
                    'prod' => 'A_SURFACE',
                ]

            ],

        ];
        /**@var StatDataRepository $repository */
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

    /**
     * @param $aData
     * @param $minDate
     * @param $maxDate
     * @return array ['xAxis' => ['p1' => ['y1','Yn']], 'p1'=>['d1','dn'], 'pn'=>['d1','dm']]
     */
    protected function formatDataGraph($aData, $minDate, $maxDate)
    {
        $translator = $this->get('translator');
        $formattedData = ['xAxis'=>[],'yAxis'=>[]];
        for($y = $minDate; $y<=$maxDate; $y++) {
            $formattedData['xAxis'][]= $y;
        }
        $mesure = '1000 QX';
        foreach ($aData as $product) {
            $productName = $product['name'];
            $formattedData['yAxis'][$productName] = [];
            array_walk($product['stat'], function ($value, $key) use (&$formattedData, $productName, $translator, $mesure) {
                $formattedData['yAxis'][$productName][] = $this->getDataProductGraph($key,$value, $formattedData['xAxis'],$translator,$mesure);
                $formattedData['mesure'] = $mesure;
            });
            //var_dump($formattedData );die;
        }
        return $formattedData;
    }

    protected function getDataProductGraph($productName,$aListData, $aListYears,$translator,&$mesure)
    {
        $formattedData['data'] = [];
        $formattedData['name'] = $translator->trans($productName);
        foreach ($aListYears as $year) {
            $formattedData['data'][$year] = '';
        }
        if ($aListData) {
            foreach ($aListData as $stat) {
                $mesure = isset($stat['measureType']) ? $stat['measureType']:'';
                if ($stat['value']) {
                    $formattedData['data'][$stat['year']] = floatval($stat['value']);
                }
            }
            if ($mesure) {
                $mesure = $translator->trans($mesure);
                $formattedData['name'] = $translator->trans($productName) . ' ('.$mesure.')';
            }
        }
        $formattedData['data'] = array_values($formattedData['data']);
        return $formattedData;
    }

    /**
     * format and translate data table
     * @param $data
     * @return array
     */
    public function formatDataTable($data)
    {
        $aResult = [];
        if (count($data)) {
            $translator = $this->get('translator');
            $aResult['labelfields'] = [];
            foreach($data[0] as $field => $val) {
                if (in_array($field, ['countryNameFr','countryNameEn','countryNameIt','countryNameEs'])) {
                    $aResult['labelfields']['countryCode'] = $translator->trans($field);
                } else {
                    $aResult['labelfields'][$field] = $translator->trans($field);
                }
            }
            $aResult['data'] = $data;
        }
        return $aResult;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getCriteriaRequest(Request $request)
    {
        $aCriteria = [];
        $table = ucfirst($request->request->get('dbType')).'Data';
        if ($request->request->has('countryCode')) {
            $aCriteria['countryCode'] = $request->request->get('countryCode');
        }
        if ($request->request->has('year')) {
            $aCriteria['yearMax'] = $request->request->get('year');
            $aCriteria['yearMin'] = $request->request->get('year');
        }else{
            if($request->request->get('yearMax')) {
                $aCriteria['yearMax'] = $request->request->get('yearMax');
            }
            if($request->request->get('yearMin')) {
                $aCriteria['yearMin'] = $request->request->get('yearMin');
            }
        }
        if ($request->request->has('value')) {
            $aCriteria['valueMax'] = $request->request->get('value');
            $aCriteria['valueMin'] = $request->request->get('value');
        }else{
            if($request->request->get('valueMax')) {
                $aCriteria['valueMax'] = $request->request->get('valueMax');
            }
            if($request->request->get('valueMin')) {
                $aCriteria['valueMin'] = $request->request->get('valueMin');
            }
        }
        foreach($request->request->all() as $field => $val) {
            if (property_exists('OivBundle\\Entity\\'.$table, $field) && $val) {
                $aCriteria[$field] = $val;
            }
        }
        if ($aTableFilters = $request->request->get('tableFilters')) {
            if (isset($aTableFilters['countryCode'])){
                $aTableFilters['countryCode'] = $this->getCountryCode($aTableFilters['countryCode']);
            }
            foreach($aTableFilters as $field => $val) {
                if (property_exists('OivBundle\\Entity\\'.$table, $field) && $val) {
                    $aCriteria[$field] = $val;
                }
            }
        }
        return $aCriteria;
    }

    /**
     * @return Logger object
     */
    protected function getLogger()
    {
        return $this->get('logger');
    }

    protected function checkIsXHTMLRequest(Request $request)
    {
        if ($request->isXmlHttpRequest() && !$request->isMethod('POST')) {
            return false;
        }
        return true;
    }

    protected function getCountryCode($countryName)
    {
        $countryCode = $countryName;
        /**@var Country $result */
        $result = $this->getDoctrine()->getRepository('OivBundle:Country')->getCountryCode($countryName);
        if ($result) {
            $countryCode = $result->getIso3();
        }
        return $countryCode;
    }

    protected function getSerelizer()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        return new Serializer($normalizers, $encoders);

    }
}