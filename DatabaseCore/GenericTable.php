<?php
   /**
    * Class with the common properties and method of the tables to access to 
    * them expecific data.
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once 'TableIf.php';
   include_once '../LoggerMgr/LoggerMgr.php';
   include_once 'DatabaseMgr.php';
   
   class GenericTable implements TableIf{
      
      /**************** Properties **************/
      /**
       * 
       * @var Logger. Writes a file log.
       */
      private $loggerM = NULL;
      
      /**
       * 
       * @var integer. The current row index of the table
       */
      private $rowIdxM = 0;
      
      /**
       * 
       * @var array. The table date is stored in a multi dimensional array
       */
      private $tableDataM = array();
      
      /**
       * Property where the table definition is saved.
       * @var TableDef
       */
      protected $tableDefinitionM = NULL;
      
      /**
       * Property where the mapping between phisical and logical table
       * is saved
       * @var TableMapping
       */
      protected $tableMappingM = NULL;
      
      /**************** Methods **************/
      /**
       * Constructor of the class. It is protected to avoid it is instanced 
       */
      protected function __construct(){
         
         $this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Open the table. Load the table date from database into memory
       */
      public function open(){
         $this->loggerM->trace("Enter");
         DatabaseMgr::openTable($this->tableMappingM, $this->tableDataM);
         $this->loggerM->trace("Exit");
         
      }
      
      /**
       * Refresh the table data and initialize the cursor
       */
      public function refresh(){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
          
      }
      
      /**
       * Go to the next table row to allow the access to its data.
       * @return boolean. When the table cursor has arrived to the table finish
       */
      public function next(){
         $this->loggerM->trace("Enter");
         $thereAreMoreRows = true;
         if ( $this->rowIdxM == count($this->tableDataM)){
            $thereAreMoreRows = false;
         }else{
            $this->rowIdxM ++;
         }
         $this->loggerM->trace("Exit");
         return $thereAreMoreRows;
          
      }
      
      
      /**
       * 
       * @return boolean. True when the table has not rows.
       */
      public function isEmtpy(){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
         return ( count($this->tableDataM) == 0);
      }
      
      /**
       * Initialize the table cursor
       */
      public function rewind(){
         $this->loggerM->trace("Enter");
         $this->rowIdxM = 0;
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Detele the selected row
       */
      public function delete(){
         $this->loggerM->trace("Enter");
         
         $this->loggerM->trace("Exit");
      }
      /**
       * Insert a new row in the table
       */
      public function insert(){
         $this->loggerM->trace("Enter");
          
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Update the current row
       */
      public function update(){
         $this->loggerM->trace("Enter");
          
         $this->loggerM->trace("Exit");
      }
      
      protected function get($theColumn){
         
         $this->loggerM->trace("Enter");
         $this->loggerM->debug("Get value from column [ $theColumn ]->
                [ ".$this->tableDataM[$this->rowIdxM][$theColumn] ." ]");
         
         $this->loggerM->trace("Exit");
         return $this->tableDataM[$this->rowIdxM][$theColumn];
      }
      
      protected function set($theColumn, $theValue){
          
         $this->loggerM->trace("Enter");
         $this->loggerM->debug("Set value [ $theValue ] into column [ $theColumn ]");
         $this->tableDataM[$this->rowIdxM][$theColumn] = $theValue;
         $this->loggerM->trace("Exit");
                     
      }
   }
?>