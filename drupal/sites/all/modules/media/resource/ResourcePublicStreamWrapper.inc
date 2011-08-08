<?php

/**
 * public:// stream wrapper class.
 *
 * This class provides support for storing publicly
 * accessible files with the Drupal resource api.
 */
class ResourcePublicStreamWrapper extends ResourceStreamWrapper {

  // A handle to the file opened by stream_open().
  private $pathKey = 'stream_public_path';
  private $pathDefault = 'sites/default/files';

  /**
   * Return the HTML Url of a public file.
   */
  function htmlUrl($url) {
    $basepath = variable_get($this->pathKey, $this->pathDefault);
    $path = parse_url($url, PHP_URL_PATH);
    return $GLOBALS['base_url'] . '/' . $basepath . '/' . str_replace('\\', '/', $path);
  }

  /**
   * Interpolate the url path, adding the public files path.
   */
  function interpolateUrl($url) {
    $basepath = variable_get($this->pathKey, $this->pathDefault);

    // just in case stream_public_path is s3://, ftp://, etc. Don't call PHP's
    // realpath().
    if (parse_url($basepath, PHP_URL_SCHEME)) {
      $path =  $basepath . parse_url($url, PHP_URL_PATH);;
    }
    else {
      // interpolate relative paths for basepath, and strip relative paths from
      // url path.
      $path = realpath($basepath) . str_replace('/..','', parse_url($url, PHP_URL_PATH));
    }
    return $path;
  }

  /**
   * Return the mime type of a file.
   */
  function mime($url) {
    return file_get_mimetype(basename($url));
  }
}


