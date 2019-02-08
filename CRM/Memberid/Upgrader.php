<?php
use CRM_Memberid_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Memberid_Upgrader extends CRM_Memberid_Upgrader_Base {

  /**
   * Add a setting to save latest Member ID instead of taking max
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1100() {

    $this->ctx->log->info('Applying update 1100');

    $fieldId = get_memberid_customfield_id();
    // nothing to do
    if (empty($fieldId)) {
      return TRUE;
    }

    $fieldName = "custom_" . $fieldId;

    // get latest using old method
    $result = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'return' => $fieldName,
      'options' => array('sort' => "$fieldName desc", 'limit' => 1),
    ));
    $newID = 1;
    if (!empty($result['values'][0][$fieldName])) {
      $newID = ((int) $result['values'][0][$fieldName]) + 1;
    }

    // update setting with latest
    civicrm_api3('Setting', 'create', ['memberid_latest' => $newID]);

    return TRUE;
  }

}
