<?php
   /**
    * Interface where are defined the table functions to handle the table data
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
   interface TableIf{
      
      /**
       * Opens the table and loads its information in memory to be access it.
       */
      public function open();
      
      /**
       * Move the row cursor to the next row in the table and allow access 
       * to the row data.
       */
      public function next();
      
      /**
       * Refresh the table data
       */
      public function refresh();
      
      /**
       * Check if the table has almost one row or the table has not rows
       */
      public function isEmpty();
      
      /**
       * Move the row cursor to the first table row
       */
      public function rewind();
      
      /**
       * Delete the selected row of the table, both the memory and the its 
       * corresponding table
       */
      public function delete();
      
      /**
       * Insert a new row in the memory and in the table
       */
      public function insert();
      
      /**
       * Modify all the data contains in the current row, and also in the 
       * database table
       */
      public function update();
      
   }
?>