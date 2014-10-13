<?php
   /**
    * Class with the common properties and method of the tables to access to 
    * them expecific data.
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once 'TableIf.php';
   include_once 'LoggerMgr/LoggerMgr.php';
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
      private $rowIdxM = -1;
      
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
      
      /**
       * Multi dimensional array where the table keys is stored to a quick access
       * The format is: [<key name>, array[<key>, <data>]]
       * @var array
       */
      private $keysM = array();
      
      /**************** Methods **************/
      /**
       * Constructor of the class. It is protected to avoid it is instanced 
       */
      protected function __construct(){
         
         $this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
      }
      
      /**************** Private methods *******/
      /**
       * Funtion which with the table definition create the index key
       */
      private function createKeys(){
         $this->loggerM->trace("Enter");
         $keys = $this->tableDefinitionM->getKeys();
         for ($idx = 0; $idx < count($keys); $idx ++){
            $this->loggerM->trace("Create index key for [ $keys[$idx] ]");
            $this->keysM[$keys[$idx]] = array();
            for ($idxData = 0; $idxData < count($this->tableDataM); $idxData++){
               $this->loggerM->trace("Create entry: [ " .$this->tableDataM[$idxData][$keys[$idx]] . " ]");
               
               $this->keysM[$keys[$idx]][$this->tableDataM[$idxData][$keys[$idx]]] = $idxData;
            }
             
            
         }
         
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Open the table. Load the table date from database into memory
       */
      public function open(){
         
         $this->loggerM->trace("Enter");
         DatabaseMgr::openTable($this->tableMappingM, $this->tableDataM);
         $this->createKeys();
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
         $this->loggerM->trace("Enter: ". count($this->tableDataM));
         $thereAreMoreRows = true;
         if ( $this->rowIdxM == (count($this->tableDataM) -1) ){
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
      public function isEmpty(){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
         return ( count($this->tableDataM) == 0);
      }
      
      /**
       * Initialize the table cursor
       */
      public function rewind(){
         $this->loggerM->trace("Enter");
         $this->rowIdxM = -1;
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
       * (non-PHPdoc)
       * @see TableIf::update()
       */
      public function update(){
         $this->loggerM->trace("Enter");
          
         $this->loggerM->trace("Exit");
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::updateRow()
       */
      public function updateRow(){
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
         $this->tableDataM[$this->rowIdxM][DatabaseMgr::modifiedRowC] = true;
         $this->loggerM->trace("Exit");
                     
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::searchByKey()
       */
      public function searchByKey($theKey){
         
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Search key [ $theKey ]");
         $definedKey = $this->tableDefinitionM->getKeys()[0];
         $this->loggerM->trace("Search in [ $definedKey ][ $theKey ]");
         if ( ! array_key_exists($theKey, $this->keysM[$definedKey]) ){
            $this->loggerM->trace("The key was not found");
            $this->loggerM->trace("Exit");
            return false;
         }else{
            
            
            $row = $this->keysM[$definedKey][$theKey];
            $this->loggerM->trace("The row is [ $row ]");
            $this->rowIdxM = $row;
            $this->loggerM->trace("Exit");
            return true;
         }
        
         
      }
   }
?>