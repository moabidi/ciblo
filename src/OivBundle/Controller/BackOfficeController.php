<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 25/11/18
 * Time: 20:38
 */

namespace OivBundle\Controller;

use Doctrine\ORM\Mapping\Entity;
use OivBundle\Entity\EducationData;
use OivBundle\Entity\NamingData;
use OivBundle\Entity\StatData;
use OivBundle\Entity\Users;
use OivBundle\Entity\VarietyData;
use OivBundle\Handlers\HandleNamingData;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use OivBundle\Command\SaveStatDataFromFileCommand;
use OivBundle\Handlers\HandleParameterStat;
use Symfony\Component\HttpFoundation\Response;


class BackOfficeController extends BaseController
{
    /**
     * @param Request $request
     * @Route("/login",name="oiv_login")
     */
    public function indexAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'OivBundle:backOffice/blocks:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }

    /**
     * @Route("/backoffice/login-check", name="oiv_check_login")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
        throw new \Exception('Symfony devrait intercepter cette route !');
    }

    /**
     * @param Request $request
     * @Route("/access-denied",name="access_denied")
     */
    public function accessDeniedAction(Request $request)
    {
        return $this->render('OivBundle:backOffice:access-denied.html.twig');
    }

    /**
     * @param Request $request
     * @Route("/backoffice/profile",name="profile")
     */
    public function profileAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $changed = false;
            $oTranslator = $this->get('translator');
            $aError = [
                'name' => $oTranslator->trans('The value of name is not valid'),
                'username' => $oTranslator->trans('The value of username is not valid'),
                'password' => $oTranslator->trans('The value of password is not valid'),
                'rpassword' => $oTranslator->trans('You must enter the same passwords'),
            ];
            if (preg_match('/^([a-zA-Z]|\s){4,10}$/',$request->request->get('name'))) {
                unset($aError['name']);
            }
            if (preg_match('/^(\w|-){4,10}$/',$request->request->get('username'))) {
                unset($aError['username']);
            }
            if (preg_match('/^\S{6,10}$/',$request->request->get('password'))) {
                unset($aError['password']);
            }
            if ($request->request->get('password') == $request->request->get('rpassword')) {
                unset($aError['rpassword']);
            }
            $oUser = $this->getUser();
            $oUser->setName($request->request->get('name'));
            $oUser->setUsername($request->request->get('username'));
            if (count($aError) == 0) {
                $changed = true;
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($oUser, $request->request->get('password'));
                $oUser->setPassword($encoded);
                $this->getDoctrine()->getManager()->persist($oUser);
                $this->getDoctrine()->getManager()->flush();
            }
            $oUser->setPassword($request->request->get('password'));
            return $this->render('OivBundle:backOffice/blocks:user_profile.html.twig',['user'=> $oUser, 'errors' => $aError,'changed'=>$changed]);
        }
        return $this->render('OivBundle:backOffice/blocks:user_profile.html.twig',['user'=>$this->getUser()]);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/users", name="manager_users")
     */
    public function manageUsersAction(Request $request)
    {
        return $this->render('OivBundle:backOffice/blocks:list_users.html.twig',['user'=>$this->getUser()]);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/edit-user", name="edit_user")
     */
    public function editUsersAction(Request $request)
    {
        return $this->render('OivBundle:backOffice/blocks:edit_user.html.twig',['user'=>$this->getUser()]);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/manager",name="manager_backoffice")
     */
    public function adminAction(Request $request)
    {
        if ($request->getLocale() == 'en') {
            return $this->redirectToRoute('manager_backoffice', ['_locale'=>'fr']);
        }
        $selectedCountryCode = $request->query->get('countryCode','oiv');
        $selectedYear = $request->query->get('year',date('Y'));
        $aStatType  = $this->getDoctrine()->getRepository('OivBundle:StatDataParameter')->getListProduct('public');
        array_walk($aStatType, function(&$v, $k){
            $list = [];
            foreach($v as $row){
                $list[] = $row['indicator'];
            }
            $v = implode(',',$list);
        });
        $aStatType = implode(',',$aStatType);
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => $selectedYear, 'statType'=>$aStatType];
        $aCriteria['countryName'] = 'countryName'.ucfirst($this->get('translator')->getLocale());
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getCountries($aCriteria['countryName']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled('private');
        $aParams['forms'] = $this->getFiledForms();
        $aParams['countResult'] = $this->getDoctrine()->getRepository('OivBundle:StatData')->getTotalResult($aCriteria);
        $aParams['globalResult'] = $this->getResultGLobalSearch('StatData', $aCriteria,'tab3');
        $aParams['headerTable'] =  $this->getTaggedFields('StatData','tab3');
        $aParams['parameters'] =  $this->getDoctrine()->getRepository('OivBundle:Parameters')->getAvailablesParameters();
        $aParams['calcStat'] = array_keys(StatData::getCalculatedStat());
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
            'no_result_export'=> $oTranslator->trans('No data to exported'),
            'select_country'=> $oTranslator->trans('Please select at least one country'),
            'no_type_export'=> $oTranslator->trans('Export type not available'),
            'error_year'=> $oTranslator->trans('Year Min must be less than Year Max'),
            'text_all'=> $oTranslator->trans('All'),
        ];
        $aParams['editableFields'] = [
            'stat'=>$this->getTaggedFields('StatData','editable'),
            'naming'=>$this->getTaggedFields('NamingData','editable'),
            'education'=>$this->getTaggedFields('EducationData','editable'),
            'variety'=>$this->getTaggedFields('VarietyData','editable'),
        ];
        $aParams['requiredFields'] = [
            'stat'=>$this->getTaggedFields('StatData','required'),
            'naming'=>$this->getTaggedFields('NamingData','required'),
            'education'=>$this->getTaggedFields('EducationData','required'),
            'variety'=>$this->getTaggedFields('VarietyData','required'),
        ];
        $aParams['versioning'] = [
            'stat' => $this->getDoctrine()->getRepository('OivBundle:StatData')->getMaxVersion(),
            'naming' => $this->getDoctrine()->getRepository('OivBundle:StatData')->getMaxVersion(),
            'variety' => $this->getDoctrine()->getRepository('OivBundle:StatData')->getMaxVersion(),
            'education' => $this->getDoctrine()->getRepository('OivBundle:StatData')->getMaxVersion()
        ];
        return $this->render('OivBundle:backOffice:index.html.twig',$aParams);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/create-data",name="create_data_backoffice")
     */
    public function createData(Request $request)
    {
        if (! $this->checkIsXHTMLRequest($request)) {
            return new JsonResponse('Request not valid',400);
        }
        $msg = "Probleme d'enregistrment des données";
        $result = [];
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table)) {
            $class = 'OivBundle\\Entity\\'.$table;
            $oData = new $class();
            if($id = intval($request->request->get('id'))){
                $oData = $this->getDoctrine()->getRepository('OivBundle:'.$table)->find($id);
            };
            $validator = $this->get('validator');
            try {
                foreach ($request->request->all() as $field => $value) {
                    if (property_exists($class, $field) && $field != 'id' ) {
                        $oData->{'set' . ucfirst($field)}($value);
                    }
                }
                /**@var ConstraintViolationList $errors */
                $errors = $validator->validate($oData);
                $msgErrors = [];
                $prefixMsg = 'form-'.$request->request->get('dbType');
                if ($oData->getId()) {
                    $prefixMsg = 'form-edit-'.$request->request->get('dbType');
                }
                foreach ( $errors->getIterator() as $index => $error) {
                    /**@var ConstraintViolation $error */
                    $msgErrors[$prefixMsg.'-'.$error->getPropertyPath()] = $error->getMessage();
                }
                if ($oData instanceof NamingData &&
                    ($oData->getAppellationCode() == $oData->getParentCode()  || $oData->getAppellationName() == $oData->getParentName())) {
                    $msgErrors[$prefixMsg.'-parentCode'] = "Le code/Nom IG/AO ne doit pas être identique à celui de l'unité géographique séperieure";
                }
                if (count($msgErrors)) {
                    return new JsonResponse(['response'=>'error','idForm'=>$prefixMsg,'messages'=>$msgErrors],400);
                }
                if ($table == 'VarietyData' && !array_key_exists('isMainVariety',$request->request->all())) {
                    $oData->setIsMainVariety(false);
                }
                $oData->setLastData(1);
                $oData->setLastDate(new \DateTime());

                if ($oData instanceof NamingData) {

                    $oData->setUsableData(1);
                    $aCombinedDataNaming = HandleNamingData::getCombineDataNaming($request->request->all());
                    $idNamingList = $this->getDoctrine()->getRepository('OivBundle:'.$table)->getIdListByAppelationCode($oData->getAppellationName(),$oData->getAppellationCode(),$oData->getVersioning());

                    if (($indexCurrentId = array_search($id,$idNamingList))!== false) {
                        unset($idNamingList[$indexCurrentId]);
                        $idNamingList = array_values($idNamingList);
                    }
                    //var_dump($aCombinedDataNaming);die;
                    $oData->setProductType($aCombinedDataNaming[0]['productType']);
                    $oData->setProductCategoryName($aCombinedDataNaming[0]['productCategoryName']);
                    $oData->setReferenceName($aCombinedDataNaming[0]['referenceName']);
                    $oData->setUrl($aCombinedDataNaming[0]['url']);
                    $this->getDoctrine()->getManager()->persist($oData);

                    for($i=1;$i<count($aCombinedDataNaming);$i++) {
                        $indexId = isset($idNamingList[$i-1]) ? $idNamingList[$i-1]:null;
                        $oDataNaming = null;
                        if($indexId) {
                            $oDataNaming = $this->getDoctrine()->getRepository('OivBundle:' . $table)->find($indexId);
                        }
                        if(!$oDataNaming) {
                            $oDataNaming = clone $oData;
                            $oDataNaming->setId(null);
                        }
                        $oDataNaming->setProductType($aCombinedDataNaming[$i]['productType']);
                        $oDataNaming->setProductCategoryName($aCombinedDataNaming[$i]['productCategoryName']);
                        $oDataNaming->setReferenceName($aCombinedDataNaming[$i]['referenceName']);
                        $oDataNaming->setUrl($aCombinedDataNaming[$i]['url']);
                        $this->getDoctrine()->getManager()->persist($oDataNaming);
                        if(isset($idNamingList[$i-1])) {
                            unset($idNamingList[$i-1]);
                        }
                    }
                }
                $this->getDoctrine()->getManager()->persist($oData);
                $this->getDoctrine()->getManager()->flush();
                return new JsonResponse(['response'=> 'success','idForm'=>$prefixMsg]);
            } catch(\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $msg = $e->getMessage();
            }
        }
        $result['response'] = $msg;
        return new JsonResponse($result,400);
    }


    /**
     * @param Request $request
     * @Route("/backoffice/edit-data",name="edit_data_backoffice")
     */
    public function editData(Request $request)
    {
        if (! $this->checkIsXHTMLRequest($request)) {
            return new JsonResponse('Request not valid',400);
        }
        $dbType = $request->request->get('dbType');
        $result = [];
        $table = ucfirst($dbType).'Data';
        $id = intval($request->request->get('id'));
        if (class_exists('OivBundle\\Entity\\'.$table) && $id) {
            $oData = $this->getDoctrine()->getRepository('OivBundle:'.$table)->find($id);
            try {
                $serliser = $this->getSerelizer();
                $result['data'] = json_decode($serliser->serialize($oData,'json'), true);
                $result['dbType'] = $dbType;
                if ($dbType == 'naming') {
                    $result['namingProducts'] = $this->getDoctrine()->getRepository('OivBundle:'.$table)->getInfoNaming($oData->getAppellationName(),$oData->getAppellationCode(),true,false);
                    $result['namingReferences'] = $this->getDoctrine()->getRepository('OivBundle:'.$table)->getInfoNaming($oData->getAppellationName(),$oData->getAppellationCode(),false,false);
                }
                return new JsonResponse($result);
            } catch(\Exception $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }
        $result['response'] = 'error';
        return new JsonResponse($result,400);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/import-data",name="create_muli_data_backoffice")
     */
    public function importDataFile(Request $request)
    {
        $result = [];
        if (! $this->checkIsXHTMLRequest($request)) {
            $result['response'] = 'Request not valid';
            return new JsonResponse($result,400);
        }
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table)) {

            try {
                /**@var UploadedFile $file*/
                $file = $request->files->get('dataFile');
                $result = $this->container->get('oiv.handler.import_file')->getContentFile($file, $table);
                if ($table == 'NamingData') {
					$result['data'] = $this->container->get('oiv.handler.naming_data')->checkContentAppelationCode($result['data'],'importBo');
                    $aListCodeNaming = [];
                    if (!$request->files->get('dataFileProduts')) {
                        $aIndexCode = NamingData::getIndexCodeByTag('importBo');
                        array_walk($result['data'],function($row)use(&$aListCodeNaming,$aIndexCode){
                            $aListCodeNaming[$row[$aIndexCode['indexAppelationCode']]] = $row[$aIndexCode['indexAppelationName']];
                        });
                        $request->getSession()->set('listCodeNaming',$aListCodeNaming);
                    } else {
                        $aProducts = $this->container->get('oiv.handler.import_file')->getContentFile($request->files->get('dataFileProduts'), $table,'namingProduct');
                        $aReferences = $this->container->get('oiv.handler.import_file')->getContentFile($request->files->get('dataFileReferences'), $table,'namingReference');
                        $result = HandleNamingData::combineAndMergeNamingData($result, $aProducts, $aReferences);
                        $result['labelfields'] = NamingData::getImportFieldsIdentifier(true);
                    }
                }

				/** Save uploaded file into directory var/import/  */
                if ($request->request->get('save') == '1') {
                    $fileName = $table.'_'.date('Y-m-d-H-i-s').'.'.$file->getClientOriginalExtension();
                    $file->move(
                        $this->getParameter('import_bdd_file'),
                        $fileName
                    );
                    if ($table == 'StatData') {
                        return new JsonResponse(['save'=>true]);
                    } else {
                        $success = $this->container->get('oiv.handler.import_file')->saveContentFile( $result, $table);
                        return new JsonResponse(['save'=>$success]);
                    }
                }
                return new JsonResponse($result);
            } catch(\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $result['response'] = 'Les données à importer sont eronnées. '.$e->getMessage();
                $result['response'] = mb_convert_encoding($result['response'], 'UTF-8', 'UTF-8');
                //var_dump($result);die;
                return new JsonResponse($result,400);
            }
        }
        $result['response'] = 'Request not valid';
        return new JsonResponse($result,400);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/backoffice/generate-export-bo",name="generate-export-bo-search")
     */
    public function saveCriteriaExportAction(Request $request)
    {
        return $this->saveCriteriaExport($request);
    }

    /**
     * @param Request $request
     * @param string $exportKey
     * @return StreamedResponse
     * @Route("/backoffice/export-csv-bo/{exportKey}",name="export-csv-bo-search")
     */
    public function exportDataAction(Request $request, $exportKey)
    {
        ini_set('memory_limit', '-1');
        return $this->getExportedCSVData($request->getSession()->get($exportKey));
    }

    /**
     * @param Request $request
     * @param string $exportKey
     * @return Response
     * @Route("/backoffice/export-pdf-bo/{exportKey}",name="export-pdf-bo-search")
     */
    public function exportPdfAction(Request $request, $exportKey)
    {
        return $this->getExportedPdfData($request->getSession()->get($exportKey));
    }

    /**
     * @param Request $request
     * @Route("/backoffice/generate-model-imported-file",name="generate-model-imported-file")
     */
    public function generateModelImportedFile(Request $request)
    {
        ini_set('memory_limit', '-1');
        $table = ucfirst($request->query->get('dbType')).'Data';
        $fileType = $request->query->get('fileType');
        if (class_exists('OivBundle\\Entity\\'.$table)) {
            $class = 'OivBundle\\Entity\\'.$table;
            if ($table == 'NamingData' && in_array($fileType, ['product','reference'])) {
                if ($request->getSession()->get('listCodeNaming')) {
                    $aCriteria = [
                        'appellationCode' => array_keys($request->getSession()->get('listCodeNaming')),
                        'appellationName' => $request->getSession()->get('listCodeNaming')
                    ];
                    $typeImport = $fileType == 'product' ? 'importBoNamingProduct':'importBoNamingReference';
                    return $this->getExportedCSVData(['criteria' => $aCriteria, 'table' => 'NamingData'], $typeImport,'appellationCode');
                } else {
                    $aFieldsName = $fileType == 'product' ? $class::getImportCustumFieldsIdentifier(true):$class::getImportCustumFieldsIdentifier(false);
                }
            } elseif (in_array($table, ['NamingData','EducationData','VarietyData'])) {
                $aCriteria = $request->query->all();
                $sort = $table == 'NamingData' ? 'appellationCode':'formationTitle';
                $sort = $table == 'VarietyData' ? 'grapeVarietyName':$sort;
                if (isset($aCriteria['countryCode']) && $aCriteria['countryCode'] == 'oiv') {
                    $sort = 'countryCode';
                }
                return $this->getExportedCSVData(['criteria'=>$aCriteria,'table'=>$table],'importBo',$sort);
            } elseif ($table == 'StatData') {
                $countryLocal = 'countryName'.ucfirst($this->get('translator')->getLocale());
                $aCriteria['countryCode'] = implode(',', array_keys($this->getDoctrine()->getRepository('OivBundle:Country')->getCountries($countryLocal)));
                $aCriteria = [
                    'statType' => implode(',',array_values($this->getDoctrine()->getRepository('OivBundle:StatDataParameter')->getNotCalculatedStatType())),
                    'countryCode' => implode(',', array_keys($this->getDoctrine()->getRepository('OivBundle:Country')->getCountries($countryLocal)))
                ];
                return $this->getExportedCSVData(['criteria'=>$aCriteria,'table'=>'StatData'],'importBo');
            } else {
                $aFieldsName = $class::getImportFieldsIdentifier();
            }

            //$aRequiredFields = $class::getRequiredFields();
            $aRequiredFields = $this->getTaggedFields($table,'required');
            $response = new StreamedResponse();
            $response->setCallback(function() use ($aFieldsName,&$aRequiredFields) {
                $handle = fopen('php://output', 'w+');
                fputcsv($handle, $aFieldsName, ';');
                foreach($aFieldsName as $fieldName => &$label) {
                    if (isset($aRequiredFields[$fieldName]) && $aRequiredFields[$fieldName]){
                        $label = 'Valeur obligatoire';
                    }else{
                        $label = 'Valeur non obligatoire';
                    }
                }
                fputcsv($handle, $aFieldsName, ';');
                fclose($handle);
            });

            $response->setStatusCode(200);
            $response->headers->set('Content-Encoding', ' UTF-8');
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition','attachment; filename="import-exemple-'.$table.ucfirst($fileType).'-'.date('Y-m-d-H-i-s').'.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            return $response;
        }
        return $this->redirectToRoute('manager_backoffice');
    }

    /**
     * @param Request $request
     * @Route("/backoffice/calculate-stat",name="calculate-stat")
     */
    public function calculateStat(Request $request)
    {
		$pathFile = $this->getParameter('import_bdd_file').'/CalcStatData_'.date('Y-m-d-H-i-s').'.csv';
        $oFile = new Filesystem();
        $oFile->dumpFile($pathFile,'Post caclulate StatData created on '.date('Y-m-d H:i:s'));
        return new JsonResponse(['response'=>'success']);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/edit-parameters",name="edit-parameters")
     */
    public function editParameters(Request $request)
    {
        try {
            /**
             *
             * @var HandleParameterStat $oHandler
             */
            $oHandler = $this->container->get('oiv.handler.parameter_stat');
            if ($oHandler->updateParameters($request->request->all())) {
                return new JsonResponse(['response'=>'success']);
            }
        }catch(\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            return new JsonResponse(['response'=>'Un erreur interne, les modifications ne sont pas enregistrées'],400);
        }
        return new JsonResponse(['response'=>'les données envoyées ne sont pas valides'],400);
    }

    /**
     * @param Request $request
     * @Route("/backoffice/linked-naming-country",name="linked-naming-country")
     */
    public function getlinkedNamingDataBycountry(Request $request)
    {
        $countryCode = $request->request->get('countryCode');
        $namingRepository = $this->getDoctrine()->getRepository('OivBundle:NamingData');
        $data = [
            'countryAppellationCode' => $namingRepository->getAllAppelationCode(['countryCode'=>$countryCode]),
//            'countryParentCode' => $namingRepository->getDistinctValueField('parentCode',['countryCode'=>$countryCode]),
//            'countryParentCode' => $namingRepository->getParentCode(['countryCode'=>$countryCode]),
            'countryTypeNationalCode' => $namingRepository->getDistinctValueField('typeNationalCode',['countryCode'=>$countryCode]),
            'productCategory' => $namingRepository->getDistinctValueField('productType')
        ];
//        $data['countryParentCode'] = array_map(function($row){
//            return $row['parentCode'];
//        },$data['countryParentCode']);
        $data['countryTypeNationalCode'] = array_map(function($row){
          return $row['typeNationalCode'];
        },$data['countryTypeNationalCode']);

        $oTranslator = $this->get('translator');
        $aProductCategory =[];
        array_walk($data['productCategory'], function($v,$k) use (&$aProductCategory,$oTranslator){
            $aProductCategory[$v['productType']] = $oTranslator->trans($v['productType']);
        });
        $data['productCategory'] = $aProductCategory;
        return new JsonResponse(['response'=>$data]);
    }
    /**
     * @return array
     */
    protected function getFiledForms()
    {
        $aFiltredFields = [];
        foreach ($this->_aTableType as $table) {
            $aFiltredFields[$table] = $this->getTaggedFields($table,'form');
            foreach ($aFiltredFields[$table] as $field => &$field) {
                $aValues = [];
                if (in_array($field,['statType', 'productType'])) {
                    $aValues = $this->getDoctrine()->getRepository('OivBundle:' . $table)->getDistinctValueField($field);
                }
                $field = ['label' => $field, 'values' => $aValues];
            }
        }
        if (isset($aFiltredFields['StatData']['statType'])) {
            $aFiltredFields['StatData']['statType']['values'] = $this->getDoctrine()->getRepository('OivBundle:StatDataParameter')->getListProduct('private');
        }
        return $aFiltredFields;
    }


}