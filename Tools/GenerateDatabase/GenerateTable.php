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
      $text .= "   }";
     
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
      for ($idx= 0; $idx <count($theKey); $idx++){
         $text .="\t\t\$this->tableDefinitionM->addKey(self::"
               .$theKey[$idx]->column."ColumnC);\n";
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
      $conditions = $thePhisicalDef->conditions;
      foreach ($conditions as $condition){
         $logger->trace("Add Condition [ $condition->condition ]");
         $text .= "\n";
         $text .= "      \$this->tableMappingM->addCondition(\"$condition->condition\");\n";
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
      $text .= "         parent::insertData(\$arrayData);\n";
      $text .= "         \$this->loggerM->trace(\"Exit\");\n";
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
      $text .= "   set_include_path( get_include_path() . PATH_SEPARATOR . \$_SERVER['DOCUMENT_ROOT'].\n";
      $text .= "                      '/controlpanel/Cursos/php');\n";
      $text .= "\n";
      $text .= "   include_once 'LoggerMgr/LoggerMgr.php';\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeRequestFromWebIncludes($theFileHandler, $theTablesDefinition){
      global $logger;
      $logger->trace("Enter");
      $text = "";
      
      foreach ($theTablesDefinition as $tableDefinition){
     
         $text .= "   include_once 'Database/".$tableDefinition->name.".php';\n";
      }
      fwrite($theFileHandler, $text);
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
      $text .= "   \$COMMAND = \"command\";\n";
      $text .= "   \$PARAMS = \"paramsCommand\";\n";
      $text .= "   \$PARAM_TABLE = \"Table\";\n";
      $text .= "   \$PARAM_ROWS = \"rows\";\n";
      $text .= "   \$COMMAND_UPDATE = \"U\";\n";
      $text .= "   \$PARAM_KEY = \"key\";\n";
      $text .= "\n";
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
      $text = "   function updateData(\$theTable, \$theRows){\n";
      $text .= "      global \$logger;\n";
      $text .= "      global \$PARAM_KEY;\n\n";
      $text .= "      \$logger->trace(\"Enter\");\n";
      $text .= "      \$logger->trace(\"Rows: [ \".json_encode(\$theRows).\" ]\");\n";
      $text .= "      \$logger->trace(\"Update data of [ \" . \$theTable->getTableName() .\" ]\");\n";
      $text .= "      foreach ( \$theRows as \$row){\n";
      $text .= "         \$key = \$row[\$PARAM_KEY];\n";
      $text .= "         \$logger->trace(\"Search by [ \$key ]\");\n";
      $text .= "         if ( \$theTable->searchByKey(\$key)){\n";
      $text .= "            \$logger->trace(\"The Key has been found.\");\n";
      $logger->trace("number of tables: " . count($theTablesDefinition));
      foreach ($theTablesDefinition as $tableDefinition){
         
         $text .="            if (strcmp(\$theTable->getTableName(),".
                                      $tableDefinition->name."::".
                                      $tableDefinition->name."TableC) == 0){\n";
         $logger->trace("number of colummns: " . count($tableDefinition->column));
         foreach ($tableDefinition->column as $column){
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
         $text .= "         \$logger->trace(\"Update the data in the database\");\n";
         $text .= "         \$theTable->updateRow();\n";
          
      }
      $text .= "         }else{\n";
      $text .= "            \$logger->trace(\"The Key has not been found.\");\n";
      $text .= "         }\n";
       $text .= "      }\n";
      
      
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
                       $definitions->table_definition[$idx]->column);
      writePhisicalConstants($fileHandler,
                       $definitions->table_definition[$idx]->phisical_tables);
      writeConstructor($fileHandler, 
                       $definitions->table_definition[$idx]->name,
                       $definitions->table_definition[$idx]->column,
                       $definitions->table_definition[$idx]->key,
                        $definitions->table_definition[$idx]->phisical_tables);
      writeMethodInsert($fileHandler, $definitions->table_definition[$idx]->column,
                       $definitions->table_definition[$idx]->key);
      writeMethodsGetSet($fileHandler, $definitions->table_definition[$idx]->column,
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
   writeRequestFromWebMain($fileHandler);
   fflush($fileHandler);
   $logger->debug("Close RequestFromWeb.php");
   print("... end\n");
?>
