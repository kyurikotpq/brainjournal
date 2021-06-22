<?php
/*
 * This file contains the code for functionalities related to
 * the parsing of posts for the population of the
 * {TABLE_PREFIX}brainjournal_links table.
 */

if (!defined('ABSPATH')) exit;

class BrainJournal_Links
{
    private $table_name;
    private $create_links_table_query;
    private $drop_links_table_query;

    private $select_posts_with_links_query;
    private $mass_insert_query_start;
    private $mass_delete_query_for_post;
    private $parse_link_regex = '/<a href=.*? data-type="post" data-id="([0-9]+?)">/m';

    public function __construct(BrainJournal_Constants $constants)
    {
        // Initialize the queries
        $this->table_name = $constants->links_table_name;
        $this->create_links_table_query = "
            CREATE TABLE IF NOT EXISTS {$this->table_name} (
                source INT(11) NOT NULL,
                target INT(11) NOT NULL,
                PRIMARY KEY (source, target)
            ) {$constants->charset_collate};
        ";

        $this->drop_links_table_query = "DROP TABLE IF EXISTS {$this->table_name};";
        $this->select_posts_with_links_query = "
            SELECT ID, post_content FROM {$constants->table_prefix}posts 
            WHERE post_type = 'post' AND 
            post_content REGEXP '<a href=.*? data-type=\"post\" data-id=\"[0-9]+?\">'
        ";
        $this->mass_insert_query_start = "INSERT IGNORE INTO {$this->table_name}(source, target) VALUES ";
        $this->mass_delete_query_for_post = "DELETE FROM {$this->table_name} WHERE source = %d OR target = %d";

        // Call the update_links function whenever the post is saved
        add_action('save_post', [$this, "update_links"], 10, 2);
    }

    // Call this whenever the plugin is activated
    function create_links()
    {
        // Get the charset collate and table prefix
        global $wpdb;

        // Run select_posts_with_links_query to get the results in an associative array format
        $posts = $wpdb->get_results(
            $this->select_posts_with_links_query,
            ARRAY_A
        );

        // Loop through the posts and, for each post,
        // parse the post_content globally & multiline
        // and add the IDs into the links table
        $values = [];

        foreach ($posts as $p) {
            $source = $p["ID"];
            $values = array_merge($values, $this->prepare_insert_values($source, $p["post_content"]));
        }

        $final_query = $this->mass_insert_query_start . implode(",", $values);
        $wpdb->query($final_query);
    }

    function prepare_insert_values($source, $post_content)
    {
        global $wpdb;

        $targets = [];
        $values = [];

        preg_match_all($this->parse_link_regex, $post_content, $targets);

        foreach (array_slice($targets, 1, count($targets) - 1) as $target_arr) {
            foreach ($target_arr as $target) {
                if (trim($target) != "") {
                    $values[] = $wpdb->prepare("(%d, %d)", $source, $target);
                }
            }
        }

        return $values;
    }

    // call this whenever a post is saved
    function update_links($post_ID, $post)
    {
        global $wpdb;

        // Note: If you are updating a post, the post_type would be "revision"
        // and that has its own ID! But we only want to get the original post's ID
        // Hence, account for this difference in ID.
        $original_post_ID = ($post->post_type == "revision") ? $post->post_parent : $post_ID;

        $values = $this->prepare_insert_values($original_post_ID, $post->post_content);
        
        if (count($values) > 0) {
            // Remove existing values
            $wpdb->query($wpdb->prepare($this->mass_delete_query_for_post, $original_post_ID, $original_post_ID));

            // Insert new values
            $final_query = $this->mass_insert_query_start . implode(",", $values);
            $wpdb->query($final_query);
        }
    }


    /**
     * 
     */
    function setup_table()
    {
        // Get the charset collate and table prefix
        global $wpdb;

        // Run the query
        $wpdb->query($this->create_links_table_query);

        // Call create_links to populate the created table
        // NOTE: Please import the provided data first!
        $this->create_links();
    }

    // only called upon plugin uninstall
    function remove_table()
    {
        // Get the table prefix
        global $wpdb;

        // Run the query
        $wpdb->query($this->drop_links_table_query);
    }
}
