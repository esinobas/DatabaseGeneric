<?php
   /**
    * Class with static methods that allows manipulate the database data
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once '../DatabaseType/MySqlDatabase.php';
   include_once '../../LoggerMgr.php';
   include_once './TableMapping.php';
   
   class DatabaseMgr {
      
      const DatabaseConfigC = "../DatabaseType/database.ini";
      
      /**
       * Create a database object with the parameters saved in the config file
       * @return MySqlDatabase
       */
      static private function getDatabase(){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         // Now only is used MySql. In a futher a factory should be created
         
         $logger->debug("Create database with data within [ " . self::DatabaseConfigC ." ]");
         $database = new MySqlDatabase(self::DatabaseConfigC);
         $logger->trace("Exit");
         return $database;
      }
      
      /**
       * Creates the sql stament to read data from database 
       * @param TableMapping $theTableMapping
       * @return string
       */
      static private function createSqlSelect(TableMapping $theTableMapping){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlSelect = "select ";
         $logger->trace("Get columns from table mapping");
         $columns = $theTableMapping->getColumns();
         $isFirst = true;
         for ($i = 0; $i < count($columns); $i++){
            $logger->trace("Column Name [ $i ] -> [ " . $columns[$i] . "]");
            if ($isFirst){
               $sqlSelect .= $columns[$i];
               $isFirst = false;
            }else{
               $columns[$i] .= ", ".$columns[$i];
            }
         }
         $logger->trace("Get tables from table mapping");
         $sqlSelect .= " from ";
         $tables = $theTableMapping->getTables();
         $isFirst = true;
         for ($i = 0; $i < count($tables); $i++){
            $logger->trace("Table Name [ $i ] -> [ " . $tables[$i] . "]");
            if ($isFirst){
               $sqlSelect .= $tables[$i];
               $isFirst = false;
            }else{
               $tables[$i] .= ", ".$tables[$i];
            }
         }
         $logger->trace("Get conditions from table mapping. It is not implemented");
         $logger->debug($sqlSelect);
         $logger->trace("Exit");
         return $sqlSelect;
      }
      
      static public function openTable(TableMapping $theTableMapping, $theReturnData){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlSelect = self::createSqlSelect($theTableMapping);
         $logger->trace("Exit");
      }
   }

?>