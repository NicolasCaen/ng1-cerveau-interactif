<?php
/**
 * Plugin Name:       Cerveau Interactif
 * Description:       Affiche un cerveau SVG interactif avec contenu éditable via un shortcode [cerveau_interactif].
 * Version:           1.1.0
 * Author:            GEHIN NIcolas
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cerveau-interactif
 */

/**
 * Changelog
 *
 * 1.1.0
 * - Mise à jour des interactions utilisateur : affichage initial des boutons, gestion des survols et ouverture des popups au clic.
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- Default Content ---

/**
 * Gets the default content for the brain regions.
 *
 * @return array Default content.
 */
function ci_get_default_brain_content() {
    // Default texts for each brain zone
    return [
        'meninges' => [
            'title' => 'Méninges',
            'text' => 'Les méninge sont des membranes qui enveloppent et protègent le système nerveux central : encéphale et moelle épinière.',
        ],
        'cervelet' => [
            'title' => 'Cervelet',
            'text' => 'Cette zone à l’arrière du cerveau réunit les données en provenance des aires perceptives pour guider les fonctions motrices fines.',
        ],
        'lobes-temporaux' => [
            'title' => 'Lobes Temporaux',
            'text' => 'C’est le « traducteur sonore et mémoire » : il gère l’audition, la compréhension du langage et stocke les souvenirs.',
        ],
        'lobes-occipitaux' => [
            'title' => 'Lobe Occipital',
            'text' => 'C’est le « projecteur visuel » : il traite tout ce que les yeux voient \n Sans lui, pas d’images mentales.',
        ],
        'lobes-parietaux' => [
            'title' => 'Lobes Pariétaux',
            'text' => 'C’est le « GPS sensoriel » : il reçoit les infos du toucher, de la douleur, de la température, et aide à se repérer dans l’espace.',
        ],
        'lobes-frontaux' => [
            'title' => 'Lobes Frontaux',
            'text' => 'C’est le « chef d’orchestre » : il gère la planification, la prise de décision, la concentration, les émotions et aussi les mouvements volontaires.',
        ],
    ];
}

// --- Admin Page ---

/**
 * Adds the admin menu page.
 */
function ci_add_admin_menu() {
    add_menu_page(
        'Cerveau Interactif Options', // Page title
        'Cerveau Interactif',        // Menu title
        'manage_options',            // Capability required
        'cerveau-interactif',        // Menu slug
        'ci_options_page_html',      // Function to display the page
        'dashicons-brain',           // Icon URL or dashicon class
        20                           // Position
    );
}
add_action( 'admin_menu', 'ci_add_admin_menu' );

/**
 * Displays the options page HTML and handles saving.
 */
function ci_options_page_html() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle saving options
    if ( isset( $_POST['ci_nonce'] ) && wp_verify_nonce( $_POST['ci_nonce'], 'ci_save_options' ) ) {
        $defaults = ci_get_default_brain_content();
        $submitted_content = isset($_POST['ci_content']) ? $_POST['ci_content'] : [];
        $sanitized_content = [];

        foreach ( $defaults as $zone => $zone_defaults ) {
            $sanitized_content[$zone] = [
                'title' => isset($submitted_content[$zone]['title']) ? sanitize_text_field( $submitted_content[$zone]['title'] ) : $zone_defaults['title'],
                'text'  => isset($submitted_content[$zone]['text']) ? wp_kses_post( $submitted_content[$zone]['text'] ) : $zone_defaults['text'], // Allows basic HTML like <br>, <strong> etc. Use sanitize_textarea_field for plain text.
            ];
        }

        update_option( 'ci_brain_content', $sanitized_content );

        // Add success message
        add_settings_error('ci_messages', 'ci_message', __('Contenu sauvegardé avec succès.', 'cerveau-interactif'), 'updated');
    }

    // Show error/update messages
    settings_errors( 'ci_messages' );

    // Get current saved content or defaults
    $current_content = get_option( 'ci_brain_content', ci_get_default_brain_content() );
    $defaults = ci_get_default_brain_content(); // Load defaults again for comparison/structure

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php esc_html_e('Modifiez ici les titres et textes qui apparaissent dans les popups du cerveau interactif.', 'cerveau-interactif'); ?></p>
        <form method="post" action="">
            <?php wp_nonce_field( 'ci_save_options', 'ci_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <?php foreach ( $defaults as $zone => $zone_defaults ) :
                        $zone_content = isset($current_content[$zone]) ? $current_content[$zone] : $zone_defaults;
                        $title = isset($zone_content['title']) ? $zone_content['title'] : $zone_defaults['title'];
                        $text = isset($zone_content['text']) ? $zone_content['text'] : $zone_defaults['text'];
                        $label = ucwords(str_replace('-', ' ', $zone)); // Create a readable label from the key
                    ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($zone); ?>-title"><?php echo esc_html($label); ?></label>
                            </th>
                            <td>
                                <input
                                    type="text"
                                    id="<?php echo esc_attr($zone); ?>-title"
                                    name="ci_content[<?php echo esc_attr($zone); ?>][title]"
                                    value="<?php echo esc_attr($title); ?>"
                                    placeholder="Titre"
                                    style="width: 100%; max-width: 400px; margin-bottom: 5px;"
                                >
                                <br>
                                <textarea
                                    id="<?php echo esc_attr($zone); ?>-text"
                                    name="ci_content[<?php echo esc_attr($zone); ?>][text]"
                                    rows="4"
                                    placeholder="Texte descriptif"
                                    style="width: 100%; max-width: 400px;"
                                ><?php echo esc_textarea($text); ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button( __('Sauvegarder les modifications', 'cerveau-interactif') ); ?>
        </form>
    </div>
    <?php
}

// --- Shortcode ---

/**
 * Registers the shortcode [cerveau_interactif].
 */
function ci_register_shortcode() {
    add_shortcode( 'cerveau_interactif', 'ci_display_interactive_brain' );
}
add_action( 'init', 'ci_register_shortcode' );

/**
 * Displays the interactive brain HTML, CSS, and JS.
 * Pulls content from saved options.
 *
 * @param array $atts Shortcode attributes (not used here).
 * @return string HTML output for the shortcode.
 */
function ci_display_interactive_brain( $atts ) {
    // Get saved content or defaults
    $saved_content = get_option( 'ci_brain_content', [] ); // Get potentially incomplete saved data
    $defaults = ci_get_default_brain_content();
    $content = [];

    // Ensure all zones exist and have both title and text, merging saved with defaults
    foreach ($defaults as $zone => $zone_defaults) {
        $content[$zone] = [
            'title' => isset($saved_content[$zone]['title']) && !empty($saved_content[$zone]['title']) ? $saved_content[$zone]['title'] : $zone_defaults['title'],
            'text'  => isset($saved_content[$zone]['text']) && !empty($saved_content[$zone]['text']) ? $saved_content[$zone]['text'] : $zone_defaults['text'],
        ];
    }

    // Enqueue frontend JS for interactions
    wp_enqueue_style(
        'cerveau-interactif-css',
        plugins_url( 'assets/cerveau-interactif.css', __FILE__ ),
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'cerveau-interactif-js',
        plugins_url( 'assets/cerveau-interactif.js', __FILE__ ),
        [],
        '1.0.0',
        true
    );

    // Start output buffering
    ob_start();
    ?>
    <div class="cerveau-interactif-wrapper">
        <div class="cerveau-container">
          
            <div class='animated-svg__grid'>
                <div class="svg-container" id="svg-container-<?php echo esc_attr( uniqid() ); ?>">
                <?php include 'cerveau.svg'; ?>
                </div>

                <?php foreach ($content as $zone => $zone_data): ?>
                    <div class="info-box" id="info-<?php echo esc_attr($zone); ?>">
                        <h2><?php echo esc_html($zone_data['title']); ?></h2>
                        <p><?php echo wp_kses_post(nl2br($zone_data['text'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
    // Return buffered content
    return ob_get_clean();
}