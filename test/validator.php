<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/web_tester.php');
require_once('simpletest/reporter.php');
require_once('../validator.php');

class ValidatorTest extends WebTestCase {

  function setUp()
  {
    $this->get('http://localhost/julienPHPFormValidation/index.php');
    $this->assertText('Hello');
  }

  function testSubmit() 
  {
    $this->click('Submit');
    $this->assertText('form sent!');
  }

  function testPasswordNotTheSame()
  {
    $this->setField('first_name', 'Julien');
    $this->setField('last_name', 'Desrosiers');
    $this->setField('username', 'jdesrosiers');
    $this->setField('password', '23r29fh2f2');
    $this->setField('password', 'not the same!');
    $this->click('Submit');
    $this->assertPattern('/Password Confirmation is not the same as Password/');
    $this->assertNoPattern('/First Name must not be empty/');
  }

  function testNotEmptyError()
  {
    $this->setField('first_name', '');
    $this->click('Submit');
    $this->assertPattern('/First Name must not be empty/');
  }

}


$test = new ValidatorTest();
$test->run(new HtmlReporter());
