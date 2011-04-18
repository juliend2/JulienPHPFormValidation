<?php
// function pr($obj)
// {
//   echo '<pre>';
//   print_r($obj);
//   echo '</pre>';
// }


/**
 * Field
 * @author Julien Desrosiers
 */
class Field {

  var $_formats     = array(
    'email' => '/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/'
  );
  var $_posted_data = array();
  var $_error       = array(); // keys: message, type
  var $_rules       = array();
  var $_is_valid    = true;
  var $_type        = 'text';
  var $_human_name  = '';
  var $_name        = '';

  function Field($name, $rules, $human_name, $type) 
  {
    $this->_name = $name;
    $this->_rules = $rules;
    $this->_human_name = $human_name;
    $this->_type = $type;
  }

  /**
   * Getters and Setters
   */
  function name()
  {
    return $this->_name;
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

  function _is_form_posted()
  {
    return count($this->_posted_data) > 0;
  }

  function value( $default_value='' )
  {
    if ( $this->_is_form_posted() )
    {
      if ( $this->_type === 'checkbox' && $this->_posted_value() !== '' )
      {
        echo " checked='checked'";
      }
      else
      {
        echo " value='{$this->_posted_value()}'";
      }
    }
    else
    {
      if ( $this->_type === 'checkbox' )
      {
      }
      else
      {
        echo " value='{$default_value}'";
      }
    }
  }

  function _posted_value()
  {
    return ($this->_posted_data[$this->_name] ? $this->_posted_data[$this->_name] : '');
  }

  function error()
  {
    return $this->_error;
  }

  function _dispatch_validation()
  {
    foreach ( $this->_rules as $rule )
    {
      $message = is_array($rule) && $rule['message'] ? $rule['message'] : '' ;
      if ( $rule === 'not_empty' || $rule['not_empty'])
      {
        $this->_validate_not_empty( $message );
      }
      else if ( $rule['format'] )
      {
        $this->_validate_format( $rule['format'], $message );
      }
    }
  }

  function _validate_not_empty($message='')
  {
    if ( empty($this->_posted_data[ $this->_name ]) )
    {
      $this->_error = array(
        'type'=>'not_empty', 
        'message'=>$this->_format_message($message, ' must not be empty.') );
      $this->_is_valid = false;
      return false;
    }
  }

  function _validate_format($format, $message='')
  {
    if ( !preg_match($this->_formats[$format], $this->_posted_data[ $this->_name ]) )
    {
      $this->_error = array(
        'type'=>'format',
        'message'=>$this->_format_message($message, ' must have a valid format.'));
      $this->_is_valid = false;
      return false;
    }
  }

  function _format_message($message, $default_message)
  {
    return   ($this->_human_name !== '' ? $this->_human_name : $this->_name)
           . ($message !== '' ? $message : $default_message);
  }

} // Field


/**
 * Validator
 * @author Julien Desrosiers
 */
class Validator {

  var $error_template = '<li class="error">{error_msg}</li>';
  var $_posted        = array();
  var $_fields        = array();
  var $_errors        = array();
  var $_is_form_valid = true;

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
        $field_infos['human_name'] ? $field_infos['human_name'] : '',
        $field_infos['type'] ? $field_infos['type'] : 'text'
        );
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
    $msg_string = '';
    foreach ( $this->_errors as $k => $v )
    {
      $msg_string .= str_replace('{error_msg}', $v['message'], $this->error_template);
    }
    echo $msg_string;
  }

  function get_fields()
  {
    $fields = array();
    foreach ( $this->_fields as $k => $v )
    {
      $fields[ $v->name() ] = $v;
    }
    return $fields;
  }

} // Validator


