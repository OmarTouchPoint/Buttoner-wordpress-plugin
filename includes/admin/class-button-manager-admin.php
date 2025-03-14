<?php
if (!defined('ABSPATH')) {
    exit;
}

class Button_Manager_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_menu_page() {
        add_menu_page(
            __('Button Manager', 'button-manager'),
            __('Button Manager', 'button-manager'),
            'manage_options',
            'button_manager',
            array($this, 'render_admin_page'),
            'dashicons-admin-generic',
            30
        );
    }

    public function register_settings() {
        register_setting('button_manager_settings', 'button_manager_sets');
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_button_manager') {
            return;
        }
        wp_enqueue_style('button-manager-admin-styles', plugin_dir_url(__FILE__) . '../assets/css/styles.css');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_media();
        wp_enqueue_script('button-manager-admin-script', plugin_dir_url(__FILE__) . '../assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
    }

    public function render_admin_page() {
        $button_sets = get_option('button_manager_sets', []);
    
        // Procesar la creación de una nueva botonera
        if (isset($_POST['create_button_set']) && wp_verify_nonce($_POST['button_manager_nonce'], 'create_button_set_nonce')) {
            $set_id = sanitize_title($_POST['set_id']);
            if (!empty($set_id)) {
                $button_sets[$set_id] = [];
                update_option('button_manager_sets', $button_sets);
                echo '<div class="updated"><p>' . esc_html__('Button set created successfully!', 'button-manager') . '</p></div>';
            }
        }
    
        // Procesar la eliminación de una botonera
        if (isset($_GET['action']) && $_GET['action'] === 'delete_set' && isset($_GET['set_id']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_set_nonce')) {
            $set_id = sanitize_key($_GET['set_id']);
            if (isset($button_sets[$set_id])) {
                unset($button_sets[$set_id]);
                update_option('button_manager_sets', $button_sets);
                echo '<div class="updated"><p>' . esc_html__('Button set deleted successfully!', 'button-manager') . '</p></div>';
            }
        }
    
        // Mostrar la página de edición si se solicita
        if (isset($_GET['action']) && $_GET['action'] === 'edit_set' && isset($_GET['set_id'])) {
            $set_id = sanitize_key($_GET['set_id']);
            if (isset($button_sets[$set_id])) {
                $this->render_edit_set_page($set_id);
                return; // Salir para no mostrar la lista de botoneras
            }
        }
    
        // Mostrar la lista de botoneras
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Button Manager', 'button-manager'); ?></h1>
            
            <h2><?php esc_html_e('Button Sets', 'button-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Set ID', 'button-manager'); ?></th>
                    <th><?php esc_html_e('Actions', 'button-manager'); ?></th>
                </tr>
                <?php
                if (!empty($button_sets)) {
                    foreach ($button_sets as $set_id => $buttons) {
                        echo '<tr>';
                        echo '<td>' . esc_html($set_id) . '</td>';
                        echo '<td>';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=button_manager&action=edit_set&set_id=' . $set_id)) . '">' . esc_html__('Edit', 'button-manager') . '</a> | ';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=button_manager&action=delete_set&set_id=' . $set_id . '&_wpnonce=' . wp_create_nonce('delete_set_nonce'))) . '">' . esc_html__('Delete', 'button-manager') . '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="2">' . esc_html__('No button sets available.', 'button-manager') . '</td></tr>';
                }
                ?>
            </table>
            
            <h2><?php esc_html_e('Create New Button Set', 'button-manager'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('create_button_set_nonce', 'button_manager_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="set_id"><?php esc_html_e('Set ID', 'button-manager'); ?></label></th>
                        <td><input type="text" name="set_id" id="set_id" required /></td>
                    </tr>
                </table>
                <input type="submit" name="create_button_set" value="<?php esc_html_e('Create Set', 'button-manager'); ?>" class="button-primary" />
            </form>
        </div>
        <?php
    }
    
    public function render_edit_set_page($set_id) {
        $button_sets = get_option('button_manager_sets', []);
        $buttons = isset($button_sets[$set_id]) ? $button_sets[$set_id] : [];
    
        // Procesar la edición de botones
        if (isset($_POST['save_buttons']) && wp_verify_nonce($_POST['button_manager_nonce'], 'save_buttons_nonce')) {
            $buttons = [];
            foreach ($_POST['buttons'] as $button) {
                $buttons[] = [
                    'text' => sanitize_text_field($button['text']),
                    'icon' => esc_url_raw($button['icon']),
                    'link' => esc_url_raw($button['link'])
                ];
            }
            $button_sets[$set_id] = $buttons;
            update_option('button_manager_sets', $button_sets);
            echo '<div class="updated"><p>' . esc_html__('Buttons updated successfully!', 'button-manager') . '</p></div>';
        }
    
        // Procesar la eliminación de un botón
        if (isset($_GET['action']) && $_GET['action'] === 'delete_button' && isset($_GET['button_id']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_button_nonce')) {
            $button_id = intval($_GET['button_id']);
            if (isset($buttons[$button_id])) {
                unset($buttons[$button_id]);
                $button_sets[$set_id] = array_values($buttons); // Reindexar el array
                update_option('button_manager_sets', $button_sets);
                echo '<div class="updated"><p>' . esc_html__('Button deleted successfully!', 'button-manager') . '</p></div>';
            }
        }
    
        // Procesar la adición de un nuevo botón
        if (isset($_POST['add_button']) && wp_verify_nonce($_POST['button_manager_nonce'], 'add_button_nonce')) {
            $button_text = sanitize_text_field($_POST['button_text']);
            $button_icon = esc_url_raw($_POST['button_icon']);
            $button_link = esc_url_raw($_POST['button_link']);
    
            if (!empty($button_text) && !empty($button_icon) && !empty($button_link)) {
                $buttons[] = [
                    'text' => $button_text,
                    'icon' => $button_icon,
                    'link' => $button_link
                ];
                $button_sets[$set_id] = $buttons;
                update_option('button_manager_sets', $button_sets);
                echo '<div class="updated"><p>' . esc_html__('Button added successfully!', 'button-manager') . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__('Please fill all fields.', 'button-manager') . '</p></div>';
            }
        }
    
        // Mostrar el formulario de edición
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Edit Button Set', 'button-manager'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('save_buttons_nonce', 'button_manager_nonce'); ?>
                <input type="hidden" name="button_order" id="button_order" value="" />
                <table class="button-manager-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Icon', 'button-manager'); ?></th>
                            <th><?php esc_html_e('Button Text', 'button-manager'); ?></th>
                            <th><?php esc_html_e('Link', 'button-manager'); ?></th>
                            <th><?php esc_html_e('Actions', 'button-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="button-list">
                        <?php
                        if (!empty($buttons)) {
                            foreach ($buttons as $index => $button) {
                                echo '<tr class="button-item" data-index="' . esc_attr($index) . '">';
                                echo '<td>';
                                echo '<input type="text" name="buttons[' . $index . '][icon]" value="' . esc_url($button['icon']) . '" placeholder="Icon URL" class="icon-url" />';
                                echo '<button type="button" class="button button-secondary upload-icon-button">' . esc_html__('Upload Icon', 'button-manager') . '</button>';
                                echo '<div class="icon-preview">';
                                if (!empty($button['icon'])) {
                                    echo '<img src="' . esc_url($button['icon']) . '" alt="Icon Preview" class="icon-preview-image" />';
                                } else {
                                    echo '<span class="icon-preview-placeholder">' . esc_html__('No icon selected', 'button-manager') . '</span>';
                                }
                                echo '</div>';
                                echo '</td>';
                                echo '<td><input type="text" name="buttons[' . $index . '][text]" value="' . esc_attr($button['text']) . '" placeholder="Button Text" /></td>';
                                echo '<td><input type="url" name="buttons[' . $index . '][link]" value="' . esc_url($button['link']) . '" placeholder="Button Link" /></td>';
                                echo '<td>';
                                echo '<button type="button" class="button button-secondary move-up">&#9650;</button>';
                                echo '<button type="button" class="button button-secondary move-down">&#9660;</button>';
                                echo '<a href="' . esc_url(admin_url('admin.php?page=button_manager&action=edit_set&set_id=' . $set_id . '&action=delete_button&button_id=' . $index . '&_wpnonce=' . wp_create_nonce('delete_button_nonce'))) . '" class="button button-secondary">' . esc_html__('Delete', 'button-manager') . '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">' . esc_html__('No buttons available.', 'button-manager') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <input type="submit" name="save_buttons" value="<?php esc_html_e('Save Buttons', 'button-manager'); ?>" class="button-primary" />
            </form>
    
            <!-- Botón para agregar un nuevo botón -->
            <h2><?php esc_html_e('Add New Button', 'button-manager'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('add_button_nonce', 'button_manager_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="button_text"><?php esc_html_e('Button Text', 'button-manager'); ?></label></th>
                        <td><input type="text" name="button_text" id="button_text" required /></td>
                    </tr>
                    <tr>
                        <th><label for="button_icon"><?php esc_html_e('Icon (SVG)', 'button-manager'); ?></label></th>
                        <td>
                            <input type="text" name="button_icon" id="button_icon" class="regular-text" required />
                            <button type="button" class="button button-secondary" id="upload_svg_button">Upload SVG</button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="button_link"><?php esc_html_e('Link', 'button-manager'); ?></label></th>
                        <td><input type="url" name="button_link" id="button_link" required /></td>
                    </tr>
                </table>
                <input type="submit" name="add_button" value="<?php esc_html_e('Add Button', 'button-manager'); ?>" class="button-primary" />
            </form>
        </div>
        <?php
    }
}