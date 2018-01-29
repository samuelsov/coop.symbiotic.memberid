# coop.symbiotic.memberid

Simple extension to have a sequential Member ID in any contact that has a membership.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl coop.symbiotic.memberid@https://github.com/FIXME/coop.symbiotic.memberid/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/coop.symbiotic.memberid.git
cv en memberid
```

## Usage

* create a new custom field in the custom group you want. The custom field should be numeric and read only
* get the id of this field and init the configuration using the api :

```php
$result = civicrm_api3('Setting', 'create', array(
  'sequential' => 1,
  'memberid_custom_field_id' => 26,
));
```

## Known Issues

* there is no verification of the custom field type
* there is no verification yet of unicity of the member ID (if the field is not read only, nothing prevent from having several members with the same ID)
* there is no admininistration UI to define which custom field the extension must use (the api is the way to go)

