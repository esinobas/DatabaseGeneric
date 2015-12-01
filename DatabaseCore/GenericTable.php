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
       * 
       * @var array. Array used like backup of the table data when the the 
       * methods searchByColumn or searchByKey has been executed and the 
       * $tableDataM is modified due to that reason.
       * When a commando like insert or update or delete is called, the next
       * action is merge the current data table with the backup
       */
      private $backupTableDataM = null;
      
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
      
      /**
       * Property where is saved the message error when a operation or action
       * on the table is executed with fail.
       * 
       * @var string
       */
      private $strErrorM = "";
      
      /**************** Methods **************/
      /**
       * Constructor of the class. It is protected to avoid it is instanced 
       */
      protected function __construct(){
         
         $this->loggerM = LoggerMgr::Instance()->getLogger(__CLASS__);
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
      }
      
      public function __destruct(){
         $this->loggerM->trace("Enter");
         DatabaseMgr::closeConnection();
         $this->loggerM->trace("Exit");
      }
      
     
      private function mergeTableData(){
         
         $this->loggerM->trace("Enter");
         $keys = array_keys($this->tableDataM);
         foreach ($keys as $key){
            $newData = $this->tableDataM[$key];
            $this->backupTableDataM[$key] = $newData;
            
            //$this->loggerM->trace("Key [ $key ] Modified " .
            //      ($this->backupTableDataM[$key][DatabaseMgr::modifiedRowC]?"[TRUE]":"[FALSE]"));
         }
         unset($this->tableDataM);
         $this->tableDataM =  $this->backupTableDataM;
         unset($this->backupTableDataM);
         $this->backupTableDataM = null;
         $this->rowIdxM = -1;
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
         unset($this->tableDataM);
         $this->tableDataM = array();
         $this->open();
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
            if ($this->rowIdxM != -1){
               next($this->tableDataM);
            }
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
         return ( count($this->tableDataM ) == 0);
      }
      
      /**
       * Initialize the table cursor
       */
      public function rewind(){
         $this->loggerM->trace("Enter");
         $this->rowIdxM = -1;
         reset($this->tableDataM);
         $this->loggerM->trace("Exit");
      }
      
      /**
       * Detele the selected row
       */
      public function delete(){
         $this->loggerM->trace("Enter");
         DatabaseMgr::delete($this->tableMappingM, current($this->tableDataM));
         //$this->refresh();
         $this->loggerM->trace("Exit");
      }
      /**
       * Inserts data in the table. The data are passed in an array parameter, 
       * where the array key is the column name and the array value is the
       * data value.
       * @param array $theDataArray
       */
      public function insertData($theDataArray){
         $this->loggerM->trace("Enter");
         $newId = -1;
         if (DatabaseMgr::insert($this->tableMappingM, $theDataArray, 
                             $this->tableDataM,
                             $this->tableDefinitionM->getKeys()[0],
                             $newId)){
            $this->loggerM->trace("The data was inserted successfully. ".
                                  "The new Id is [ $newId ]");
            $this->strErrorM = "";
         }else{
            $this->strErrorM = DatabaseMgr::getDatabaseError();
            $this->loggerM->error("The data was not inserted [ " .
                    $this->strErrorM ." ]");
         }
         $this->rewind();
         
         $this->loggerM->trace("Exit");
         return $newId;
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::update()
       */
      public function update(){
         $this->loggerM->trace("Enter");
         if ($this->backupTableDataM != null){
         
            $this->mergeTableData();
         }
         $result = DatabaseMgr::updateTable($this->tableMappingM, $this->tableDataM);
         if ($result){
            //$this->mergeTableData();
            $this->strErrorM ="";
         }else{
            $this->strErrorM = DatabaseMgr::getDatabaseError();
            $this->loggerM->error("The row was not updated [ " .
                  $this->strErrorM ." ]");
         }
         $this->loggerM->trace("Exit");
         return $result;
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::updateRow()
       */
      public function updateRow(){
         $this->loggerM->trace("Enter");
         if ($this->backupTableDataM != null){
         
            $this->mergeTableData();
         }
         $result = DatabaseMgr::updateTable($this->tableMappingM, array(current($this->tableDataM)));
         if ($result){
            //$this->mergeTableData();
            $this->strErrorM ="";
         }else{
            $this->strErrorM = DatabaseMgr::getDatabaseError();
            $this->loggerM->error("The row was not updated [ " .
                  $this->strErrorM ." ]");
         }
         $this->loggerM->trace("Exit");
         return $result;
      }
      
      protected function get($theColumn){
         
         $this->loggerM->trace("Enter");
         $this->loggerM->debug("Get value from column [ $theColumn ]->".
                "[ ".current($this->tableDataM)[$theColumn] ." ]");
         
         $this->loggerM->trace("Exit");
         return current($this->tableDataM)[$theColumn];
      }
      
      protected function set($theColumn, $theValue){
          
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Current value in [ $theColumn ] -> [ ".
               current($this->tableDataM)[$theColumn] ." ]");
         $this->loggerM->debug("Set value [ $theValue ] into column [ $theColumn ]");
         if (current($this->tableDataM)[$theColumn] != $theValue){
            
            $this->tableDataM[key($this->tableDataM)][$theColumn] = $theValue;
            $this->tableDataM[key($this->tableDataM)][DatabaseMgr::modifiedRowC] = true;
         }
        
         $this->loggerM->trace("Exit");
                     
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::searchByKey()
       */
      public function searchByKey($theKey){
         
         $this->loggerM->trace("Enter");
         if (!is_array($theKey)){
            $this->loggerM->trace("Search key [ $theKey ]");
         }else{
            $this->loggerM->trace("Search key [ ".json_encode($theKey)." ]");
         }
         
         $result = false;
         //$columnkey = $this->tableDefinitionM->getKeys()[0];
         foreach ($this->tableDefinitionM->getKeys() as $columnKey){
            if ( ! is_array($theKey)){
               $this->loggerM->debug("Search in [ $columnKey ][ $theKey ]");
               if ($this->searchByColumn($columnKey, $theKey)){
                  $this->loggerM->debug("The [ $theKey ] has been found in [ $columnKey ]");
                  $result = true;
               }else{
                  $this->loggerM->debug("The [ $theKey ] has NOT been found in [ $columnKey ]");
                  $this->loggerM->trace("Exit");
                  return false;
               }
            }else{
               $this->loggerM->debug("Search in [ $columnKey ][ $theKey[$columnKey] ]");
               if ($this->searchByColumn($columnKey, $theKey[$columnKey])){
                  $this->loggerM->debug("The [ $theKey[$columnKey] ] has been found in [ $columnKey ]");
                  $result = true;
               }else{
                  $this->loggerM->debug("The [ $theKey[$columnKey] ] has NOT been found in [ $columnKey ]");
                  $this->loggerM->trace("Exit");
                  return false;
               }
            }
         }
         
         
         $this->loggerM->trace("Exit");
         return $result;
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::searchByColummn()
       */
      public function searchByColumn($theColumn, $theValue){
         $this->loggerM->trace("Enter");
         
         if ($this->backupTableDataM != null){
            
            $this->mergeTableData();
         }
         
         $callbackSearchByColumn = function ($var) use ($theColumn, $theValue){
            $this->loggerM->trace("Enter");
            $this->loggerM->trace("Exit");
            return ($var[$theColumn] == $theValue);
         };
         
         $this->loggerM->debug("Searching value [ $theValue ] in column ".
               "[ $theColumn ]");
         
         $resultArray = array_filter($this->tableDataM, $callbackSearchByColumn);
         $this->loggerM->trace("The search has had [ ". count ($resultArray) ." ]");
         if ( count ($resultArray) > 0){
            $this->backupTableDataM = $this->tableDataM;
            unset($this->tableDataM);
            $this->tableDataM = $resultArray;
            $this->rowIdxM = -1;
            $this->loggerM->trace("Exit");
            return true;
         }else{
            $this->loggerM->trace("Exit");
            return false;
         }
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::getCardinality()
       */
      public function getCardinality(){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
         return count($this->tableDataM);
         
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::getStrError()
       */
      public function getStrError(){
         $this->loggerM->trace("Enter");
         $this->loggerM->trace("Exit");
         return $this->strErrorM;
      }
      
      /**
       * Skips to the a row.
       * @param integer $theNumRows
       */
      public function skip($theNumRows){
         
         if ($theNumRows >= $this->getCardinality()){
            $this->loggerM->warn("The skipped [ $theNumRows ] is greater then or equal the tables rows [ " .
                        $this->getCardinality() . " ]");
         }
         if ($theNumRows == 0){
            $this->loggerM->warn("The skipped num rows is 0");
         }
         if ($theNumRows > 0 && $theNumRows < $this->getCardinality()){
            for ($i = 0;  $i < $theNumRows; $i++){
               $this->next();
            }
         }
      }
      
      /**
       * (non-PHPdoc)
       * @see TableIf::getTableName()
       */
      /*public function getTableName(){
         return "";
      }*/
   }
?>