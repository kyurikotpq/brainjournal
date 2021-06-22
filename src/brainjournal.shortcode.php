<?php
// This file contains the code for shortcode-related functionalities

if (!defined('ABSPATH')) exit;

class BrainJournal_Shortcode
{
    private $REST_NAMESPACE;

    public function __construct(BrainJournal_Constants $constants)
    {
        // Register the shortcode in Wordpress
        add_shortcode("brainjournal", [$this, "setup_shortcode"]);
        $this->REST_NAMESPACE = $constants::REST_NAMESPACE;

        // Include the css file
        add_action("wp_enqueue_scripts", [$this, "include_css"]);
    }

    function include_css()
    {
        wp_enqueue_style("brainjournal-d3", BRAINJOURNAL_PLUGIN_URL . "/css/d3.css");
    }

    /**
     * Called when the user uses the shortcode.
     * 
     * You should use this function to:
     * - Process your shortcode attributes, if any
     * - Call the output_shortcode function
     * 
     * @param $atts Shortcode attributes
     */
    function setup_shortcode($atts)
    {
        $attributes = shortcode_atts([
            'radius' => 5,
        ], $atts);

        // Include code to make the AJAX call to your REST endpoint
        add_action("wp_footer", function () use ($attributes) {
            $this->output_javascript(intval($attributes["radius"]));
        });

        // Return HTML markup for D3 to work
        $markup = '<div id="graph"><svg width="400" height="400"></svg></div>';
        return $markup;
    }

    /**
     * Apart from including d3.min.js, d3-interactions.js files,
     * you need to write the JS code to do the AJAX call to your REST API endpoint
     */
    function output_javascript($radius)
    {
?>
        <script type="text/javascript" src="<?php echo BRAINJOURNAL_PLUGIN_URL; ?>/js/d3.min.js"></script>
        <script type="text/javascript" src="<?php echo BRAINJOURNAL_PLUGIN_URL; ?>/js/d3-interactions.js"></script>
        <script>
            // Get the REST API Endpoint
            window.onload = () => {
                const REST_API_ENDPOINT = document.querySelector('link[rel="https://api.w.org/"]').href;
                const GET_ALL_LINKS_URL = REST_API_ENDPOINT + "<?php echo $this->REST_NAMESPACE; ?>/links";

                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == XMLHttpRequest.DONE) {
                        // Parse the response and assign it to the GRAPH_DATA variable
                        const responseText = xhr.responseText;
                        const data = JSON.parse(responseText);

                        // Call the D3 Setup function
                        const d3Interactions = new D3Interactions();
                        d3Interactions.formGraph(data, <?php echo $radius; ?>);
                    }
                }
                xhr.open('GET', GET_ALL_LINKS_URL);
                xhr.send();
            }
        </script>
<?php
    }
}
