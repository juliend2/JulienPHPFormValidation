<?php

/**
 * Field
 * @author Julien Desrosiers
 */
class Field {

  var $messages    = array(
    'not_empty' => "{{attribute}} must not be empty.",
    'checked' => "{{attribute}} must be checked.",
    'format' => "{{attribute}} must have a valid format.",
    'min_length' => "{{attribute}} must be at least {{min_length}} characters.",
    'max_length' => "{{attribute}} must be less than {{max_length}} characters.",
    'same_as' => "{{attribute}} must be the same as {{same_as}}."
  );
  var $_formats     = array(
    'url' => '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@',
    'email' => '/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/'
  );
  var $_posted_data = array();
  var $_error       = array(); // keys: message, type
  var $_rules       = array();
  var $_is_valid    = true;
  var $_type        = 'text';
  var $_human_name  = '';
  var $_name        = '';
  var $_form_instance;

  function Field($form, $name, $rules, $human_name, $type)
  {
    $this->_form_instance = $form;
    $this->_name = $name;
    $this->_rules = $rules;
    $this->_human_name = $human_name;
    $this->_type = $type;
  }

  /**
   * Getters and Setters
   */

  // @return String
  function name()
  {
    return $this->_name;
  }

  // @return String
  function human()
  {
    return $this->_human_name;
  }

  // @return Boolean
  function is_valid( $posted_data )
  {
    $this->_posted_data = $posted_data;
    $this->_dispatch_validation();
    return $this->_is_valid;
  }

  // @return String
  function error()
  {
    return $this->_error;
  }

  // @return String
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
        echo " value='on'";
      }
      else
      {
        echo " value='{$default_value}'";
      }
    }
  }

  function _dispatch_validation()
  {
    foreach ( $this->_rules as $rule )
    {
      $message = is_array($rule) && $rule['message'] ? $rule['message'] : '' ;

      if (!$this->_validate_not_empty( $rule, $message ))  break;
      if (!$this->_validate_format( $rule, $message ))     break;
      if (!$this->_validate_checked( $rule, $message ))    break;
      if (!$this->_validate_min_length( $rule, $message )) break;
      if (!$this->_validate_max_length( $rule, $message )) break;
      if (!$this->_validate_same_as( $rule, $message ))    break;
    }
  }

  /**
   * Validation rules
   */

  function _validate_not_empty($rule, $message)
  {
    if ( $rule === 'not_empty' && empty($this->_posted_data[ $this->_name ]) )
    {
      $this->_error = array(
        'type'=>'not_empty', 
        'message'=>$this->_format_message($message, $this->messages['not_empty']) );
      return $this->_is_valid = false;
    }
    else return true;
  }

  function _validate_format($rule, $message)
  {
    if ( is_array($rule) && array_key_exists('format', $rule) && 
         !preg_match($this->_formats[ $rule['format'] ], $this->_posted_data[ $this->_name ]) )
    {
      $this->_error = array(
        'type'=>'format',
        'message'=>$this->_format_message($message, $this->messages['format']));
      return $this->_is_valid = false;
    } 
    else return true;
  }

  function _validate_min_length($rule, $message)
  {
    if ( is_array($rule) && array_key_exists('min_length', $rule) && 
         strlen(trim($this->_posted_data[ $this->_name ])) < $rule['min_length'] )
    {
      $this->_error = array(
        'type'=>'format',
        'message'=>$this->_format_message($message, str_replace("{{min_length}}", $rule['min_length'], $this->messages['min_length'])));
      return $this->_is_valid = false;
    } 
    else return true;
  }

  function _validate_max_length($rule, $message)
  {
    if ( is_array($rule) && array_key_exists('max_length', $rule) && 
         strlen(trim($this->_posted_data[ $this->_name ])) > $rule['max_length'] )
    {
      $this->_error = array(
        'type'=>'format',
        'message'=>$this->_format_message($message, str_replace("{{max_length}}", $rule['max_length'], $this->messages['max_length'])));
      return $this->_is_valid = false;
    } 
    else return true;
  }

  function _validate_same_as($rule, $message)
  {
    if ( is_array($rule) && array_key_exists('same_as', $rule) && 
         $this->_form_instance->get_field_by_name($rule['same_as']) !== $this->_posted_data[ $this->_name ] )
    {
      $error_msg = str_replace("{{same_as}}", $this->_form_instance->get_field_by_name($rule['same_as'])->human(), $this->messages['same_as']);
      $this->_error = array(
        'type'=>'format',
        'message'=>$this->_format_message($message, $error_msg));
      return $this->_is_valid = false;
    } 
    else return true;
  }

  function _validate_checked($rule, $message)
  {
    if ( $rule === 'checked' && empty($this->_posted_data[ $this->_name ]) )
    {
      $this->_error = array(
        'type'=>'checked',
        'message'=>$this->_format_message($message, $this->messages['checked']));
      return $this->_is_valid = false;
    } 
    else return true;
  }

  /**
   * Useful methods
   */
  
  // format the error message's string
  function _format_message($message, $default_message)
  {
    return str_replace("{{attribute}}", 
      ($this->_human_name !== '' ? $this->_human_name : $this->_name), 
      ($message !== '' ? $message : $default_message) );
  }

  // get the POSTed values
  function _posted_value()
  {
    return ($this->_posted_data[$this->_name] ? $this->_posted_data[$this->_name] : '');
  }

  // is the form POSTed? 
  // @return true if so. false otherwise.
  function _is_form_posted()
  {
    return count($this->_posted_data) > 0;
  }

} // Field


/**
 * Validator
 * @author Julien Desrosiers
 */
class Validator {

  var $error_template = '<li class="error">{{error_msg}}</li>';
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
      $this->_fields[] = new Field(
        $this, 
        $field_name, 
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

  function is_valid()
  {
    return $this->_is_form_valid;
  }

  function display_errors()
  {
    $msg_string = '';
    foreach ( $this->_errors as $k => $v )
    {
      $msg_string .= str_replace('{{error_msg}}', $v['message'], $this->error_template);
    }
    echo $msg_string;
  }

  // @return Array of Field objects
  function get_fields()
  {
    $fields = array();
    foreach ( $this->_fields as $k => $v )
    {
      $fields[ $v->name() ] = $v;
    }
    return $fields;
  }

  // @return Field object
  function get_field_by_name( $name )
  {
    $field = $this->get_fields();
    return $field[ $name ];
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

} // Validator


