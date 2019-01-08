<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 25/11/18
 * Time: 20:38
 */

namespace OivBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        $selectedYear = $request->query->get('year',date('Y')-2);
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => $selectedYear];
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
        $aParams['countResult'] = $this->getDoctrine()->getRepository('OivBundle:StatData')->getTotalResult($aCriteria);
        $aParams['globalResult'] = $this->getResultGLobalSearch('StatData', $aCriteria,'tab2');
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['selectedYear'] = $selectedYear;
        $oTranslator = $this->get('translator');
        $aParams['transData'] = [
            'infoCodeVivc'=>$oTranslator->trans('infoCodeVivc'),
            'data_not_available'=> $oTranslator->trans('Data not available'),
            'no_result_search'=> $oTranslator->trans('No results found for your search'),
            'no_result_found'=> $oTranslator->trans('No results found'),
            'error_response'=> $oTranslator->trans('error response'),
            'no_result_export'=> $oTranslator->trans('No data to exported'),
            'select_country'=> $oTranslator->trans('Please select at least one country'),
            'no_type_export'=> $oTranslator->trans('Export type not available'),
            'error_year'=> $oTranslator->trans('Year Min must be less than Year Max'),
            'text_all'=> $oTranslator->trans('All'),
        ];
        return $this->render('OivBundle:advancedSearch:index.html.twig',$aParams);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/generate-export",name="generate-export-adv-search")
     */
    public function saveCriteriaExportAction(Request $request)
    {
        $aCriteria = $this->getCriteriaRequest($request);
        $exportType = $request->request->get('exportType','csv');
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table) && in_array($exportType, ['csv','pdf'])) {
            $exportKey = uniqid($table);
            $request->getSession()->set($exportKey,['table'=>$table, 'criteria'=>$aCriteria]);
            $route = $exportType == 'pdf' ? 'export-pdf-adv-search':'export-csv-adv-search';
            return new JsonResponse(['href'=>$this->generateUrl($route, ['exportKey'=>$exportKey])]);
        }
        return new JsonResponse([]);
    }

    /**
     * @param Request $request
     * @param string $exportKey
     * @Route("/export-csv/{exportKey}",name="export-csv-adv-search")
     */
    public function exportDataAction(Request $request, $exportKey)
    {
        if ($aDataSession = $request->getSession()->get($exportKey)) {
            $aCriteria = $aDataSession['criteria'];
            $table = $aDataSession['table'];
            $results = $this->getResultGLobalSearch($table, $aCriteria, false,0,null);
            $translator = $this->get('translator');
            $response = new StreamedResponse();
            $response->setCallback(function() use ($results, $translator) {
                $handle = fopen('php://output', 'w+');
                $header = [];
                foreach(array_keys($results[0]) as $field) {
                    $header[] = mb_convert_encoding($translator->trans($field), 'ISO-8859-1', 'UTF-8');;
                }
                fputcsv($handle, $header, ';');
                foreach ($results as $row) {
                    fputcsv($handle, $row, ';');
                }
                fclose($handle);
            });

            $response->setStatusCode(200);
            $response->headers->set('Content-Encoding', ' ISO-8859-1');
            $response->headers->set('Content-Type', 'text/csv; charset=ISO-8859-1');
            $response->headers->set('Content-Disposition','attachment; filename="export-'.date('Ymd-his').'.csv"');

            return $response;
        }
        return;
    }

    /**
     * @param Request $request
     * @param string $exportKey
     * @return Response
     * @Route("/export-pdf/{exportKey}",name="export-pdf-adv-search")
     */
    public function exportPdfAction(Request $request, $exportKey)
    {
        if ($aDataSession = $request->getSession()->get($exportKey)) {
            $aCriteria = $aDataSession['criteria'];
            $table = $aDataSession['table'];
            $aParams['globalResult'] = $this->getResultGLobalSearch($table, $aCriteria, false,0,null);
            $html = $this->renderView('OivBundle:advancedSearch:print.html.twig', $aParams);
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
     * @param Request $request
     * @return array
     * @Route("/stattype-countries",name="stattype-adv-search")
     */
    public function getStatProductCountriesAction(Request $request)
    {
        /**@var StatDataRepository $repository */
        $aCountries = explode(',', $request->request->get('countryCode'));
       // $aCountries = ['FRA','ESP','ZAF'];
        $statType = $request->request->get('statType','');
        if (count($aCountries) && $aCountries[0] && $statType) {
            $aCriteria = [];
            $aResults = [];
            $aCriteria['yearMin'] = $request->request->get('yearMin','1995');
            $aCriteria['yearMax'] = $request->request->get('yearMax',date('Y')-2);
            $repository = $this->get('oiv.stat_repository');
            $mesure = '1000 QX';
            foreach ($aCountries as $countryCode) {
                $result = $repository->getMultiValueStatType($statType, array_merge($aCriteria, ['countryCode'=>$countryCode]));
                $aResults[$countryCode] = $result;
            }

            $translator = $this->get('translator');
            $formattedData = ['xAxis'=>[],'yAxis'=>[],'label'=> $translator->trans($statType), 'statType'=>$statType];
            for($y = $aCriteria['yearMin']; $y<=$aCriteria['yearMax']; $y++) {
                $formattedData['xAxis'][]= $y;
            }
            array_walk($aResults, function ($value, $countryCode) use (&$formattedData, $statType, $translator, $mesure) {
                $formattedData['yAxis'][$statType][] = $this->getDataProductGraph($countryCode,$value, $formattedData['xAxis'],$translator,$mesure);
                $formattedData['mesure'] = $mesure;
            });
//            var_dump($formattedData );die;
            return new JsonResponse($formattedData);
        }
        //var_dump($aProducts);die;
        return new JsonResponse([]);
    }
}