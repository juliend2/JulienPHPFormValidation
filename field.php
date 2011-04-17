<?php

class Field {
  
  var $_name = '';
  var $_rules = array();
  var $_human_name = '';
  var $_error = array(); // keys: message, type
  var $_is_valid = true;
  var $_posted_data = array();

  function Field($name, $rules, $human_name) 
  {
    $this->_name = $name;
    $this->_rules = $rules;
    $this->_human_name = $human_name;
  }

  /**
   * @return Boolean
   */
  function is_valid( $posted_data )
  {
    $this->_posted_data = $posted_data;
    $this->_dispatch_validation();
    return $this->_is_valid;
  }

  function error()
  {
    return $this->_error;
  }

  function _dispatch_validation()
  {
    foreach ( $this->_rules as $rule )
    {
      if ( $rule === 'not_empty' || $rule['not_empty'])
      {
        $this->_validate_not_empty( is_array($rule) && $rule['message'] ? $rule['message'] : '' );
        $this->_is_valid = false;
      }
    }
  }

  function _validate_not_empty($message='')
  {
    if ( empty($this->_posted_data[ $this->_name ]) )
    {
      $this->_error = array(
        'type'=>'not_empty', 
        'message'=>$this->_format_message($message, ' must not be empty.'));
    }
  }

  function _format_message($message, $default_message)
  {
    return   ($this->_human_name !== '' ? $this->_human_name : $this->_name) 
           . ($message !== '' ? $message : $default_message);
  }

}
