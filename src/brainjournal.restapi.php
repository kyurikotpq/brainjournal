<?php
// This file contains the code for REST API-related functionalities

if (!defined('ABSPATH')) exit;

class BrainJournal_RestAPI
{
    private $REST_NAMESPACE;
    private $get_all_links_query;

    public function __construct(BrainJournal_Constants $constants)
    {
        $this->REST_NAMESPACE = $constants::REST_NAMESPACE;
        $this->get_all_links_query = "SELECT * FROM {$constants->table_prefix}brainjournal_links;";
        $this->get_all_nodes_query = "
            SELECT DISTINCT(ID) AS id, post_title AS title, guid AS href FROM {$constants->table_prefix}posts
            WHERE post_type = 'post' and post_status = 'publish';
        ";

        add_action('rest_api_init', [$this, 'setup_rest']);
    }

    /**
     * Endpoint: GET http://example.com/wp-json/brainjournal/v1/links
     * 
     * NOTE: For this to work, please set your permalink structure to "Post name"
     * 
     * You should call this function to:
     * - Register your REST API endpoint
     * - Specify the callback function to retrieve the links
     */
    function setup_rest()
    {
        register_rest_route($this->REST_NAMESPACE, 'links', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_all_links'],
        ));
    }

    /**
     * Callback to call when the route is called
     */
    function get_all_links()
    {
        // Get the table prefix
        global $wpdb;

        // Run the query to get the links in an associative array format
        $links = $wpdb->get_results(
            $this->get_all_links_query,
            ARRAY_A
        );

        // Run the query to get the nodes in an associative array format
        $nodes = $wpdb->get_results(
            $this->get_all_nodes_query,
            ARRAY_A
        );

        // Do additional processing to add node colors
        foreach ($nodes as $key => $node) {
            $categories = get_the_category($node["id"]);

            // For brevity sake, we will take the first category
            if (count($categories) > 0) {
                $first_cat = $categories[0];
                $nodes[$key]["category"] = $first_cat->term_id;
            }
        }

        $node_colors = get_option('brainjournal_options');

        // return the data
        return ["links" => $links, "nodes" => $nodes, "node_colors" => $node_colors];
    }
}
