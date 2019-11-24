<?php

/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 03/10/19
 * Time: 20:38
 */

namespace OivBundle\Command;

use OivBundle\Handlers\HandleImortFile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

class SaveStatDataFromFileCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('import:file');
//        $this->addArgument('fileName',InputArgument::REQUIRED,'Imported CSV file for All STAT DATA');
        $this->addArgument('dataTable',InputArgument::REQUIRED,'Name of table to be updated by content of imported file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getContainer()->getParameter('import_bdd_file');
        $dataTable = $input->getArgument('dataTable');
        $oFinder = new Finder();
        $oFinder = $oFinder->files()->name($dataTable.'_*')->in($path);
        if ($oFinder->count()) {
            foreach ($oFinder as $oSplFile) {
                /**@var SplFileInfo $oSplFile */
                $fileName = $oSplFile->getFilename();
                $table = substr($fileName, 0, strpos($fileName, '_'));
                $oFile = new File($oSplFile->getPathname());
                $oFile = $oFile->move($path, 'TMP_' . $fileName);
                switch ($input->getArgument('dataTable')) {
                    case 'StatData':
                        $this->importStatData($oFile, $path, $fileName);
                    case 'CalcStatData':
                        $this->calculateStatData($oFile, $path, $fileName);
                }
            }
        }
    }

    /**
     * @param File $oFile
     * @param $path
     * @param $fileName
     */
    private function calculateStatData(File $oFile, $path, $fileName)
    {
        /**@var \Symfony\Bridge\Monolog\Logger */
        $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Start Calcul StatData : ' . $fileName);
        try {
            $aResult = $this->getContainer()->get('oiv.handler.claculate_stat')->calculateStat();
            $version = $this->getContainer()->get('doctrine')->getRepository('OivBundle:StatData')->getMaxVersion();
            $successFileName = 'OK_CalcStatData_' . $version . '_' . $fileName;
            $oFile->move($path, $successFileName);
            $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Success Calcul StatData : ' . $successFileName);
            $message = \Swift_Message::newInstance()
                ->setSubject('Calcul des statistiques - Version ' . $version)
                ->setFrom('contact@openwise.fr')
                ->setTo('abidi.moohamed@gmail.com')
                ->addCc('shekman.kim@gmail.com')
                ->addCc('skim@openwise.fr')
                //->addCc('data@oiv.int')
                ->setBody(
                    $this->getContainer()->get('templating')->render(
                        'OivBundle:backOffice/mails:confirmation_import_file.html.twig',
                        [
                            'action'=>'calcStat',
                            'title' => 'Calcul des Statistiques',
                            'version' => $version,
                            'success'=>true,
                            'fileName'=> $successFileName,
                            'infoCalcStat' => $aResult
                        ]
                    ),
                    'text/html'
                );
            $this->getContainer()->get('mailer')->send($message);
        } catch(\Exception $e) {
            $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Error Calcul StatData : KO_' . $fileName);
            $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Error Calcul StatData : Msg ' . $e->getMessage());
            $oFile->move($path, 'KO_' . $fileName);
        }
    }

    /**
     * @param File $oFile
     * @param $path
     * @param $fileName
     */
    private function importStatData(File $oFile, $path, $fileName)
    {
        try {
            /**@var \Symfony\Bridge\Monolog\Logger */
            $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Start import file to StatData : ' . $fileName);
            $content = $this->getContainer()->get('oiv.handler.import_file')->getContentFile($oFile, 'StatData');
            $version = $this->getContainer()->get('oiv.handler.import_file')->saveContentFile($content, 'StatData');
            if ($version) {
                $successFileName = 'OK_StatData_' . $version . '_' . $fileName;
                $oFile->move($path, $successFileName);
                $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Success import file to StatData : ' . $successFileName);
                //$nbNewLines = $content['data'];
                $message = \Swift_Message::newInstance()
                    ->setSubject('Import des statistiques - Version ' . $version)
                    ->setFrom('contact@openwise.fr')
                    ->setTo('abidi.moohamed@gmail.com')
                    ->addCc('shekman.kim@gmail.com')
                    ->addCc('skim@openwise.fr')
                    //->addCc('data@oiv.int')
                    ->setBody(
                        $this->getContainer()->get('templating')->render(
                            'OivBundle:backOffice/mails:confirmation_import_file.html.twig',
                            [
                                'action'=>'importStat',
                                'title' => 'Import des Statistiques',
                                'version' => $version,
                                'success' => true,
                                'fileName'=> $successFileName
                            ]
                        ),
                        'text/html'
                    );
                $this->getContainer()->get('mailer')->send($message);
            } else {
                $oFile->move($path, 'KO_' . $fileName);
                $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Error import file to StatData : KO_' . $fileName);
            }
        } catch(\Exception $e) {
            $oFile->move($path, 'KO_' . $fileName);
            $this->getContainer()->get('monolog.logger.import_file_bdd')->addInfo('>>> Error import file to StatData : KO_' . $fileName);
        }
    }
}