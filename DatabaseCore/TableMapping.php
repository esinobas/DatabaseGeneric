<?php
   /**
    * Table mapping between the logical table and its corresponding 
    * phisical table. 
    * With this mapping, the application is able to access to the 
    * database
    */
   
   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   class TableMapping{
      
      /*** private properties ***/
      /**
       * Tables names that correspoding with the phisical tables.
       * @var 
       */
      private $phisicalTablesM = array();
      
      /**
       * Structure where are saved the mapping between the logial column
       * with the phisical column. The phisical columns must be defined
       * table name "." column name
       */
      private $columnsMappingM = array();
      
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
         
          
       }
      
      /**
       * Function that addes a new table in the mapping
       * 
       * @param $theTable: The phisical table name
       */ 
      public function addTable($theTable){
         $this->phisicalTablesM[count($this->phisicalTablesM)] = $theTable;
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
         $composedColumn = $theTable.'.'.$thePhisicalColumn;
         //Should be check if the parameter $the table exist before
         //inserte the mapping
         $this->columnsMappingM[$composedColumn] = $theLogicalColumn;
      }
      
      /**
       * Function that addes a condition to be comply for the query.
       * @param string $theCondition:
       */
      public function addCondition($theCondition){
         $this->conditionsM[count($this->conditionsM)] = $theCondition;
      }
      
      /**
       * Returns the defined columns in the mapping
       * 
       * @return An array with the phisical names columns of the table
       */
      public function getColumns(){
         
         return  $this->columnsMappingM;
      }
      
      /**
       * Returns the phisical tables names
       */
      public function getTables(){
         
         return $this->phisicalTablesM;
      }
   }
?>