<?php
   /**
    * Script makes a template php file witha a object or class that allows the
    * database access
    */
   set_include_path( get_include_path() . PATH_SEPARATOR . 
   dirname(__FILE__)."/../../..");
   
   include_once 'LoggerMgr/LoggerMgr.php';
   $logger = LoggerMgr::Instance()->getLogger("main");
  
  
   
   function closeClassDefinition($theFileHandler, $theClassName){
      global $logger;
      $logger->trace("Enter");
      $logger->trace("Write getTableName method for the class [ $theClassName ]");
      $text = "\n      public function getTableName(){\n";
      $text .= "         \$this->loggerM->trace(\"Enter / Exit\");\n";
      $text .= "         return self::".$theClassName."TableC;\n";
      $text .= "      }\n";
      $text .= "   }\n";
      $text .= "?>\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
      
   }
   
   function writeHeaderTemplate($theFileHandler,$theClassName){
      
      global $logger;
      $logger->trace("Enter");
      $handle = fopen("TableTemplate.txt", "r");
      $newLine ="";
      if ($handle) {
         while (($readedLine = fgets($handle)) !== false) {
            // process the line read.
            
            $newLine.=str_replace("\$theClassName", $theClassName,$readedLine);
             
         }
      } else {
         // error opening the file.
      }
      fclose($handle);
      fwrite($theFileHandler, $newLine);
      $logger->trace("Exit");
   }
   
   function writeColumnsConstanDefinition($theFileHandler, $theColumns){
      global $logger;
      
      $logger->trace("Enter");
      $text  = "     /*\n";
      $text .= "      * Contants table columns\n";
      $text .= "      */\n";
      for ($idx= 0; $idx <count($theColumns); $idx++){
         $text .= "     const ".$theColumns[$idx]->name."ColumnC = ".
               "\"".$theColumns[$idx]->name."\";\n";
      }
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function getColumnType($theType){
      
      if (strtoupper($theType) == "STRING"){
         return "ColumnType::stringC";
      }
      if (strtoupper($theType) == "INTEGER"){
         return "ColumnType::integerC";
      }
      if (strtoupper($theType) == "FLOAT"){
         return "ColumnType::floatC";
      }
      if (strtoupper($theType) == "BOOLEAN"){
         return "ColumnType::booleanC";
      }
      if (strtoupper($theType) == "TIMESTAMP"){
         return "ColumnType::timestampC";
      }
   }
   
   function writeConstructor($theFileHandler, $theClassName, $theColumns, $theKey,
                              $thePhisicalDef){
      global $logger;
   
      $logger->trace("Enter");
      $text  = "     /*\n";
      $text .= "      * Constructor. The table definition is done here\n";
      $text .= "      */\n";
      $text .= "\tpublic function __construct(){\n";
      $text .= "      \$this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);\n";
      $text .= "       \n";
      $text .= "      \$this->loggerM->trace(\"Enter\");\n";
      $text .= "        parent::__construct();\n";
      $text .= "\t\t\$this->tableDefinitionM = new TableDef(self::".$theClassName."TableC);\n";
      for ($idx= 0; $idx <count($theColumns); $idx++){
         $text .="\t\t\$this->tableDefinitionM->addColumn(new ColumnDef(
                              self::".$theColumns[$idx]->name."ColumnC,"
                                       .getColumnType($theColumns[$idx]->type)."));\n";
      }
      
      foreach ($theKey as $columnKey){
      
         
         $text .="\t\t\$this->tableDefinitionM->addKey(self::"
               .$columnKey."ColumnC);\n";
         
      }
      
      /*** Write the phisical mapping ***/
      $text .= "   \n";
      $text .= "      \$this->tableMappingM = new TableMapping();\n";
      $tables = $thePhisicalDef->table;
      for( $idx = 0; $idx < count($tables); $idx++){
         $text .= "      \n";
         $logger->debug("Add phisical table [ " . $tables[$idx]->name . " ]");
         $text .= "      \$this->tableMappingM->addTable(self::phisical".$tables[$idx]->name.
         "C);\n";
         $columns = $tables[$idx]->columns->column;
          
         for ($idxColumns = 0; $idxColumns < count($columns); $idxColumns++){
            $logger->debug("Add mapping between phisical column: [ ".
                  $tables[$idx]->name.".".$columns[$idxColumns]->name ." ] and [ ".
                  $columns[$idxColumns]->logical ." ] with type [ " .
                  $columns[$idxColumns]->type ." ]");
            $columnType = "";
            if (strtoupper($columns[$idxColumns]->type) == "STRING"){
               $logger->trace("Data type is [ ColumnType::stringC ]" );
               $columnType = "ColumnType::stringC";
            }
            $columnType = getColumnType($columns[$idxColumns]->type);
            $text .= "      \$this->tableMappingM->addColumn(\n            self::phisical".
                  $tables[$idx]->name."C ,\n            self::phisical".
                  $tables[$idx]->name.$columns[$idxColumns]->name."ColumnC ,\n            self::".
                  $columns[$idxColumns]->logical ."ColumnC,\n            " .
                  $columnType . ");\n";
   
         }
         if (isset($tables[$idx]->key)){
            $logger->trace("Add the table key [ " . $tables[$idx]->key . " ]");
            $text .= "      \n";
            $text .= "      \$this->tableMappingM->addKey(self::phisical".
                               $tables[$idx]->name."C,\n            self::phisical".
                               $tables[$idx]->name.$tables[$idx]->key."ColumnC );\n";
         }
         
      }
      $logger->trace("Add the conditions");
      $conditions = $thePhisicalDef->conditions->condition;
      
      for ($idx = 0; $idx < count($conditions); $idx++){
         $logger->trace("Add Condition [ ". $conditions[$idx]." ]");
         $text .= "\n";
         $text .= "      \$this->tableMappingM->addCondition(\"". $conditions[$idx]. "\");\n";
      }
      $text .= "      \n";
      
      $logger->trace("Add the order by clausule if it exists");
      foreach ($thePhisicalDef->orderBy as $orderBy){
         $logger->trace("Add clausule order by[ ".json_encode($orderBy). " ]");
         $text .= "      \$this->tableMappingM->addOrderBy(array(\"column\"=>".
                     "\"".$orderBy->column ."\"";
         if ($orderBy->type != null){
            $text .= ", \"type\"=>\"".$orderBy->type."\"";
         }
         $text .= "));\n";
      }
      
      $text .= "      \n";
      $text .= "      \$this->loggerM->trace(\"Exit\");\n";
      $text .= "\t}\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeMethodsGetSet($theFileHandler, $theColumns, $theKey){
      global $logger;
      $logger->trace("Enter");
      $text = "      \n";
      for ($idx= 0; $idx <count($theColumns); $idx++){
         
         $text.= "      public function get".$theColumns[$idx]->name."(){\n";
         $text.= "         \$this->loggerM->trace(\"Enter/Exit\");\n";
         $text.= "         return \$this->get(self::".
                         $theColumns[$idx]->name."ColumnC);\n";
         $text.= "      }\n";
         $text.= "      \n";
         
         if ( strcmp($theColumns[$idx]->name, $theKey[0]->column) != 0){
         
            $text.= "      public function set".$theColumns[$idx]->name."($".
                             $theColumns[$idx]->name."){\n";
            $text.= "         \$this->loggerM->trace(\"Enter\");\n";
            $text.= "         \$this->set(self::".
                  $theColumns[$idx]->name."ColumnC, \$".
                                 $theColumns[$idx]->name.");\n";
            $text.= "         \$this->loggerM->trace(\"Exit\");\n";
            $text.= "      }\n";
         }
      }
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writePhisicalConstants($theFileHandler, $thePhisicalDef){
      
      global $logger;
      $logger->trace("Enter");
      $text = "      \n";
      $text .= "      /*** Phisical constants ***/\n\n";
      $tables = $thePhisicalDef->table;
      $logger->debug("Number of phisical tables: [ ".count($tables) ." ]");
      for( $idx = 0; $idx < count($tables); $idx++){
         $text .= "   \n";
         $logger->trace("Phisical table name: ".$tables[$idx]->name);
         $text .= "      const phisical".$tables[$idx]->name."C = \"".$tables[$idx]->name."\";\n";
         $columns = $tables[$idx]->columns->column;
         $logger->debug("The phisical table ".$tables[$idx]->name.
                         " has ". count($columns)." columns");
         for ($idxColumns = 0; $idxColumns < count($columns); $idxColumns++){
            $logger->debug("Phisical column name: ".$columns[$idxColumns]->name);
         
            $text .= "      const phisical".$tables[$idx]->name.$columns[$idxColumns]->name."ColumnC = \"".
                      $columns[$idxColumns]->name."\";\n";
         }
         $text .= "\n";
      }
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeMethodInsert($theFileHandler, $theColumns, $theKey){
      global $logger;
      $logger->trace("Enter");
      $text = "      \n";
      $text .= "      public function insert(";
      $isFirts = true;
      foreach ($theColumns as $column){
         if (strcmp($column->name, $theKey->column))
            if ($isFirts ){
               $text .= " \$the".$column->name;
               $isFirts = false;
            }else{
               $text .= "\n                              ,\$the".$column->name;
            }
      }
      $text .= "\n                                ){\n";
      $text .= "         \$this->loggerM->trace(\"Enter\");\n";
      $text .= "         \$arrayData = array();\n";
      foreach ($theColumns as $column){
         if (strcmp($column->name, $theKey->column)){
            $text .= "         \$arrayData[self::".$column->name."ColumnC] = \$the".$column->name.";\n";
         }
      }
      $text .= "         \$this->loggerM->trace(\"Exit\");\n";
      $text .= "\n";
      $text .= "         return parent::insertData(\$arrayData);\n";
      $text .= "      }\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }

   
   function writeRequestFromWebHeader($theFileHandler){
      global $logger;
      $logger->trace("Enter");
      $text = "<?php\n";
      $text .= "   /**\n";
      $text .= "    * File used for receive the request from the web and map the request params\n";
      
      $text .= "    * in functions\n";
      $text .= "    */\n\n";
      $text .= "   /****************** INCLUDES ******************************/\n";
      $text .= "   set_include_path( get_include_path() . PATH_SEPARATOR . \$_SERVER['DOCUMENT_ROOT']);";
      //$text .= "                      '/php/');\n";
      $text .= "\n";
      $text .= "   require_once 'php/Database/Tables/RequestFromWebConstants.php';\n";
      $text .= "   include_once 'php/LoggerMgr/LoggerMgr.php';\n";
      //$text .= "   include_once 'DatabaseCore/DatabaseMgr.php';\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebIncludes($theFileHandler, $theTablesDefinition){
      global $logger;
      $logger->trace("Enter");
      $text = "";
      
      foreach ($theTablesDefinition as $tableDefinition){
     
         $text .= "   include_once 'php/Database/Tables/".$tableDefinition->name.".php';\n";
      }
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   function writeRequestFromWebConstantFile($outDir){
      global $logger;
      $logger->trace("Enter");
      $fileHandler = fopen($outDir."RequestFromWebConstants.php", "w");
      $text = "<?php\n";
      $text .= "   /**\n";
      $text .= "    * File where are defined the constas used in RequestFromWeb\n";
      $text .= "    */\n\n";
      $text .= "   define(COMMAND, \"command\");\n";
      $text .= "   define(PARAMS, \"paramsCommand\");\n";
      $text .= "   define(PARAM_TABLE, \"Table\");\n";
      $text .= "   define(PARAM_ROWS, \"rows\");\n";
      $text .= "   define(PARAM_ROW, \"row\");\n";
      $text .= "   define(PARAM_DATA, \"data\");\n";
      $text .= "   define(COMMAND_INSERT, \"I\");\n";
      $text .= "   define(COMMAND_UPDATE, \"U\");\n";
      $text .= "   define(COMMAND_DELETE, \"D\");\n";
      $text .= "   define(COMMAND_SELECT, \"S\");\n";
      $text .= "   define(PARAM_KEY, \"key\");\n";
      $text .= "   define(PARAM_SKIP_ROWS, \"skipRows\");\n";
      $text .= "   define(PARAM_NUM_ROWS, \"numRows\");\n";
      $text .= "   define(PARAM_SEARCH_BY, \"searchBy\");\n";
      $text .= "   define(PARAM_SEARCH_COLUMN, \"searchColumn\");\n";
      $text .= "   define(PARAM_SEARCH_VALUE, \"searchValue\");\n";
      $text .= "   define(RESULT_CODE, \"ResultCode\");\n";
      $text .= "   define(MSG_ERROR, \"ErrorMsg\");\n";
      $text .= "   define(RESULT_CODE_SUCCESS, 200);\n";
      $text .= "   define(RESULT_CODE_INTERNAL_ERROR, 500);\n";
      $text .= "   define(RETURN_LAST_ID, \"lastID\");\n";
      $text .= "   define(ADD_TO_CALLBACK, \"addToCallback\");\n";
      $text .= "\n";
      $text .= "?>";
      fwrite($fileHandler, $text);
      fflush($fileHandler);
      fclose($fileHandler);
      $logger->trace("Exit");
   }
   function writeRequestFromWebConstants($theFileHandler){
      global $logger;
      $logger->trace("Enter");
      $text = "\n";
      $text .= "   /*** Definition of the global variables and constants ***/\n";
      $text .= "   /**\n";
      $text .= "    * Object for write the log in a file\n";
      $text .= "    */\n";
      $text .= "\n";
      $text .= "   \$logger = null;\n";
      $text .= "\n";
     /* $text .= "   define(COMMAND, \"command\");\n";
      $text .= "   define(PARAMS, \"paramsCommand\");\n";
      $text .= "   define(PARAM_TABLE, \"Table\");\n";
      $text .= "   define(PARAM_ROWS, \"rows\");\n";
      $text .= "   define(PARAM_ROW, \"row\");\n";
      $text .= "   define(PARAM_DATA, \"data\");\n";
      $text .= "   define(COMMAND_INSERT, \"I\");\n";
      $text .= "   define(COMMAND_UPDATE, \"U\");\n";
      $text .= "   define(COMMAND_DELETE, \"D\");\n";
      $text .= "   define(COMMAND_SELECT, \"S\");\n";
      $text .= "   define(PARAM_KEY, \"key\");\n";
      $text .= "   define(PARAM_SKIP_ROWS, \"skipRows\");\n";
      $text .= "   define(PARAM_NUM_ROWS, \"numRows\");\n";
      $text .= "   define(PARAM_SEARCH_BY, \"searchBy\");\n";
      $text .= "   define(PARAM_SEARCH_COLUMN, \"searchColumn\");\n";
      $text .= "   define(PARAM_SEARCH_VALUE, \"searchValue\");\n";
      $text .= "   define(RESULT_CODE, \"ResultCode\");\n";
      $text .= "   define(MSG_ERROR, \"ErrorMsg\");\n";
      $text .= "   define(RESULT_CODE_SUCCESS, 200);\n";
      $text .= "   define(RESULT_CODE_INTERNAL_ERROR, 500);\n";
      $text .= "   define(RETURN_LAST_ID, \"lastID\");\n";
      $text .= "   define(ADD_TO_CALLBACK, \"addToCallback\");\n";
      */$text .= "\n";
      $text .= "\n";
      $text .= "   /****************** Functions *****************************/\n\n";
      
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
      
   function writeRequestFromWebFunctionGetTable($theFileHandler, $theTablesDefinition){
      global $logger;
      $logger->trace("Enter");
      $text = "   function getTable(\$theTableName){\n";
      $text .= "      global \$logger;\n";
      $text .= "      \$logger->trace(\"Enter\");\n";
      $text .= "      \$logger->trace(\"Create object [ \$theTableName ]\");\n";
      $text .= "      \$returnedTable = null;\n";
      
      foreach ($theTablesDefinition as  $tableDefinition){
         $text .= "\n";
         $text .= "      if (strcmp(\$theTableName, ". $tableDefinition->name .
                                "::" . $tableDefinition->name .
                                "TableC) == 0){\n";
         $text .= "         \$returnedTable = new ". $tableDefinition->name ."();\n";
         $text .= "      }\n";
      }
      
      $text .= "      \$logger->trace(\"Exit\");\n";
      $text .= "      return  \$returnedTable;\n";
      $text .= "   }\n\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebFunctionUpdateData($theFileHandler, $theTablesDefinition){
      global $logger;
      $logger->trace("Enter");
      $text = "   function updateData(\$theTable, \$theRows, &\$theResult){\n";
      $text .= "      global \$logger;\n";
      $text .= "      \$logger->trace(\"Enter\");\n";
      $text .= "      \$logger->trace(\"Rows: [ \".json_encode(\$theRows).\" ]\");\n";
      $text .= "      \$logger->trace(\"Update data of [ \" . \$theTable->getTableName() .\" ]\");\n";
      $text .= "      foreach ( \$theRows as \$row){\n";
      $text .= "         \$key = \$row[PARAM_KEY];\n";
      $text .= "         \$logger->trace(\"Search by [ \$key ]\");\n";
      $text .= "         if ( \$theTable->searchByKey(\$key)){\n";
      $text .= "            \$logger->trace(\"The Key has been found.\");\n";
      $logger->trace("number of tables: " . count($theTablesDefinition));
      foreach ($theTablesDefinition as $tableDefinition){
         
         $text .="            if (strcmp(\$theTable->getTableName(),".
                                      $tableDefinition->name."::".
                                      $tableDefinition->name."TableC) == 0){\n";
         $logger->trace("number of colummns: " . count($tableDefinition->columns->column));
         foreach ($tableDefinition->columns->column as $column){
            if (strcmp($column->name, $tableDefinition->key->column) != 0){
               $text .= "               if (isset(\$row[". $tableDefinition->name."::". 
                                     $column->name . "ColumnC])){\n";
               $text .= "                  \$logger->trace(\"Set value to column [ \".".
                               "\n                             ".$tableDefinition->name."::".
                                            $column->name . "ColumnC .\" ] -> [ " .
                                            "\".\n".
                                "                             \$row[". $tableDefinition->name."::".
                                            $column->name . "ColumnC] .\" ]\");\n";
               $text .= "                  \$theTable->set" .$column->name . 
                                          "(\$row[". $tableDefinition->name."::". 
                                          $column->name . "ColumnC ]);\n";
               $text .= "                }\n";
            }
         }
         $text .="            }\n\n";
         
      }
      
      
      $text .= "            }else{\n";
      $text .= "               \$theResult[RESULT_CODE] = RESULT_CODE_INTERNAL_ERROR;\n";
      $text .= "               \$theResult[MSG_ERROR] = \"The Key has not been found.\";\n";
      $text .= "               \$logger->warn(\$theResult[MSG_ERROR]);\n";
      $text .= "               break;\n";
      $text .= "            }\n";
      $text .= "         }\n";
      
      $text .= "         \$logger->trace(\"Update the data in the database\");\n";
      $text .= "         if ( ! \$theTable->update()){\n";
      $text .= "            \$theResult[RESULT_CODE] = RESULT_CODE_INTERNAL_ERROR;\n";
      $text .= "            \$theResult[MSG_ERROR] = \$theTable->getStrError();\n";
      $text .= "            \$logger->error(\"The update failed. Error [ \" . \$theResult[MSG_ERROR] . \" ]\");\n";
      $text .= "         }\n";
      $text .= "      \$logger->trace(\"Exit\");\n";
      $text .= "   }\n\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebSelect($theFileHandler, $theTablesDefinition){
      global $logger;
      
      $logger->trace("Enter");
      $text = "   function selectData(\$theTable, \$theData, &\$theResult){\n";
      $text .= "      global \$logger;\n";
      $text .= "      \$logger->trace(\"Enter\");\n";
      $text .= "      \$logger->trace(\"Select data from  [ \" . \$theTable->getTableName() .\" ]\");\n";
      $text .= "      \$logger->trace(\"with params: [ \".json_encode(\$theData).\" ]\");\n";
      $text .= "      if (isset(\$theData[PARAM_SEARCH_BY])){\n";
      $text .= "         \$logger->trace(\"Search by column [ \".
                                  \$theData[PARAM_SEARCH_BY][PARAM_SEARCH_COLUMN] .
                                  \" ] value [ \" .
                                  \$theData[PARAM_SEARCH_BY][PARAM_SEARCH_VALUE] . 
                                   \" ]\");\n";
      
      $text .= "         if (! \$theTable->searchByColumn(\$theData[PARAM_SEARCH_BY][PARAM_SEARCH_COLUMN],
                                     \$theData[PARAM_SEARCH_BY][PARAM_SEARCH_VALUE])){\n";
      $text .= "            \$logger->trace(\"The search has not had success\");\n";
      $text .= "            return;\n";
      $text .= "         }\n";
      $text .= "      }\n";
      $text .= "      \$numRows = 0;\n";
      $text .= "      \n";
      $text .= "      \$skipRows = 0;\n";
      $text .= "      if (isset(\$theData[PARAM_SKIP_ROWS])){\n";
      $text .= "         \$skipRows = \$theData[PARAM_SKIP_ROWS];\n";
      $text .= "      }\n";
      $text .= "      if (isset(\$theData[PARAM_NUM_ROWS])){\n";
      $text .= "         \$numRows = \$theData[PARAM_NUM_ROWS];\n";
      $text .= "      }\n";
      $text .= "      if (\$numRows == 0){\n";
      $text .= "         \$numRows = \$theTable->getCardinality() - \$skipRows;\n";
      $text .= "      }\n";
      $text .= "      \$theTable->skip(\$skipRows);\n";
      $text .= "\n";
      $text .= "      \$idx = 0;\n";
      $text .= "      \$theResult[PARAM_DATA] = array();\n";
      $text .= "      while (\$theTable->next() && \$idx < \$numRows){\n";
      $text .= "         \$rowData = array();\n";
      $logger->trace("number of tables: " . count($theTablesDefinition));
      foreach ($theTablesDefinition as $tableDefinition){
         
         $text .="\n         if (strcmp(\$theTable->getTableName(),".
               $tableDefinition->name."::".
               $tableDefinition->name."TableC) == 0){\n";
         $logger->trace("The table [ ". $tableDefinition->name .
               " ] has [ " . count($tableDefinition->columns->column).
               " ] colummns.");
         $text .="\n";
         foreach ($tableDefinition->columns->column as $column){
            $logger->trace("Get value from column [ " . $column->name .  " ]");
            
            $text .= "             \$rowData['" . $column->name . "'] = \$theTable->get" . 
                   $column->name . "();\n";
         }
         $text .= "         }\n";
         
      }
      $text .= "         \$logger->trace(\"Add row [ \$idx ] [ \" . json_encode(\$rowData) .\" ]\");\n";
      $text .= "         \$theResult[PARAM_DATA][strval(\$idx)] = \$rowData;\n";
      $text .= "         \$idx++;\n";
      $text .= "      }\n";
      $text .= "      \$logger->trace(\"Exit\");\n";
      $text .= "   }\n";
      fwrite($theFileHandler, $text);
      
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebFunctionInsertData($theFileHandler, $theTablesDefinition){
      global $logger;
      $logger->trace("Enter");
      $text = "   function insertData(\$theTable, \$theData, &\$theResult){\n";
      $text .= "      global \$logger;\n";
      $text .= "      \$logger->trace(\"Enter\");\n";
      $text .= "      \$logger->trace(\"Insert data: [ \".json_encode(\$theData).\" ]\");\n";
      $text .= "      \$logger->trace(\"Into [ \" . \$theTable->getTableName() .\" ]\");\n";
      $logger->trace("number of tables: " . count($theTablesDefinition));
      foreach ($theTablesDefinition as $tableDefinition){
         $text .="\n      if (strcmp(\$theTable->getTableName(),".
               $tableDefinition->name."::".
               $tableDefinition->name."TableC) == 0){\n";
         $logger->trace("The table [ ". $tableDefinition->name .
                        " ] has [ " . count($tableDefinition->columns->column).
                        " ] colummns.");
         $text .="\n         //Declare variables\n";
         $data = "";
         $logger->trace("Key: " . $tableDefinition->key->column);
         foreach ($tableDefinition->columns->column as $column){
            $logger->trace("Column: " . $column->name);
            if (strcmp($tableDefinition->key->column, $column->name) != 0 ){
               $text .="         \$var".$column->name . " = \$theData[\"" .
                                    $column->name . "\"];\n";
               $data .= "\$var".$column->name."\n                                ,";
            }
         }
         $data = substr($data, 0, strlen($data)-1);
         $text .= "\n         \$newId = \$theTable->insert($data);\n";
         $text .= "      }\n";
      }
      $text .= "\n      if( \$newId != -1){\n";
      $text .= "           \$logger->trace(\"The insertion was exectuted successfully. \".\n";
      $text .= "                           \"The new Id is [ \$newId ]\");\n";
      $text .= "           \$theResult[RETURN_LAST_ID]=\$newId;\n";
      $text .= "        }else{\n";
      $text .= "           \$theResult[RESULT_CODE] = RESULT_CODE_INTERNAL_ERROR;\n";
      $text .= "           \$theResult[MSG_ERROR] = \$theTable->getStrError();\n";
      $text .= "           \$logger->error(\"The insert failed. Error [ \" . \$theResult[MSG_ERROR] . \" ]\");\n";
      $text .= "        }\n";
       
      $text .= "      \$logger->trace(\"Exit\");\n";
      $text .= "   }\n\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebFunctionDelete($theFileHandler, $theTablesDefinition){
      global $logger;
      $logger->trace("Enter");
      
      $text = "   function delete(\$theTable, \$theData, &\$theResult){\n";
      $text .= "      global \$logger;\n";
      $text .= "      \$logger->trace(\"Enter\");\n";
      $text .= "      \$jsonKey = \$theData[PARAM_KEY];\n";
      $text .= "      \$logger->trace(\"Delete from table \".\$theTable->getTableName().\n";
      $text .= "                    \" with key [ \".json_encode(\$jsonKey).\" ]\");\n";
      
      $logger->trace("number of tables: " . count($theTablesDefinition));
      foreach ($theTablesDefinition as $tableDefinition){
         $text .="\n      if (strcmp(\$theTable->getTableName(),".
               $tableDefinition->name."::".
               $tableDefinition->name."TableC) == 0){\n";
         $logger->trace("The table [ ". $tableDefinition->name .
                        " ] has [ " . count($tableDefinition->key->column).
                        " ] key columns");
         $text .= "         \$composedKey = array();\n";
         foreach ($tableDefinition->key->column as $columnKey){
            $logger->trace("For the table [ " . $tableDefinition->name .
                           " ] getting column key [ \"$columnKey \" ]" );
            $text.= "         \$composedKey[\"$columnKey\"] = json_encode(\$jsonKey);\n";
         }
         $text .= "         \$logger->trace(\"Order table [ \".\$theTable->getTableName().\n";
         $text .= "                  \" ] with key [ \" . json_encode(\$composedKey). \" ]\");\n";
         $text .= "          \$theTable->searchByKey(\$composedKey);\n";
         $text .= "      }\n";
      }
      $text .= "      \$logger->trace(\"Delete data in the database\");\n";
      $text .= "      if (! \$theTable->delete()){\n";
      $text .= "         \$theResult[RESULT_CODE] = RESULT_CODE_INTERNAL_ERROR;\n";
      $text .= "         \$theResult[MSG_ERROR] = \$theTable->getStrError();\n";
      $text .= "         \$logger->error(\"The delete failed. Error [ \" . \$theResult[MSG_ERROR] . \" ]\");\n";
      $text .= "      }\n";
      $text .= "      \$logger->trace(\"Exit\");\n";
      $text .= "   }\n\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebMain($theFileHandler){
      global $logger;
      $logger->trace("Enter");
      $handle = fopen("RequestFromWebMainTemplate.txt", "r");
      $newLine ="";
      if ($handle) {
         while (($readedLine = fgets($handle)) !== false) {
            // process the line read.
      
            $newLine.= $readedLine;
             
         }
      } else {
         // error opening the file.
      }
      fclose($handle);
      fwrite($theFileHandler, $newLine);
      $logger->trace("Exit");
   }
   
/**************** MAIN *********************/
   
   print("Starting ...\n");
   
   $file = $argv[1];
   $outDir = $argv[2];
   $logger->trace("File [ $file ]. Ouput dir [ $outDir ]");
   
   $logger->info("Starting to read the file $file");
   $definitions = simplexml_load_file($file);
   
   $logger->info("Read the database tables definition");
   $logger->info("The file contains ".count($definitions->table_definition)." table definitions.");
   $logger->info("Write the tables files");
   for ($idx = 0; $idx < count($definitions->table_definition); $idx++){
      
      $fileName = $outDir.$definitions->table_definition[$idx]->name.".php";
      $logger->debug("Writing file: $fileName");
      
      $fileHandler = fopen($fileName, "w");
      
      writeHeaderTemplate($fileHandler, 
                       $definitions->table_definition[$idx]->name);
      writeColumnsConstanDefinition($fileHandler,
                       $definitions->table_definition[$idx]->columns->column);
      writePhisicalConstants($fileHandler,
                       $definitions->table_definition[$idx]->phisical_tables);
      writeConstructor($fileHandler, 
                       $definitions->table_definition[$idx]->name,
                       $definitions->table_definition[$idx]->columns->column,
                       $definitions->table_definition[$idx]->key->column,
                        $definitions->table_definition[$idx]->phisical_tables);
      writeMethodInsert($fileHandler, $definitions->table_definition[$idx]->columns->column,
                       $definitions->table_definition[$idx]->key);
      writeMethodsGetSet($fileHandler, $definitions->table_definition[$idx]->columns->column,
                         $definitions->table_definition[$idx]->key);
      
      closeClassDefinition($fileHandler,  $definitions->table_definition[$idx]->name);
      fflush($fileHandler);
      
      $logger->debug("Closing the file $fileName");
      fclose($fileHandler);
      
     
      
   }
   $logger->info("Write the file RequestFromWeb.php");
   $fileHandler = fopen($outDir."RequestFromWeb.php", "w");
   writeRequestFromWebHeader($fileHandler);
   writeRequestFromWebIncludes($fileHandler, $definitions->table_definition);
   writeRequestFromWebConstants($fileHandler);
   writeRequestFromWebFunctionGetTable($fileHandler, $definitions->table_definition);
   writeRequestFromWebFunctionUpdateData($fileHandler, $definitions->table_definition);
   writeRequestFromWebFunctionInsertData($fileHandler, $definitions->table_definition);
   writeRequestFromWebFunctionDelete($fileHandler, $definitions->table_definition);
   writeRequestFromWebSelect($fileHandler, $definitions->table_definition);
   writeRequestFromWebMain($fileHandler);
   fflush($fileHandler);
   writeRequestFromWebConstantFile($outDir);
   $logger->debug("Close RequestFromWeb.php");
   print("... end\n");
?>
