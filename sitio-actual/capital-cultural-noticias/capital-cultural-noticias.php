<?php
/**
 * Plugin Name: Capital Cultural Noticias
 * Description: Muestra las últimas noticias de una categoría de Santa Fe Noticias (santafenoticias.gob.ar) en Capital Cultural. Usar shortcode [capital_noticias].
 * Version: 1.3.0
 * Author: Esteban
 * Text Domain: capital-cultural-noticias
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Capital_Cultural_Noticias {
    const VERSION = '1.3.0';
    const OPTION = 'ccn_settings';
    const STATUS_OPTION = 'ccn_status';
    const LAST_GOOD_OPTION = 'ccn_last_good';
    const CATEGORY_OPTION = 'ccn_category';
    const TRANSIENT_KEY = 'ccn_posts';
    const AJAX_ACTION = 'ccn_noticias';
    const REFRESH_ACTION = 'ccn_refresh';
    const RETRY_MINUTES = 10;
    const MAX_POSTS = 20;
    const EXCERPT_CHARS = 200;

    private static $instance_count = 0;

    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_post_' . self::REFRESH_ACTION, [$this, 'handle_refresh']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'ajax_posts']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'ajax_posts']);
        add_shortcode('capital_noticias', [$this, 'shortcode']);
        add_action('update_option_' . self::OPTION, [$this, 'clear_cache']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'action_links']);
    }

    public function action_links($links) {
        array_unshift($links, '<a href="' . esc_url(admin_url('options-general.php?page=cc-noticias')) . '">Ajustes</a>');
        return $links;
    }

    public static function deactivate() {
        delete_transient(self::TRANSIENT_KEY);
    }

    public static function defaults() {
        return [
            'category_url'  => 'https://santafenoticias.gob.ar/categoria/cultura/',
            'cache_minutes' => 30,
        ];
    }

    private function settings() {
        $saved = get_option(self::OPTION, []);
        return wp_parse_args(is_array($saved) ? $saved : [], self::defaults());
    }

    /* ---------------------------------------------------------------------
     * Lectura de las noticias
     * ------------------------------------------------------------------- */

    /**
     * Devuelve las noticias cacheadas; si la caché venció, las vuelve a pedir.
     * Si el sitio de noticias no responde, usa la última lectura correcta.
     */
    public function get_posts($force = false) {
        if (!$force) {
            $cached = get_transient(self::TRANSIENT_KEY);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $settings = $this->settings();
        $result = $this->fetch_posts($settings['category_url']);

        if (is_wp_error($result)) {
            update_option(self::STATUS_OPTION, [
                'time'   => time(),
                'ok'     => false,
                'count'  => 0,
                'source' => '',
                'error'  => $result->get_error_message(),
            ], false);

            $last_good = get_option(self::LAST_GOOD_OPTION, []);
            $posts = isset($last_good['posts']) && is_array($last_good['posts']) ? $last_good['posts'] : [];
            set_transient(self::TRANSIENT_KEY, $posts, self::RETRY_MINUTES * MINUTE_IN_SECONDS);
            return $posts;
        }

        update_option(self::STATUS_OPTION, [
            'time'   => time(),
            'ok'     => true,
            'count'  => count($result['posts']),
            'source' => $result['source'],
            'error'  => '',
        ], false);
        update_option(self::LAST_GOOD_OPTION, ['time' => time(), 'posts' => $result['posts']], false);

        $minutes = max(5, intval($settings['cache_minutes']));
        set_transient(self::TRANSIENT_KEY, $result['posts'], $minutes * MINUTE_IN_SECONDS);
        return $result['posts'];
    }

    /**
     * Primero la API de WordPress del sitio de noticias (trae la imagen
     * destacada); si falla, el RSS de la categoría.
     */
    private function fetch_posts($category_url) {
        $parsed = $this->parse_category_url($category_url);
        if (!$parsed) {
            return new WP_Error('ccn_url', 'La dirección de la categoría no es válida. Tiene que ser como https://santafenoticias.gob.ar/categoria/cultura/.');
        }

        $posts = $this->fetch_rest($parsed);
        if (!is_wp_error($posts)) {
            return ['posts' => $posts, 'source' => 'api'];
        }

        $feed = $this->fetch_rss(trailingslashit($category_url) . 'feed/');
        if (!is_wp_error($feed)) {
            return ['posts' => $feed, 'source' => 'rss'];
        }

        return new WP_Error('ccn_fetch', $posts->get_error_message() . ' Tampoco respondió el RSS: ' . $feed->get_error_message());
    }

    /**
     * "https://santafenoticias.gob.ar/categoria/cultura/" => base del sitio y slug "cultura".
     */
    private function parse_category_url($url) {
        $parts = wp_parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
            return null;
        }
        $segments = array_values(array_filter(explode('/', $parts['path'] ?? '')));
        if (!$segments) {
            return null;
        }
        return [
            'base' => $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''),
            'slug' => sanitize_title(end($segments)),
        ];
    }

    private function remote_json($url) {
        $response = wp_remote_get($url, [
            'timeout'    => 12,
            'user-agent' => 'Capital Cultural Noticias/' . self::VERSION . '; ' . home_url('/'),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('ccn_http', 'No se pudo conectar con el sitio de noticias: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return new WP_Error('ccn_http', 'El sitio de noticias respondió con el código ' . intval($code) . '.');
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data)) {
            return new WP_Error('ccn_format', 'La respuesta del sitio de noticias no es válida.');
        }
        return $data;
    }

    /**
     * El ID de la categoría se guarda para no pedirlo en cada lectura.
     */
    private function category_id($parsed) {
        $key = $parsed['base'] . '|' . $parsed['slug'];
        $stored = get_option(self::CATEGORY_OPTION, []);
        if (isset($stored['key'], $stored['id']) && $stored['key'] === $key) {
            return intval($stored['id']);
        }

        $data = $this->remote_json(add_query_arg([
            'slug'    => $parsed['slug'],
            '_fields' => 'id,name',
        ], $parsed['base'] . '/wp-json/wp/v2/categories'));

        if (is_wp_error($data)) {
            return $data;
        }
        if (empty($data[0]['id'])) {
            return new WP_Error('ccn_category', 'No existe la categoría «' . $parsed['slug'] . '» en el sitio de noticias.');
        }

        update_option(self::CATEGORY_OPTION, ['key' => $key, 'id' => intval($data[0]['id']), 'name' => sanitize_text_field($data[0]['name'] ?? '')], false);
        return intval($data[0]['id']);
    }

    private function fetch_rest($parsed) {
        $category = $this->category_id($parsed);
        if (is_wp_error($category)) {
            return $category;
        }

        $data = $this->remote_json(add_query_arg([
            'categories' => $category,
            'per_page'   => self::MAX_POSTS,
            '_embed'     => 'wp:featuredmedia',
            '_fields'    => 'id,date_gmt,link,title,excerpt,_links,_embedded',
        ], $parsed['base'] . '/wp-json/wp/v2/posts'));

        if (is_wp_error($data)) {
            return $data;
        }

        $posts = [];
        foreach ($data as $item) {
            $url = $this->clean_url($item['link'] ?? '');
            $title = $this->clean_text($item['title']['rendered'] ?? '');
            if (!$url || $title === '') {
                continue;
            }
            $posts[] = [
                'id'      => intval($item['id'] ?? 0),
                'title'   => $title,
                'url'     => $url,
                'date'    => !empty($item['date_gmt']) ? strtotime($item['date_gmt'] . ' UTC') : 0,
                'excerpt' => $this->excerpt($item['excerpt']['rendered'] ?? ''),
                'image'   => $this->featured_image($item),
            ];
        }
        return $posts;
    }

    /**
     * La imagen destacada en tamaño mediano; algunas notas la tienen privada
     * y la API devuelve un error en su lugar.
     */
    private function featured_image($item) {
        $media = $item['_embedded']['wp:featuredmedia'][0] ?? null;
        if (!is_array($media) || !empty($media['code'])) {
            return '';
        }
        $sizes = $media['media_details']['sizes'] ?? [];
        foreach (['medium_large', 'large', 'medium'] as $size) {
            if (!empty($sizes[$size]['source_url'])) {
                return $this->clean_url($sizes[$size]['source_url']);
            }
        }
        return $this->clean_url($media['source_url'] ?? '');
    }

    private function fetch_rss($url) {
        if (!function_exists('simplexml_load_string')) {
            return new WP_Error('ccn_rss', 'El servidor no puede leer RSS.');
        }

        $response = wp_remote_get($url, [
            'timeout'    => 12,
            'user-agent' => 'Capital Cultural Noticias/' . self::VERSION . '; ' . home_url('/'),
        ]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('ccn_rss', 'no se pudo leer ' . $url . '.');
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string(wp_remote_retrieve_body($response));
        libxml_use_internal_errors($previous);
        if (!$xml || empty($xml->channel->item)) {
            return new WP_Error('ccn_rss', 'el RSS no tiene noticias.');
        }

        $posts = [];
        foreach ($xml->channel->item as $item) {
            $content = (string) $item->children('http://purl.org/rss/1.0/modules/content/')->encoded;
            $image = '';
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
                $image = $this->clean_url(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
            }
            $url = $this->clean_url((string) $item->link);
            $title = $this->clean_text((string) $item->title);
            if (!$url || $title === '') {
                continue;
            }
            $posts[] = [
                'id'      => crc32($url),
                'title'   => $title,
                'url'     => $url,
                'date'    => strtotime((string) $item->pubDate) ?: 0,
                'excerpt' => $this->excerpt((string) $item->description),
                'image'   => $image,
            ];
            if (count($posts) >= self::MAX_POSTS) {
                break;
            }
        }
        return $posts;
    }

    private function clean_url($url) {
        $url = trim($url);
        return preg_match('#^https?://#i', $url) ? esc_url_raw($url) : '';
    }

    private function clean_text($html) {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES, 'UTF-8')));
    }

    /**
     * Texto plano cortado en una palabra. Santa Fe Noticias recorta los
     * resúmenes a mitad de frase sin marcarlo: si no terminan en un signo de
     * puntuación, se les agrega "…".
     */
    private function excerpt($html) {
        $text = trim(preg_replace('/\s*(\[(…|&hellip;|\.\.\.)\]|Leer más.*)$/u', '', $this->clean_text($html)));
        if (mb_strlen($text) <= self::EXCERPT_CHARS) {
            return ($text === '' || preg_match('/[.!?…"”»)]$/u', $text)) ? $text : rtrim($text, " ,;:") . '…';
        }
        $cut = mb_substr($text, 0, self::EXCERPT_CHARS);
        $space = mb_strrpos($cut, ' ');
        return rtrim(mb_substr($cut, 0, $space ?: self::EXCERPT_CHARS), " ,.;:") . '…';
    }

    public function clear_cache() {
        delete_transient(self::TRANSIENT_KEY);
    }

    /* ---------------------------------------------------------------------
     * Frontend
     * ------------------------------------------------------------------- */

    public function frontend_assets() {
        wp_register_style('cc-noticias', plugin_dir_url(__FILE__) . 'assets/noticias.css', [], self::VERSION);
        wp_register_script('cc-noticias', plugin_dir_url(__FILE__) . 'assets/noticias.js', [], self::VERSION, true);
    }

    /**
     * Las noticias se piden por admin-ajax para que la caché de página
     * (LiteSpeed) no deje el bloque desactualizado.
     */
    public function ajax_posts() {
        $posts = $this->get_posts();
        $status = get_option(self::STATUS_OPTION, []);

        // admin-ajax envía cabeceras sin caché; se permite al navegador reusar la respuesta 5 minutos.
        header('Cache-Control: public, max-age=300');

        wp_send_json([
            'ok'     => !empty($posts) || !empty($status['ok']),
            'now'    => time(),
            'offset' => wp_timezone()->getOffset(new DateTime('now')),
            'posts'  => $posts,
        ]);
    }

    public static function shortcode_defaults() {
        return [
            'titulo'   => 'Noticias de cultura',
            'cantidad' => 6,
            'paginas'  => 3,
            'imagenes' => 'si',
            'resumen'  => 'si',
            'boton'    => 'Ver todas las noticias',
        ];
    }

    public function shortcode($atts) {
        $atts = shortcode_atts(self::shortcode_defaults(), $atts, 'capital_noticias');

        wp_enqueue_style('cc-noticias');
        wp_enqueue_script('cc-noticias');

        return $this->render_block($atts);
    }

    /**
     * Noticias por página, en tres columnas: una fila (3) o dos (6).
     */
    private static function grid_size($cantidad) {
        return intval($cantidad) > 3 ? 6 : 3;
    }

    /**
     * Entre 1 y 3 páginas; con 6 por página son 18 noticias, dentro de las 20 que se leen.
     */
    private static function page_count($paginas) {
        return min(3, max(1, intval($paginas)));
    }

    private function render_block($atts) {
        $settings = $this->settings();
        $size = self::grid_size($atts['cantidad']);
        $pages = self::page_count($atts['paginas']);

        self::$instance_count++;
        $title_id = 'ccn-title-' . self::$instance_count;
        $more_url = $settings['category_url'];
        $has_button = $more_url && $atts['boton'] !== '';

        ob_start();
        ?>
        <section class="ccn-noticias" data-ccn-noticias
            data-endpoint="<?php echo esc_url(add_query_arg('action', self::AJAX_ACTION, admin_url('admin-ajax.php'))); ?>"
            data-limit="<?php echo esc_attr($size); ?>"
            data-pages="<?php echo esc_attr($pages); ?>"
            data-images="<?php echo $atts['imagenes'] === 'no' ? '0' : '1'; ?>"
            data-excerpt="<?php echo $atts['resumen'] === 'no' ? '0' : '1'; ?>"
            <?php echo $atts['titulo'] !== '' ? 'aria-labelledby="' . esc_attr($title_id) . '"' : 'aria-label="Noticias"'; ?>>
            <div class="ccn-head">
                <?php if ($atts['titulo'] !== '') : ?>
                    <h2 class="ccn-title" id="<?php echo esc_attr($title_id); ?>"><span class="ccn-barra" aria-hidden="true"></span><span class="ccn-title-text"><?php echo esc_html($atts['titulo']); ?></span></h2>
                <?php endif; ?>
                <div class="ccn-actions">
                    <div class="ccn-pager" hidden>
                        <button type="button" class="ccn-arrow ccn-prev" aria-label="Noticias anteriores"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                        <button type="button" class="ccn-arrow ccn-next" aria-label="Noticias siguientes"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                        <span class="screen-reader-text ccn-status" aria-live="polite"></span>
                    </div>
                    <?php if ($has_button) : ?>
                        <a class="ccn-more" href="<?php echo esc_url($more_url); ?>" target="_blank" rel="noopener">
                            <span class="ccn-barra" aria-hidden="true"></span>
                            <span class="ccn-more-label"><?php echo esc_html($atts['boton']); ?></span>
                            <svg class="ccn-external" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true" focusable="false"><path d="M5 11 11 5M6 5h5v5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="screen-reader-text">(se abre en una pestaña nueva)</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ccn-pages" aria-busy="true">
                <ul class="ccn-list">
                    <?php for ($i = 0; $i < $size; $i++) : ?>
                        <li class="ccn-item ccn-skeleton" aria-hidden="true"><?php echo $atts['imagenes'] === 'no' ? '<span class="ccn-card ccn-card--text">' : '<span class="ccn-card"><span class="ccn-media"></span>'; ?><span class="ccn-body"><span></span><span></span><span></span></span></span></li>
                    <?php endfor; ?>
                </ul>
            </div>

            <p class="ccn-message" role="status" hidden></p>
        </section>
        <?php
        return ob_get_clean();
    }

    /* ---------------------------------------------------------------------
     * Administración
     * ------------------------------------------------------------------- */

    public function admin_menu() {
        add_options_page('Noticias de cultura', 'Noticias de cultura', 'manage_options', 'cc-noticias', [$this, 'render_settings_page']);
    }

    public function admin_assets($hook) {
        if ($hook !== 'settings_page_cc-noticias') {
            return;
        }
        $this->frontend_assets();
        wp_enqueue_style('cc-noticias');
        wp_enqueue_script('cc-noticias');
        wp_enqueue_style('cc-noticias-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', ['cc-noticias'], self::VERSION);
        wp_enqueue_script('cc-noticias-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['cc-noticias'], self::VERSION, true);
    }

    public function register_settings() {
        register_setting('cc_noticias', self::OPTION, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => self::defaults(),
        ]);
    }

    public function sanitize_settings($input) {
        $defaults = self::defaults();
        $input = is_array($input) ? $input : [];

        $category_url = isset($input['category_url']) ? esc_url_raw(trim($input['category_url'])) : '';
        $minutes = isset($input['cache_minutes']) ? absint($input['cache_minutes']) : $defaults['cache_minutes'];

        return [
            'category_url'  => $category_url ? trailingslashit($category_url) : $defaults['category_url'],
            'cache_minutes' => min(1440, max(5, $minutes)),
        ];
    }

    public function handle_refresh() {
        if (!current_user_can('manage_options') || !check_admin_referer(self::REFRESH_ACTION)) {
            wp_die('No tenés permisos para actualizar las noticias.');
        }
        $this->get_posts(true);
        wp_safe_redirect(admin_url('options-general.php?page=cc-noticias&ccn_refreshed=1'));
        exit;
    }

    public function render_settings_page() {
        $settings = $this->settings();
        $status = get_option(self::STATUS_OPTION, []);
        $defaults = self::shortcode_defaults();
        $last_good = get_option(self::LAST_GOOD_OPTION, []);
        $stored_posts = isset($last_good['posts']) && is_array($last_good['posts']) ? count($last_good['posts']) : 0;
        $sources = ['api' => 'la API del sitio', 'rss' => 'el RSS (sin la API)'];
        ?>
        <div class="wrap ccn-admin">
            <h1>Noticias de cultura</h1>
            <p class="ccn-admin-intro">Muestra las últimas noticias de una categoría de Santa Fe Noticias en Capital Cultural. Las noticias se publican y editan allá; acá solo elegís cómo se ven.</p>

            <?php if (isset($_GET['ccn_refreshed'])) : ?>
                <div class="notice notice-info is-dismissible"><p>Se volvieron a leer las noticias. Revisá el estado más abajo.</p></div>
            <?php endif; ?>

            <div class="ccn-admin-panel">
                <h2>Armá el shortcode</h2>
                <p>Elegí las opciones, copiá el shortcode y pegalo en un widget «Shortcode» de Elementor.</p>

                <form id="ccn-generator" class="ccn-generator" data-defaults="<?php echo esc_attr(wp_json_encode($defaults)); ?>" onsubmit="return false;">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="ccn_g_titulo">Título del bloque</label></th>
                            <td>
                                <input type="text" class="regular-text" id="ccn_g_titulo" value="<?php echo esc_attr($defaults['titulo']); ?>">
                                <p class="description">Dejalo vacío para no mostrar título.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ccn_g_cantidad">Noticias por página</label></th>
                            <td>
                                <select id="ccn_g_cantidad">
                                    <option value="6" <?php selected(self::grid_size($defaults['cantidad']), 6); ?>>6 · dos filas de tres</option>
                                    <option value="3" <?php selected(self::grid_size($defaults['cantidad']), 3); ?>>3 · una fila</option>
                                </select>
                                <p class="description">En tablet pasan a dos columnas y en el celular a una.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ccn_g_paginas">Páginas</label></th>
                            <td>
                                <select id="ccn_g_paginas">
                                    <option value="3" <?php selected(self::page_count($defaults['paginas']), 3); ?>>3 páginas</option>
                                    <option value="2" <?php selected(self::page_count($defaults['paginas']), 2); ?>>2 páginas</option>
                                    <option value="1" <?php selected(self::page_count($defaults['paginas']), 1); ?>>1 página, sin flechas</option>
                                </select>
                                <p class="description">Las flechas junto al botón pasan de una página a otra con un fundido.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Mostrar</th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">Partes del bloque</legend>
                                    <label class="ccn-check"><input type="checkbox" id="ccn_g_imagenes" <?php checked($defaults['imagenes'], 'si'); ?>> Imágenes de las noticias</label>
                                    <label class="ccn-check"><input type="checkbox" id="ccn_g_resumen" <?php checked($defaults['resumen'], 'si'); ?>> Resumen de cada noticia</label>
                                    <label class="ccn-check"><input type="checkbox" id="ccn_g_boton_on" <?php checked($defaults['boton'] !== ''); ?>> Botón a todas las noticias</label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ccn_g_boton">Texto del botón</label></th>
                            <td><input type="text" class="regular-text" id="ccn_g_boton" value="<?php echo esc_attr($defaults['boton']); ?>"></td>
                        </tr>
                    </table>

                    <div class="ccn-output">
                        <label for="ccn_g_output">Tu shortcode</label>
                        <div class="ccn-output-row">
                            <input type="text" id="ccn_g_output" class="large-text code" readonly value="[capital_noticias]">
                            <button type="button" class="button button-primary" id="ccn_g_copy">Copiar shortcode</button>
                        </div>
                        <p class="ccn-copy-feedback" id="ccn_g_feedback" role="status"></p>
                    </div>
                </form>

                <h3>Vista previa</h3>
                <p class="description">Con las noticias reales. En la página toma el ancho de la columna donde lo pegues.</p>
                <div class="ccn-preview" id="ccn-preview"></div>
                <template id="ccn-preview-template"><?php echo $this->render_block($defaults); ?></template>
            </div>

            <div class="ccn-admin-panel">
                <h2>Estado de la conexión</h2>
                <table class="widefat striped ccn-status">
                    <tbody>
                        <tr>
                            <th scope="row">Última lectura</th>
                            <td><?php echo !empty($status['time']) ? esc_html(wp_date('j/m/Y H:i', $status['time'])) : 'Todavía no se leyeron las noticias.'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Resultado</th>
                            <td>
                                <?php if (empty($status)) : ?>
                                    —
                                <?php elseif (!empty($status['ok'])) : ?>
                                    Correcto: <?php echo intval($status['count']); ?> noticias, leídas desde <?php echo esc_html($sources[$status['source']] ?? 'el sitio'); ?>.
                                <?php else : ?>
                                    <strong>Error:</strong> <?php echo esc_html($status['error']); ?>
                                    <?php if ($stored_posts) : ?>
                                        Se siguen mostrando las <?php echo intval($stored_posts); ?> noticias de la última lectura correcta.
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ccn-refresh">
                    <input type="hidden" name="action" value="<?php echo esc_attr(self::REFRESH_ACTION); ?>">
                    <?php wp_nonce_field(self::REFRESH_ACTION); ?>
                    <?php submit_button('Actualizar ahora', 'secondary', 'submit', false); ?>
                </form>
            </div>

            <div class="ccn-admin-panel">
                <h2>Conexión con las noticias</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('cc_noticias'); ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="ccn_category_url">Página de la categoría</label></th>
                            <td>
                                <input type="url" class="regular-text" id="ccn_category_url" name="<?php echo esc_attr(self::OPTION); ?>[category_url]" value="<?php echo esc_attr($settings['category_url']); ?>">
                                <p class="description">Copiala del navegador, por ejemplo https://santafenoticias.gob.ar/categoria/cultura/. También es el destino del botón.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ccn_cache_minutes">Actualizar cada</label></th>
                            <td>
                                <input type="number" min="5" max="1440" step="5" class="small-text" id="ccn_cache_minutes" name="<?php echo esc_attr(self::OPTION); ?>[cache_minutes]" value="<?php echo esc_attr($settings['cache_minutes']); ?>"> minutos
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Guardar ajustes'); ?>
                </form>
            </div>
        </div>
        <?php
    }
}

register_deactivation_hook(__FILE__, ['Capital_Cultural_Noticias', 'deactivate']);
new Capital_Cultural_Noticias();
