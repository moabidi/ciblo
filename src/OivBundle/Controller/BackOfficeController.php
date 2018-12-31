<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 25/11/18
 * Time: 20:38
 */

namespace OivBundle\Controller;

use Doctrine\ORM\Mapping\Entity;
use OivBundle\Entity\Users;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/backoffice/manager",name="manager_backoffice")
     */
    public function adminAction(Request $request)
    {
        $selectedCountryCode = $request->query->get('countryCode','oiv');
        $selectedYear = $request->query->get('year',date('Y')-2);
        $aCriteria = ['countryCode' => $selectedCountryCode, 'year' => $selectedYear];
        $aParams['countries'] = $this->getDoctrine()->getRepository('OivBundle:Country')->findBy([], ['countryNameFr' => 'ASC']);
        $aParams['tradeBlocs'] = $this->getDoctrine()->getRepository('OivBundle:Country')->getDistinctValueField('tradeBloc');
        $aParams['filters'] = $this->getFiltredFiled('private');
        $aParams['forms'] = $this->getFiledForms();
        $aParams['countResult'] = $this->getDoctrine()->getRepository('OivBundle:StatData')->getTotalResult($aCriteria);
        $aParams['globalResult'] = $this->getResultGLobalSearch('StatData', $aCriteria,'tab3');
        $aParams['countryCode'] = $selectedCountryCode;
        $aParams['selectedYear'] = $selectedYear;
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
        $result = [];
        $table = ucfirst($request->request->get('dbType')).'Data';
        if (class_exists('OivBundle\\Entity\\'.$table)) {
            $class = 'OivBundle\\Entity\\'.$table;
            $oData = new $class();
            if($id = intval($request->request->get('id'))){
                $oData = $this->getDoctrine()->getRepository('OivBundle:'.$table)->find($id);
            };

            try {
                foreach ($request->request->all() as $field => $value) {
                    if (property_exists($class, $field) && $field != 'id' ) {
                        $oData->{'set' . ucfirst($field)}($value);
                    }
                }
                $oData->setLastDate(new \DateTime());
                $oData->setUsableData(0);
                $oData->setLastData(1);
                $this->getDoctrine()->getManager()->persist($oData);
                $this->getDoctrine()->getManager()->flush();
                $result['response'] = 'success';
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
     * @Route("/backoffice/edit-data",name="edit_data_backoffice")
     */
    public function editData(Request $request)
    {
        if (! $this->checkIsXHTMLRequest($request)) {
            return new JsonResponse('Request not valid',400);
        }
        $result = [];
        $table = ucfirst($request->request->get('dbType')).'Data';
        $id = intval($request->request->get('id'));
        if (class_exists('OivBundle\\Entity\\'.$table) && $id) {
            $oData = $this->getDoctrine()->getRepository('OivBundle:'.$table)->find($id);
            try {
                $serliser = $this->getSerelizer();
                $result['data'] = json_decode($serliser->serialize($oData,'json'), true);
                $result['dbType'] = $request->request->get('dbType');
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
                $result = $this->getContentFile($file, $table);
                if ($request->request->get('save') == '1') {
                    $content = $result;
                    $result = [];
                    $result['save'] = $this->saveContentFile($content, $table);
                }
                return new JsonResponse($result);
            } catch(\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $result['response'] = $e->getMessage();
                return new JsonResponse($result,400);
            }
        }
        $result['response'] = 'Request not valid';
        return new JsonResponse($result,400);
    }

    /**
     * @param array $result
     * @param string $table
     * @param bool $save
     */
    protected function saveContentFile($result, $table)
    {
        $success = false;
        if (count($result['labelfields']) && count($result['data'])>0) {
            $aFields = array_keys($result['labelfields']);
            $class = 'OivBundle\\Entity\\'.$table;
            foreach ($result['data'] as $row){
                $oData = new $class();
                $oData->setUsableData(1);
                $oData->setLastData(1);
                foreach($row as $key => $val) {
                    $idField = $aFields[$key];
                    if ($idField == 'lastDate') {
                        $val = new \DateTime($val);
                    }elseif($idField == 'countryCode') {
                        $val = $this->getCountryCode($val);
                    }
                    $oData->{'set'.ucfirst($idField)}($val);
                }
                $this->getDoctrine()->getManager()->persist($oData);
            }
            $this->getDoctrine()->getManager()->flush();
            $success = true;
        }
        return $success;
    }

    /**
     * @param UploadedFile $file
     * @param string $table
     * @param bool $save
     */
    protected function getContentFile($file, $table)
    {
        $result = [];
        if ($file && $file->getClientMimeType() == 'text/csv') {
            if (($handle = fopen($file->getPathname(), "r")) !== false) {
                $class = 'OivBundle\\Entity\\'.$table;
                $isHeader = true;
                $aHeaderIdentifier = [];
                $aData = [];
                while (($contentLine = fgetcsv($handle, 0, ",")) !== false) {
                    if ($isHeader) {
                        $aHeaderIdentifier = $this->getHeaderIdentfier($contentLine,$class);
                        $isHeader = false;
                    } else {
                        $aData[] = $contentLine;
                    }
                }
                fclose($handle);
                $result = ['labelfields'=>$aHeaderIdentifier,'data'=>$aData];
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getFiledForms()
    {
        foreach ($this->_aTableType as $table) {
            $repository = $this->getDoctrine()->getRepository('OivBundle:' . $table);
            $aFiltredFields[$table] = $repository->getTaggedFields('form');
            foreach ($aFiltredFields[$table] as $field => &$field) {
                $aValues = [];
                if (in_array($field,['statType', 'productType'])) {
                    $aValues = $repository->getDistinctValueField($field);
                }
                $field = ['label' => $field, 'values' => $aValues];
            }
        }
        if (isset($aFiltredFields['StatData']['statType'])) {
            $aFiltredFields['StatData']['statType']['values'] = $this->getDoctrine()->getRepository('OivBundle:StatDataParameter')->getListProduct('private');
        }
        return $aFiltredFields;
    }

    /**
     * @param $aHeader
     * @param Entity $table
     * @return array|null
     */
    protected function getHeaderIdentfier($aHeader,$class)
    {
        $aHeaderIdentfier = [];
        $aFieldsName = $class::getImportFieldsIdentifier();
        foreach ($aHeader as $val) {
            if (($identifier = array_search(trim($val), $aFieldsName)) === false) {
                if (property_exists($class, trim($val))) {
                    $identifier = $val;
                } else {
                    $msg = 'Les noms des colonnes de fichier importé ne sont pas valide. Voilà la liste des colonnes disponibles : '.PHP_EOL;
                    $msg .= implode(' | ',$aFieldsName);
                    throw new \Exception($msg);
                }
            }
            $aHeaderIdentfier[$identifier] = $val;
        }
        return $aHeaderIdentfier;
    }
}