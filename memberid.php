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

    // automatically set the member number to the entity id (imported member will not have the same entity id and member id)
    _update_member_id($objectId, $objectRef->contact_id);

  }

}

function _update_member_id($membershipID, $contactID) {
  // get custom field id
  $result = civicrm_api3('Setting', 'get', array(
    'sequential' => 1,
    'return' => array("memberid_custom_field_id"),
  ));

  if (empty($result['values'][0]['memberid_custom_field_id'])
    || !ctype_digit($result['values'][0]['memberid_custom_field_id'])) {
    return;
  }
  $fieldName = "custom_" . (int)$result['values'][0]['memberid_custom_field_id'];

  $results = civicrm_api3('Contact', 'get', array('sequential' => 1, 'return' => $fieldName, 'contact_id' => $contactID));

  // value may not be created or be empty. i.e. delete the value in the interface to reset the field.
  if (empty($results['values'][0][$fieldName])) {


    // get latest member id (or could be saved in setting ?)
    $result = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'return' => $fieldName,
      'options' => array('sort' => "$fieldName desc", 'limit' => 1),
    ));

    $newID = 1;
    if (!empty($result['values'][0][$fieldName])) {
      $newID = ((int) $result['values'][0][$fieldName]) + 1;
    }

    // set the ID
    civicrm_api3("Contact", "create", array('id' => $contactID, $fieldName => $newID));

  }

}

