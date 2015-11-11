<?php
   /**
    * Table mapping between the logical table and its corresponding 
    * phisical table. 
    * With this mapping, the application is able to access to the 
    * database
    */
   
   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   include_once 'LoggerMgr/LoggerMgr.php';
   include_once 'PhisicalTableDef.php';
   
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
      
      /**
       * Structure where is saved the query order
       */
      private $orderByM = array();
      
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
         $this->phisicalTablesM[$theTable] = new PhisicalTableDef($theTable);
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Function that addes a new mapping between a phisical column and 
       * a logical column
       * 
       * @param string $theTable: The phisical table name
       * @param string $thePhisicalColumn: The phisical column name
       * @param string $theLogicalColumn: The logical column name
       * @param integer $theDataType: The phisical column data type.
       */
      public function addColumn($theTable, $thePhisicalColumn, 
                               $theLogicalColumn, $theDataType){
         $this->loggerM->trace("Enter");
        
         $composedColumn = $thePhisicalColumn;
         $this->loggerM->trace("Add column [ $thePhisicalColumn ] in [ $theTable ]. ".
               "With data type [ $theDataType ]. Num. colums [ " . count($this->phisicalTablesM[$theTable]) . " ]");
         //Should be check if the parameter $the table exist before
         //inserte the mapping
         $this->phisicalTablesM[$theTable]->addColumn($thePhisicalColumn, 
                                                      $theDataType,
                                                      $theLogicalColumn);
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
       * @return An array with the phisical table definition
       */
      public function getColumns($theTable){
         
         $this->loggerM->trace("Exit");
         $this->loggerM->trace("Get columns of the table [ ".
              $theTable . " ] that has [ " . 
           count($this->phisicalTablesM[$theTable]->getColumns()) . " ]");
         $this->loggerM->trace("Exit");
         return  $this->phisicalTablesM[$theTable]->getColumns();
      }
      
      /**
       * Returns the definition phisical tables
       * 
       * @return An array with the definition phisical tables
       */
      public function getTables(){
         $this->loggerM->trace("Enter/Exit");
         return $this->phisicalTablesM;
      }
      
      /**
       * Adds the column table key
       * @param string $theTable
       * @param string $theKey
       */
      public function addKey($theTable, $theKey){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Add key [ $theKey ] to table [ $theTable ]");
         $this->phisicalTablesM[$theTable]->addKey($theKey);
         $this->loggerM->trace("Exit");
         
      }
      
       public function getKey($theTable){
          $this->loggerM->trace("Enter/Exit");
          return $this->phisicalTablesM[$theTable]->getKey();
       }
      
      /**
       * Returns the phisical table definition
       * @param string $theTable
       * @return A PhisicalTableDef class
       */
      public function getTableDefinition($theTable){
         
         $this->loggerM->trace("Enter/Exit");
         return $this->phisicalTablesM[$theTable];
      }
      
      /**
       * Returns the conditions that must comply the query for select the data
       * @return Array with the conditions in sql format.
       */
      public function getConditions(){
         
         $this->loggerM->trace("Enter/Exit");
         return $this->conditionsM;
      }
      
      /**
       * Add the order by search in the table
       * 
       * @param theOrderBy The clausule order by
       */
      public function addOrderBy($theOrderBy){
         $this->loggerM->trace("Enter/Exit");
         $this->orderByM = $theOrderBy;
      }
      
      
      /**
       * Returns the clausule order by
       * @return Array with the order
       */
      public function getOrderBy(){
         $this->loggerM->trace("Enter/Exit");
         return $this->orderByM;
      }
   }
?>