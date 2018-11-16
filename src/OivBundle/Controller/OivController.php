<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 20:33
 */

namespace OivBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $aParams['stats'] = $this->getStatsCountry(['countryCode' => 'FRA', 'year' => '2016']);
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled();
        $aParams['globalResult'] = $this->getResultGLobalSearch('EducationData',['countryCode' => 'FRA', 'year' => '2016']);
        var_dump($aParams['globalResult']);die;
        return $this->render('OivBundle:search:result.html.twig', $aParams);
    }

    public function statCountryAction(Request $request)
    {

    }

    /**
     * @param string $table
     * @param array $aCriteria
     * @return array
     */
    private function getResultGLobalSearch($table, $aCriteria = [])
    {
        return $this->getDoctrine()->getRepository('OivBundle:'.$table)->getGlobalResult($aCriteria);
    }

    /**
     * @return array
     */
    private function getFiltredFiled()
    {
        $aFiltredFields = [
            'StatData' =>
                [
                    'versioning' => 'label Versionning',
                    'countryCode' => 'label countryCode',
                    'statType' => 'label statType'
                ],
            'EducationData' =>
                [
                    'countryCode' => 'label countryCode',
                    'formationTitle' => 'label formationTitle',
                    'university' => 'label university',
                    'tutelle' => 'label tutelle',
                    'level' => 'label level',
                    'diploma' => 'label diploma',
                    'cooperation' => 'label cooperation',
                ],
            'NamingData' =>
                [
                    'countryCode' => 'label countryCode',
                    'appellationName' => 'label appellationName',
                    'appellationCode' => 'label appellationCode',
                    'typeInternationalCode' => 'label typeInternationalCode',
                    'productCategoryName' => 'label productCategoryName',
                    'productType' => 'label productType',
                ],
            'VarietyData' =>
                [
                    'countryCode' => 'label countryCode',
                    'areaCultivated' => 'label areaCultivated',
                    'areaYear' => 'label areaYear',
                    'grapeVarietyName' => 'label grapeVarietyName',
                    'codeVivc' => 'label codeVivc',
                    'grapeVarietyName' => 'label grapeVarietyName',
                ],
        ];
        foreach($aFiltredFields as $table => &$fields) {
            foreach($fields as $field => $label) {
                $aValues = $this->getDoctrine()->getRepository('OivBundle:'.$table)->getDistinctValueField($field);
                $fields[$field] = ['label' => $label,'values'=>$aValues];
            }
        }
        return $aFiltredFields;
    }

    /**
     * @param array $aCriteria
     * @return array
     */
    private function getStatsCountry($aCriteria = [])
    {
        $Products = [
            [
                'label' => 'Raisin frais ( toutes usages)',
                'stat' => [
                    'prod' => 'C_PROD_GRP',
                    'export' => 'I_EXPORT_GRP',
                    'import' => 'H_IMPORT_GRP',
                    'consumption' => ''
                ]

            ],
            [
                'label' => 'Vin',
                'stat' => [
                    'prod' => 'P_PRODUCTION_WINE',
                    'export' => 'R_EXPORT_WINE',
                    'import' => 'Q_IMPORT_WINE',
                    'consumption' => 'S_CONSUMPTION_WINE'
                ]

            ],
            [
                'label' => 'Raisin de tables',
                'stat' => [
                    'prod' => '',
                    'export' => '',
                    'import' => '',
                    'consumption' => 'L_COMSUMPTION_TABLE_GRP'
                ]

            ],
            [
                'label' => 'Raisin sec',
                'stat' => [
                    'prod' => 'G_PROD_DRIED_GRP',
                    'export' => 'K_EXPORT_DRIED_GRP',
                    'import' => 'J_IMPORT_DRIED_GRP',
                    'consumption' => 'N_CONSUMPTION_DRIED_GRP'
                ]

            ]
        ];
        $repository = $this->get('oiv.stat_repository');
        foreach ($Products as &$product) {
            foreach ($product['stat'] as $key => &$statType) {
                $product['stat'][$key] = $repository->getValueStatType($statType, $aCriteria);
            }
        }
        return [
            'products' => $Products,
            'globalArea' => $repository->getValueStatType('A_SURFACE', $aCriteria),
            'usedArea' => $repository->getValueStatType('C_PROD_GRP', $aCriteria),
            'nbVariety' => $this->get('oiv.variety_repository')->getCountVariety($aCriteria),
            'nbEducation' => $this->get('oiv.education_repository')->getCountEducation($aCriteria),
            'nbNaming' => $this->get('oiv.naming_repository')->getCountNaming($aCriteria)
        ];
    }
}