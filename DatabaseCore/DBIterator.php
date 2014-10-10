<?php

   /**
    * File that contains the class DBIterator.
    * That class is used to access at the query results
    */
    set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
    
    class DBIterator{
       
       private $rowsM;
       
       private $indexM;
    
       public function __construct($theData) {
       
          $this->rowsM = $theData;
          $this->indexM = 0;
          
       }   
       
       public function __destruct(){
          
       }
       
       public function next(){
            
          if ($this->indexM < count($this->rowsM)){
             
             $this->indexM ++;
             return true;          
          }else{
             return false;
          }
       }
       
       public function getRow(){
      
          return $this->rowsM[$this->indexM -1 ];       
       }
       
       public function getRowByIndex($theIndex){
           
          return $this->rowsM[$theIndex];       
       }
       
       public function reset(){
       
          $this->indexM = 0;   
       }
       
       public function getNumRows(){
       
          return count($this->rowsM);   
       }
      
    }


?>