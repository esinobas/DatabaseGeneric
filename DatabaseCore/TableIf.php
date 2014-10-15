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
      public function updateRow();
      
      /**
       * Saves in the database, all the rows modified in the table
       */
      public function update();
      
      /**
       * Searchs a row in the table by the table key.
       * It the searched row is not found, the function returns a boolean value
       * to false. Otherwise it returns a true.
       * @param unknown $theKey
       */
      public function searchByKey($theKey);
      
      /**
       * Searchs a row or a set of rows which the column value is equal to 
       * the searched value. 
       * @param string $theColumn
       * @param unknown $theValue
       * 
       * @return A boolen value that indicates if the row or rows have been found
       */
      public function searchByColummn($theColumn, $theValue);
      
      /**
       * Returns the number of rows that the table has.
       * 
       * @return A integer with the number of rows which the table has.
       */
      public function getCardinality();
   }
?>