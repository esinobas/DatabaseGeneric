<?php
   /**
    * Class with static methods that allows manipulate the database data
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once 'DatabaseType/MySqlDatabase.php';
   include_once 'LoggerMgr/LoggerMgr.php';
   include_once 'TableMapping.php';
   include_once 'ColumnType.php';
   
   class DatabaseMgr {
      
      //const DatabaseConfigC = "/home/tebi/Datos/webserver/MEMcakesandcookies/www/controlpanel/Cursos/php/Database/DatabaseType/Database.ini";
      const DatabaseConfigC = "DatabaseType/Database.ini";
      
      const modifiedRowC ="ColumnModifiedRow";
      
      static private $databaseM = null;
      
      /**
       * Create a database object with the parameters saved in the config file
       * @return MySqlDatabase
       */
      static private function getDatabase($theAutoCommit = true){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         // Now only is used MySql. In a futher a factory should be created
         if (self::$databaseM == null){
         
            $logger->debug("Create database with data within [ " . self::DatabaseConfigC ." ]");
         
            self::$databaseM = new MySqlDatabase(self::DatabaseConfigC);
            if (self::$databaseM->connect($theAutoCommit)){
               $logger->debug("The connection with the database was established successfull");
            }else{
               $error = self::$databaseM->getConnectError();
               $logger->error("An error has been produced in connect with database. Error [ $error ]");
               self::$databaseM = null;
            }
            
         }
         
         $logger->trace("Exit");
         return self::$databaseM;
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
            $columns = $theTableMapping->getColumns($tablesName[$i]);
            $logger->trace("The table has [ ". count($columns). " ] columns");
            $columnsKey = array_keys($columns);
            for ($x = 0; $x < count($columns); $x++){
               $logger->trace("Column Name [ $x ] -> [ " . $columnsKey[$x] . " ]");
               if ($isFirstColum){
                  $sqlColumns .= $tablesName[$i].".".$columnsKey[$x];
                  $isFirstColum = false;
               }else{
                  $sqlColumns .= ", ".$tablesName[$i].".".$columnsKey[$x];
               }
            }
         }
         
         
         $sqlSelect .= $sqlColumns . " from " . $sqlTables;
         $logger->trace("Get conditions from table mapping. There is/are [ " .
                    count($theTableMapping->getConditions()) ." ] conditions");
         $isFirst = true;
         foreach ($theTableMapping->getConditions() as $condition){
            $logger->trace("Condition [ $condition ]");
            if ($isFirst){
               $isFirst = false;
               $sqlSelect .= " where " . $condition;
            }else{
               $sqlSelect .= " and " . $condition;
            }
         }
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
         
         $database = self::getDatabase();
         if ($database != null){
            //$logger->debug("The connection with the database was established successfull");
            $sqlSelect = self::createSqlSelect($theTableMapping);
            
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
            
         }else{
            
            $logger->warn("The table can not be opened");
         }
         
         $logger->trace("Exit");
      }
      
      /**
       * Creates the sql update stament with the information saved in the 
       * parameter $theTableMapping
       * 
       * @param $theTableDef. Class PhisicalTableDef where is saved the 
       * phisical table definition
       * @return string with the sql update stament
       */
      static protected function createSqlUpdate(PhisicalTableDef $theTableDef,
                                                array $theRowData){
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlUpdate = "update ".$theTableDef->getName() ." set ";
         $isFirst = true;
         for ($i = 0; $i < count ($theRowData); $i++){
            $key = key($theRowData);
            $value = current($theRowData);
            next($theRowData);
            $phisicalColumn = array_search($key, $theTableDef->getColumns());
            if ($phisicalColumn){ 
               if ($isFirst){
                  $isFirst = false;
                  $sqlUpdate .= $phisicalColumn . " = ";
                  if ($theTableDef->getDataType($phisicalColumn) == ColumnType::stringC){
                     $sqlUpdate .= "'" . $value ."'";
                  }else{
                     $sqlUpdate .= $value;
                  }
               }else{
                  $sqlUpdate .= ", ".$phisicalColumn . " = ";
                  if ($theTableDef->getDataType($phisicalColumn) == ColumnType::stringC){
                     $sqlUpdate .= "'" . $value ."'";
                  }else{
                     $sqlUpdate .= $value;
                  }
               }
            }
         }
         $sqlUpdate .= " where " . $theTableDef->getKey() . " = ";
         if ($theTableDef->getDataType($theTableDef->getKey()) == ColumnType::stringC ){
            $sqlUpdate .= "'".$theRowData[$theTableDef->getLogicalColumn($theTableDef->getKey())];
            $sqlUpdate .= "'";
         }else{
            $sqlUpdate .= $theRowData[$theTableDef->getLogicalColumn($theTableDef->getKey())];
         }
         $logger->debug($sqlUpdate);
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
         $result = false;
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
         if (count($arrayModifiedRows) == 0){
            $logger->trace("Exit");
            return;
         }
         $database = self::getDatabase(false);
         if ( $database != null){
            //$logger->debug("The connection with the database was established successfull");
            $tables = $theTableMapping->getTables();
            $error = false;
            for ($i = 0; $i < count($arrayModifiedRows); $i++){
               for ($x = 0; $x < count($tables); $x++){
                  $sqlStament = self::createSqlUpdate(
                                          $theTableMapping->getTableDefinition(current($tables)->getName()),
                                          current($arrayModifiedRows));
                  next($tables);
               }
               reset($tables);
               next($arrayModifiedRows);
               $logger->debug("Execute sql stament [ $sqlStament ]");
               if ($database->sqlCommand($sqlStament) == 0 ){
                  $logger->debug("The command was successfull executed");
               }else{
                  $logger->error("A error has produced [ " . 
                             $database->getSqlError() . " ]");
                  $error = true;
                  $result = false;
               }
            }
            if (! $error ){
               $logger->trace("The commit is executed");
               $database->commit();
            }
            
         }else{
            
            $logger->warn("The table can not be updated");
            $result = false;
            
         }
         $logger->trace("Exit");
         return $result;
      }
      
      /**
       * Creates the delete sql command
       * @param PhisicalTableDef $thePhisicalTableDef
       * @param array $theRowData
       * @return string
       */
      static protected function createSqlDelete(PhisicalTableDef $thePhisicalTableDef,
                                         array $theRowData){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlDelete = "delete from " . $thePhisicalTableDef->getName() . " where ";
         $sqlDelete .= $thePhisicalTableDef->getKey() ." = ";
         if ($thePhisicalTableDef->getDataType($thePhisicalTableDef->getKey()) == 
                                                   ColumnType::stringC ){
            $sqlDelete .= "'". $theRowData[$thePhisicalTableDef->getLogicalColumn($thePhisicalTableDef->getKey())];
            $sqlDelete .= "'";
         }else{
            $sqlDelete .= $theRowData[$thePhisicalTableDef->getLogicalColumn($thePhisicalTableDef->getKey())];
         }
        
         $logger->debug($sqlDelete);
         $logger->trace("Exit");
         return $sqlDelete;
      }
      
      /**
       * Delete the current row from the table
       * @param TableMapping $theTableMapping
       * @param array $theRow
       */
      static public function delete(TableMapping $theTableMapping,
                                          array $theRow){
         
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $database = self::getDatabase();
         if ( $database->connect(false)){
            $error = false;
            //Go to the last table in the array. It is the last table in the 
            //database configuration xml file.
            //In a relationship, always are removed the last rows of the it.
            $table = end($theTableMapping->getTables());
               
               $sqlDelete = self::createSqlDelete(
                                                  $table,
                                                  $theRow);
            //Todavia no esta hecho el borrado, falta implementar.
            //Cuando este terminado el insert, borramos
            
            $database->closeConnection();
         }
         $logger->trace("Exit");
      }
      
      static protected function createSqlInsert(PhisicalTableDef $theTableDef,
                                                array $theData){
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $sqlInsert = "insert into ".$theTableDef->getName() ." (";
         $values = " values (";
         $isFirst = true;
         $phisicalColumns = array_keys($theTableDef->getColumns());
         foreach ($phisicalColumns as $phisicalColumn){
            if (strcmp($phisicalColumn,$theTableDef->getKey()) != 0){
               $logicalColumn = $theTableDef->getLogicalColumn($phisicalColumn);
               $dataType = $theTableDef->getDataType($phisicalColumn);
               if ($isFirst){
                  $sqlInsert .= $phisicalColumn;
                  if (strcmp($dataType, ColumnType::stringC) == 0){
                     $values .= "'".$theData[$logicalColumn]."'";
                  }else{
                     $values .= $theData[$logicalColumn];
                  }
                  $isFirst = false;
               }else{
                  $sqlInsert .= ", ".$phisicalColumn;
                  if (strcmp($dataType, ColumnType::stringC) == 0){
                     $values .= ", '".$theData[$logicalColumn]."'";
                  }else{
                     $values .= ", ".$theData[$logicalColumn];
                  }
               }
            }
         }
         $values .= ")";
         $sqlInsert .= ")" .$values;
         $logger->debug($sqlInsert);
         $logger->trace("Exit");
         return $sqlInsert;
      }
      
      /**
       * Inserts in the database a new row with the passed data like argument
       * A boolean value is returned indicanding if the insert is performanced
       * with success or not.
       * @param TableMapping $theTableMapping
       * @param array $theNewData
       * @param array $theReturnData
       * @param string $theColumnKey. 
       * @param Integer [in|out] $theNewId: It is the id of the new row inserted in
       * into the table in the database.
       * @return boolean
       */
      static public function insert(TableMapping $theTableMapping,
                                    array $theNewData,
                                    array &$theReturnData,
                                    $theColumnKey){
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         $result = true;
         $database = self::getDatabase(false);
         if ($database != null){
            //$logger->debug("The connection with the database was established successfull");
            foreach ($theTableMapping->getTables() as $table){
               if ($table->getKey()!=null){
                  $logger->trace("The table [ " .$table->getName()." ] has key");
                  $sqlInsert = self::createSqlInsert($table, $theNewData);
                  $logger->debug("Execute command [ $sqlInsert ]");
                  if ($database->sqlCommand($sqlInsert) == 0){
                     $logger->trace("Command executed successful");
                     $logger->trace("The column key [ $theColumnKey ] will be ".
                           "set to [ ".$database->getLastId() ." ]");
                     $idx = count($theReturnData);
                     $theReturnData[$idx] = array();
                     $theReturnData[$idx][$theColumnKey] = $database->getLastId();
                     $theNewId = $database->getLastId();
                     $keys = array_keys($theNewData);
                     foreach ($keys as $key){
                        $logger->trace("Add new data [ $theNewData[$key] ] in column [ $key ]");
                        $theReturnData[$idx][$key] = $theNewData[$key];
                     }
                  }else{
                     $logger->error("The command [ $sqlInsert] fails. Error [" .
                           $database->getSqlError() ." ]");
                     $result = false;
                     break;
                  }
               }else{
                  $logger->trace("The table [ ". $table->getName(). " ] has not key. Skip table");
               }
               
            }
            if ($result){
               $database->commit();
            }else{
               $database->rollback();
            }
            
            
         }else{
            $logger->warn("The row has not could be inserted");
            $result = false;
         }
         $logger->trace("Exit");
         return $result;
      }
      
      static public function closeConnection(){
         $logger = LoggerMgr::Instance()->getLogger(__CLASS__);
         $logger->trace("Enter");
         if (self::$databaseM != null){
            if (self::$databaseM->isConnected()){
               $logger->trace("The connection is closing");
               self::$databaseM->closeConnection();
               
            }else{
               $logger->trace("The connection is already close");
            }
            self::$databaseM = null;
         }else{
            $logger->trace("The connection doesn't exist");
         }
         $logger->trace("Exit");
      }
   }

?>