<?php
/**
 * Plugin Name:       Cerveau Interactif
 * Description:       Affiche un cerveau SVG interactif avec contenu éditable via un shortcode [cerveau_interactif].
 * Version:           1.0.0
 * Author:            Votre Nom
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cerveau-interactif
 * Domain Path:       /languages
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
            'title' => 'Les Méninges (Défaut)',
            'text' => 'Contenu par défaut pour les méninges. Modifiez ceci dans la page d\'administration.',
        ],
        'cervelet' => [
            'title' => 'Le Cervelet (Défaut)',
            'text' => 'Contenu par défaut pour le cervelet. Modifiez ceci dans la page d\'administration.',
        ],
        'lobes-temporaux' => [
            'title' => 'Les Lobes Temporaux (Défaut)',
            'text' => 'Contenu par défaut pour les lobes temporaux. Modifiez ceci dans la page d\'administration.',
        ],
        'lobes-occipitaux' => [
            'title' => 'Les Lobes Occipitaux (Défaut)',
            'text' => 'Contenu par défaut pour les lobes occipitaux. Modifiez ceci dans la page d\'administration.',
        ],
        'lobes-parietaux' => [
            'title' => 'Les Lobes Pariétaux (Défaut)',
            'text' => 'Contenu par défaut pour les lobes pariétaux. Modifiez ceci dans la page d\'administration.',
        ],
        'lobes-frontaux' => [
            'title' => 'Les Lobes Frontaux (Défaut)',
            'text' => 'Contenu par défaut pour les lobes frontaux. Modifiez ceci dans la page d\'administration.',
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
                                <label for="ci-<?php echo esc_attr($zone); ?>-title"><?php echo esc_html($label); ?></label>
                            </th>
                            <td>
                                <input
                                    type="text"
                                    id="ci-<?php echo esc_attr($zone); ?>-title"
                                    name="ci_content[<?php echo esc_attr($zone); ?>][title]"
                                    value="<?php echo esc_attr($title); ?>"
                                    placeholder="Titre"
                                    style="width: 100%; max-width: 400px; margin-bottom: 5px;"
                                >
                                <br>
                                <textarea
                                    id="ci-<?php echo esc_attr($zone); ?>-text"
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

    // Start output buffering
    ob_start();
    ?>
    <style>
        /* --- Paste ALL your CSS rules here, prefixed --- */
         .ci-plugin-container h1 { /* Prefix titles within the plugin scope */
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .ci-svg-container { /* Added prefix ci- */
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }
        .ci-svg-container svg { /* Added prefix ci- */
            max-width: 100%;
            height: auto;
        }
        /* Masquer par défaut les fonds et boutons (IDs from SVG are NOT prefixed) */
        .ci-svg-container .fond-action { /* Referenced by class in SVG */
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .ci-svg-container .btn-action { /* Referenced by class in SVG */
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        /* Les zones doivent toujours être au-dessus (IDs from SVG are NOT prefixed) */
        .ci-svg-container .zone-action { /* Referenced by class in SVG */
            cursor: pointer;
            pointer-events: all !important;
            fill: transparent !important; /* Keep transparent */
            /* stroke: red; /* Uncomment temporarily to visualize zones */
            /* stroke-width: 1px; */
        }
        /* Zone cervelet-disable (ID from SVG is NOT prefixed) */
        .ci-svg-container #zone-cervelet-disable {
            pointer-events: none;
            cursor: default; /* Change cursor for disabled */
        }
        .ci-svg-container #zone-cervelet-disable.active {
           pointer-events: all !important;
           cursor: pointer; /* Restore cursor when active */
        }
        /* Info box styles - positionnées autour du SVG (IDs ARE prefixed) */
        .ci-info-box { /* Added prefix ci- */
            position: absolute;
            opacity: 0;
            visibility: hidden;
            background: white;
            width: 273px;
            z-index: 9999;
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s; /* Delay hiding visibility */
            border-radius: 12px;
            transform: translateY(-10px);
            pointer-events: none;
            padding: 1rem 1.5rem;
            border: 2px solid #D17490; /* Use actual color */
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-sizing: border-box;
        }
        .ci-info-box.active { /* Added prefix ci- */
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0s; /* Show visibility immediately */
        }
        .ci-info-box h2 { /* Added prefix ci- */
            font-family: Interstate, sans-serif; /* Added fallback */
            font-size: 20px;
            font-weight: 900;
            color: #3B2783; /* Use actual color */
            margin: 0 0 10px 0;
            padding: 0;
            font-size: 1.3em; /* Keep original */
        }
        .ci-info-box p { /* Added prefix ci- */
            color: #3B2783; /* Use actual color */
            font-family: Interstate, sans-serif; /* Added fallback */
            font-size: 15px;
            font-style: normal;
            font-weight: 300;
            line-height: 18px; /* 120% */
            margin: 0;
            padding: 0;
        }
        /* Conteneur principal avec position relative */
        .ci-animated-svg__grid { /* Added prefix ci- */
            position: relative;
            width: 100%;
            /* max-width: 800px; */ /* Consider adding a max-width if needed */
            height: 570px; /* Adjust as needed */
            margin: 0 auto;
        }
        /* SVG Cerveau - centré */
        .ci-svg-container { /* Added prefix ci- */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 430px; /* SVG original width */
            max-width: 100%; /* Make responsive */
        }
        /* Positionnement des info-boxes autour du cerveau (IDs ARE prefixed) */
        /* Lobes Pariétaux - en haut au centre */
        #ci-info-lobes-parietaux { top: 20px; right: 90px; }
        /* Lobes Frontaux - à gauche, haut */
        #ci-info-lobes-frontaux { top: 80px; left: 50px; }
        /* Méninges - à gauche, milieu */
        #ci-info-meninges { top: 220px; left: 50px; }
        /* Lobes Temporaux - à gauche, bas */
        #ci-info-lobes-temporaux { top: 275px; left: 50px; }
        /* Lobes Occipitaux - à droite, haut */
        #ci-info-lobes-occipitaux { top: 256px; right: 50px; }
        /* Cervelet - à droite, bas */
        #ci-info-cervelet { top: 360px; right: 70px; }

        /* Responsive pour mobile */
        @media (max-width: 960px) {
            .ci-animated-svg__grid { /* Added prefix ci- */
                height: auto;
                min-height: 600px; /* Adjust as needed */
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 20px;
                padding-bottom: 200px; /* Space for the info box */
            }
            .ci-svg-container { /* Added prefix ci- */
                position: relative;
                top: auto;
                left: auto;
                transform: none;
                width: 90%;
                max-width: 350px; /* Smaller max width on mobile */
                order: 1; /* SVG first */
                margin-top: 20px;
            }
            .ci-info-box { /* Added prefix ci- */
                position: absolute; /* Keep absolute */
                width: 90%;
                max-width: 300px;
                bottom: 20px; /* Position near bottom */
                left: 50% !important; /* Center horizontally */
                transform: translateX(-50%) translateY(10px) !important; /* Start slightly below */
                top: auto !important; /* Override desktop top */
                right: auto !important; /* Override desktop right */
                pointer-events: all; /* Allow interaction */
                order: 2; /* Info box second */
                /* Ensure visibility transitions correctly */
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;
            }
             /* Adjust active state transform for mobile */
            .ci-info-box.active {
                opacity: 1;
                visibility: visible;
                transform: translateX(-50%) translateY(0) !important;
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0s;
            }
             /* Override specific desktop positions for mobile */
             #ci-info-lobes-parietaux,
             #ci-info-lobes-frontaux,
             #ci-info-meninges,
             #ci-info-lobes-temporaux,
             #ci-info-lobes-occipitaux,
             #ci-info-cervelet {
                 top: auto;
                 left: 50% !important;
                 right: auto !important;
                 bottom: 20px;
                 transform: translateX(-50%) translateY(10px) !important;
             }
            #ci-info-lobes-parietaux.active,
            #ci-info-lobes-frontaux.active,
            #ci-info-meninges.active,
            #ci-info-lobes-temporaux.active,
            #ci-info-lobes-occipitaux.active,
            #ci-info-cervelet.active {
                 transform: translateX(-50%) translateY(0) !important;
            }
        }

        /* Button Pulse Animation (ID/Class from SVG are NOT prefixed) */
        .ci-svg-container .btn-back {
            fill : transparent !important;
            stroke: white !important;        /* couleur du contour */
            stroke-width: 0;        /* on masque le stroke au repos */
            stroke-opacity: 0;
            animation: ciStrokePulse 1.5s ease-in-out forwards infinite; /* Prefix keyframe name */
            transform-origin: center;
        }
        @keyframes ciStrokePulse { /* Prefix keyframe name */
            0%   { stroke-width: 0;  stroke-opacity: 0; }
            50%  { stroke-width: 7.5; stroke-opacity: .7; }
            100% { stroke-width: 0;  stroke-opacity: 0; }
        }
        /* --- End of CSS rules --- */
    </style>

    <div class="ci-plugin-container"> <?php // Wrapper div for scoping CSS if needed ?>
        <div class='ci-animated-svg__grid'>
            <div class="ci-svg-container" id="ci-svg-container-<?php echo esc_attr( uniqid() ); ?>">
                <?php include 'cerveau.svg'; ?>
                </div>

            <?php // Generate info boxes with dynamic content and prefixed IDs ?>
            <?php foreach ($content as $zone => $zone_data): ?>
                <div class="ci-info-box" id="ci-info-<?php echo esc_attr($zone); ?>">
                    <h2><?php echo esc_html($zone_data['title']); ?></h2>
                    <p><?php echo wp_kses_post(nl2br($zone_data['text'])); // Using wp_kses_post and nl2br to allow basic HTML and line breaks ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find the specific container for this shortcode instance if multiple exist
        const allGrids = document.querySelectorAll('.ci-animated-svg__grid');
        allGrids.forEach(grid => {
            initializeInteractions(grid);
        });
    });

    function initializeInteractions(gridElement) {
        const svgContainer = gridElement.querySelector('.ci-svg-container');
        if (!svgContainer) return; // Exit if SVG container not found

        const svg = svgContainer.querySelector('svg');
        if (!svg) return; // Exit if SVG not found

        let currentActiveZone = null;
        let activeZones = {};
        let zoneTimeouts = {};
        const zones = [
            'meninges',
            'cervelet',
            'lobes-temporaux',
            'lobes-occipitaux',
            'lobes-parietaux',
            'lobes-frontaux'
        ];

        // --- Element getters scoped to the current gridElement ---
        const getElementByIdPrefixed = (id) => gridElement.querySelector('#' + id);
        const getElementByIdSvg = (id) => svg.querySelector('#' + id); // For elements *inside* SVG

        const cerveletDisable = getElementByIdSvg('zone-cervelet-disable'); // Inside SVG

        // Reorganize SVG elements (optional but good practice)
        if (svg) {
            if (cerveletDisable) {
                svg.appendChild(cerveletDisable); // Move to end
            }
            zones.forEach(zone => {
                const zoneElement = getElementByIdSvg(`zone-${zone}`); // Inside SVG
                const btnElement = getElementByIdSvg(`btn-${zone}`);   // Inside SVG
                if (zoneElement) svg.appendChild(zoneElement); // Move zones to end
                if (btnElement) svg.appendChild(btnElement);   // Move buttons to end
            });
        }

        // Apply initial state (show all backgrounds, hide buttons except meninges)
        applyDefaultState();

        zones.forEach(zone => {
            const zoneElement = getElementByIdSvg(`zone-${zone}`); // Inside SVG
            const fondElement = getElementByIdSvg(`fond-${zone}`); // Inside SVG
            const btnElement = getElementByIdSvg(`btn-${zone}`);   // Inside SVG
            const infoElement = getElementByIdPrefixed(`ci-info-${zone}`); // Prefixed, outside SVG

            if (zoneElement) {
                zoneElement.addEventListener('mouseenter', () => {
                    if (zoneTimeouts[zone]) {
                        clearTimeout(zoneTimeouts[zone]);
                        delete zoneTimeouts[zone];
                    }
                    if (activeZones[zone]) return;

                    // Deactivate other zones
                    zones.forEach(otherZone => {
                        if (otherZone !== zone) {
                            resetZoneVisuals(otherZone);
                        }
                    });

                    // Specifically hide meninges button if hovering another zone
                    if (zone !== 'meninges') {
                         const btnMeninges = getElementByIdSvg('btn-meninges');
                         if (btnMeninges) {
                            btnMeninges.style.opacity = '0';
                            btnMeninges.style.pointerEvents = 'none';
                            btnMeninges.classList.remove('active');
                         }
                    }


                    activeZones[zone] = true;
                    console.log('🎯 ZONE ENTER:', zone);

                    // Activate current zone visuals
                    if (fondElement) {
                        fondElement.style.opacity = '1';
                        fondElement.classList.add('active'); // Still keep pointer-events: none via CSS
                    }
                    if (btnElement) {
                        btnElement.style.opacity = '1';
                        btnElement.classList.add('active'); // Still keep pointer-events: none via CSS
                    }
                    if (infoElement) {
                        // On mobile, hide all other info boxes before showing this one
                        if (window.innerWidth <= 960) {
                            gridElement.querySelectorAll('.ci-info-box').forEach(box => {
                                if (box !== infoElement) {
                                    box.classList.remove('active');
                                }
                            });
                        }
                        infoElement.classList.add('active');
                    }

                    // Special handling for cervelet-disable zone
                    if (zone === 'cervelet' && cerveletDisable) {
                        cerveletDisable.classList.add('active');
                        // Ensure it's visually above the background but below other interactive zones
                        if (fondElement && fondElement.parentNode) {
                           fondElement.parentNode.insertBefore(cerveletDisable, fondElement.nextSibling);
                        }
                    }

                    currentActiveZone = zone;
                });

                zoneElement.addEventListener('mouseleave', () => {
                    zoneTimeouts[zone] = setTimeout(() => {
                        // Only reset if this zone is still the active one (mouse hasn't entered another)
                        if (currentActiveZone === zone) {
                            console.log('ZONE LEAVE (Timeout):', zone);
                            resetZone(zone);
                            currentActiveZone = null; // Mark no zone as active
                            applyDefaultState(); // Reapply default state after a leave
                        }
                        delete zoneTimeouts[zone];
                    }, 50); // Small delay
                });
            }
        });

        // Special listener for 'zone-cervelet-disable'
        if (cerveletDisable) {
            cerveletDisable.addEventListener('mouseenter', () => {
                 // If the mouse enters the 'disable' zone while 'cervelet' is active, reset 'cervelet'
                if (currentActiveZone === 'cervelet') {
                    console.log('🎯 ZONE ENTER: cervelet-disable (resetting cervelet)');
                     if (zoneTimeouts['cervelet']) { // Clear any pending leave timeout for cervelet
                        clearTimeout(zoneTimeouts['cervelet']);
                        delete zoneTimeouts['cervelet'];
                    }
                    resetZone('cervelet');
                    currentActiveZone = null;
                    applyDefaultState(); // Reapply default after disable zone interaction
                }
            });
             // No 'mouseleave' needed for disable zone, as leaving it should just let the default state apply
        }

        // --- Helper Functions ---

        function applyDefaultState() {
            console.log('Applying Default State');
            zones.forEach(zone => {
                const fondElement = getElementByIdSvg(`fond-${zone}`);
                const btnElement = getElementByIdSvg(`btn-${zone}`);
                const infoElement = getElementByIdPrefixed(`ci-info-${zone}`); // Outside SVG

                if (fondElement) {
                    fondElement.style.opacity = '1'; // All backgrounds visible by default
                    fondElement.classList.add('active');
                    fondElement.style.pointerEvents = 'none'; // Ensure backgrounds don't block clicks
                }
                if (btnElement) {
                    if (zone === 'meninges') { // Only meninges button visible by default
                        btnElement.style.opacity = '1';
                        btnElement.classList.add('active');
                    } else {
                        btnElement.style.opacity = '0';
                        btnElement.classList.remove('active');
                    }
                    btnElement.style.pointerEvents = 'none'; // Ensure buttons don't block clicks
                }
                 if (infoElement) {
                     infoElement.classList.remove('active'); // All info boxes hidden by default
                 }
            });
             // Ensure cervelet-disable is not active and visually below interaction zones
            if (cerveletDisable) {
                cerveletDisable.classList.remove('active');
                if(svg && cerveletDisable.parentNode === svg) {
                   const firstChild = svg.firstChild;
                    if(firstChild) {
                       svg.insertBefore(cerveletDisable, firstChild); // Move to visually bottom
                    }
                }
            }
        }

        function resetZoneVisuals(zoneToReset) {
            // This function ONLY resets the visual elements (fond, btn, info)
            // It does NOT change the activeZones state or currentActiveZone
             const fondElement = getElementByIdSvg(`fond-${zoneToReset}`);
             const btnElement = getElementByIdSvg(`btn-${zoneToReset}`);
             const infoElement = getElementByIdPrefixed(`ci-info-${zoneToReset}`); // Outside SVG

             if (fondElement) {
                // Keep background visible unless explicitly resetting to default state
                // fondElement.style.opacity = '0'; // Don't hide fond immediately on mouseleave of another zone
                fondElement.classList.remove('active');
             }
             if (btnElement) {
                 btnElement.style.opacity = '0';
                 btnElement.classList.remove('active');
             }
             if (infoElement) {
                 infoElement.classList.remove('active');
             }

             // Special handling for cervelet-disable
             if (zoneToReset === 'cervelet' && cerveletDisable) {
                 cerveletDisable.classList.remove('active');
                  if(svg && cerveletDisable.parentNode === svg) {
                     const firstChild = svg.firstChild;
                      if(firstChild) {
                         svg.insertBefore(cerveletDisable, firstChild); // Move to visually bottom
                      }
                  }
             }
        }

        function resetZone(zoneToReset) {
            // Resets state AND visuals for a specific zone
            console.log('Resetting Zone:', zoneToReset);
            activeZones[zoneToReset] = false;
            resetZoneVisuals(zoneToReset);
        }
    }
    </script>
    <?php
    // Return buffered content
    return ob_get_clean();
}