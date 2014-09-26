<?php
   /**
    * Class to define a table
    */

   include_once 'ColumnDef.php';
   
   class TableDef{
      
      /*
       * **********************
       * Properties
       * **********************
       */
      
      private $nameM;
      private $columnsM = array();
      private $keyM = array();
      
      /*
       * *******************
       * Functions
       * *******************
       */
      /**
       * Constructor of the class
       * @param string $theName: The table name
       */
      public function __construct($theName){
         
         $this->nameM = $theName;
      }
      
      /**
       * Add a column into the table definition
       * @param ColumnDef $column: The column definition
       */
      public function addColumn($theColumn){
         
         $this->columnsM[count($this->columnsM)] = $theColumn;
      }
      
      /**
       * Add a key into the table definition
       * @param string $theKey: The column name
       */
      public function addKey($theKey){
         
         $this->keyM[count($this->keyM)] = $theKey;
      }
      
      /**
       * Returns the table columns definition
       * @return An array with the columns definition.
       */
      public function getColumns(){
         
         return $this->columnsM;
      }
      
      /**
       * Returns the table keys
       */
      public function getKeys(){
         
         return $this->keyM;
      }
   }
?>
