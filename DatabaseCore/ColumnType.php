<?php
   /**
    * Class that contains the constats with the column types
    */
   
   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   
   class ColumnType{
      
      /*
       * Constants
       */
      const integerC =   0;
      const stringC  =   1;
      const timestampC = 2;
      const floatC     = 3;
      const booleanC    = 4;
      
   }
?>