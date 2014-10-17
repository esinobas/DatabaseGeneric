<?php
   /**
    * Class that saves the functions ands properties where the phisical columns
    * and the kyes of a phisical tables are saved.
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   include_once 'LoggerMgr/LoggerMgr.php';
   
   class PhisicalTableDef{
      
      /*** Private properties ***/
      private $loggerM = NULL;
      
      /**
       * Property where is saved the key column name
       * @var string
       */
      private $keyM = "";
      
      /**
       * Array where the relationship between the phisical columns and the 
       * logical columns is saved
       * The relation is: (key)phisical->(value)logical 
       * @var array;
       */
      private $phisicalLogicalColumnsM = array();
      
      /**
       * Array that contains the phisical columns data type
       * @var array;
       */
      private $columnDataTypeM = array();
      
      /**
       * The phisical table name
       * @var string
       */
      private $tableNameM = "";
      
      /*** Functions **/
      
      /**
       * Contructor of the class
       * @param string $theTableName
       */
      public function __construct($theTableName){
         $this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Create [ " . __CLASS__ . " ] for [ $theTableName ]");
         $this->tableNameM = $theTableName;
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Destructor of the class
       */
      public function __destruct(){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Adds the column key into the table definition
       * @param string $theKey
       */
      public function addKey($theKey){
         $this->loggerM->trace("Enter");
         $this->keyM = $theKey;
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Addes a columns definition into the table definicion
       * @param string $thePhisicalColumn
       * @param integer $theDataType
       * @param string $theLogicColumn
       */
      public function addColumn($thePhisicalColumn, $theDataType, $theLogicColumn){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Add phisical column [ $thePhisicalColumn ]".
                           " with data type [ $theDataType ] and logic column ".
                           "[ $theLogicColumn ] into table [ ". $this->tableNameM.
                            " ]");
         $this->phisicalLogicalColumnsM[$thePhisicalColumn] = $theLogicColumn;
         $this->columnDataTypeM[$thePhisicalColumn] = $theDataType;
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Returns the array with the relation between phisical and logical columns
       * @return An array
       */
      public function getColumns(){
         
         $this->loggerM->trace("Enter/Exit");
         return $this->phisicalLogicalColumnsM;
      }
      
      /**
       * Returns the correspondig logical column
       * @param string $thePhisicalColumn
       * @return array;
       */
      public function getLogicalColumn($thePhisicalColumn){
         $this->loggerM->trace("Enter/Exit");
         return $this->phisicalLogicalColumnsM[$thePhisicalColumn];
      }
      
     /**
      * Returns the correspondig columns datatype
      * @param string $thePhisicalColumn
      * @return array;
      */
      public function getDataType($thePhisicalColumn){
         
         $this->loggerM->trace("Enter/Exit");
         return $this->columnDataTypeM[$thePhisicalColumn];
      }
      
   }
?>