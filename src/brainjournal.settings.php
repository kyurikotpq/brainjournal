<?php
/*
 * This file contains the code for functionalities related to
 * the plugin's own Settings page. It also sets up the database table
 * {TABLE_PREFIX}brainjournal_settings to store the users' settings
 */

if (!defined('ABSPATH')) exit;

class BrainJournal_Settings
{
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'setup_admin_menu']);
        }
    }


    /**
     * 
     */
    function setup_admin_menu()
    {
        add_menu_page(
            'BrainJournal - Settings',
            'BrainJournal',
            'administrator',
            "brainjournal",
            [$this, "output_settings_page"],
            "dashicons-format-status"
        );
    }

    function output_settings_page()
    {
        $options = [];

        if ($_POST['node_colors']) {
            $this->save_options($_POST["node_colors"]);
?>
            <div class="wrap">
                <div class='updated notice notice-success is-dismissible'>
                    <p>Changes saved.</p>
                </div>
            </div>
        <?php
            $options = $_POST['node_colors'];
        } else
            $options = get_option('brainjournal_options') ? get_option('brainjournal_options') : [];

        // Get all categories (including those not in use/do not have a post attached to them)
        // and populate the Options array if needed
        $categories = get_categories([
            'hide_empty' => false,
        ]);
        foreach ($categories as $category_object) {
            if (!isset($options[$category_object->term_id])) {
                $options[$category_object->term_id] = [
                    "category_name" => $category_object->name,
                    "category_slug" => $category_object->slug,
                    "color" => "#000000"
                ];
            } else {
                $color = $options[$category_object->term_id];
                $options[$category_object->term_id] = [
                    "category_name" => $category_object->name,
                    "category_slug" => $category_object->slug,
                    "color" => $color
                ];
            }
        }
        ?>
        <h1>Graph Colors</h1>
        <p>Set the colors for the different categories' nodes.</p>
        <form action="#" method="post">
            <?php

            foreach ($options as $k => $option) :
                $id = "node_colors_" . $option["category_slug"];
                $name = "node_colors[" . $k . "]";

            ?>
                <div style="display: flex; align-items: center; margin-top: 20px;">
                    <input style="margin-right: 10px;" type="color" name="<?php echo $name ?>" id="<?php echo $id ?>" value="<?php echo $option["color"] ?>" />
                    <label for="<?php echo $id; ?>"><?php echo $option["category_name"]; ?></label>
                </div>
            <?php
            endforeach;
            submit_button("Save");
            ?>
        </form>
        <?php
    }

    function save_options($node_colors)
    {
        // You MUST use the WordPress Options API to save your options
        update_option("brainjournal_options", $node_colors);
    }
}
