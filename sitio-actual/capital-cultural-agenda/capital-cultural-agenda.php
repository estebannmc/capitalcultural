<?php
/**
 * Plugin Name: Capital Cultural Agenda
 * Description: Muestra los eventos de la agenda institucional (agenda.santafeciudad.gov.ar) en Capital Cultural. Usar shortcode [capital_agenda].
 * Version: 1.6.0
 * Author: Esteban
 * Text Domain: capital-cultural-agenda
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Capital_Cultural_Agenda {
    const VERSION = '1.6.0';
    const OPTION = 'cca_settings';
    const STATUS_OPTION = 'cca_status';
    const LAST_GOOD_OPTION = 'cca_last_good';
    const TRANSIENT_KEY = 'cca_events';
    const AJAX_ACTION = 'cca_eventos';
    const REFRESH_ACTION = 'cca_refresh';
    const CRON_HOOK = 'cca_cron_refresh';
    const HOME_CATEGORY = 'Cultura';
    const PROPUESTAS_KEY = 'cca_propuestas';
    const RETRY_MINUTES = 10;
    const MAX_EVENTS = 60;

    private static $instance_count = 0;

    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_post_' . self::REFRESH_ACTION, [$this, 'handle_refresh']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'ajax_events']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'ajax_events']);
        add_shortcode('capital_agenda', [$this, 'shortcode']);
        add_action('update_option_' . self::OPTION, [$this, 'clear_cache']);
        add_filter('cron_schedules', [$this, 'cron_schedules']);
        add_action('init', [$this, 'schedule_refresh']);
        add_action(self::CRON_HOOK, [$this, 'cron_refresh']);
        add_action('save_post_cc_propuesta', [$this, 'clear_propuestas']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'action_links']);
    }

    public function action_links($links) {
        array_unshift($links, '<a href="' . esc_url(admin_url('options-general.php?page=cc-agenda')) . '">Ajustes</a>');
        return $links;
    }

    public static function deactivate() {
        delete_transient(self::TRANSIENT_KEY);
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    public static function defaults() {
        return [
            'feed_url'      => 'https://agenda.santafeciudad.gov.ar/events.ics',
            'agenda_url'    => 'https://agenda.santafeciudad.gov.ar/',
            'cache_minutes' => 60,
            'ocultos'       => [],
            'mostrar'       => [],
        ];
    }

    private function settings() {
        $saved = get_option(self::OPTION, []);
        return wp_parse_args(is_array($saved) ? $saved : [], self::defaults());
    }

    /* ---------------------------------------------------------------------
     * Lectura del calendario
     * ------------------------------------------------------------------- */

    /**
     * Devuelve los eventos cacheados; si la caché venció, vuelve a leer el
     * iCal. Si la agenda no responde, usa la última lectura correcta.
     */
    public function get_events($force = false) {
        if (!$force) {
            $cached = get_transient(self::TRANSIENT_KEY);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $settings = $this->settings();
        $result = $this->fetch_events($settings['feed_url']);

        if (is_wp_error($result)) {
            update_option(self::STATUS_OPTION, [
                'time'  => time(),
                'ok'    => false,
                'count' => 0,
                'error' => $result->get_error_message(),
            ], false);

            $last_good = get_option(self::LAST_GOOD_OPTION, []);
            $events = isset($last_good['events']) && is_array($last_good['events']) ? $last_good['events'] : [];
            set_transient(self::TRANSIENT_KEY, $events, self::RETRY_MINUTES * MINUTE_IN_SECONDS);
            return $events;
        }

        update_option(self::STATUS_OPTION, [
            'time'  => time(),
            'ok'    => true,
            'count' => count($result),
            'error' => '',
        ], false);
        update_option(self::LAST_GOOD_OPTION, ['time' => time(), 'events' => $result], false);

        $minutes = max(5, intval($settings['cache_minutes']));
        // Dura el doble del intervalo: el cron la renueva antes de que venza.
        set_transient(self::TRANSIENT_KEY, $result, 2 * $minutes * MINUTE_IN_SECONDS);
        return $result;
    }

    private function fetch_events($url) {
        $response = wp_remote_get($url, [
            'timeout'    => 10,
            'user-agent' => 'Capital Cultural Agenda/' . self::VERSION . '; ' . home_url('/'),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('cca_http', 'No se pudo conectar con la agenda: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            return new WP_Error('cca_http', 'La agenda respondió con el código ' . intval($code) . '.');
        }
        if (strpos($body, 'BEGIN:VCALENDAR') === false) {
            return new WP_Error('cca_format', 'La respuesta de la agenda no es un calendario iCal válido.');
        }

        return $this->parse_ics($body);
    }

    /**
     * Convierte el iCal de Events Manager en un arreglo de eventos.
     * Solo conserva los que todavía no terminaron.
     */
    public function parse_ics($ics) {
        $ics = str_replace(["\r\n", "\r"], "\n", $ics);
        // Las líneas largas del iCal continúan en la siguiente con un espacio inicial.
        $ics = preg_replace("/\n[ \t]/", '', $ics);

        if (!preg_match_all('/^BEGIN:VEVENT$(.*?)^END:VEVENT$/ms', $ics, $matches)) {
            return [];
        }

        $calendar_tz = $this->calendar_timezone($ics);
        $now = time();
        $events = [];

        foreach ($matches[1] as $block) {
            $props = $this->parse_properties($block);
            if (empty($props['SUMMARY']) || empty($props['DTSTART'])) {
                continue;
            }

            $start = $this->parse_date($props['DTSTART'][0], $calendar_tz);
            if (!$start) {
                continue;
            }

            $end = !empty($props['DTEND']) ? $this->parse_date($props['DTEND'][0], $calendar_tz) : null;
            // Algunos eventos de la agenda tienen la misma hora de inicio y de fin.
            if ($end && $end['ts'] > $start['ts']) {
                $end_ts = $end['ts'];
            } elseif ($start['all_day']) {
                $end_ts = $start['ts'] + DAY_IN_SECONDS;
            } else {
                $end_ts = $start['ts'] + 2 * HOUR_IN_SECONDS;
            }

            if ($end_ts <= $now) {
                continue;
            }

            [$title, $kicker] = $this->split_title($this->unescape($props['SUMMARY'][0]['value']));

            $categories = [];
            foreach ($props['CATEGORIES'] ?? [] as $prop) {
                foreach (preg_split('/(?<!\\\\),/', $prop['value']) as $category) {
                    $category = trim($this->unescape($category));
                    if ($category !== '') {
                        $categories[] = $category;
                    }
                }
            }

            // "Sala Marechal, San Martín 2020, SANTA FE, ..." => "Sala Marechal".
            $venue = '';
            if (!empty($props['LOCATION'])) {
                $parts = preg_split('/[,;]/', $this->unescape($props['LOCATION'][0]['value']));
                $venue = trim($parts[0]);
            }

            $image = '';
            foreach ($props['ATTACH'] ?? [] as $prop) {
                $is_image = stripos($prop['params'], 'FMTTYPE=image/') !== false
                    || preg_match('/\.(jpe?g|png|webp|gif|avif)(\?.*)?$/i', $prop['value']);
                if ($is_image && $this->clean_url($prop['value'])) {
                    $image = $this->clean_url($prop['value']);
                    break;
                }
            }

            $events[] = [
                'id'         => !empty($props['UID']) ? $props['UID'][0]['value'] : md5($title . $start['ts']),
                'title'      => $title,
                'kicker'     => $kicker,
                'url'        => !empty($props['URL']) ? $this->clean_url($props['URL'][0]['value']) : '',
                'image'      => $image,
                'venue'      => $venue,
                'categories' => array_values(array_unique($categories)),
                'start'      => $start['ts'],
                'end'        => $end_ts,
                'allDay'     => $start['all_day'],
            ];
        }

        usort($events, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        return array_slice($events, 0, self::MAX_EVENTS);
    }

    private function parse_properties($block) {
        $props = [];
        foreach (explode("\n", trim($block)) as $line) {
            // NOMBRE;PARAM=valor;PARAM="valor:con:dos:puntos":VALOR
            if (!preg_match('/^([A-Za-z0-9-]+)((?:;[^:;"]+=(?:"[^"]*"|[^:;"]*))*):(.*)$/', $line, $m)) {
                continue;
            }
            $props[strtoupper($m[1])][] = ['params' => $m[2], 'value' => $m[3]];
        }
        return $props;
    }

    private function calendar_timezone($ics) {
        if (preg_match('/^X-WR-TIMEZONE:(.+)$/m', $ics, $m)) {
            try {
                return new DateTimeZone(trim($m[1]));
            } catch (Exception $e) {
                // Se usa la zona horaria del sitio.
            }
        }
        return wp_timezone();
    }

    private function parse_date($prop, $default_tz) {
        $value = trim($prop['value']);
        $tz = $default_tz;

        if (preg_match('/;TZID=("?)([^";:]+)\1/', $prop['params'], $m)) {
            try {
                $tz = new DateTimeZone($m[2]);
            } catch (Exception $e) {
                $tz = $default_tz;
            }
        }

        if (preg_match('/^\d{8}$/', $value)) {
            $date = DateTimeImmutable::createFromFormat('!Ymd', $value, $tz);
            return $date ? ['ts' => $date->getTimestamp(), 'all_day' => true] : null;
        }
        if (preg_match('/^(\d{8}T\d{6})Z$/', $value, $m)) {
            $date = DateTimeImmutable::createFromFormat('Ymd\THis', $m[1], new DateTimeZone('UTC'));
            return $date ? ['ts' => $date->getTimestamp(), 'all_day' => false] : null;
        }
        if (preg_match('/^\d{8}T\d{6}$/', $value)) {
            $date = DateTimeImmutable::createFromFormat('Ymd\THis', $value, $tz);
            return $date ? ['ts' => $date->getTimestamp(), 'all_day' => false] : null;
        }
        return null;
    }

    private function clean_url($url) {
        $url = trim($url);
        return preg_match('#^https?://#i', $url) ? esc_url_raw($url) : '';
    }

    private function unescape($text) {
        $text = preg_replace_callback('/\\\\([\\\\;,nN])/', function ($m) {
            return strtolower($m[1]) === 'n' ? "\n" : $m[1];
        }, $text);
        return trim(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * "Hábitat | Muestra" => ['Hábitat', 'Muestra'].
     */
    private function split_title($summary) {
        $parts = array_map('trim', explode('|', $summary));
        if (count($parts) > 1) {
            $kicker = array_pop($parts);
            if ($kicker !== '' && mb_strlen($kicker) <= 40) {
                return [implode(' | ', $parts), $kicker];
            }
            $parts[] = $kicker;
        }
        return [implode(' | ', $parts), ''];
    }

    /**
     * Al guardar los ajustes: se borra la caché y se reprograma el cron con el
     * intervalo nuevo (lo vuelve a programar schedule_refresh en el próximo init).
     */
    public function clear_cache() {
        delete_transient(self::TRANSIENT_KEY);
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /* ---------------------------------------------------------------------
     * Renovación automática: WP-Cron vuelve a leer la agenda cada «Actualizar
     * cada» minutos, antes de que venza la caché, y ningún visitante espera
     * la lectura del iCal.
     * ------------------------------------------------------------------- */

    public function cron_schedules($schedules) {
        $schedules['cca_intervalo'] = [
            'interval' => max(5, intval($this->settings()['cache_minutes'])) * MINUTE_IN_SECONDS,
            'display'  => 'Agenda cultural',
        ];
        return $schedules;
    }

    public function schedule_refresh() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'cca_intervalo', self::CRON_HOOK);
        }
    }

    public function cron_refresh() {
        $this->get_events(true);
    }

    /**
     * Saca los eventos que ya terminaron y marca con «inicio» los que van en la
     * home (ver on_home). Los de Cultura que se sacaron no van a ningún lado.
     */
    private function visible_events($events) {
        $settings = $this->settings();
        $propuestas = $this->propuestas();
        $now = time();
        $out = [];
        foreach ($events as $event) {
            if ($event['end'] <= $now) {
                continue;
            }
            $event['inicio'] = $this->on_home($event, $settings);
            $event['cc_url'] = $propuestas[$this->normalize($event['title'])] ?? '';
            if (!$event['inicio'] && $this->is_home_category($event)) {
                continue;
            }
            $out[] = $event;
        }
        return $out;
    }

    private function is_home_category($event) {
        return in_array(self::HOME_CATEGORY, $event['categories'], true);
    }

    /**
     * Los de Cultura van en el inicio salvo que estén en «ocultos»; los de otras
     * categorías, sólo si están en «mostrar». Se reconocen por dirección o título.
     */
    private function on_home($event, $settings) {
        $keys = [$this->event_key($event), $this->normalize($event['title'])];
        if ($this->is_home_category($event)) {
            return !array_intersect($keys, $this->key_list($settings['ocultos']));
        }
        return (bool) array_intersect($keys, $this->key_list($settings['mostrar']));
    }

    /**
     * Las propuestas del plugin de Programación, por título normalizado: la dirección de
     * su ficha, que es la página del espacio con #ccp-propuesta-ID (esa ancla abre la
     * ficha). Si hay dos con el mismo título, gana la más nueva. Se guarda una hora y se
     * borra cuando se guarda una propuesta.
     */
    private function propuestas() {
        $map = get_transient(self::PROPUESTAS_KEY);
        if (is_array($map)) {
            return $map;
        }
        $map = [];
        $shortcodes = 'CapitalCultural\Programacion\CCP_Shortcodes';
        if (post_type_exists('cc_propuesta') && class_exists($shortcodes)) {
            $tags = $shortcodes::space_shortcode_tags();
            $pages = [];
            $ids = get_posts([
                'post_type'   => 'cc_propuesta',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'fields'      => 'ids',
            ]);
            update_object_term_cache($ids, 'cc_propuesta');
            foreach ($ids as $id) {
                $key = $this->normalize(html_entity_decode(get_post_field('post_title', $id), ENT_QUOTES, 'UTF-8'));
                if ($key === '' || isset($map[$key])) {
                    continue;
                }
                $terms = get_the_terms($id, 'cc_espacio');
                foreach (is_array($terms) ? $terms : [] as $term) {
                    if (empty($tags[$term->slug])) {
                        continue;
                    }
                    if (!array_key_exists($term->slug, $pages)) {
                        $pages[$term->slug] = $this->space_page($tags[$term->slug]);
                    }
                    if ($pages[$term->slug] !== '') {
                        $map[$key] = $pages[$term->slug] . '#ccp-propuesta-' . $id;
                        break;
                    }
                }
            }
        }
        set_transient(self::PROPUESTAS_KEY, $map, HOUR_IN_SECONDS);
        return $map;
    }

    /**
     * La página publicada más vieja que lleva el shortcode del espacio, por ejemplo
     * [sorjosefa], en su contenido o en los datos de Elementor.
     */
    private function space_page($tag) {
        global $wpdb;
        $exact = '%' . $wpdb->esc_like('[' . $tag . ']') . '%';
        $with_atts = '%' . $wpdb->esc_like('[' . $tag . ' ') . '%';
        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_elementor_data'
             WHERE p.post_type = 'page' AND p.post_status = 'publish'
               AND (p.post_content LIKE %s OR p.post_content LIKE %s OR m.meta_value LIKE %s OR m.meta_value LIKE %s)
             ORDER BY p.ID LIMIT 1",
            $exact, $with_atts, $exact, $with_atts
        ));
        return $id ? (string) get_permalink((int) $id) : '';
    }

    public function clear_propuestas() {
        delete_transient(self::PROPUESTAS_KEY);
    }

    private function event_key($event) {
        return $this->normalize($event['url'] !== '' ? $event['url'] : $event['title']);
    }

    /**
     * Lista de claves normalizadas. Acepta el texto de la versión 1.4.0, uno por línea.
     */
    private function key_list($value) {
        $items = is_array($value) ? $value : explode(chr(10), str_replace(chr(13), '', (string) $value));
        return array_values(array_unique(array_filter(array_map([$this, 'normalize'], $items))));
    }

    /**
     * Los eventos de la última lectura que no terminaron, sin repetir: la lista de los ajustes.
     */
    private function upcoming_events() {
        $last_good = get_option(self::LAST_GOOD_OPTION, []);
        $events = isset($last_good['events']) && is_array($last_good['events']) ? $last_good['events'] : [];
        $out = [];
        foreach ($events as $event) {
            if ($event['end'] > time() && !isset($out[$this->event_key($event)])) {
                $out[$this->event_key($event)] = $event;
            }
        }
        return array_values($out);
    }

    private function normalize($text) {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(remove_accents((string) $text))));
    }

    /* ---------------------------------------------------------------------
     * Frontend
     * ------------------------------------------------------------------- */

    public function frontend_assets() {
        wp_register_style('cc-agenda', plugin_dir_url(__FILE__) . 'assets/agenda.css', [], self::VERSION);
        wp_register_script('cc-agenda', plugin_dir_url(__FILE__) . 'assets/agenda.js', [], self::VERSION, true);
    }

    /**
     * Los eventos se piden por admin-ajax para que la caché de página
     * (LiteSpeed) no deje la agenda desactualizada.
     */
    public function ajax_events() {
        $events = get_transient(self::TRANSIENT_KEY);
        $last_good = get_option(self::LAST_GOOD_OPTION, []);
        if (!is_array($events) && !empty($last_good['events']) && is_array($last_good['events'])) {
            // Venció la caché porque el cron no corrió a tiempo: se responde ya con la
            // última lectura correcta y la agenda se vuelve a leer en segundo plano.
            $events = $last_good['events'];
            wp_schedule_single_event(time(), self::CRON_HOOK);
            spawn_cron();
        } elseif (!is_array($events)) {
            // Nunca se leyó la agenda: no hay nada que mostrar mientras tanto.
            $events = $this->get_events();
        }
        $events = $this->visible_events($events);
        $status = get_option(self::STATUS_OPTION, []);

        // admin-ajax envía cabeceras sin caché; se permite al navegador reusar la respuesta 5 minutos.
        header('Cache-Control: public, max-age=300');

        wp_send_json([
            'ok'       => !empty($events) || !empty($status['ok']),
            'now'      => time(),
            'offset'   => wp_timezone()->getOffset(new DateTime('now')),
            'timezone' => wp_timezone_string(),
            'events'   => $events,
        ]);
    }

    public static function shortcode_defaults() {
        return [
            'titulo'     => 'Agenda cultural',
            'categorias' => 'Cultura',
            'cantidad'   => 12,
            'filtro'     => 'semana',
            'filtros'    => 'si',
            'imagenes'   => 'si',
            'boton'      => 'Ver agenda completa',
        ];
    }

    public static function filter_labels() {
        return [
            'hoy'      => 'Hoy',
            'finde'    => 'Este finde',
            'semana'   => 'Esta semana',
            'proximos' => 'Próximos',
        ];
    }

    public function shortcode($atts) {
        $atts = shortcode_atts(self::shortcode_defaults(), $atts, 'capital_agenda');

        wp_enqueue_style('cc-agenda');
        wp_enqueue_script('cc-agenda');

        return $this->render_block($atts);
    }

    private function render_block($atts) {
        $settings = $this->settings();
        $filters = self::filter_labels();
        $default_filter = array_key_exists($atts['filtro'], $filters) ? $atts['filtro'] : 'semana';

        self::$instance_count++;
        $title_id = 'cca-title-' . self::$instance_count;
        $agenda_url = $settings['agenda_url'];

        ob_start();
        ?>
        <section class="cca-agenda" data-cca-agenda
            data-endpoint="<?php echo esc_url(add_query_arg('action', self::AJAX_ACTION, admin_url('admin-ajax.php'))); ?>"
            data-categories="<?php echo esc_attr($atts['categorias']); ?>"
            data-limit="<?php echo esc_attr(max(1, intval($atts['cantidad']))); ?>"
            data-filter="<?php echo esc_attr($default_filter); ?>"
            data-filters="<?php echo $atts['filtros'] === 'no' ? '0' : '1'; ?>"
            data-images="<?php echo $atts['imagenes'] === 'no' ? '0' : '1'; ?>"
            <?php echo $atts['titulo'] !== '' ? 'aria-labelledby="' . esc_attr($title_id) . '"' : 'aria-label="Agenda"'; ?>>
            <div class="cca-head">
                <?php if ($atts['titulo'] !== '') : ?>
                    <h2 class="cca-title" id="<?php echo esc_attr($title_id); ?>"><span class="cca-barra" aria-hidden="true"></span><span class="cca-title-text"><?php echo esc_html($atts['titulo']); ?></span></h2>
                <?php endif; ?>
                <div class="cca-filters" role="group" aria-label="Filtrar actividades por fecha" hidden>
                    <?php foreach ($filters as $key => $label) : ?>
                        <button type="button" class="cca-filter" data-filter="<?php echo esc_attr($key); ?>" aria-pressed="false"><?php echo esc_html($label); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <ul class="cca-list" aria-busy="true">
                <?php for ($i = 0; $i < 4; $i++) : ?>
                    <li class="cca-item cca-skeleton" aria-hidden="true"><span class="cca-card"><?php echo $atts['imagenes'] === 'no' ? '' : '<span class="cca-media"></span>'; ?><span class="cca-body"><span></span><span></span><span></span></span></span></li>
                <?php endfor; ?>
            </ul>

            <p class="cca-message" role="status" hidden></p>

            <?php if ($agenda_url && $atts['boton'] !== '') : ?>
                <p class="cca-footer">
                    <a class="cca-more" href="<?php echo esc_url($agenda_url); ?>" target="_blank" rel="noopener">
                        <span class="cca-barra" aria-hidden="true"></span>
                        <span class="cca-more-label"><?php echo esc_html($atts['boton']); ?></span>
                        <svg class="cca-external" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true" focusable="false"><path d="M5 11 11 5M6 5h5v5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="screen-reader-text">(se abre en una pestaña nueva)</span>
                    </a>
                </p>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    /* ---------------------------------------------------------------------
     * Administración
     * ------------------------------------------------------------------- */

    public function admin_menu() {
        add_options_page('Agenda cultural', 'Agenda cultural', 'manage_options', 'cc-agenda', [$this, 'render_settings_page']);
    }

    public function admin_assets($hook) {
        if ($hook !== 'settings_page_cc-agenda') {
            return;
        }
        $this->frontend_assets();
        wp_enqueue_style('cc-agenda');
        wp_enqueue_script('cc-agenda');
        wp_enqueue_style('cc-agenda-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', ['cc-agenda'], self::VERSION);
        wp_enqueue_script('cc-agenda-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['cc-agenda'], self::VERSION, true);
    }

    public function register_settings() {
        register_setting('cc_agenda', self::OPTION, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => self::defaults(),
        ]);
    }

    public function sanitize_settings($input) {
        $defaults = self::defaults();
        $input = is_array($input) ? $input : [];

        $feed_url = isset($input['feed_url']) ? esc_url_raw(trim($input['feed_url'])) : '';
        $agenda_url = isset($input['agenda_url']) ? esc_url_raw(trim($input['agenda_url'])) : '';
        $minutes = isset($input['cache_minutes']) ? absint($input['cache_minutes']) : $defaults['cache_minutes'];

        // De la lista de eventos se guarda sólo lo que se aparta de la regla (Cultura
        // marcado, el resto sin marcar). Lo de eventos que ya no figuran se conserva.
        $current = $this->settings();
        $ocultos = $this->key_list($current['ocultos']);
        $mostrar = $this->key_list($current['mostrar']);
        if (isset($input['lista']) && is_array($input['lista'])) {
            $listed = $this->key_list($input['lista']);
            $chosen = $this->key_list(isset($input['elegidos']) && is_array($input['elegidos']) ? $input['elegidos'] : []);
            foreach ($this->upcoming_events() as $event) {
                $key = $this->event_key($event);
                if (!in_array($key, $listed, true)) {
                    continue;
                }
                $both = [$key, $this->normalize($event['title'])];
                $ocultos = array_values(array_diff($ocultos, $both));
                $mostrar = array_values(array_diff($mostrar, $both));
                $on = in_array($key, $chosen, true);
                if ($this->is_home_category($event) && !$on) {
                    $ocultos[] = $key;
                } elseif (!$this->is_home_category($event) && $on) {
                    $mostrar[] = $key;
                }
            }
        }

        return [
            'feed_url'      => $feed_url ?: $defaults['feed_url'],
            'agenda_url'    => $agenda_url ?: $defaults['agenda_url'],
            'cache_minutes' => min(1440, max(5, $minutes)),
            'ocultos'       => $ocultos,
            'mostrar'       => $mostrar,
        ];
    }

    public function handle_refresh() {
        if (!current_user_can('manage_options') || !check_admin_referer(self::REFRESH_ACTION)) {
            wp_die('No tenés permisos para actualizar la agenda.');
        }
        $this->get_events(true);
        wp_safe_redirect(admin_url('options-general.php?page=cc-agenda&cca_refreshed=1'));
        exit;
    }

    /**
     * Categorías de la última lectura correcta, con la cantidad de eventos de cada una.
     */
    private function category_counts() {
        $last_good = get_option(self::LAST_GOOD_OPTION, []);
        $events = isset($last_good['events']) && is_array($last_good['events']) ? $last_good['events'] : [];
        $counts = [];
        foreach ($events as $event) {
            foreach ($event['categories'] as $category) {
                $counts[$category] = ($counts[$category] ?? 0) + 1;
            }
        }
        ksort($counts);
        return $counts;
    }

    public function render_settings_page() {
        $settings = $this->settings();
        $status = get_option(self::STATUS_OPTION, []);
        $defaults = self::shortcode_defaults();
        $counts = $this->category_counts();
        $last_good = get_option(self::LAST_GOOD_OPTION, []);
        $stored_events = isset($last_good['events']) && is_array($last_good['events']) ? count($last_good['events']) : 0;

        $categories = $counts;
        foreach (array_map('trim', explode(',', $defaults['categorias'])) as $category) {
            if ($category !== '' && !isset($categories[$category])) {
                $categories[$category] = 0;
            }
        }
        ksort($categories);
        $default_categories = array_map('trim', explode(',', $defaults['categorias']));
        ?>
        <div class="wrap cca-admin">
            <h1>Agenda cultural</h1>
            <p class="cca-admin-intro">Muestra los eventos de la agenda institucional en Capital Cultural. Los eventos se cargan y editan en la agenda; acá solo elegís cómo se ven.</p>

            <?php if (isset($_GET['cca_refreshed'])) : ?>
                <div class="notice notice-info is-dismissible"><p>Se volvió a leer la agenda. Revisá el estado más abajo.</p></div>
            <?php endif; ?>

            <div class="cca-admin-panel">
                <h2>Armá el shortcode</h2>
                <p>Elegí las opciones, copiá el shortcode y pegalo en un widget «Shortcode» de Elementor.</p>

                <form id="cca-generator" class="cca-generator" data-defaults="<?php echo esc_attr(wp_json_encode($defaults)); ?>" onsubmit="return false;">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="cca_g_titulo">Título del bloque</label></th>
                            <td>
                                <input type="text" class="regular-text" id="cca_g_titulo" value="<?php echo esc_attr($defaults['titulo']); ?>">
                                <p class="description">Dejalo vacío para no mostrar título.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Categorías</th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">Categorías de la agenda a mostrar</legend>
                                    <?php foreach ($categories as $category => $count) : ?>
                                        <label class="cca-check">
                                            <input type="checkbox" name="cca_g_categorias" value="<?php echo esc_attr($category); ?>" <?php checked(in_array($category, $default_categories, true)); ?>>
                                            <?php echo esc_html($category); ?>
                                            <span class="cca-count"><?php echo intval($count); ?> <?php echo $count === 1 ? 'evento' : 'eventos'; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description">Si no marcás ninguna, se muestran todas. La lista sale de la última lectura de la agenda.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cca_g_cantidad">Cantidad de tarjetas</label></th>
                            <td>
                                <select id="cca_g_cantidad">
                                    <option value="4" <?php selected(intval($defaults['cantidad']), 4); ?>>Hasta 4 · una fila fija</option>
                                    <option value="8" <?php selected(intval($defaults['cantidad']), 8); ?>>Hasta 8 · carrusel</option>
                                    <option value="12" <?php selected(intval($defaults['cantidad']), 12); ?>>Hasta 12 · carrusel</option>
                                    <option value="20" <?php selected(intval($defaults['cantidad']), 20); ?>>Hasta 20 · carrusel</option>
                                </select>
                                <p class="description">Con más de 4 actividades el bloque pasa a carrusel: avanza solo, vuelve a empezar al final y se detiene al pasar el mouse.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cca_g_filtro">Período inicial</label></th>
                            <td>
                                <select id="cca_g_filtro">
                                    <?php foreach (self::filter_labels() as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($defaults['filtro'], $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Si ese período no tiene actividades, el bloque pasa solo a «Esta semana» o «Próximos».</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Mostrar</th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">Partes del bloque</legend>
                                    <label class="cca-check"><input type="checkbox" id="cca_g_filtros" <?php checked($defaults['filtros'], 'si'); ?>> Botones para filtrar por fecha</label>
                                    <label class="cca-check"><input type="checkbox" id="cca_g_imagenes" <?php checked($defaults['imagenes'], 'si'); ?>> Imágenes de los eventos</label>
                                    <label class="cca-check"><input type="checkbox" id="cca_g_boton_on" <?php checked($defaults['boton'] !== ''); ?>> Botón a la agenda completa</label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cca_g_boton">Texto del botón</label></th>
                            <td><input type="text" class="regular-text" id="cca_g_boton" value="<?php echo esc_attr($defaults['boton']); ?>"></td>
                        </tr>
                    </table>

                    <div class="cca-output">
                        <label for="cca_g_output">Tu shortcode</label>
                        <div class="cca-output-row">
                            <input type="text" id="cca_g_output" class="large-text code" readonly value="[capital_agenda]">
                            <button type="button" class="button button-primary" id="cca_g_copy">Copiar shortcode</button>
                        </div>
                        <p class="cca-copy-feedback" id="cca_g_feedback" role="status"></p>
                    </div>
                </form>

                <h3>Vista previa</h3>
                <p class="description">Con los eventos reales de la agenda. En la página toma el ancho de la columna donde lo pegues.</p>
                <div class="cca-preview" id="cca-preview"></div>
                <template id="cca-preview-template"><?php echo $this->render_block($defaults); ?></template>
            </div>

            <div class="cca-admin-panel">
                <h2>Estado de la conexión</h2>
                <table class="widefat striped cca-status">
                    <tbody>
                        <tr>
                            <th scope="row">Última lectura</th>
                            <td><?php echo !empty($status['time']) ? esc_html(wp_date('j/m/Y H:i', $status['time'])) : 'Todavía no se leyó la agenda.'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Resultado</th>
                            <td>
                                <?php if (empty($status)) : ?>
                                    —
                                <?php elseif (!empty($status['ok'])) : ?>
                                    Correcto: <?php echo intval($status['count']); ?> eventos vigentes.
                                <?php else : ?>
                                    <strong>Error:</strong> <?php echo esc_html($status['error']); ?>
                                    <?php if ($stored_events) : ?>
                                        Se siguen mostrando los <?php echo intval($stored_events); ?> eventos de la última lectura correcta.
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Próxima lectura automática</th>
                            <td><?php $next = wp_next_scheduled(self::CRON_HOOK); echo $next ? esc_html(wp_date('j/m/Y H:i', $next)) : 'Sin programar: se programa con la próxima visita.'; ?></td>
                        </tr>
                    </tbody>
                </table>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cca-refresh">
                    <input type="hidden" name="action" value="<?php echo esc_attr(self::REFRESH_ACTION); ?>">
                    <?php wp_nonce_field(self::REFRESH_ACTION); ?>
                    <?php submit_button('Actualizar ahora', 'secondary', 'submit', false); ?>
                </form>
            </div>

            <div class="cca-admin-panel">
                <h2>Conexión con la agenda</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('cc_agenda'); ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="cca_feed_url">Calendario iCal</label></th>
                            <td>
                                <input type="url" class="regular-text" id="cca_feed_url" name="<?php echo esc_attr(self::OPTION); ?>[feed_url]" value="<?php echo esc_attr($settings['feed_url']); ?>">
                                <p class="description">Dirección pública del calendario de la agenda.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cca_agenda_url">Página de la agenda</label></th>
                            <td>
                                <input type="url" class="regular-text" id="cca_agenda_url" name="<?php echo esc_attr(self::OPTION); ?>[agenda_url]" value="<?php echo esc_attr($settings['agenda_url']); ?>">
                                <p class="description">Destino del botón «Ver agenda completa».</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cca_cache_minutes">Actualizar cada</label></th>
                            <td>
                                <input type="number" min="5" max="1440" step="5" class="small-text" id="cca_cache_minutes" name="<?php echo esc_attr(self::OPTION); ?>[cache_minutes]" value="<?php echo esc_attr($settings['cache_minutes']); ?>"> minutos
                                <p class="description">Cada cuánto se vuelve a leer la agenda en segundo plano.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Eventos en el inicio</th>
                            <td>
                                <?php $upcoming = $this->upcoming_events(); ?>
                                <?php if (!$upcoming) : ?>
                                    <p>Todavía no se leyó la agenda.</p>
                                <?php else : ?>
                                    <fieldset id="cca_eventos">
                                        <legend class="screen-reader-text">Eventos que se muestran en el inicio</legend>
                                        <?php foreach ($upcoming as $event) : $key = $this->event_key($event); ?>
                                            <input type="hidden" name="<?php echo esc_attr(self::OPTION); ?>[lista][]" value="<?php echo esc_attr($key); ?>">
                                            <label class="cca-check">
                                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[elegidos][]" value="<?php echo esc_attr($key); ?>" <?php checked($this->on_home($event, $settings)); ?>>
                                                <?php echo esc_html($event['title'] . ($event['kicker'] !== '' ? ' · ' . $event['kicker'] : '')); ?>
                                                <span class="cca-count"><?php echo esc_html(wp_date('j/m', $event['start']) . ($event['venue'] !== '' ? ' · ' . $event['venue'] : '') . ' · ' . ($event['categories'] ? implode(', ', $event['categories']) : 'sin categoría')); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                <?php endif; ?>
                                <p class="description">Marcá los que van en el inicio y guardá. Los eventos nuevos de Cultura aparecen marcados y los de otras categorías sin marcar. Sacarlo de acá no lo saca de la agenda municipal.</p>
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

register_deactivation_hook(__FILE__, ['Capital_Cultural_Agenda', 'deactivate']);
new Capital_Cultural_Agenda();
