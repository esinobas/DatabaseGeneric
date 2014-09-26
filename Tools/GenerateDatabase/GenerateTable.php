<?php
   /**
    * Script makes a template php file witha a object or class that allows the
    * database access
    */
   set_include_path( get_include_path() . PATH_SEPARATOR . "../");
   
   include_once '../LoggerMgr/LoggerMgr.php';
   $logger = LoggerMgr::Instance()->getLogger("main");
  
   function writeHeader($theFileHandler){
      global $logger;
      
      $logger->trace("Enter");
      
      $text = "<?php\n";
      $text.= "   /**\n";
      $text.= "    * Class where the logical and physical database is defined.\n";
      $text.= "    * Also It is defined the mapping between both schemas.\n";
      $text.= "    * In this class are the tables definition in logical and physical level\n".
      $text.= "    *";
      $text.= "    * The class has 2 parts:\n";
      $text.= "    *    1.- The table and columns names.\n";
      $text.= "    *       The names are constans\n";
      $text.= "    *    2.- The tables definitions using the declared names previously\n";
      $text.= "    */\n";
      $text.= "\n";
      $text.= "   include_once dirname(__FILE__).'/Core/TableDef.php';\n";
      $text.= "   include_once dirname(__FILE__).'/Core/ColumnDef.php';\n";
      $text.= "   include_once dirname(__FILE__).'/Core/ColumnType.php';\n";
      $text.= "\n";
      $text.= "   include_once 'LoggerMgr/LoggerMgr.php';\n";
      $text.= "\n";
      fwrite($theFileHandler, $text);
     
      $logger->trace("Exit");
   }
   
   function writeClassDefinition($theFileHandler, $theClassName, $theColumns){
      global $logger;
      $logger->trace("Enter");
      $text = "   class $theClassName {\n";
      $text .= "\n";
      $text .= "     private \$loggerM;\n";
      $text .= "\n";
      
      $logger->debug("Write the constans name and name columns");
      
      $text .= "     /*\n";
      $text .= "      * Contant Table Name\n";
      $text .= "      */\n";
      $text .= "     const ".$theClassName."TableC = \"$theClassName\";\n";
      $text .= "\n";
      $text .= "     /*\n";
      $text .= "      * Contants table columns\n";
      $text .= "      */\n";
      for ($idx= 0; $idx <count($theColumns); $idx++){
         $text .= "     const ".$theColumns[$idx]->name."ColumnC = ".
                                   "\"".$theColumns[$idx]->name."\";\n";
      }
      
      
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function closeClassDefinition($theFileHandler){
      global $logger;
      $logger->trace("Enter");
      $text = "   }\n";
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
      
      if ($theType == "string"){
         return "ColumnType::stringC";
      }
   }
   
   function writeConstructor($theFileHandler, $theClassName, $theColumns, $theKey){
      global $logger;
      
      $logger->trace("Enter");
      $text  = "     /*\n";
      $text .= "      * Constructor. The table definition is done here\n";
      $text .= "      */\n";
      $text .= "\tpublic function __constructor(){\n";
      $text .= "      \$this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);\n";
      $text .= "       \n";
      $text .= "\t\t\$this->tableDefinitionM = new TableDef(self::".$theClassName."TableC);\n";
      for ($idx= 0; $idx <count($theColumns); $idx++){
         $text .="\t\t\$this->tableDefinitionM.addColumn(new ColumnDef(
                              self::".$theColumns[$idx]->name."ColumnC,"
                               .getColumnType($theColumns[$idx]->type)."));\n";
      }
      for ($idx= 0; $idx <count($theKey); $idx++){
         $text .="\t\t\$this->tableDefinitionM.addKey(self::"
                                    .$theColumns[$idx]->name."ColumnC);\n";
      }
      $text .= "\t}\n";
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }
   
   function writeMethodsGetSet($theFileHandler, $theColumns){
      global $logger;
      $logger->trace("Enter");
      $text = "      \n";
      for ($idx= 0; $idx <count($theColumns); $idx++){
         $text.= "      public function get".$theColumns[$idx]->name."(){\n";
         $text.= "         \$logger->trace(\"Enter\");\n";
         $text.= "         return \$this->get(self::".
                         $theColumns[$idx]->name."ColumnC);\n";
         $text.= "         \$logger->trace(\"Exit\");\n";
         $text.= "      }\n";
         $text.= "      \n";
         $text.= "      public function set".$theColumns[$idx]->name."($".
                          $theColumns[$idx]->name."){\n";
         $text.= "         \$logger->trace(\"Enter\");\n";
         $text.= "         \$this->set(self::".
               $theColumns[$idx]->name."ColumnC, \$".
                              $theColumns[$idx]->name.");\n";
         $text.= "         \$logger->trace(\"Exit\");\n";
         $text.= "      }\n";
      }
      fwrite($theFileHandler, $text);
      $logger->trace("Exit");
   }

/**************** MAIN *********************/
   
   print("Starting ...\n");
   
   $file = $argv[1];
   $outDir = $argv[2];
   $logger->trace("File [ $file ]. Ouput dir [ $outDir ]");
   
   $logger->info("Starting to read the file $file");
   $definitions = simplexml_load_file($file);
   
   $logger->debug("The file contains ".count($definitions->table_definition)." table definitions.");
   for ($idx = 0; $idx < count($definitions->table_definition); $idx++){
      $fileName = $outDir.$definitions->table_definition[$idx]->name.".php";
      $logger->trace("Writing file: $fileName");
      
      $fileHandler = fopen($fileName, "w");
      /*writeHeader($fileHandler);
      writeClassDefinition($fileHandler, 
                       $definitions->table_definition[$idx]->name,
                       $definitions->table_definition[$idx]->column);
      closeClassDefinition($fileHandler);
      */
      writeHeaderTemplate($fileHandler, 
                       $definitions->table_definition[$idx]->name);
      writeColumnsConstanDefinition($fileHandler,
                       $definitions->table_definition[$idx]->column);
      writeConstructor($fileHandler, 
                       $definitions->table_definition[$idx]->name,
                       $definitions->table_definition[$idx]->column,
                       $definitions->table_definition[$idx]->key);
      writeMethodsGetSet($fileHandler, $definitions->table_definition[$idx]->column);
      
      closeClassDefinition($fileHandler);
      fflush($fileHandler);
      
      $logger->debug("Closing the file $fileName");
      fclose($fileHandler);
   }
   
   print("... end\n");
?>