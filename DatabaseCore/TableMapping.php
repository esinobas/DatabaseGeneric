<?php
   /**
    * Table mapping between the logical table and its corresponding 
    * phisical table. 
    * With this mapping, the application is able to access to the 
    * database
    */
   
   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   include_once 'LoggerMgr/LoggerMgr.php';
   
   class TableMapping{
      
      /*** private properties ***/
      
      private $loggerM = NULL;
      /**
       * Tables names that correspoding with the phisical tables.
       * @var 
       */
      private $phisicalTablesM = array();
      
      
      /**
       * Strucuture where are saved the conditions which the query must comply.
       * @var 
       */
      private $conditionsM = array();
      
      /*** To be used in a futher ***/
      private $pathDatabaseConfigM = "";
      
      private $databaseTypeM = 0;
      
      
      /****** Public Functions *********/
      
      /**
       * Constructor
       */
       public function __construct(){
         
          $this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);
          $this->loggerM->trace("Enter");
          $this->loggerM->trace("Exit");
          
       }
      
      /**
       * Function that addes a new table in the mapping
       * 
       * @param $theTable: The phisical table name
       */ 
      public function addTable($theTable){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Add table [ $theTable ]. Num. tables [ " .
                            count($this->phisicalTablesM) . " ]");
         $this->phisicalTablesM[$theTable] = array();
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Functiosn that addes a new mapping between a phisical column and 
       * a logical column
       * 
       * @param string $theTable: The phisical table name
       * @param string $thePhisicalColumn: The phisical column name
       * @param string $theLogicalColumn: The logical column name
       */
      public function addColumn($theTable, $thePhisicalColumn, $theLogicalColumn){
         $this->loggerM->trace("Enter");
        
         $composedColumn = $thePhisicalColumn;
         $this->loggerM->trace("Add column [ $composedColumn ] in [ $theTable ]. ".
               "Num. colums [ " . count($this->phisicalTablesM[$theTable]) . " ]");
         //Should be check if the parameter $the table exist before
         //inserte the mapping
         $this->phisicalTablesM[$theTable][$composedColumn] = $theLogicalColumn;
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Function that addes a condition to be comply for the query.
       * @param string $theCondition:
       */
      public function addCondition($theCondition){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Add condition [ $theCondition ]. Num. conditions [ " .
               count($this->conditionsM) . " ]");
         $this->conditionsM[count($this->conditionsM)] = $theCondition;
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Returns the defined columns in the mapping
       * @param string The table name
       * @return An array with the phisical names columns of the table
       */
      public function getColumns($theTable){
         $this->loggerM->trace("Enter/Exit");
         return  $this->phisicalTablesM[$theTable];
      }
      
      /**
       * Returns the phisical tables names
       */
      public function getTables(){
         $this->loggerM->trace("Enter/Exit");
         return $this->phisicalTablesM;
      }
   }
?>