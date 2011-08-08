<?php

/**
 * @file
 * Media AHAH
 *
 * AHAH functions for the Media module.
 */

/**
 * This function builds the form elements for the file formater based
 * on the node type and field for settings and the file extension to get the
 * formatters.
 *
 * @param string $registration_id
 *   The id of the registration creating this formatter.
 * @param string $node_type
 *   The kind of node.
 * @param string $field
 *   The machine name of the field being operated on.
 * @param string $uri
 *   (optional) The uri to the item, or file://path if it is being uploaded.
 * @param string $file_extension
 *   (optional) The kind of file that will be operated on.
 */
function media_ahah_formatter_load() { //$registration_id, $node_type, $field, $uri = NULL, $file_extension = NULL) {
  // The form is generated in an include file which we need to include manually.
  include_once 'modules/node/node.pages.inc';
  // We're starting in step #3, preparing for #4.
  $form_state = array('storage' => NULL, 'submitted' => FALSE);
  $form_build_id = $_POST['form_build_id'];
  // Step #4.
  $form = form_get_cache($form_build_id, $form_state);

  // Preparing for #5.
  $args = $form['#parameters'];
  $form_id = array_shift($args);
  $form_state['post'] = $form['#post'] = $_POST;
  $form['#programmed'] = $form['#redirect'] = FALSE;

  // Step #5.
  drupal_process_form($form_id, $form, $form_state);
  // Step #6 and #7 and #8.
  $form = drupal_rebuild_form($form_id, $form_state, $args, $form_build_id);

  // Do whatever we need here for Step #9.
  // ...
  drupal_set_message('media_ahah_formatter_load can add anything here (if necessary).');

  // Step #10: Output any drupal messages and the new form.
  $media_tabs = $form[$field]['media']['media_tabs'];
  $output = theme('status_messages') . drupal_render($media_tabs);

  // Ensure any changes to Drupal.settings js are preserved.
  $javascript = drupal_add_js(NULL, NULL, 'header');

  // Final rendering callback.
  drupal_json(array(
    'status' => TRUE,
    'data' => $output,
    'settings' => call_user_func_array('array_merge_recursive', $javascript['setting']),
  ));
}

/**
 * TODO: Document
 */
function media_ahah_metadata_ahah() {
  // The form is generated in an include file which we need to include manually.
  include_once 'modules/node/node.pages.inc';
  // We're starting in step #3, preparing for #4.
  $form_state = array('storage' => NULL, 'submitted' => FALSE);
  $form_build_id = $_POST['form_build_id'];
  // Step #4.
  $form = form_get_cache($form_build_id, $form_state);

  // Preparing for #5.
  $args = $form['#parameters'];
  $form_id = array_shift($args);
  $form_state['post'] = $form['#post'] = $_POST;
  $form['#programmed'] = $form['#redirect'] = FALSE;

  // Step #5.
  drupal_process_form($form_id, $form, $form_state);
  // Step #6 and #7 and #8.
  $form = drupal_rebuild_form($form_id, $form_state, $args, $form_build_id);

  // Do whatever we need here for Step #9.
  // ...
  drupal_set_message('final submission: media_ahah_metadata_ahah can do something here, maybe display a new add file form?');

  // Step #10: Output any drupal messages and the new form.
  $media_tabs = $form[$field]['media']['media_tabs'];
  $output = theme('status_messages') . drupal_render($media_tabs);

  // Ensure any changes to Drupal.settings js are preserved.
  $javascript = drupal_add_js(NULL, NULL, 'header');

  // Final rendering callback.
  drupal_json(array(
    'status' => TRUE,
    'data' => $output,
    'settings' => call_user_func_array('array_merge_recursive', $javascript['setting']),
  ));
}

