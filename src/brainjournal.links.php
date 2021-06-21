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


    private const CREATE_LINKS_QUERY = "
        
    ";

    private const UPDATE_LINKS_QUERY = "
        
    ";

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
    }

    // Call this whenever the plugin is activated
    function create_links()
    {
    }

    /**
     * <!-- wp:paragraph -->
        <p>It shall not have any categories. <a href="http://localhost:50002/wordpress/third-event/" data-type="post" data-id="48">Link</a> to another post, Third Event.</p>
        <!-- /wp:paragraph -->
     */

    // call this whenever a post is updated
    function update_links($post_content, $post_id)
    {
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
