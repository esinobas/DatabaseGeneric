<?php
   /**
    * Class to define a table column from a database
    */
   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   include_once 'ColumnType.php';
   
   class ColumnDef{
      
      /*
       ******************************
       * Properties
       * ****************************
       */
      /*
       * Private
       */
      private $nameM;
      private $typeM;
      
      
      /*
       * ********************
       * Functions
       * ********************
       */
      /**
       * Constructor of the class
       * @param string $theName: The column name
       * @param ColumnType $theType: The column type
       */
      public function __construct($theName, $theType){
         
         $this->nameM = $theName;
         $this->typeM = $theType;
      }
      
      /**
       * Returns the columna name.
       */
      public function getName(){
         
         return $this->nameM;
      }
      
      /**
       * Returns the columns type.
       */
      public function getType(){
         
         return $this->typeM;
      }
   }
?>