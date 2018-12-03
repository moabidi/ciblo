<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 25/11/18
 * Time: 22:30
 */

namespace OivBundle\Controller;


use OivBundle\Repository\StatDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{

    /**
     * @param string $table
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @return array
     */
    protected function getResultGLobalSearch($table, $aCriteria = [], $view=false, $offset=0, $limit =100)
    {
        $result = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getGlobalResult($aCriteria,$offset, $limit);
        if (in_array($view, ['tab1','tab2'])) {
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
    protected function getFiltredFiled()
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
    protected function getStatsCountry($aCriteria = [], $minDate = false, $maxDate = false)
    {
        $repository = $this->get('oiv.stat_repository');
        $minData = $minDate ? $minDate:'1990';
        $maxDate = $maxDate ? $maxDate:date('Y')-2;
        $allStats = $this->getStatProducts(array_merge($aCriteria, ['yearMin' => $minData, 'yearMax' => $maxDate]));
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
    protected function getStatProducts($aCriteria = [], $single = false)
    {
        $aProducts = [
            [
                'label' => 'Raisins totales',
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
                    'prod' => 'F_PROD_TABLE_GRP',
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

        foreach ($aData as $product) {
            $productName = $product['name'];
            $formattedData['yAxis'][$productName] = [];
            array_walk($product['stat'], function ($value, $key) use (&$formattedData, $productName, $translator) {
                $formattedData['yAxis'][$productName][] = $this->getDataProductGraph($productName,$value, $formattedData['xAxis'],$translator);

            });
            //var_dump($formattedData );die;
        }
        return $formattedData;
    }

    protected function getDataProductGraph($productName,$aListData, $aListYears,$translator)
    {
        $formattedData['name'] = $translator->trans($productName);
        $formattedData['data'] = [];
        foreach ($aListYears as $year) {
            $formattedData['data'][$year] = '';
        }
        if ($aListData) {
            foreach ($aListData as $stat) {
                if ($stat['value']) {
                    $formattedData['data'][$stat['year']] = floatval($stat['value']);
                }
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
                $aResult['labelfields'][] = $translator->trans($field);
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
        return $aCriteria;
    }

}