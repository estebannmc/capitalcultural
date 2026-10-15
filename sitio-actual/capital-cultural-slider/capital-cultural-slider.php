<?php
/**
 * Plugin Name: Capital Cultural Slider
 * Description: Slider principal administrable para Capital Cultural. Usar shortcode [capital_slider].
 * Version: 2.2.1
 * Author: Esteban
 * Text Domain: capital-cultural-slider
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Capital_Cultural_Slider {
    const VERSION = '2.2.1';
    const POST_TYPE = 'cc_slide';
    const NONCE = 'cc_slider_nonce';
    const NONCE_ACTION = 'cc_slider_save_meta';
    const ORDER_NONCE = 'cc_slider_order_nonce';
    const TRANSIENT_KEY = 'cc_slider_active_ids';

    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'save_meta'], 10, 2);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_shortcode('capital_slider', [$this, 'shortcode']);

        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'column_content'], 10, 2);
        add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', [$this, 'sortable_columns']);
        add_action('pre_get_posts', [$this, 'admin_order']);

        add_filter('post_row_actions', [$this, 'row_actions'], 10, 2);
        add_action('admin_action_cc_duplicate_slide', [$this, 'duplicate_slide']);
        add_action('wp_ajax_cc_slider_save_order', [$this, 'ajax_save_order']);

        add_action('before_delete_post', [$this, 'clear_cache']);
        add_action('trashed_post', [$this, 'clear_cache']);
        add_action('untrashed_post', [$this, 'clear_cache']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'clear_cache']);
    }

    public static function activate() {
        $self = new self();
        $self->register_post_type();
        flush_rewrite_rules();
        delete_transient(self::TRANSIENT_KEY);
    }

    public static function deactivate() {
        flush_rewrite_rules();
        delete_transient(self::TRANSIENT_KEY);
    }

    public function register_post_type() {
        $labels = [
            'name'               => 'Slider Capital Cultural',
            'singular_name'      => 'Slide',
            'menu_name'          => 'Slider Capital Cultural',
            'name_admin_bar'     => 'Slide',
            'add_new'            => 'Agregar nuevo',
            'add_new_item'       => 'Agregar nuevo slide',
            'new_item'           => 'Nuevo slide',
            'edit_item'          => 'Editar slide',
            'view_item'          => 'Ver slide',
            'all_items'          => 'Todos los slides',
            'search_items'       => 'Buscar slides',
            'not_found'          => 'No se encontraron slides',
            'not_found_in_trash' => 'No se encontraron slides en la papelera',
        ];

        register_post_type(self::POST_TYPE, [
            'labels'          => $labels,
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'menu_icon'       => 'dashicons-images-alt2',
            'supports'        => ['title', 'thumbnail', 'page-attributes'],
            'show_in_rest'    => true,
            'capability_type' => 'post',
            'hierarchical'    => false,
        ]);
    }

    public function admin_menu() {
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            'Ordenar slides',
            'Ordenar slides',
            'edit_posts',
            'cc-slider-order',
            [$this, 'render_order_page']
        );
    }

    public function add_meta_boxes() {
        add_meta_box('cc_slider_fields', 'Datos del slide', [$this, 'render_meta_box'], self::POST_TYPE, 'normal', 'high');
        add_meta_box('cc_slider_preview', 'Vista previa', [$this, 'render_preview_box'], self::POST_TYPE, 'side', 'default');
    }

    private function get_meta($post_id, $key, $default = '') {
        $value = get_post_meta($post_id, '_cc_' . $key, true);
        return $value !== '' ? $value : $default;
    }

    private function split_title_label($title) {
        if (strpos($title, '|') === false) {
            return [trim($title), ''];
        }

        $parts = array_map('trim', explode('|', $title, 2));
        return [$parts[0] ?: trim($title), $parts[1] ?? ''];
    }

    private function get_label_options($current_post_id = 0) {
        $slides = get_posts([
            'post_type'      => self::POST_TYPE,
            'post_status'    => ['publish', 'draft', 'pending', 'future'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'exclude'        => [$current_post_id],
            'no_found_rows'  => true,
        ]);

        $labels = [];
        foreach ($slides as $slide_id) {
            $label = trim((string) get_post_meta($slide_id, '_cc_label', true));
            if (!$label) {
                [, $label] = $this->split_title_label(get_the_title($slide_id));
            }
            if ($label) {
                $labels[$label] = $label;
            }
        }

        natcasesort($labels);
        return array_values($labels);
    }

    public function render_meta_box($post) {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE);

        $label = $this->get_meta($post->ID, 'label');
        if (!$label) {
            [, $label] = $this->split_title_label(get_the_title($post));
        }
        $label_options = $this->get_label_options($post->ID);
        $description = $this->get_meta($post->ID, 'description');
        $url = $this->get_meta($post->ID, 'url', '#');
        $active = $this->get_meta($post->ID, 'active', '1');
        $new_tab = $this->get_meta($post->ID, 'new_tab', '0');
        $start_date = $this->get_meta($post->ID, 'start_date');
        $end_date = $this->get_meta($post->ID, 'end_date');
        ?>
        <div class="cc-slider-admin-grid">
            <div class="cc-slider-admin-field">
                <label for="cc_label">Etiqueta</label>
                <input type="text" id="cc_label" name="cc_label" value="<?php echo esc_attr($label); ?>" list="cc_label_options" placeholder="Convocatoria">
                <datalist id="cc_label_options">
                    <?php foreach ($label_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <div class="cc-slider-admin-help">Escribí para buscar una existente o cargá una nueva. El título queda solo con el nombre.</div>
            </div>

            <div class="cc-slider-admin-field cc-slider-admin-full">
                <label for="cc_description">Texto descriptivo</label>
                <textarea id="cc_description" name="cc_description" placeholder="Texto que aparece debajo del título en el slider."><?php echo esc_textarea($description); ?></textarea>
                <div class="cc-slider-admin-help">Admite saltos de línea. Se muestra como texto blanco dentro de la placa del slide.</div>
            </div>

            <div class="cc-slider-admin-field">
                <label for="cc_url">URL del slide</label>
                <input type="text" id="cc_url" name="cc_url" value="<?php echo esc_attr($url); ?>" placeholder="/capitalcultural/.../">
                <div class="cc-slider-admin-help">Puede ser relativa, por ejemplo: /capitalcultural/nochedelosmuseos/</div>
            </div>

            <div class="cc-slider-admin-field">
                <label>Opciones</label>
                <div class="cc-slider-admin-checks">
                    <label><input type="checkbox" name="cc_active" value="1" <?php checked($active, '1'); ?>> Activo</label>
                    <label><input type="checkbox" name="cc_new_tab" value="1" <?php checked($new_tab, '1'); ?>> Abrir en nueva pestaña</label>
                </div>
            </div>

            <div class="cc-slider-admin-field">
                <label for="cc_start_date">Fecha de inicio</label>
                <input type="date" id="cc_start_date" name="cc_start_date" value="<?php echo esc_attr($start_date); ?>">
                <div class="cc-slider-admin-help">Opcional. Si se completa, el slide aparece desde esa fecha.</div>
            </div>

            <div class="cc-slider-admin-field">
                <label for="cc_end_date">Fecha de fin</label>
                <input type="date" id="cc_end_date" name="cc_end_date" value="<?php echo esc_attr($end_date); ?>">
                <div class="cc-slider-admin-help">Opcional. Si se completa, el slide se oculta después de esa fecha.</div>
            </div>

            <div class="cc-slider-admin-field cc-slider-admin-full cc-slider-note">
                <strong>Orden</strong>
                <span>Podés usar el campo “Orden” del panel lateral o ir a <strong>Slider Capital Cultural → Ordenar slides</strong> para arrastrarlos visualmente.</span>
            </div>
        </div>
        <?php
    }

    public function render_preview_box($post) {
        $title = get_the_title($post) ?: 'Título del slide';
        $label = $this->get_meta($post->ID, 'label');
        if (!$label) {
            [$title, $label] = $this->split_title_label($title);
        }
        $description = $this->get_meta($post->ID, 'description', 'Texto descriptivo del slide.');
        $image = get_the_post_thumbnail_url($post->ID, 'medium_large');
        if (!$image) {
            $image = plugin_dir_url(__FILE__) . 'assets/fallback.svg';
        }
        ?>
        <div class="cc-admin-preview">
            <img src="<?php echo esc_url($image); ?>" alt="">
            <div class="cc-admin-preview-content">
                <?php if ($label) : ?>
                    <span class="cc-admin-preview-label"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
                <strong><?php echo esc_html($title); ?></strong>
                <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($description), 18)); ?></p>
            </div>
        </div>
        <p class="cc-slider-admin-help">La vista final puede variar según el ancho de pantalla de la home.</p>
        <?php
    }

    public function save_meta($post_id, $post) {
        if (!isset($_POST[self::NONCE]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE])), self::NONCE_ACTION)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $description = isset($_POST['cc_description']) ? wp_kses_post(wp_unslash($_POST['cc_description'])) : '';
        $label = isset($_POST['cc_label']) ? sanitize_text_field(wp_unslash($_POST['cc_label'])) : '';
        $url = isset($_POST['cc_url']) ? sanitize_text_field(wp_unslash($_POST['cc_url'])) : '#';
        if ($url === '') {
            $url = '#';
        }
        if (strpos($url, '/') === 0) {
            $clean_url = $url;
        } else {
            $clean_url = esc_url_raw($url);
        }
        if ($clean_url === '') {
            $clean_url = '#';
        }

        $active = isset($_POST['cc_active']) ? '1' : '0';
        $new_tab = isset($_POST['cc_new_tab']) ? '1' : '0';
        $start_date = isset($_POST['cc_start_date']) ? sanitize_text_field(wp_unslash($_POST['cc_start_date'])) : '';
        $end_date = isset($_POST['cc_end_date']) ? sanitize_text_field(wp_unslash($_POST['cc_end_date'])) : '';

        if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            $start_date = '';
        }
        if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $end_date = '';
        }

        [$clean_title, $title_label] = $this->split_title_label($post->post_title);
        if (!$label && $title_label) {
            $label = $title_label;
        }
        if ($clean_title !== $post->post_title) {
            remove_action('save_post_' . self::POST_TYPE, [$this, 'save_meta'], 10);
            wp_update_post([
                'ID'         => $post_id,
                'post_title' => $clean_title,
            ]);
            add_action('save_post_' . self::POST_TYPE, [$this, 'save_meta'], 10, 2);
        }

        update_post_meta($post_id, '_cc_label', $label);
        update_post_meta($post_id, '_cc_description', $description);
        update_post_meta($post_id, '_cc_url', $clean_url);
        update_post_meta($post_id, '_cc_active', $active);
        update_post_meta($post_id, '_cc_new_tab', $new_tab);
        update_post_meta($post_id, '_cc_start_date', $start_date);
        update_post_meta($post_id, '_cc_end_date', $end_date);
        $this->clear_cache($post_id);
    }

    public function admin_assets($hook) {
        global $post_type;
        if ($post_type === self::POST_TYPE || $hook === self::POST_TYPE . '_page_cc-slider-order') {
            wp_enqueue_style('cc-slider-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', [], self::VERSION);
        }

        if ($hook === self::POST_TYPE . '_page_cc-slider-order') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('cc-slider-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery', 'jquery-ui-sortable'], self::VERSION, true);
            wp_localize_script('cc-slider-admin', 'ccSliderAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce(self::ORDER_NONCE),
            ]);
        }
    }

    public function frontend_assets() {
        wp_register_style('cc-slider', plugin_dir_url(__FILE__) . 'assets/slider.css', [], self::VERSION);
        wp_register_script('cc-slider', plugin_dir_url(__FILE__) . 'assets/slider.js', [], self::VERSION, true);
    }

    private function get_slide_ids($limit = -1) {
        $today = current_time('Y-m-d');
        $cache_key = self::TRANSIENT_KEY . '_' . md5((string) $limit . '_' . $today);
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $query = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => intval($limit),
            'fields'         => 'ids',
            'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_cc_active',
                    'value'   => '1',
                    'compare' => '=',
                ],
                [
                    'relation' => 'OR',
                    ['key' => '_cc_start_date', 'compare' => 'NOT EXISTS'],
                    ['key' => '_cc_start_date', 'value' => '', 'compare' => '='],
                    ['key' => '_cc_start_date', 'value' => $today, 'compare' => '<=', 'type' => 'DATE'],
                ],
                [
                    'relation' => 'OR',
                    ['key' => '_cc_end_date', 'compare' => 'NOT EXISTS'],
                    ['key' => '_cc_end_date', 'value' => '', 'compare' => '='],
                    ['key' => '_cc_end_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE'],
                ],
            ],
            'no_found_rows' => true,
        ]);

        $ids = array_map('intval', $query->posts);
        set_transient($cache_key, $ids, HOUR_IN_SECONDS);
        return $ids;
    }

    public function shortcode($atts) {
        $atts = shortcode_atts(['limit' => -1], $atts, 'capital_slider');
        $slide_ids = $this->get_slide_ids($atts['limit']);

        if (empty($slide_ids)) {
            if (current_user_can('edit_posts')) {
                return '<p class="cc-slider-empty">No hay slides activos publicados.</p>';
            }
            return '';
        }

        wp_enqueue_style('cc-slider');
        wp_enqueue_script('cc-slider');

        ob_start();
        ?>
        <div class="cc-slider" data-slider>
            <?php foreach ($slide_ids as $i => $post_id) :
                $title = get_the_title($post_id);
                $label = trim((string) get_post_meta($post_id, '_cc_label', true));
                if (!$label) {
                    [$title, $label] = $this->split_title_label($title);
                }
                $description = get_post_meta($post_id, '_cc_description', true);
                $url = get_post_meta($post_id, '_cc_url', true);
                $new_tab = get_post_meta($post_id, '_cc_new_tab', true);
                $image = get_the_post_thumbnail_url($post_id, 'full');
                if (!$image) {
                    $image = plugin_dir_url(__FILE__) . 'assets/fallback.svg';
                }
                $target = $new_tab === '1' ? ' target="_blank" rel="noopener noreferrer"' : '';
                ?>
                <div class="cc-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url($url ?: '#'); ?>"<?php echo $target; ?>>
                        <span class="cc-media">
                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
                        </span>
                        <div class="cc-overlay"></div>
                        <div class="cc-content">
                            <div class="cc-progress"></div>
                            <?php if ($label) : ?>
                                <div class="cc-label"><?php echo esc_html($label); ?></div>
                            <?php endif; ?>
                            <div class="cc-title"><?php echo esc_html($title); ?></div>
                            <?php if ($description) : ?>
                                <div class="cc-text"><?php echo wpautop(wp_kses_post($description)); ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <button class="cc-arrow cc-prev" type="button" aria-label="Slide anterior">&#10094;</button>
            <button class="cc-arrow cc-next" type="button" aria-label="Slide siguiente">&#10095;</button>
            <div class="cc-dots" aria-label="Navegación del slider"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = 'Título';
        $new['thumbnail'] = 'Imagen';
        $new['order'] = 'Orden';
        $new['active'] = 'Estado';
        $new['dates'] = 'Vigencia';
        $new['date'] = 'Fecha';
        return $new;
    }

    public function column_content($column, $post_id) {
        if ($column === 'thumbnail') {
            echo get_the_post_thumbnail($post_id, [80, 45]) ?: '—';
        }
        if ($column === 'order') {
            echo intval(get_post_field('menu_order', $post_id));
        }
        if ($column === 'active') {
            echo esc_html($this->slide_status_label($post_id));
        }
        if ($column === 'dates') {
            $start = get_post_meta($post_id, '_cc_start_date', true);
            $end = get_post_meta($post_id, '_cc_end_date', true);
            echo esc_html(($start ?: 'sin inicio') . ' / ' . ($end ?: 'sin fin'));
        }
    }

    private function slide_status_label($post_id) {
        if (get_post_meta($post_id, '_cc_active', true) !== '1') {
            return 'Inactivo';
        }
        $today = current_time('Y-m-d');
        $start = get_post_meta($post_id, '_cc_start_date', true);
        $end = get_post_meta($post_id, '_cc_end_date', true);
        if ($start && $start > $today) {
            return 'Programado';
        }
        if ($end && $end < $today) {
            return 'Vencido';
        }
        return 'Activo';
    }

    public function sortable_columns($columns) {
        $columns['order'] = 'menu_order';
        return $columns;
    }

    public function admin_order($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        if ($query->get('post_type') === self::POST_TYPE && !$query->get('orderby')) {
            $query->set('orderby', 'menu_order');
            $query->set('order', 'ASC');
        }
    }

    public function row_actions($actions, $post) {
        if ($post->post_type !== self::POST_TYPE || !current_user_can('edit_post', $post->ID)) {
            return $actions;
        }
        $url = wp_nonce_url(admin_url('admin.php?action=cc_duplicate_slide&post=' . $post->ID), 'cc_duplicate_slide_' . $post->ID);
        $actions['cc_duplicate'] = '<a href="' . esc_url($url) . '">Duplicar slide</a>';
        return $actions;
    }

    public function duplicate_slide() {
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        if (!$post_id || !current_user_can('edit_post', $post_id) || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'cc_duplicate_slide_' . $post_id)) {
            wp_die('No tenés permisos para duplicar este slide.');
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== self::POST_TYPE) {
            wp_die('Slide no encontrado.');
        }

        $new_id = wp_insert_post([
            'post_type'   => self::POST_TYPE,
            'post_status' => 'draft',
            'post_title'  => $post->post_title . ' — copia',
            'menu_order'  => intval($post->menu_order) + 1,
        ]);

        if ($new_id && !is_wp_error($new_id)) {
            foreach (['_cc_label', '_cc_description', '_cc_url', '_cc_active', '_cc_new_tab', '_cc_start_date', '_cc_end_date'] as $meta_key) {
                update_post_meta($new_id, $meta_key, get_post_meta($post_id, $meta_key, true));
            }
            $thumb_id = get_post_thumbnail_id($post_id);
            if ($thumb_id) {
                set_post_thumbnail($new_id, $thumb_id);
            }
        }

        wp_safe_redirect(admin_url('post.php?action=edit&post=' . absint($new_id)));
        exit;
    }

    public function render_order_page() {
        $slides = get_posts([
            'post_type'      => self::POST_TYPE,
            'post_status'    => ['publish', 'draft', 'pending', 'future'],
            'posts_per_page' => -1,
            'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
        ]);
        ?>
        <div class="wrap cc-slider-order-page">
            <h1>Ordenar slides</h1>
            <p>Arrastrá los slides para definir el orden de aparición en la home. Los cambios se guardan con el botón inferior.</p>
            <ul id="cc-slider-sortable" class="cc-slider-sortable">
                <?php foreach ($slides as $slide) :
                    $thumb = get_the_post_thumbnail($slide->ID, [96, 54]);
                    ?>
                    <li class="cc-slider-sortable-item" data-id="<?php echo esc_attr($slide->ID); ?>">
                        <span class="cc-drag-handle dashicons dashicons-menu" aria-hidden="true"></span>
                        <span class="cc-order-thumb"><?php echo $thumb ?: '<span class="cc-order-no-thumb">Sin imagen</span>'; ?></span>
                        <span class="cc-order-title"><?php echo esc_html(get_the_title($slide)); ?></span>
                        <span class="cc-order-status"><?php echo esc_html($this->slide_status_label($slide->ID)); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p>
                <button type="button" class="button button-primary" id="cc-slider-save-order">Guardar orden</button>
                <span id="cc-slider-order-feedback" aria-live="polite"></span>
            </p>
        </div>
        <?php
    }

    public function ajax_save_order() {
        check_ajax_referer(self::ORDER_NONCE, 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Sin permisos.'], 403);
        }

        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('absint', $_POST['ids']) : [];
        foreach ($ids as $order => $post_id) {
            if ($post_id && get_post_type($post_id) === self::POST_TYPE) {
                wp_update_post([
                    'ID'         => $post_id,
                    'menu_order' => $order + 1,
                ]);
            }
        }
        $this->clear_cache();
        wp_send_json_success(['message' => 'Orden guardado.']);
    }

    public function clear_cache($post_id = 0) {
        global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_' . self::TRANSIENT_KEY . '_%'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_' . self::TRANSIENT_KEY . '_%'));
    }
}

register_activation_hook(__FILE__, ['Capital_Cultural_Slider', 'activate']);
register_deactivation_hook(__FILE__, ['Capital_Cultural_Slider', 'deactivate']);
new Capital_Cultural_Slider();
