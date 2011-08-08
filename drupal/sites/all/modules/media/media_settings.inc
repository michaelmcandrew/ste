<?php


/**
 * @file
 * This file contains the admin functions for the Media module.
 */

/**
 * Provide per content type settings.
 *
 * @param array &form
 *  A form structured array.
 * @param string $type_name
 *  A string representing the content type.
 * @return
 *  Nothing is returned.
 */
function media_settings_content_type(&$form, $type_name = NULL) {
  $form['media'] = array(
    '#type'         => 'fieldset',
    '#title'        => t('Media settings'),
    '#collapsible'  => 'true',
    '#collapsed'    => 'true',
  );
  
  $type = _media_content_field_types($type_name);

  // Master settings override.
  $form['media']['media_'. $type_name .'_override'] = array(
    '#title'          => t($type["name"] .' overrides default values'),
    '#type'           => 'checkbox',
    '#description'    => t('Override the default settings for this content type.  The options below will only be used if this box is checked.'),
    '#default_value'  => variable_get('media_'. $type_name .'_override', NULL),
  );
  $form['media']['media_'. $type_name .'_enabled'] = array(
    '#title'          => t('Enable Media resource browser'),
    '#type'           => 'checkbox',
    '#description'    => t('Enable the Media resource browser for this node type.'),
    '#default_value'  => variable_get('media_'. $type_name .'_enabled', NULL),
  );
  
  // Extract the fields for this node type
  foreach ((array)$type['fields'] as $field_name => $field) {
    // Create the field identifier
    $form['media'][$field['field_name']] = array(
      '#type'         => 'fieldset',
      '#title'        => t('Field name: !name', array('!name' => $field['widget']['label'])),
      '#collapsible'  => 'true',
    );
    
    // Build a form for each type of module that we have
    foreach (media_registration_kinds() as $kind) {
      // Get all the kinds that match this field
      if ($registrations = media_get_fields($field['type'], $kind)) {
        $options = array();
        foreach ($registrations as $id => $registration) {
          $options[$field['field_name'] .'--'. $id] = $registration['name'] .': '. $registration['description'];
        }
        $form['media'][$field['field_name']]['media_'. $type_name .'_'. $field['field_name'] .'_'. $kind] = array(
          '#title'          => t('Enable !kind options for this field', array('!kind' => $kind)),
          '#description'    => t('Choose which !kind options you would like to have enabled on this field', array('!kind' => $kind)),
          '#type'           => 'checkboxes',
          '#options'        => $options,
          '#default_value'  => variable_get('media_'. $type_name .'_'. $field['field_name'] .'_'. $kind, array()),
        );
      }
    }
    
    // if we didn't get any additional data, remove this field form
    // this is ugly but hey, sue me
    if (count($form['media'][$field['field_name']]) == 3) {
      unset($form['media'][$field['field_name']]);
    }
  }
}

/**
 * Provide global settings.
 *
 * @param array $form
 *  A form structure.
 * @param string $type_name
 *  Content type name.
 * @return array
 *  A form structure.
 */
function media_settings_global($form = array(), $type_name = NULL) {
  // Create global options form structure.
  $form = array();
  
  // Global settings are currently just defaults for content types
  if ($type_name == 'global') {
    $form['media_global_enabled'] = array(
      '#title'          => t('Media resource browser'),
      '#type'           => 'checkbox',
      '#description'    => t('Enable or Disable the Media resource browser for all types, unless specifically set for a given type.'),
      '#default_value'  => variable_get('media_global_enabled', 1),
    );
  }

  return system_settings_form($form);
}