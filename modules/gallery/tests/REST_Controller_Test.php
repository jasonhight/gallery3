<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class REST_Controller_Test extends Unit_Test_Case {
  public function setup() {
    $this->_post = $_POST;
    $this->mock_controller = new Mock_RESTful_Controller("mock");
    $this->mock_not_loaded_controller = new Mock_RESTful_Controller("mock_not_loaded");
    $_POST = array();
  }

  public function teardown() {
    $_POST = $this->_post;
  }

  public function dispatch_index_test() {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_POST["_method"] = "";
    $this->mock_controller->__call("index", "");
    $this->assert_equal("index", $this->mock_controller->method_called);
  }

  public function dispatch_show_test() {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_POST["_method"] = "";
    $this->mock_controller->__call("3", "");
    $this->assert_equal("show", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function dispatch_update_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "PUT";
    $_POST["csrf"] = access::csrf_token();
    $this->mock_controller->__call("3", "");
    $this->assert_equal("update", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function dispatch_update_fails_without_csrf_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "PUT";
    try {
      $this->mock_controller->__call("3", "");
      $this->assert_false(true, "this should fail with a forbidden exception");
    } catch (Exception $e) {
      // pass
    }
  }

  public function dispatch_delete_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "DELETE";
    $_POST["csrf"] = access::csrf_token();
    $this->mock_controller->__call("3", "");
    $this->assert_equal("delete", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function dispatch_delete_fails_without_csrf_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "DELETE";
    try {
      $this->mock_controller->__call("3", "");
      $this->assert_false(true, "this should fail with a forbidden exception");
    } catch (Exception $e) {
      // pass
    }
  }

  public function dispatch_404_test() {
    /* The dispatcher should throw a 404 if the resource isn't loaded and the method isn't POST. */
    $methods = array(
      array("GET", ""),
      array("POST", "PUT"),
      array("POST", "DELETE"));

    foreach ($methods as $method) {
      $_SERVER["REQUEST_METHOD"] = $method[0];
      $_POST["_method"] = $method[1];
      $exception_caught = false;
      try {
        $this->mock_not_loaded_controller->__call(rand(), "");
      } catch (Kohana_404_Exception $e) {
        $exception_caught = true;
      }
      $this->assert_true($exception_caught, "$method[0], $method[1]");
    }
  }

  public function dispatch_create_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "";
    $_POST["csrf"] = access::csrf_token();
    $this->mock_not_loaded_controller->__call("", "");
    $this->assert_equal("create", $this->mock_not_loaded_controller->method_called);
    $this->assert_equal(
      "Mock_Not_Loaded_Model", get_class($this->mock_not_loaded_controller->resource));
  }

  public function dispatch_create_fails_without_csrf_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "";
    try {
      $this->mock_not_loaded_controller->__call("", "");
      $this->assert_false(true, "this should fail with a forbidden exception");
    } catch (Exception $e) {
      // pass
    }
  }

  public function dispatch_form_test_add() {
    $this->mock_controller->form_add("args");
    $this->assert_equal("form_add", $this->mock_controller->method_called);
    $this->assert_equal("args", $this->mock_controller->resource);
  }

  public function dispatch_form_test_edit() {
    $this->mock_controller->form_edit("1");
    $this->assert_equal("form_edit", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function routes_test() {
    $this->assert_equal("mock/form_add/args", router::routed_uri("form/add/mock/args"));
    $this->assert_equal("mock/form_edit/args", router::routed_uri("form/edit/mock/args"));
    $this->assert_equal(null, router::routed_uri("rest/args"));
  }
}

class Mock_RESTful_Controller extends REST_Controller {
  public $method_called;
  public $resource;

  public function __construct($type) {
    $this->resource_type = $type;
    parent::__construct();
  }

  public function _index() {
    $this->method_called = "index";
  }

  public function _create($resource) {
    $this->method_called = "create";
    $this->resource = $resource;
  }

  public function _show($resource) {
    $this->method_called = "show";
    $this->resource = $resource;
  }

  public function _update($resource) {
    $this->method_called = "update";
    $this->resource = $resource;
  }

  public function _delete($resource) {
    $this->method_called = "delete";
    $this->resource = $resource;
  }

  public function _form_add($args) {
    $this->method_called = "form_add";
    $this->resource = $args;
  }

  public function _form_edit($resource) {
    $this->method_called = "form_edit";
    $this->resource = $resource;
  }
}

class Mock_Model {
  public $loaded = true;
}

class Mock_Not_Loaded_Model {
  public $loaded = false;
}
