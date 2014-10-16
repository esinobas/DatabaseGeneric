<?php
   /**
    * Class with static methods that allows manipulate the database data
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once 'DatabaseType/MySqlDatabase.php';
   include_once 'LoggerMgr/LoggerMgr.php';
   include_once 'TableMapping.php';
   
   class DatabaseMgr {
      
      //const DatabaseConfigC = "/home/tebi/Datos/webserver/MEMcakesandcookies/www/controlpanel/Cursos/php/Database/DatabaseType/Database.ini";
      const DatabaseConfigC = "DatabaseType/Database.ini";
      
      const modifiedRowC ="ColumnModifiedRow";
      
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
         $sqlTables = "";
         $sqlColumns = "";
         
         $logger->trace("Get tables from table mapping");
         $tables = $theTableMapping->getTables();
         $tablesName = array_keys($tables);
         $isFirst = true;
         $isFirstColum = true;
         for ($i = 0; $i < count($tables); $i++){
            $logger->trace("Table Name [ $i ] -> [ " . $tablesName[$i] . " ]");
            if ($isFirst){
               $sqlTables .= $tablesName[$i];
               $isFirst = false;
            }else{
               $sqlTables .= ", ".$tablesName[$i];
            }
            $logger->trace("Get columns from table mapping");
            $columns = $tables[$tablesName[$i]];
            $logger->trace("The table has [ ". count($columns). " ] columns");
            $columnsKey = array_keys($columns);
            for ($x = 0; $x < count($columns); $x++){
               $logger->trace("Column Name [ $x ] -> [ " . $columnsKey[$x] . " ]");
               if ($isFirstColum){
                  $sqlColumns .= $columnsKey[$x];
                  $isFirstColum = false;
               }else{
                  $sqlColumns .= ", ".$columnsKey[$x];
               }
            }
         }
         
         $logger->trace("Get conditions from table mapping. It is not implemented");
         $sqlSelect .= $sqlColumns . " from " . $sqlTables;
         $logger->debug($sqlSelect);
         $logger->trace("Exit");
         return $sqlSelect;
      }
      
      /**
       * Performes a query to the data base, and reads the table data and loads
       * in memory the data table.
       * 
       * @param TableMapping $theTableMapping
       * @param array $theReturnData
       */
      static public function openTable(TableMapping $theTableMapping, array &$theReturnData){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlSelect = self::createSqlSelect($theTableMapping);
         $database = self::getDatabase();
         if ($database->connect()){
            $logger->debug("The connection with the database was established successfull");
            
            $logger->debug("Execute query [ $sqlSelect ]");
            $resultQuery = $database->query($sqlSelect);
            $logger->debug("The query has [ " .count($resultQuery) ." ] rows");
            $tableNames = array_keys($theTableMapping->getTables());
            
            for($idx = 0; $idx < count($resultQuery); $idx++){
               $theReturnData[$idx] = array();
               
               for ($i = 0; $i < count($tableNames); $i++){
                  $keys = array_keys($theTableMapping->getColumns($tableNames[$i]));
                  $logger->trace("Get columns from table [ " . $tableNames[$i] ." ]");
                  for ($idxKeys = 0; $idxKeys < count($keys); $idxKeys++){
                     $logger->trace("Get value for row [ $idx ] key [ $keys[$idxKeys] ]".
                           " -> [ " . $resultQuery[$idx][$keys[$idxKeys]]. " ]");
                  
                  
                     $theReturnData[$idx][$theTableMapping->getColumns($tableNames[$i])[$keys[$idxKeys]]] =
                                     $resultQuery[$idx][$keys[$idxKeys]];
                  }
                }
             $theReturnData[$idx][self::modifiedRowC] = false;
               
            }
            
            $database->closeConnection();
         }else{
            $error = $database->getConnectError();
            $logger->error("An error has been produced in connect [ $error ]");
         }
         
         $logger->trace("Exit");
      }
      
      /**
       * Creates the sql update stament with the information saved in the 
       * parameter $theTableMapping
       * 
       * @param TableMapping $theTableMapping
       * @return string with the sql update stament
       */
      static protected function createSqlUpdate(TableMapping $theTableMapping,
                             array $theRowData){
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlUpdate = "update ";
         $logger->trace("Exit");
         return $sqlUpdate;
      }
      
      /**
       * Updates the table data that are in the memory.
       * 
       * @param TableMapping $theTableMapping
       * @param array $theTableData
       */
      static public function updateTable(TableMapping $theTableMapping,
                                          array $theTableData){
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $logger->trace("Filter the modified rows. The table has [ ".
                  count($theTableData) . " ] rows before the filter" );
         $callbackFilter = function ($var) use ($logger){
            $logger->trace("Enter/Exit");
            return $var[self::modifiedRowC];
         };
         $arrayModifiedRows = array_filter($theTableData, $callbackFilter);
         $logger->trace("The table has been filter. The table has [ ".
               count($arrayModifiedRows) . " ] rows after the filter" );
         $database = self::getDatabase();
         if ($database->connect(false)){
            $logger->debug("The connection with the database was established successfull");
            for ($i = 0; $i < count($arrayModifiedRows); $i++){
               $sqlStament = self::createSqlUpdate($theTableMapping, current($arrayModifiedRows));
               next($arrayModifiedRows);
               $logger->debug("Execute sql stament [ $sqlStament ]");
            }
            $database->closeConnection();
         }else{
            $error = $database->getConnectError();
            $logger->error("An error has been produced in connect [ $error ]");
         }
         $logger->trace("Exit");
      }
   }

?>