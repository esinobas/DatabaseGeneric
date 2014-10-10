<?php
   /**
    * Interface where are defined the functions to handle a database.
    * These functions are commons to all database and they allow to access to
    * their data.
    */

   set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

   interface DatabaseIf {
      
      /**
       * Connect with the database
       * @param string $autoCommit. If the commit operations are done in 
       * automatic mode
       * @return A boolean value indicading whether the connection was 
       * established with success or not.
       */
      //public function connect($autoCommit = true);
      
      /**
       * When the database connect fails, the error is produced is get with
       * this function.
       * @return 
       */
      //public function getConnectError();
      
      /**
       * Returns a boolean value indicading if the database is connected or not.
       * 
       * @return A boolean value.
       */
      //public function isConnected();
      
      /**
       * Close the database connection.
       */
      //public function closeConnection();
      
      /**
       * Excutes a query stament and returns its result.
       * @param string $theQuery The sql query.
       * @return
       */
      //public function query($theQuery);
      
      /**
       * Excutes a slq commnado
       * @param string $theSqlCommand: The sql command to execute
       * @return A boolean value indicating whether the command was executed
       * successfully
       */
      //public function sqlCommand($theSqlCommand);
      
      /**
       * Returns the sql error when a sql command fails.
       * @return A string with the sql error explain.
       *        
       */
      //public function getSqlError();
      
      /**
       * Returns the id key after an insert sql command with auto increment
       * @return The id
       */
      //public function getLastId();
      
      /**
       * Performances the database commit
       */
      //public function commit();
      
      /**
       * Performances the database rollback
       */
      //public function rollback();
      
      
   }