<?php

require_once('field.php');

function pr($obj)
{
  echo '<pre>';
  print_r($obj);
  echo '</pre>';
}

/**
 * Validator
 * @author Julien Desrosiers
 */
class Validator {

  var $_posted = array();
  var $_fields = array();
  var $_is_form_valid = true;
  var $_errors = array();

  /**
   * @param Array $rules
   */
  function Validator ($fields) // this library is Backward-Compatible™
  {
    $this->_posted = $_POST;

    foreach ( $fields as $field_name => $field_infos )
    {
      $this->_fields[] = new Field($field_name, 
        $field_infos['rules'], 
        $field_infos['human_name'] ? $field_infos['human_name'] : '');
    }

    if ( $this->_posted )
    {
      $this->_validate();
    }
  }

  function _validate()
  {
    foreach ( $this->_fields as $field )
    {
      if ( !$field->is_valid( $this->_posted ) ) 
      {
        $this->_is_form_valid = false;
        $this->_errors[] = $field->error();
      }
    }
  }

  function display_errors()
  {
    $messages = array();
    foreach ( $this->_errors as $k => $v )
    {
      $messages[] = $v['message'];
    }
    print implode($messages, '<br/> ');
  }

} // Validator


