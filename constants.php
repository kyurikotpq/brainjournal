<?php

if (!defined('ABSPATH')) exit;

class BrainJournal_Constants {
    public $table_prefix;
    public $charset_collate;
    
    public const REST_NAMESPACE = 'brainjournal/v1';
    
    public function __construct() {
        global $wpdb;

        // Obtain the table prefix and charset collate for use in other parts of the plugin
        $this->table_prefix = $wpdb->prefix;
        $this->charset_collate = $wpdb->get_charset_collate();
    }
}
