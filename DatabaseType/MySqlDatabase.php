<?php

   /**
    * Class with the implemantation to connect and access to MySql Database
    * @author tebi
    *
    */
   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once 'DatabaseCore/DatabaseIf.php';
    
   class MySqlDatabase implements DatabaseIf{
           
      /**
       * Properties
       */
      private $hostM;
      
      private $userM;
      
      private $pwdM;
      
      private $ddbbM;
      
      private $connectionM;
      
      private $isConnectedM;
      
      /**
       * Constructor
       * 
       * @param $theConnectionData. Array with the connection data to can 
       * connecto with the database.
       */
      function __construct($theFilePathConnectionData){
         
         $connetion_data = parse_ini_file($theFilePathConnectionData);
         if ($connetion_data != false ){
            $this->hostM = $connetion_data["server"];
            $this->userM = $connetion_data["user"];
            $this->pwdM = $connetion_data["pwd"];
            $this->ddbbM = $connetion_data["ddbb"];
            $this->isConnectedM = false;
         }else{
            //print("The file $theFilePathConnectionData doesn't exist");
         }
      }
      
      /**
       * Constructor
       * @param string $theHost
       * @param string $theUser
       * @param string $thePwd
       * @param string $theDDBB
       */
      /*
         $this->userM = $theUser;
         $this->pwdM = $thePwd;
         $this->ddbbM = $theDDBB;      
         $this->isConnectedM = false;  
      }*/
      
      /**
      * Destructor
      */
     function __destruct(){
     
        if ($this->isConnectedM){
           $this->closeConnection();        
        }     
     }
     
      /**
       * (non-PHPdoc)
       * @see DatabaseIf::connect()
       */
      public function connect($autoCommit = true) {
      
         if (! $this->isConnectedM){
            
            $this->connectionM = new mysqli($this->hostM, $this->userM, $this->pwdM,$this->ddbbM);
            
            if ($this->connectionM->connect_errno){
            
               return false;
            }   
            
            $this->isConnectedM = true;
            $this->connectionM->autocommit($autoCommit);
         }
            return true;
      }
   

      /**
       * (non-PHPdoc)
       * @see DatabaseIf::getConnectError()
       */
      public function getConnectError(){
         
         
         return $this->connectionM->connect_error;
            
      }
     
      /**
       * (non-PHPdoc)
       * @see DatabaseIf::isConnected()
       */
      public function isConnected(){
        
         return $this->isConnectedM;     
      }
     
      /**
       * (non-PHPdoc)
       * @see DatabaseIf::closeConnection()
       */
      public function closeConnection(){
           if ($this->isConnectedM){
        
              $this->connectionM->close();
              $this->isConnectedM = false;
           }
      }
      
      /**
       * (non-PHPdoc)
       * @see DatabaseIf::query()
       */
      public function query($theQuery){
         
         
         $result = $this->connectionM->query($theQuery);     
         $resultQuery = null;         
             
         if ($result){
            
            
            if ($result->num_rows > 0){
               $idx = 0;
               while ($row = $result->fetch_assoc()){
                  foreach ($row as $column => $value){
                     $dataRow[$column] = $value;
                  }
                  $resultQuery[$idx] = $dataRow;
                  $idx ++;
               }
            }            
            $result->close();
         }
         return $resultQuery;
      }     
     
     /**
      * (non-PHPdoc)
      * @see DatabaseIf::sqlCommand()
      */
     public function sqlCommand($theCommand){
        
        $this->connectionM->query($theCommand);
        return $this->connectionM->errno;     
     }
     
     /**
      * (non-PHPdoc)
      * @see DatabaseIf::getSqlError()
      */
     public function getSqlError(){
        return $this->connectionM->error;
     }
 
     /**
      * (non-PHPdoc)
      * @see DatabaseIf::getLastId()
      */
     public function getLastId(){
     
        return $this->connectionM->insert_id;        
     }
     
     /**
      * (non-PHPdoc)
      * @see DatabaseIf::commit()
      */
     public function commit(){
        
        $this->connectionM->commit();
     }
     
     /**
      * (non-PHPdoc)
      * @see DatabaseIf::rollback()
      */
     public function rollback(){
        
        $this->connectionM->rollback();
     }
   
   }

?>