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
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => '2016'];
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['globalResult'] = $this->getResultGLobalSearch('StatData', $aCriteria,'tab1');
        $aParams['stats'] = $this->getStatsCountry($aCriteria);
        //var_dump($aParams['globalResult']);die;
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
                    $header[] = $translator->trans($field);
                }
                fputcsv($handle, $header, ';');
                foreach ($results as $row) {
                    fputcsv($handle, $row, ';');
                }
                fclose($handle);
            });

            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
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
}