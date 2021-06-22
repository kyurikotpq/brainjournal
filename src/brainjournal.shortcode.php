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

        // Include your css file
        add_action("wp_enqueue_scripts", [$this, "include_css"]);
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
            'has_legend' => true,
        ], $atts);

        // Include code to make the AJAX call to your REST endpoint
        add_action("wp_footer", [$this, "output_javascript"]);

        // Output HTML markup for D3 to work
        $this->output_shortcode($attributes["has_legend"]);
    }

    function include_css()
    {
        wp_enqueue_style("brainjournal-d3", BRAINJOURNAL_PLUGIN_URL . "/css/d3.css");
    }

    /**
     * You should call this function to output your HTML markup
     * 
     * Do NOT query from the database here as that should be
     * handled by your REST API endpoint in brainjournal.restapi.php
     */
    function output_shortcode($has_legend)
    {
?>
        <div id="graph">
            <svg width="400" height="400"></svg>
        </div>
    <?php
    }

    function include_javascript()
    {
    }

    /**
     * Apart from including d3.min.js, d3-interactions.js files,
     * you need to write the JS code to do the AJAX call to your REST API endpoint
     */
    function output_javascript()
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
                        d3Interactions.formGraph(data);
                    }
                }
                xhr.open('GET', GET_ALL_LINKS_URL);
                xhr.send();
            }
        </script>
<?php
    }
}
