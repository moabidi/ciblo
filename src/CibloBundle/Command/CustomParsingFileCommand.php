<?php
/**
 * Created by JetBrains PhpStorm.
 * User: moabidi
 * Date: 16/01/16
 * Time: 21:13
 * To change this template use File | Settings | File Templates.
 */

namespace CibloBundle\Command;


use CibloBundle\Entity\Order;
use CibloBundle\Entity\Visit;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CustomParsingFileCommand extends ContainerAwareCommand {

    protected function configure(){
        $this->setName('import:data')
            ->setDescription('Update the format data for custum data')
            ->addArgument('pathFile',InputArgument::REQUIRED ,'Set the path of the file to parse')
            ->addOption('analytic',null, InputOption::VALUE_NONE,'If is set, column datetime will be parsed')
            ->addOption('orders',null, InputOption::VALUE_NONE,'If is set, column date will be parsed');



    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $pathFile = $input->getArgument('pathFile');
        if( file_exists($pathFile) ){
            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getContainer()->get('doctrine')->getManager();
            if( $input->getOption('analytic') ){
                $handle = fopen($pathFile,'r+');
                $countLines = count( file( $pathFile))-1;
                $k = 0;
                while (($line = fgets($handle)) !== false) {
                    $aLine      = explode(';',$line);
                    $k++;
                    if( $k > 1 ){
                        //format date time "01/12/2015 00:03"
                        $aLine[0]   = substr($aLine[0],0,4).'-'.substr($aLine[0],4,2).'-'.substr($aLine[0],6,2);

                        $nbVisit = explode('\n',$aLine[5]);
                        $visit = new Visit();
                        $visit->setDate($aLine[0]);
                        $visit->setNavigateur($aLine[1]);
                        $visit->setOs($aLine[2]);
                        $visit->setResolution($aLine[3]);
                        $visit->setPrepherique($aLine[4]);
                        $visit->setNbVist($nbVisit[0]);
                        $em->persist($visit);
                        //Insert to db every 1000 lines.
                        if( ($k % 1000) == 0 ){
                            $em->flush();
                            $output->writeln(intval(($k*100)/$countLines).'%') ;
                        }
                    }
                }
                fclose($handle);
                $em->flush();
                $em->close();
                $output->writeln('100%') ;
                $output->writeln('All data is imported to database');
            }elseif( $input->getOption('orders')){
                $handle = fopen($pathFile,'r+');
                $k = 0;
                $countLines = count( file( $pathFile))-1;
                while (($line = fgets($handle)) !== false) {
                    $aLine      = explode(';',$line);
                    $k++;
                    if( $k > 1 ){
                        //format date time "01/12/2015 00:03"
                        $aLine[4]   = trim($aLine[4]);
                        $aLine[4]   = substr($aLine[4],6,4).'-'.substr($aLine[4],3,2).'-'.substr($aLine[4],0,2).' '.substr($aLine[4],11,2).':'.substr($aLine[4],14,2).':00';
                        $order = new Order();
                        $order->setReference($aLine[0]);
                        $order->setDate($aLine[4]);
                        $order->setConversionRate(floatval($aLine[1]));
                        $order->setTotalPaid(floatval($aLine[2]));
                        $order->setValid($aLine[3]);
                        $em->persist($order);
                        //Insert to db every 1000 lines.
                        if( ($k % 1000) == 0 ){
                            $em->flush();
                            $output->writeln(intval((int)($k*100)/$countLines).'%') ;
                        }
                    }
                }
                fclose($handle);
                $em->flush();
                $em->close();
                $output->writeln('100%') ;
                $output->writeln('All data is imported to database');
            }else{
                echo "No data to parse, please precise option (--parseDateTime or --parseDate )";
            }
        }
    }
}