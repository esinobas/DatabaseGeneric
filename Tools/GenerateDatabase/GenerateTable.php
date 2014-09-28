<?php
   /**
    * Script makes a template php file witha a object or class that allows the
    * database access
    */
   set_include_path( get_include_path() . PATH_SEPARATOR . 
   dirname(__FILE__)."/../../..");
   
   include_once 'LoggerMgr/LoggerMgr.php';
   $logger = LoggerMgr::Instance()->getLogger("main");
  
  
   
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
   
   $logger->info("Read the database tables definition");
   $logger->debug("The file contains ".count($definitions->table_definition)." table definitions.");
   for ($idx = 0; $idx < count($definitions->table_definition); $idx++){
      $fileName = $outDir.$definitions->table_definition[$idx]->name.".php";
      $logger->trace("Writing file: $fileName");
      
      $fileHandler = fopen($fileName, "w");
      
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
