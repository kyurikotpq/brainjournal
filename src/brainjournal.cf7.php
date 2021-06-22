<?php

if (!defined('ABSPATH')) exit;

class BrainJournal_CF7
{
    public function __construct()
    {
        add_filter('wpcf7_skip_mail', function ($skip_mail, $contact_form) {
            return true;
        }, 10, 3);

        add_action('wpcf7_before_send_mail', [$this, 'save_data']);
    }

    function save_data($form)
    {
        wp_insert_post([
            "post_title" => $_POST["post_title"],
            "post_content" => $_POST["post_content"],
            "post_status" => "publish",
            "post_type" => "post"
        ]);
    }
}
