<?php
/**
 * Paynow language file
 */
$_['heading_title']                  = 'Paynow';
$_['text_paynow']                    = '<a target="_BLANK" href="https://www.paynow.co.zw"><img src="' . HTTP_SERVER . 'view/image/payment/paynow.png" alt="Paynow"></a>';

// Text
$_['text_extension']                 = 'Extensions';
$_['text_success']                   = 'Success: Paynow settings successfully saved!';
$_['text_debug']                     = 'Debug';
$_['text_edit']                      = 'Configure Paynow';

// Entry
$_['entry_paynow_integration_id']    = 'Integraion ID';
$_['entry_paynow_integration_key']   = 'Integraion Key';
$_['entry_paynow_total']             = 'Total ';
$_['entry_status']                   = 'Status ';
$_['entry_sort_order']               = 'Sort Order ';
$_['entry_paynow_store_name']        = 'Store name ';
$_['entry_geo_zone']                 = 'Geo Zone ';

// Help
$_['help_paynow_integration_id']     = 'Please enter the integration ID available in your Paynow dashboard';
$_['help_paynow_integration_key']    = 'Please enter the integration Key sent to your email upon request in your Paynow dashboard';
$_['help_paynow_total']              = 'Maximum order value. 0 = unlimited';
$_['help_paynow_store_name']                = 'The name that should appear when the customer makes a payment';

// Error
$_['error_permission']               = 'Warning: You do not have permission to modify the Paynow extension!';
$_['error_paynow_integration_id']    = 'Error an Integration ID is required';
$_['error_paynow_integration_key']   = 'An Integration KEY is required';
$_['error_paynow_store_name']        = 'A Store name is required';
?>