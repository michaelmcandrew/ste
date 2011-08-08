<?php

/**
 * @file
 * Media Theming
 *
 * Theming functions for the Media module.
 */

/**
 * Display the media file browser.
 *
 * @param array $element
 *   The form element.
 * @return string
 */
function theme_media_file_browser($element) {
  // Add the CSS for our display.
  $output = '<div class="media browser">'. $element .'</div>';
  return $output;
}

/**
 * Display a media file list.
 *
 * @param array $element
 *   The form element.
 * @return string
 */
function theme_media_file_list($element) {
  // Add the CSS for our display.
  return '<div class="media-file-list">'. theme('form_element', $element, $element['#children']) .'</div>';
}

/**
 * Display a browser pane.
 *
 * @param array $form
 *   The form element.
 * @return string
 */
function theme_media_browser_pane($form) {
  $output = array();
  
  // render the drawers
  $output[] = '<div'. drupal_attributes($form['#attributes']) .'>';
  
  // render the drawer list
  $output[] = '  <div class="browser drawers">';  
  $output[] = drupal_render_form(null, $form['drawers']);
  $output[] = ' </div>';
  
  // render the drawer displays
  $output[] = drupal_render_form(null, $form);
  $output[] = '</div>';
  
  return implode("\n", $output);
}

