<?php

/**
 *  @file
 *  Administrative pages for the Resource module.
 */

/**
 * Menu callback for admin/settings/resource;
 * Displays the module's settings page.
 */
function resource_settings_overview() {
  // Check database setup if necessary.
  if (function_exists('db_check_setup') && empty($_POST)) {
    db_check_setup();
  }

  $item = menu_get_item();
  if ($content = system_admin_menu_block($item)) {
    $output = theme('admin_block_content', $content);
  }
  else {
    $output = t('You do not have any administrative items.');
  }
  return $output;

//   $item = menu_get_item('admin/settings/resource');
//   $content = system_admin_menu_block($item);
//
//   $output = theme('admin_block_content', $content);
//
//   return $output;
}

function resource_settings_public_form() {
  $form = array();
  $form['stream_public_path'] = array(
    '#type' => 'textfield',
    '#title' => t('public:// file path'),
    '#default_value' => variable_get('stream_public_path', 'sites/default/files'),
    '#maxlength' => 255,
    '#description' => t('The path where public:// files will be stored. This directory must exist and be writable by Drupal. If the download method is set to public, this directory must be relative to the Drupal installation directory and be accessible over the web. If the download method is set to private, this directory should not be accessible over the web. Changing this location will modify all download paths and may cause unexpected problems on an existing site.'),
    '#after_build' => array('system_check_directory')
  );
  return system_settings_form($form);
}

function resource_settings_private_form() {
  $form = array();
  $form['stream_private_path'] = array(
    '#type' => 'textfield',
    '#title' => t('private:// file path'),
    '#default_value' => variable_get('stream_private_path', 'sites/default/files-private'),
    '#maxlength' => 255,
    '#description' => t('The path where public:// files will be stored. This directory must exist and be writable by Drupal. If the download method is set to public, this directory must be relative to the Drupal installation directory and be accessible over the web. If the download method is set to private, this directory should not be accessible over the web. Changing this location will modify all download paths and may cause unexpected problems on an existing site.'),
    '#after_build' => array('system_check_directory')
  );

  return system_settings_form($form);
}
