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
use Spraed\PDFGeneratorBundle\PDFGenerator\PDFGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        if (in_array($view, ['tab1','tab2','tab3','export','exportBo'])) {
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
     * @param $table
     * @param array $aCriteria
     * @param bool $view
     * @param int $offset
     * @param int $limit
     * @param $sort
     * @param $order
     * @return mixed
     */
    protected function getExportGLobalSearch($table, $aCriteria = [], $view=false, $offset=0, $limit =200, $sort= null, $order = null)
    {
        $result = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getExportResult($aCriteria,$offset, $limit, $sort, $order);
        if (in_array($view, ['tab2','tab3','export','exportBo']) && $result) {
            $selectedFields = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getTaggedFields($view);
            $translator = $this->get('translator');
            array_walk($result, function (&$v, $k) use ($selectedFields, $translator) {
                $selectedData = [];
                foreach ($selectedFields as $field) {
                    if (in_array($field, ['statType','measureType','productCategoryName','productType','typeInternationalCode'])) {
                        $selectedData[$field] = $translator->trans($v[$field]);
                    } else {
                        $selectedData[$field] = $v[$field];
                    }
                }
                $v = $selectedData;
            });
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCriteriaExport(Request $request)
    {
        $aCriteria = $this->getCriteriaRequest($request);
        $exportType = $request->request->get('exportType','csv');
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table) && in_array($exportType, ['csv','pdf'])) {
            $exportKey = uniqid($table);
            $request->getSession()->set($exportKey,['table'=>$table, 'criteria'=>$aCriteria]);
            $route = $exportType == 'pdf' ? 'export-pdf-adv-search':'export-csv-adv-search';
            if ($this->getUser()) {
                $route = $exportType == 'pdf' ? 'export-pdf-bo-search' : 'export-csv-bo-search';
            }
            return new JsonResponse(['href'=>$this->generateUrl($route, ['exportKey'=>$exportKey])]);
        }
        return new JsonResponse([]);
    }

    /**
     * @param $aDataSession
     * @return StreamedResponse|void
     */
    protected function getExportedCSVData($aDataSession)
    {
        if ($aDataSession) {
            $aCriteria = $aDataSession['criteria'];
            $table = $aDataSession['table'];
            $view = 'export';
            if ($this->getUser()) {
                $view = 'exportBo';
            }
            $results = $this->getExportGLobalSearch($table, $aCriteria, $view,0,null);
            $translator = $this->get('translator');
            $response = new StreamedResponse();
            $response->setCallback(function() use ($results, $translator) {
                $handle = fopen('php://output', 'w+');
                $header = [];
                if($results) {
                    foreach (array_keys($results[0]) as $field) {
                        //$header[] = mb_convert_encoding($translator->trans($field), 'ISO-8859-1', 'UTF-8');
                        $header[] = $translator->trans($field);
                    }
                    fputcsv($handle, $header, ';');
                    $count = 1;
                    foreach ($results as $row) {
                        //$row = $this->encodeData($row);
                        fputcsv($handle, $row, ';');
                        if($count%100 ==0 ){
                            fclose($handle);
                            $handle = fopen('php://output', 'w+');
                            $count++;
                        }
                    }
                }
                fclose($handle);
            });

            $response->setStatusCode(200);
            $response->headers->set('Content-Encoding', ' UTF-8');
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition','attachment; filename="export-'.date('Ymd-his').'.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            return $response;
        }
        return;
    }

    /**
     * @param $aDataSession
     * @return Response|void
     */
    public function getExportedPdfData($aDataSession)
    {
        if ($aDataSession) {
            $aCriteria = $aDataSession['criteria'];
            $table = $aDataSession['table'];
            $view = $table == 'StatData' ? 'export':'tab2';
            if ($this->getUser()) {
                $view = $table == 'StatData' ? 'exportBo':'tab3';
            }
            $aParams['globalResult'] = $this->getResultGLobalSearch($table, $aCriteria, $view,0,null);
            $html = $this->renderView('OivBundle:advancedSearch:print.html.twig', $aParams);
            /**@var PDFGenerator $pdfGenerator */
            $pdfGenerator = $this->get('spraed.pdf.generator');

            return new Response($pdfGenerator->generatePDF($html),
                200,
                array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="out.pdf"'
                )
            );
        }
        return;
    }

    /**
     * @param $row
     * @return mixed
     */
    protected function encodeData($row)
    {
        foreach ($row as &$value) {
            //$value = htmlspecialchars($value);
            //$value = html_entity_decode($value,ENT_QUOTES,'ISO-8859-1');
            //$value = strip_tags(mb_convert_encoding($value, 'ISO-8859-5', 'UTF-8'));
        }
        return $row;
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
        $minData = $minDate ? $minDate:'1995';
        $maxDate = $maxDate ? $maxDate:date('Y')-2;
        if (date('Y') - $maxDate < 3) {
            $maxDate = date('n')>8 ? date('Y')-2:date('Y')-3;
        }
        $allStats = $this->getStatProducts(array_merge($aCriteria, ['yearMin' => $minData, 'yearMax' => $maxDate]));
        foreach ($allStats as &$product) {
            if (in_array($product['name'], ['rfresh','rin','rtable','rsec'])) {
                unset($product['stat']['indovcons']);
            }
        }
        return [
            'products' => $this->getStatProducts($aCriteria,true),
            'graphProducts' => $this->formatDataGraph($allStats,$minData,$maxDate),
            'globalArea' => $repository->getSingleValueStatType('A_SURFACE', $aCriteria),
            'nbVariety' => $this->get('oiv.variety_repository')->getCountVariety($aCriteria+['isMainVariety' => '1']),
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
            [
                'label' => $translator->trans('rfresh'),
                'name' => 'rfresh_indovcons',
                'stat' => [
                    'indovcons' => '',
                ]

            ],
            [
                'label' => $translator->trans('rin'),
                'name' => 'rin_indovcons',
                'stat' => [
                    'indovcons' => '',
                ]

            ],
            [
                'label' => $translator->trans('rtable'),
                'name' => 'rtable_indovcons',
                'stat' => [
                    'indovcons' => '',
                ]

            ],
            [
                'label' => $translator->trans('rsec'),
                'name' => 'rsec_indovcons',
                'stat' => [
                    'indovcons' => 'M_COMSUMPTION_CAPITA_GRP',
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
     * @return array ['yAxis' => ['p1' => ['y1','Yn']], 'p1'=>['d1','dn'], 'pn'=>['d1','dm']]
     */
    protected function formatDataGraph($aData, $minDate, $maxDate)
    {
        $translator = $this->get('translator');
        $formattedData = ['xAxis'=>[],'yAxis'=>[]];
        for($y = $minDate; $y<=$maxDate; $y++) {
            $formattedData['xAxis'][]= $y;
        }
        $mesure = 'tonnes';
        foreach ($aData as $product) {
            $productName = $product['name'];
            $formattedData['yAxis'][$productName] = [];
            array_walk($product['stat'], function ($value, $key) use (&$formattedData, $productName, $translator, $mesure) {
                if ($key == 'prod' && $productName == 'area') {
                    $key = 'area rin';
                }
                $statType = ucfirst(strtolower($translator->trans($key)));
                $formattedData['yAxis'][$productName][] = $this->getDataProductGraph($statType,$value, $formattedData['xAxis'],$translator,$mesure,$productName);
                $formattedData['mesure'] = $translator->trans($mesure);
            });
            //var_dump($formattedData );die;
        }
        return $formattedData;
    }

    protected function getDataProductGraph($productName,$aListData, $aListYears,$translator,&$mesure, $parentProductName=null)
    {
        $formattedData['data'] = [];
        $formattedData['name'] = $productName;
        foreach ($aListYears as $year) {
            $formattedData['data'][$year] = '';
        }
        if ($aListData) {
            foreach ($aListData as $stat) {
                $mesure = isset($stat['measureType']) ? $stat['measureType']:'';
                if ($stat['value']) {
                    $formattedData['data'][$stat['year']] = intval($stat['value']);
                }
            }
            if ($mesure) {
                $mesure = $translator->trans($mesure);
                $formattedData['name'] = $productName . ' ('.$mesure.')';
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
                $aTableFilters['countryCode'] = $this->checkFiltredCountry($aTableFilters['countryCode'], $aCriteria['countryCode']);
            }
            foreach($aTableFilters as $field => $val) {
                if (property_exists('OivBundle\\Entity\\'.$table, $field) && $val) {
                    $aCriteria[$field] = $val;
                    if ($field == 'year') {
                        $aCriteria['yearMax'] = $aCriteria['yearMin'] = $val;
                    }
                }
            }
        }
        if(isset($aCriteria['countryCode'])) {
            $aCriteria['countryCode'] = explode(',', $aCriteria['countryCode']);
            $index = array_search('', $aCriteria['countryCode']);
            if($index !== false) {
                unset($aCriteria['countryCode'][$index]);
            }
            $aCriteria['countryCode'] = implode(',', $aCriteria['countryCode']);
        }

        if($request->request->get('memberShip')) {
            $aCountries = $this->getDoctrine()->getRepository('OivBundle:OivMemberShip')->getMemberCountries($aCriteria);
            array_walk($aCountries, function(&$v,$k){
                $v = $v['iso3'];
            });
            $aCriteria['countryCode'] = implode(',',$aCountries);
        }
        return $aCriteria;
    }

    /** Check filtred country in selected country
     * @param $filtredCountry
     * @param $selectedCountry
     * @return string
     */
    protected function checkFiltredCountry($filtredCountry, $selectedCountry)
    {
        $selectedCountry = trim($selectedCountry);
        $aSelectedCountry = explode(',',$selectedCountry);
        if (in_array('oiv',$aSelectedCountry)) {
            return $this->getCountryCode($filtredCountry);
        } else {
            if (count(array_intersect([$filtredCountry],$aSelectedCountry))){
                return $this->getCountryCode($filtredCountry);;
            } elseif(count(array_intersect($aSelectedCountry, ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE']))) {
                $result = $this->getDoctrine()->getRepository('OivBundle:Country')->checkFiltredCountry($filtredCountry,$aSelectedCountry);
                if ($result) {
                    return $result->getIso3();
                }
            }
        }
        return 'noCountry';
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
        if ($result instanceof Country) {
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

    protected function getMaxYear($maxYear)
    {
        if (date('Y') - $maxYear < 3) {
            $maxYear = date('n')>8 ? date('Y')-2:date('Y')-3;
        }
        return $maxYear;
    }
}