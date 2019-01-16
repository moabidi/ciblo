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
        return $this->saveCriteriaExport($request);
    }

    /**
     * @param Request $request
     * @param string $exportKey
     * @Route("/export-csv/{exportKey}",name="export-csv-adv-search")
     */
    public function exportDataAction(Request $request, $exportKey)
    {
        return $this->getExportedCSVData($request->getSession()->get($exportKey));
    }

    /**
     * @param Request $request
     * @param string $exportKey
     * @return Response
     * @Route("/export-pdf/{exportKey}",name="export-pdf-adv-search")
     */
    public function exportPdfAction(Request $request, $exportKey)
    {
        return $this->getExportedPdfData($request->getSession()->get($exportKey));
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