<?php

/**
 * private:// stream wrapper class.
 */
class ResourcePrivateStreamWrapper extends ResourcePublicStreamWrapper {
  private $pathKey = 'stream_private_path';
  private $pathDefault = 'sites/default/files-private';

  /**
   * Return the HTML Url of a private file.
   */
  function htmlUrl($url) {
    $basepath = variable_get($this->pathKey, $this->pathDefault);
    return url('system/files/' . parse_url($url, PHP_URL_PATH), array('absolute' => TRUE));
  }
}


