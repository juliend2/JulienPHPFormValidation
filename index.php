<?php

include_once('validator.php');

$validator = new Validator(array(
  'first_name'=> array(
    'human_name'=>'First Name',
    'rules'=>array('not_empty')
  ),
  'last_name'=> array(
    'human_name'=>'Last Name',
    'rules'=>array('not_empty')
  ),
  'email'=> array(
    'human_name'=>'Email',
    'rules'=>array('not_empty', array('format'=>'email'))
  ),
  'username'=> array(
    'human_name'=>'User name',
    'rules'=>array('not_empty')
  ),
  'password'=> array(
    'human_name'=>'Password',
    'rules'=>array('not_empty')
  ),
  'confirm_password'=> array(
    'human_name'=>'Password Confirmation',
    'rules'=>array('not_empty', array('same_as'=>'password'))
  ),
  'accept_terms'=> array(
    'human_name'=>'Accept Terms',
    'rules'=>array('checked'=>true)
  ),
));

if ( $_POST )
{
  echo 'form sent!';
}

?>

<h1>Hello</h1>

<form method="post" action="index.php">

  <ul>
    <?php $validator->display_errors(); ?>
  </ul>

  <p><label>First Name</label>
  <input type="text" name="first_name" /></p>

  <p><label>Last Name</label>
  <input type="text" name="last_name" /></p>

  <p><label>Email</label>
  <input type="text" name="email" /></p>

  <p><label>Username</label>
  <input type="text" name="username" /></p>

  <p><label>Password</label>
  <input type="password" name="password" /></p>

  <p><label>Confirm Password</label>
  <input type="password" name="confirm_password" /></p>

  <p><label>I Accept the Terms and Conditions</label>
  <input type="checkbox" name="accept_terms" /></p>

  <p>
  <input type="submit" name="send" value="Submit" />
  </p>

</form>

