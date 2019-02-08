<?php

require_once 'memberid.civix.php';
use CRM_Memberid_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function memberid_civicrm_config(&$config) {
  _memberid_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function memberid_civicrm_xmlMenu(&$files) {
  _memberid_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function memberid_civicrm_install() {
  _memberid_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function memberid_civicrm_postInstall() {
  _memberid_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function memberid_civicrm_uninstall() {
  _memberid_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function memberid_civicrm_enable() {
  _memberid_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function memberid_civicrm_disable() {
  _memberid_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function memberid_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _memberid_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function memberid_civicrm_managed(&$entities) {
  _memberid_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function memberid_civicrm_caseTypes(&$caseTypes) {
  _memberid_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function memberid_civicrm_angularModules(&$angularModules) {
  _memberid_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function memberid_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _memberid_civix_civicrm_alterSettingsFolders($metaDataFolders);
}


/**
 * Implements hook_civicrm_post().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 *
 */
function memberid_civicrm_post($op, $objectName, $objectId, &$objectRef) {

  if (($objectName == 'Membership') && ($op == 'create' || $op == 'edit')) {

    // automatically add a sequential nember id if none is defined
    _update_member_id($objectId, $objectRef->contact_id);

  }

}



/**
 * Implements hook_civicrm_apiWrappers().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 *
 */
function memberid_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Contact' && $apiRequest['action'] == 'getquick' && empty($apiRequest['params']['field_name'])) {
    if (is_numeric($apiRequest['params']['name'])) {
      $wrappers[] = new CRM_Memberid_APIWrapper();
    }
  }
}






/** HELPER **/

function get_memberid_customfield_id() {
  // get custom field id
  $result = civicrm_api3('Setting', 'get', array(
    'sequential' => 1,
    'return' => array("memberid_custom_field_id"),
  ));

  if (!empty($result['values'][0]['memberid_custom_field_id']) &&
    ctype_digit($result['values'][0]['memberid_custom_field_id'])) {
    return (int)$result['values'][0]['memberid_custom_field_id'];
  }
}


/**
 * Add a sequential nember id on the contact if none is defined
 * Will use a custom field to store the value (defined using civicrm api 'Setting')
 */
function _update_member_id($membershipID, $contactID) {

  $fieldId = get_memberid_customfield_id();
  if (empty($fieldId)) {
    return;
  }
  $fieldName = "custom_" . $fieldId;

  $results = civicrm_api3('Contact', 'get', array('sequential' => 1, 'return' => $fieldName, 'contact_id' => $contactID));

  // value may not be created or be empty. i.e. delete the value in the interface to reset the field.
  if (empty($results['values'][0][$fieldName])) {

    // get latest member id using a setting (to avoid problem with deleted contact)
    $result = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => ["memberid_latest"],
    ]);

    $newID = 1;
    if (!empty($result['values'][0]['memberid_latest'])) {
      $newID = ((int) $result['values'][0]['memberid_latest']) + 1;
    }

    // update latest (before updating contact -> better have hole in numerotation than duplicate)
    civicrm_api3('Setting', 'create', ['memberid_latest' => $newID]);

    // set the ID
    civicrm_api3("Contact", "create", array('id' => $contactID, $fieldName => $newID));

  }

}

// For quicksearch, to reformat the output
function emulate_civicrm_api3_contact_getList($params) {

  // not loaded by default
  include_once "api/v3/Generic/Getlist.php";
  include_once "api/v3/utils.php";

  $apiRequest = array(
    'entity' => 'Contact',
    'action' => 'getlist',
    'params' => $params,
  );
  $res = civicrm_api3_generic_getList($apiRequest);

  $cfid = get_memberid_customfield_id();
  if (empty($cfid)) return;
  $field = 'custom_' . $cfid;

  // reformat the output to look like getquick
  foreach ($res['values'] as $idx => $value) {
    $res['values'][$idx]['data'] = $value['extra']['sort_name'] . " ({$value['extra'][$field]})";
  }

  return $res;
}
