<?php
/**
 *
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digi\Qantas\Controller\Index;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use phpseclib\Net\SFTP;


class Generate extends \Magento\Framework\App\Action\Action
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Filesystem
     */
    private $filesystem;
    
    /**
     * @var Csv
     */
    protected $csvProcessor;
    
     /**
     * @var QantasModelFactory
     */
    protected $_QantasModelFactory;
    
    /**
     * @var Sftp
     */
    
    protected $_sftp;
 
    
    public function __construct(
             \Magento\Framework\Filesystem\Io\Sftp $sftp,
           \Digi\Qantas\Model\QantasModelFactory $QantasModelFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\File\Csv $csvProcessor
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->csvProcessor = $csvProcessor;
        $this->_QantasModelFactory = $QantasModelFactory;
        $this->_sftp = $sftp;
    }

    
    public function execute()
    {
        $this->writeToText(); // Function for generating of files
        $this->sftpQantas(); // for uploading to Server          
        $this->copyFile();
        $this->readFile();
    }
    
    function sftpQantas() 
    {
      try{
          $sftp = $this->_sftp;     
            $sftp->open(
                 [
                     "host" => "filetransfer.dev.qantasloyalty.com:10022",
                     "username" => "DIGIDIRECT_QS_SFTP",
                     "password" => "d3dertpd$1"

                 ]
             );
              //This will scan your local directory and get the newest file
              $fileDirectoryPath = $this->directoryList->getPath('var');
              $filePath =  $fileDirectoryPath . '/Qantas/';        
              $files = scandir($filePath, SCANDIR_SORT_DESCENDING);
              $newest_file = $files[0];        
              $source =  $filePath.$newest_file;  
               
              $destination = '/Uploadloc';             

              if ($newest_file != '..') 
              { 
                  $sftp->cd($destination);               
                  $sftp->write($newest_file,$source);        
                  $sftp->close();
                  echo "File ".$newest_file." is sent! \n<br />";

              } 
              if ($newest_file == '..')
              { 
                  throw new \Exception(' No file to be sent! Local directory is empty. <br />'); 
              }
      }catch(\Exception $e){
         echo "Error: " . $e->getMessage(). PHP_EOL;
                         
            $filePath =  $fileDirectoryPath . '/qantas_file_error_logs/';
            if(!is_dir($filePath)){mkdir($filePath, 0777, true);}

            $handle= fopen($filePath . 'accrual_file_error.txt' , 'w' );
            $today = date("Ymd");
            $time = date('h:i:s', time());
                
            $log = $today .'/'. $time . $e->getMessage(). PHP_EOL;
            $append = fwrite($handle,$log);
            
            fclose($handle);

          
      }  
    
  }
   
    
    function copyfile() 
    {
        
        try {
            $username = "DIGIDIRECT_QS_SFTP";       
            $password = "d3dertpd$1";        
            $sftp = new SFTP('filetransfer.dev.qantasloyalty.com:10022');        
            $sftp->login($username, $password);

            $fileDirectoryPath = $this->directoryList->getPath('var');
            $destination =  $fileDirectoryPath . '/handBack/';
            
            if (!$sftp->isConnected()){
                 throw new \Exception("Cant' connect to qantas server <br />");

            }
            
            if($sftp->isConnected()) 
            {        
                $source = '/Donwloadloc/';
                
                //This will list all files inside your remote directory
                $allFiles = $sftp->nlist($source);  
                
                if(!is_array($allFiles)){
                    throw new \Exception("Your are not connected to the server or Directory does not exist! <br />");
                }
                
                if(is_array($allFiles)){
                    $files = array_diff($allFiles, array('.', '..'));            
                    $newest_file = end($files);
                    
                    
                    if(!is_dir($destination)){ mkdir($destination, 0777, true);}  
                
                    $fread = $sftp->get('/Donwloadloc/'.$newest_file);

                    if($fread){
                        echo "File download success! \n<br />";
                    }
                    if(!$fread){

                        throw new \Exception('Source directory is empty! No file to download <br />');

                    }
                    $split = explode("\n", $fread); 

                    $emptyRemoved = array_filter($split);

                    $headerRemove = current($emptyRemoved);
                    $tailRemove = end($emptyRemoved);


                    $data = array_diff($emptyRemoved, array($headerRemove, $tailRemove));


                    $members = array_chunk($data,50000);

                    foreach ($members as $key => $member) 
                    {


                        if ($key == 10) // will go back to 0 after filename999 is reached
                        {             
                            $key = 0;
                        }

                        $sss = str_pad($key+1, 3, '0', STR_PAD_LEFT); // ad 000 on the #

                        $handle = fopen($fileDirectoryPath . '/handBack/' . 'filename.'.$sss.'.txt' , 'w' ); 

                        date_default_timezone_set('Australia/Melbourne');

                        $today = date("Ymd");

                        $time = date('h:i:s', time());

                        $header = ['HABCDE'.$today,Null,$time]; 

                        $result= implode(" ",$header);

                        $test = fwrite($handle,$result. PHP_EOL);

                        foreach($member as $count=> $mem)
                        {                                                                    
                             fwrite($handle,$mem. PHP_EOL);
                        }

                        $counts = str_pad($count+1, 6, '0', STR_PAD_LEFT);

                        $trailer = ['T'.$counts];

                        fputcsv($handle,$trailer);

                        fclose($handle);

                   }         
                    
                }
                
                
                              
                 
            }

        } catch (\Exception $e) {
           
            
            echo "Error: " . $e->getMessage(). PHP_EOL;
                         
            $filePath =  $fileDirectoryPath . '/qantas_file_error_logs/';
            if(!is_dir($filePath)){mkdir($filePath, 0777, true);}

            $handle= fopen($filePath . 'handback_file_error_logs.txt' , 'w' );
            $today = date("Ymd");
            $time = date('h:i:s', time());
                
            $log = $today .'/'. $time . $e->getMessage(). PHP_EOL;
            $append = fwrite($handle,$log);
            
            fclose($handle);

            

        }
        
    }
      
    function readFile() 
    {
        try{
            $fileDirectoryPath = $this->directoryList->getPath('var');
            $filePath =  $fileDirectoryPath . '/handBack/';

            $files = scandir($filePath, SCANDIR_SORT_DESCENDING);
            $newest_file = $files[0];        


            if($newest_file != '..'){
              throw new \Exception("No file to read!");

                $source =  $filePath.$newest_file;
                $file = fopen($source,"r");

                $fread = fread($file,500000);

                if($fread){
                     echo "Points now is being process! \n<br />";
                }

                if(!$fread){
                     throw new \Exception("Can't read the file <br />");
                }

            $split = explode("\n", $fread);                        
            $emptyRemoved = array_filter($split);


            foreach ($emptyRemoved as $key => $string)
            {

                $r =  preg_split ("/\s+/", $string);

                $members = array_chunk($r,16);

                $emptyRemovedMem = array_filter($members);

                $skipped = array('0', '6', '10');

                   if(in_array($key, $skipped))
                   {
                      continue;
                   }

               foreach($emptyRemovedMem as $i => $rr)
               {      
                   $emptyRemovedrr = array_filter($rr);

                   $sdsad = count($emptyRemovedrr);

                     if(count($emptyRemovedrr) == 15)
                     {

                        $model = $this->_QantasModelFactory->create();

                        $lastName = $rr[0];

                        $qff = $rr[3];

                        $BasePointsEarned = $rr[10];

                        $BonusPointsEarned = $rr[11];

                        $TotalPointsEarned = $rr[12];

                        $model->load($qff);  

                        $model->setBasePointsEarned($BasePointsEarned);

                        $model->setBonusPointsEarned($BonusPointsEarned);

                        $model->setTotalPointsEarned($TotalPointsEarned);

                        $model->setPoints('1');

                        $model->save();

                     }

              } 

            }
            fclose($file);
           }
     
        }catch(\Exception $e){
            echo "Error: " . $e->getMessage(). PHP_EOL;
                         
            $filePath =  $fileDirectoryPath . '/qantas_file_error_logs/';
            if(!is_dir($filePath)){mkdir($filePath, 0777, true);}

            $handle= fopen($filePath . 'handback_file_error_logs.txt' , 'w' );
            $today = date("Ymd");
            $time = date('h:i:s', time());
                
            $log = $today .'/'. $time . $e->getMessage(). PHP_EOL;
            $append = fwrite($handle,$log);
            
            fclose($handle);

        }
        
        
        
    }
    
    
    public function writeToText()
    {
        try {
        $fileDirectoryPath = $this->directoryList->getPath('var');
        $filePath =  $fileDirectoryPath . '/Qantas/';
        if(!is_dir($filePath)){mkdir($filePath, 0777, true);}
              
        $data = $this->getMembers();
        if(empty($data)){
            throw new \Exception("No record to save! <br />");
        }
        
        if(!empty($data)){
            $members = array_chunk($data,50000); // will divide the data to the number it is set
        
            foreach ($members as $key => $member) 
            {
                if ($key == 999) // will go back to 0 after filename999 is reached
                {             
                    $key = 0;
                }
                
                $sss = str_pad($key+1, 3, '0', STR_PAD_LEFT); // ad 000 on the #
                
                $handle = fopen($fileDirectoryPath . '/Qantas/' . 'filename.'.$sss.'.txt' , 'w' ); 
                            
                date_default_timezone_set('Australia/Melbourne');
                
                $today = date("Ymd");
                
                $time = date('h:i:s', time());
                
                $header = ['HABCDE'.$today,Null,$time]; 
                
                $result= implode(" ",$header);
                
                fwrite($handle,$result. PHP_EOL);
                        
                foreach($member as $count=> $mem)
                {          
                    $result= implode(" ",$mem );
                        
                    fwrite($handle,$result. PHP_EOL);
                }
                
                $counts = str_pad($count+1, 6, '0', STR_PAD_LEFT);
                            
                $trailer = ['T'.$counts];
                    
                fputcsv($handle,$trailer);
                
                fclose($handle);
                
                echo "File generated successfully \n<br />";
            }
        }
        
        } catch (\Exception $e) {
            
            echo "Error: " . $e->getMessage(). PHP_EOL;
                         
            $filePath =  $fileDirectoryPath . '/qantas_file_error_logs/';
            if(!is_dir($filePath)){mkdir($filePath, 0777, true);}

            $handle= fopen($filePath . 'accrual_file_error.txt' , 'w' );
            $today = date("Ymd");
            $time = date('h:i:s', time());
                
            $log = $today .'/'. $time . $e->getMessage(). PHP_EOL;
            $append = fwrite($handle,$log);
            
            fclose($handle);
          
        }
      
    }

   
    
    public function getMembers()
    {
        try{
            $datas = $this->_QantasModelFactory->create();

            $collection = $datas->getCollection();

            $today = date("Ymd");

         foreach ($collection as $err) 
         {

             if($err['points'] == 0 )
             {
               $result[] = [                              
                                 $err['last_name'],
                                 $err['first_initial'],
                                 $err['title'],
                                 $err['qff_number'],
                                 $today,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                     NULL,
                                 $err['product_description'],
                                 $err['partner_reference'],
                                 $err['payment'],
                                 $err['amount_in_cents'],                          
                                 $err['base_points_earned'],
                                 $err['bonus_points_earned'], 
                                 $err['total_points_earned'],                             
                                 $err['record_number'],  ];  
             }


         }            

         if(empty($result))
         {
             return $result[] = [];
         }
         return $result;             
        }catch(\Exception $e){
            echo "Error: " . $e->getMessage();
        }
            
     }
         
} 