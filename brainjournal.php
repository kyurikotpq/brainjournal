<?php

/**
 * Plugin Name: Brain Journal
 * Author: Nanyang Polytechnic
 * Version: 1.0.0
 * Description: Transforms WordPress posts into a Second Brain repository. Provides support for use of Contact Form 7 as a fleeting notes-capturing tool.
 */

if (!defined('ABSPATH')) exit;

include(__DIR__ . "/constants.php");
include(__DIR__ . "/src/brainjournal.links.php");
include(__DIR__ . "/src/brainjournal.restapi.php");
include(__DIR__ . "/src/brainjournal.settings.php");
include(__DIR__ . "/src/brainjournal.shortcode.php");
include(__DIR__ . "/src/brainjournal.cf7.php");

define("BRAINJOURNAL_PLUGIN_URL", WP_PLUGIN_URL . "/brainjournal");

class BrainJournal
{
    private $constants;
    private $links;
    private $restapi;
    private $settings;
    private $shortcode;
    private $cf7;


    public function __construct()
    {
        $this->constants = new BrainJournal_Constants();

        $this->links = new BrainJournal_Links($this->constants);
        $this->restapi = new BrainJournal_RestAPI($this->constants);
        $this->settings = new BrainJournal_Settings($this->constants);
        $this->shortcode = new BrainJournal_Shortcode($this->constants);
        $this->cf7 = new BrainJournal_CF7();

        register_activation_hook(__FILE__, [$this, "activate"]);
        register_uninstall_hook(__FILE__, [$this, "uninstall"]);
    }

    // Codes to perform during activation of the plugin
    function activate()
    {
        $this->links->setup_table();
    }

    // Codes to perform during uninstallation of the plugin
    function uninstall()
    {
        // Delete all database tables
        $this->links->remove_table();
    }
}

$plugin = new BrainJournal();
