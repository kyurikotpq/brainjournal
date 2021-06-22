<?php
/*
 * This file contains the code for functionalities related to
 * the parsing of posts for the population of the
 * {TABLE_PREFIX}brainjournal_links table.
 */

if (!defined('ABSPATH')) exit;

class BrainJournal_Links
{
    private $create_links_table_query;
    private $drop_links_table_query;

    private $select_posts_with_links_query;
    private $mass_insert_query_start;

    public function __construct(BrainJournal_Constants $constants)
    {
        $this->create_links_table_query = "
            CREATE TABLE IF NOT EXISTS {$constants->table_prefix}brainjournal_links (
                source INT(11) NOT NULL,
                target INT(11) NOT NULL,
                PRIMARY KEY (source, target)
            ) {$constants->charset_collate};
        ";

        $this->drop_links_table_query = "DROP TABLE IF EXISTS {$constants->table_prefix}brainjournal_links;";
        $this->select_posts_with_links_query = "
            SELECT ID, post_content FROM {$constants->table_prefix}posts 
            WHERE post_type = 'post' AND 
            post_content REGEXP '<a href=.*? data-type=\"post\" data-id=\"[0-9]+?\">'
        ";
        $this->mass_insert_query_start = "INSERT IGNORE INTO {$constants->table_prefix}brainjournal_links(source, target) VALUES ";

        // add_action('save_post', [$this, "update_links"]);
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
            $targets = [];
            preg_match_all('/<a href=.*? data-type="post" data-id="([0-9]+?)">/m', $p["post_content"], $targets);

            foreach (array_slice($targets, 1, count($targets) - 1) as $target_arr) {
                foreach ($target_arr as $target) {
                    if (trim($target) != "") {
                        $values[] = $wpdb->prepare("(%d, %d)", $source, $target);
                    }
                }
            }
        }

        $final_query = $this->mass_insert_query_start . implode(",", $values);
        $wpdb->query($final_query);
    }

    // call this whenever a post is saved
    function update_links($post_content, $post_id)
    {
        // SELECT id, post_content FROM `pkm_posts` WHERE post_type = 'post' and post_content REGEXP '<a href=".*? data-type="post" data-id="\d+?">'
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
