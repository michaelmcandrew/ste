<?php

/**
 * Generic PHP stream wrapper interface.
 * @see: http://php.net/manual/en/function.stream-wrapper-register.php
 */
interface StreamWrapperInterface {
  public function stream_open($url, $mode, $options, &$opened_url);
  public function stream_close();
  // Undocumented @see: http://us.php.net/flock
  public function stream_lock($operation);
  public function stream_read($count);
  public function stream_write($data);
  public function stream_eof();
  public function stream_seek($offset, $whence);
  public function stream_flush();
  public function stream_tell();
  public function stream_stat();
  public function unlink($url) ;
  public function rename($fromUrl, $toUrl);
  public function mkdir($url, $mode, $options);
  public function rmdir($url, $options);
  public function url_stat($url, $flags);
  public function dir_opendir($url, $options);
  public function dir_readdir()  ;
  public function dir_rewinddir();
  public function dir_closedir() ;
}


/**
 * Definition for a Resource stream wrapper in Drupal.
 * Extends the StreamWrapperInterface with methods expected by the
 * Drupal Resource Classes.
 */
interface ResourceStreamWrapperInterface extends StreamWrapperInterface {

  /**
   * Return the html accessible URL for a resource.
   * @param $url
   *   Stream wrapper resource url.
   * @return string
   */
  function htmlUrl($url);

  /**
   * Return the mime type of a resource.
   * @param $url
   *   Stream wrapper resource url.
   * @return string
   */
  function mime($url);

  /**
   * Return an absolute stream resource URL.
   * @param $url
   *   Stream wrapper resource url.
   * @return string
   */
  function interpolateUrl($url);
}


/**
 * A base class for Resource Stream Wrappers.
 *
 * This class provides a complete stream wrapper implementation. It passes
 * incoming  URL's through an interpolation method then recursively calls
 * the invoking PHP filesystem function.
 *
 * DrupalStreamWrapper implementations need to override at least the interpolateUrl
 * method to rewrite the URL before is it passed back into the calling function.
 *
 */
abstract class ResourceStreamWrapper implements ResourceStreamWrapperInterface {
  private $handle = NULL;

  function interpolateUrl($url) {
    return $url;
  }

  function htmlUrl($url) {
    return $url;
  }

  function mime($url) {
    return 'application/octet-stream';
  }

  /**
   * Support for fopen(), file_get_contents(), file_put_contents() etc.
   *
   * @param $path
   *   A string containing the path to the file to open.
   * @param $mode
   *   The file mode ("r", "wb" etc.).
   * @param $options
   *   A bit mask of STREAM_USE_PATH and STREAM_REPORT_ERRORS.
   * @param &$opened_path
   *   A string containing the path actually opened.
   * @return
   *  TRUE if file was opened successfully.
   */
  public function stream_open($url, $mode, $options, &$opened_url) {
    resource_debug("stream open: $url");
    $url = $this->interpolateUrl($url);
    $this->handle = ($options & STREAM_REPORT_ERRORS) ? fopen($url, $mode) : @fopen($url, $mode);
    if  ((bool)$this->handle && $options & STREAM_USE_PATH)  $opened_url = $url;
    resource_debug("stream opened: $this->handle");
    return (bool)$this->handle;
  }

  // Undocumented PHP stream wrapper method.
  function stream_lock($operation) {
    if (in_array($operation, array(LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB))) {
      return flock($this->handle, $operation);
    }
    return TRUE;
  }

  /**
   * Support for fread(), file_get_contents() etc.
   *
   * @param $count
   *    Maximum number of bytes to be read.
   * @return
   *  The string that was read, or FALSE in case of an error.
   */
  public function stream_read($count) {
    resource_debug("stream_read: $this->handle");
    return fread($this->handle, $count);
  }

  /**
   * Support for fwrite(), file_put_contents() etc.
   *
   * @param $data
   *   The string to be written.
   * @return
   *   The number of bytes written.
   */
  public function stream_write($data) {
    resource_debug("stream_write: $this->handle");
    return fwrite($this->handle, $data);
  }

  /**
   * Support for feof().
   *
   * @return
   *   TRUE if end-of-file has been reached.
   */
  public function stream_eof() {
    return feof($this->handle);
  }

  /**
   * Support for fseek().
   *
   * @param $offset
   *   The byte offset to got to.
   * @param $whence
   *   SEEK_SET, SEEK_CUR, or SEEK_END.
   * @return
   *   TRUE on success
   */
  public function stream_seek($offset, $whence) {
    return fseek($this->handle, $offset, $whence);
  }

  /**
   * Support for fflush().
   *
   * @return
   *   TRUE if data was successfully stored (or there was no data to store).
   */
  public function stream_flush() {
    return fflush($this->handle);
  }

  /**
   * Support for ftell().
   *
   * @return
   *   The current offset in bytes from the beginning of file.
   */
  public function stream_tell() {
    return ftell($this->handle);
  }

  /**
   * Support for fstat().
   *
   * @return
   *   An array with file status, or FALSE in case of an error - see fstat()
   *   for a description of this array.
   */
  public function stream_stat() {
    return fstat($this->handle);
  }

  /**
   * Support for fclose().
   *
   * @return
   *   TRUE if stream was successfully closed.
   */
  public function stream_close() {
    resource_debug("streamclose: $this->handle");
    return fclose($this->handle);
  }

  /**
   * Support for unlink().
   *
   * @param $url
   *   A string containing the url to the resource to delete.
   * @return
   *   TRUE if resource was successfully deleted.
   */
  public function unlink($url) {
    return unlink($this->interpolateUrl($url));
  }

  /**
   * Support for rename().
   *
   * @param $fromUrl,
   *   The url to the file to rename.
   * @param $toUrl
   *   The new url for file.
   *
   * @return
   *   TRUE if file was successfully renamed.
   */
  public function rename($fromUrl, $toUrl) {
    return rename($this->interpolateUrl($fromUrl), $this->interpolateUrl($toUrl));
  }

  /**
   * Support for mkdir().
   *
   * @param $url
   *   A string containing the url to the directory to create.
   * @param $mode
   *   Permission flags - see mkdir().
   * @param $options
   *   A bit mask of STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE.
   * @return
   *   TRUE if directory was successfully created.
   */
  public function mkdir($url, $mode, $options) {
    $recursive = (bool)($options & STREAM_MKDIR_RECURSIVE);
    if ($options & STREAM_REPORT_ERRORS) {
      return mkdir($this->interpolateUrl($url), $mode, $recursive);
    }
    else {
      return @mkdir($this->interpolateUrl($url), $mode, $recursive);
    }
  }

  /**
   * Support for rmdir().
   *
   * @param $url
   *   A string containing the url to the directory to delete.
   * @param $options
   *   A bit mask of STREAM_REPORT_ERRORS.
   * @return
   *   TRUE if directory was successfully removed.
   */
  public function rmdir($url, $options) {
    if ($options & STREAM_REPORT_ERRORS) {
      return rmdir($this->interpolateUrl($url));
    }
    else {
      return @rmdir($this->interpolateUrl($url));
    }
  }

  /**
   * Support for stat().
   *
   * @param $url
   *   A string containing the url to get information about.
   * @param $flags
   *   A bit mask of STREAM_URL_STAT_LINK and STREAM_URL_STAT_QUIET.
   * @return
   *   An array with file status, or FALSE in case of an error - see fstat()
   *   for a description of this array.
   */
  public function url_stat($url, $flags) {
    return ($flags & STREAM_URL_STAT_QUIET) ? @stat($this->interpolateUrl($url)) : stat($this->interpolateUrl($url));
  }

  /**
   * Support for opendir().
   *
   * @param $url
   *   A string containing the url to the directory to open.
   * @param $options
   *   Unknown (parameter is not documented in PHP Manual).
   * @return
   *   TRUE on success.
   */
  public function dir_opendir($url, $options) {
    $this->handle = opendir($this->interpolateUrl($url));
    return (bool)$this->handle;
  }

  /**
   * Support for readdir().
   *
   * @return
   *   The next filename, or FALSE if there are no more files in the directory.
   */
  public function dir_readdir() {
    return readdir($this->handle);
  }

  /**
   * Support for rewinddir().
   *
   * @return
   *   TRUE on success.
   */
  public function dir_rewinddir() {
    return rewinddir($this->handle);
  }

  /**
   * Support for closedir().
   *
   * @return
   *   TRUE on success.
   */
  public function dir_closedir() {
    return closedir($this->handle);
  }
}

