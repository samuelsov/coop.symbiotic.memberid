<?php

class CRM_Memberid_APIWrapper implements API_Wrapper {

  public function fromApiInput($apiRequest) {
    $cfid = get_memberid_customfield_id();
    if (empty($cfid)) return;
    $field = 'custom_' . $cfid;


    // change api call - getquick is deprecated and doesn't work with custom field
    $apiRequest['entity'] = 'Contact';
    $apiRequest['action'] = 'getlist';
    $apiRequest['function'] = 'emulate_civicrm_api3_contact_getList';

    // change name param to field and remove useless params
    $apiRequest['params']['params'][$field] = $apiRequest['params']['name'];
    unset($apiRequest['params']['name']);
    unset($apiRequest['params']['field_name']);
    unset($apiRequest['params']['table_name']);

    // restrict return data (getlist doesn't use return param)
    $apiRequest['params']['extra'] = array('sort_name', $field);

    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    return $result;
  }

}

