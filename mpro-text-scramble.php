<?php
/**
 * Plugin Name: MPRO Text Scramble
 * Plugin URI:  https://moghadam.pro
 * Description: Text Scramble / Decode effect for any text, link or button. Works with Elementor via CSS class. Part of MPRO suite.
 * Version:     1.0.0
 * Author:      Sayid Moghadam
 * Text Domain: mpro-text-scramble
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MPRO_TS_VERSION', '1.0.0' );
define( 'MPRO_TS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MPRO_TS_URL',  plugin_dir_url( __FILE__ ) );

/* ---------------------------------------------------------------
   1. ADMIN MENU — registers MPRO parent (if not already exists)
       and adds Text Scramble as sub-item at position 4
--------------------------------------------------------------- */
add_action( 'admin_menu', 'mpro_ts_register_menu', 20 );

function mpro_ts_register_menu() {

    // Register top-level MPRO menu only once (shared across MPRO plugins)
    if ( ! isset( $GLOBALS['mpro_menu_registered'] ) ) {
        add_menu_page(
            'MPRO Suite',
            'MPRO',
            'manage_options',
            'mpro-dashboard',
            'mpro_ts_dashboard_page',
            'dashicons-superhero',
            4   // position 4 in the admin sidebar
        );
        $GLOBALS['mpro_menu_registered'] = true;
    }

    // Sub-menu item for this plugin
    add_submenu_page(
        'mpro-dashboard',
        'Text Scramble Settings',
        'Text Scramble',
        'manage_options',
        'mpro-text-scramble',
        'mpro_ts_settings_page'
    );
}

/* ---------------------------------------------------------------
   2. SETTINGS — register options
--------------------------------------------------------------- */
add_action( 'admin_init', 'mpro_ts_register_settings' );

function mpro_ts_register_settings() {
    register_setting( 'mpro_ts_options', 'mpro_ts_settings', [
        'sanitize_callback' => 'mpro_ts_sanitize',
        'default' => mpro_ts_defaults(),
    ]);
}

function mpro_ts_defaults() {
    return [
        'charset'    => 'symbols',
        'custom_charset' => '!@#$%^&*()_+-=[]{}|',
        'duration'   => 350,
        'iterations' => 5,
        'stagger'    => 18,
        'trigger'    => 'load',  // load | hover | both
        'css_class'  => 'mpro-scramble',
        'load_sitewide' => 1,
    ];
}

function mpro_ts_sanitize( $input ) {
    $defaults = mpro_ts_defaults();
    return [
        'charset'        => sanitize_text_field( $input['charset'] ?? $defaults['charset'] ),
        'custom_charset' => sanitize_text_field( $input['custom_charset'] ?? $defaults['custom_charset'] ),
        'duration'       => absint( $input['duration'] ?? $defaults['duration'] ),
        'iterations'     => absint( $input['iterations'] ?? $defaults['iterations'] ),
        'stagger'        => absint( $input['stagger'] ?? $defaults['stagger'] ),
        'trigger'        => sanitize_text_field( $input['trigger'] ?? $defaults['trigger'] ),
        'css_class'      => sanitize_html_class( $input['css_class'] ?? $defaults['css_class'] ),
        'load_sitewide'  => isset( $input['load_sitewide'] ) ? 1 : 0,
    ];
}

function mpro_ts_get( $key = null ) {
    $opts = get_option( 'mpro_ts_settings', mpro_ts_defaults() );
    if ( $key ) return $opts[ $key ] ?? null;
    return $opts;
}

/* ---------------------------------------------------------------
   3. CHARSET MAP
--------------------------------------------------------------- */
function mpro_ts_charsets() {
    return [
        'symbols'     => '!@#$%^&*()_+-=[]{}|',
        'uppercase'   => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'alphanumeric'=> 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
        'blocks'      => '░▒▓█▄▀■□▪▫◆◇○●',
        'arabic'      => 'ابتثجحخدذرزسشصضطظعغفقكلمنهوي',
        'custom'      => '',
    ];
}

/* ---------------------------------------------------------------
   4. FRONTEND — enqueue scripts & inline config
--------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', 'mpro_ts_enqueue' );

function mpro_ts_enqueue() {
    $opts = mpro_ts_get();
    if ( ! $opts['load_sitewide'] ) return;

    wp_enqueue_style(
        'mpro-text-scramble',
        MPRO_TS_URL . 'css/mpro-text-scramble.css',
        [],
        MPRO_TS_VERSION
    );

    wp_enqueue_script(
        'mpro-text-scramble',
        MPRO_TS_URL . 'js/mpro-text-scramble.js',
        [],
        MPRO_TS_VERSION,
        true
    );

    $charsets = mpro_ts_charsets();
    $charset  = $opts['charset'] === 'custom'
        ? $opts['custom_charset']
        : ( $charsets[ $opts['charset'] ] ?? $charsets['symbols'] );

    wp_localize_script( 'mpro-text-scramble', 'mproScrambleConfig', [
        'charset'    => $charset,
        'duration'   => (int) $opts['duration'],
        'iterations' => (int) $opts['iterations'],
        'stagger'    => (int) $opts['stagger'],
        'trigger'    => $opts['trigger'],
        'cssClass'   => '.' . $opts['css_class'],
    ]);
}

/* ---------------------------------------------------------------
   5. ELEMENTOR WIDGET EXTENSION — adds CSS class field to
      Heading, Button, Icon Box, Text Editor widgets
--------------------------------------------------------------- */
add_action( 'elementor/element/heading/section_title/before_section_end', 'mpro_ts_elementor_inject_control', 10, 2 );
add_action( 'elementor/element/button/section_button/before_section_end', 'mpro_ts_elementor_inject_control', 10, 2 );
add_action( 'elementor/element/text-editor/section_editor/before_section_end', 'mpro_ts_elementor_inject_control', 10, 2 );

function mpro_ts_elementor_inject_control( $element, $args ) {
    if ( ! class_exists('\Elementor\Controls_Manager') ) return;
    $element->add_control( 'mpro_scramble_enable', [
        'label'        => '✦ MPRO Scramble',
        'type'         => \Elementor\Controls_Manager::SWITCHER,
        'label_on'     => 'On',
        'label_off'    => 'Off',
        'return_value' => 'yes',
        'default'      => '',
        'separator'    => 'before',
    ]);
}

add_filter( 'elementor/widget/render_content', 'mpro_ts_maybe_add_class', 10, 2 );

function mpro_ts_maybe_add_class( $content, $widget ) {
    $settings = $widget->get_settings_for_display();
    if ( ! empty( $settings['mpro_scramble_enable'] ) && $settings['mpro_scramble_enable'] === 'yes' ) {
        $css_class = mpro_ts_get('css_class');
        $content = preg_replace(
            '/class="([^"]*)"/',
            'class="$1 ' . esc_attr( $css_class ) . '"',
            $content,
            1
        );
    }
    return $content;
}

/* ---------------------------------------------------------------
   6. SHORTCODE  [mpro_scramble]text here[/mpro_scramble]
--------------------------------------------------------------- */
add_shortcode( 'mpro_scramble', 'mpro_ts_shortcode' );

function mpro_ts_shortcode( $atts, $content = '' ) {
    $opts = mpro_ts_get();
    $a = shortcode_atts([
        'tag'     => 'span',
        'class'   => '',
        'trigger' => $opts['trigger'],
    ], $atts );

    $tag     = sanitize_key( $a['tag'] );
    $classes = trim( $opts['css_class'] . ' mpro-sc ' . sanitize_html_class( $a['class'] ) );
    $trigger = sanitize_text_field( $a['trigger'] );

    return sprintf(
        '<%1$s class="%2$s" data-scramble-trigger="%3$s">%4$s</%1$s>',
        $tag,
        esc_attr( $classes ),
        esc_attr( $trigger ),
        wp_kses_post( $content )
    );
}

/* ---------------------------------------------------------------
   7. ADMIN PAGES
--------------------------------------------------------------- */
function mpro_ts_dashboard_page() {
    ?>
    <div class="wrap">
        <h1>🦸 MPRO Suite</h1>
        <p>Welcome to the MPRO plugin suite. Select a plugin from the submenu to configure it.</p>
    </div>
    <?php
}

function mpro_ts_settings_page() {
    if ( ! current_user_can('manage_options') ) return;
    $opts     = mpro_ts_get();
    $charsets = mpro_ts_charsets();
    ?>
    <div class="wrap" id="mpro-ts-admin">
        <h1>✦ MPRO Text Scramble</h1>
        <p style="color:#777; margin-top:0;">Add text decode/scramble animation to any element. Works sitewide via CSS class, with Elementor toggle, or via shortcode.</p>

        <?php if ( isset($_GET['settings-updated']) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('mpro_ts_options'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">CSS Class</th>
                    <td>
                        <input type="text" name="mpro_ts_settings[css_class]"
                               value="<?php echo esc_attr($opts['css_class']); ?>"
                               class="regular-text">
                        <p class="description">Add this class to ANY HTML element in Elementor → Advanced → CSS Classes</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Charset</th>
                    <td>
                        <?php foreach ( $charsets as $key => $val ) : ?>
                        <label style="display:block; margin-bottom:6px;">
                            <input type="radio" name="mpro_ts_settings[charset]"
                                   value="<?php echo esc_attr($key); ?>"
                                   <?php checked($opts['charset'], $key); ?>>
                            <code><?php echo esc_html($key); ?></code>
                            <?php if($val) echo ' — <span style="opacity:.6">'.esc_html(mb_substr($val,0,20)).'…</span>'; ?>
                        </label>
                        <?php endforeach; ?>
                        <div style="margin-top:8px;">
                            <input type="text" name="mpro_ts_settings[custom_charset]"
                                   value="<?php echo esc_attr($opts['custom_charset']); ?>"
                                   placeholder="Custom charset characters…"
                                   class="regular-text">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Trigger</th>
                    <td>
                        <?php $triggers = ['load'=>'On page load','hover'=>'On hover only','both'=>'On load + hover repeat']; ?>
                        <?php foreach ($triggers as $k=>$label) : ?>
                        <label style="margin-right:20px;">
                            <input type="radio" name="mpro_ts_settings[trigger]"
                                   value="<?php echo esc_attr($k); ?>"
                                   <?php checked($opts['trigger'],$k); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Duration (ms)</th>
                    <td>
                        <input type="range" name="mpro_ts_settings[duration]"
                               min="100" max="1500" step="50"
                               value="<?php echo esc_attr($opts['duration']); ?>"
                               oninput="document.getElementById('dur_out').textContent=this.value+'ms'">
                        <span id="dur_out"><?php echo esc_html($opts['duration']); ?>ms</span>
                        <p class="description">Total animation duration per element (300–400ms recommended)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Iterations per char</th>
                    <td>
                        <input type="range" name="mpro_ts_settings[iterations]"
                               min="1" max="15" step="1"
                               value="<?php echo esc_attr($opts['iterations']); ?>"
                               oninput="document.getElementById('iter_out').textContent=this.value">
                        <span id="iter_out"><?php echo esc_html($opts['iterations']); ?></span>
                        <p class="description">How many random chars each position shows before settling</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Stagger (ms)</th>
                    <td>
                        <input type="range" name="mpro_ts_settings[stagger]"
                               min="0" max="100" step="2"
                               value="<?php echo esc_attr($opts['stagger']); ?>"
                               oninput="document.getElementById('stag_out').textContent=this.value+'ms'">
                        <span id="stag_out"><?php echo esc_html($opts['stagger']); ?>ms</span>
                        <p class="description">Delay between each character starting its animation (wave effect)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Load sitewide</th>
                    <td>
                        <label>
                            <input type="checkbox" name="mpro_ts_settings[load_sitewide]" value="1"
                                   <?php checked($opts['load_sitewide'], 1); ?>>
                            Enqueue scripts on all frontend pages
                        </label>
                        <p class="description">Disable if you only want to load on specific pages (use shortcode).</p>
                    </td>
                </tr>
            </table>

            <hr>
            <h2>How to use</h2>
            <ol style="line-height:2;">
                <li><strong>Elementor widget toggle</strong> — edit any Heading / Button / Text Editor widget → scroll to the <em>✦ MPRO Scramble</em> toggle at the bottom of the content tab → switch On.</li>
                <li><strong>CSS Class (any element)</strong> — Elementor → Advanced tab → CSS Classes → type <code><?php echo esc_html($opts['css_class']); ?></code></li>
                <li><strong>Shortcode</strong> — <code>[mpro_scramble]Your text here[/mpro_scramble]</code> — works inside any widget with shortcode support or in the WordPress editor.</li>
                <li><strong>Manual HTML</strong> — add class <code><?php echo esc_html($opts['css_class']); ?></code> to any element.</li>
            </ol>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}
