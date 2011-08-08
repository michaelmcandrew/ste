<?php

/**
 * The StreamWrapperManager provides a class for managing and querying
 * user defined stream wrappers in PHP. PHP's internal stream_get_wrappers
 * doesn't return the class registered to handle a stream. We need to be
 * able to find the handler class for instantiation.
 */

class ResourceStreamWrapperManager {
  // stream wrapper registry
  private $wrappers = array();

  // private constructor to enforce singleton.
  private function __construct() { }

  /**
   * Load the singleton instance of the StreamWrapperManager.
   * @return object:StreamWrapperManager
   */
  public static function singleton() {
    static $instance = NULL;
    if (is_null($instance)) {
      $instance = new ResourceStreamWrapperManager();
    }
    return $instance;
  }

  /**
   * Register a class to handle a scheme.
   * @param string $scheme URI scheme.
   * @param string $class  classname for the stream wrapper.
   * @return bool result of stream_wrapper_register
   * @see: http://us3.php.net/manual/en/function.stream-wrapper-register.php
   */
  function register($scheme, $classname) {
    $this->wrappers[$scheme] = $classname;
    return stream_wrapper_register($scheme, $classname);
  }

  function unregister($scheme) {
    if (stream_wrapper_unregister($scheme)) {
      unset(self::$this->wrappers[$scheme]);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Return the streamwrapper classname for a given scheme.
   * @param string $scheme stream scheme.
   * @return mixed string is a scheme has a registered handler or FALSE.
   */
  function classname($scheme) {
    if (empty(self::$this->wrappers[$scheme])) {
      return FALSE;
    }
    return self::$this->wrappers[$scheme];
  }

  /**
   * Return the stream class name for a given scheme.
   * @param string $scheme stream scheme.
   * @return mixed string is a scheme has a registered handler or FALSE.
   */
  function scheme($class) {
    return array_search(self::$this->wrappers, $class);
  }

  /**
   * Return the DrupalStreamWrapperManager's wrapper registry.
   */
  function wrappers() {
    return self::$this->wrappers;
  }
}
