<?php
/**
 * Plugin Name: Páginas Essenciais
 * Plugin URI: https://github.com/jsballarini/paginas-essenciais
 * Description: Cria automaticamente páginas essenciais como Termos de Uso, Política de Privacidade, Política de Fretes e Política de Troca e Devolução.
 * Version: 0.0.1
 * Author: Juliano Ballarini
 * Author URI: https://ballarini.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: paginas-essenciais
 * Domain Path: /languages
 */

// Se este arquivo for chamado diretamente, aborte.
if (!defined('WPINC')) {
    die;
}

// Define a versão do plugin
define('PAGINAS_ESSENCIAIS_VERSION', '0.0.1');

/**
 * Array com os slugs das páginas criadas pelo plugin
 */
function paginas_essenciais_get_slugs() {
    return array(
        'termo-de-uso',
        'politica-de-privacidade',
        'politica-de-frete',
        'politica-de-troca-e-devolucao'
    );
}

/**
 * Função executada na ativação do plugin
 */
function paginas_essenciais_activate() {
    // Obtém as configurações do site
    $site_name = get_bloginfo('name');
    $site_url = get_bloginfo('url');
    $admin_email = get_bloginfo('admin_email');
    $current_month = date_i18n('F');
    $current_year = date('Y');
    
    // Verifica se o WooCommerce está instalado
    if (class_exists('WooCommerce')) {
        $store_address = get_option('woocommerce_store_address');
        $store_phone = get_option('woocommerce_store_phone');
        $person_type = get_option('woocommerce_person_type', 'jurídica');
    } else {
        $store_address = '';
        $store_phone = '';
        $person_type = 'jurídica';
    }

    // Array com as páginas a serem criadas
    $pages = array(
        'termo-de-uso' => array(
            'title' => 'Termo de Uso',
            'content' => str_replace(
                array('%nome do site%', '%site%', '%jurídica ou física%', '%email%', '%mes%', '%ano%'),
                array($site_name, $site_url, $person_type, $admin_email, $current_month, $current_year),
                file_get_contents(plugin_dir_path(__FILE__) . 'templates/termo-de-uso.php')
            )
        ),
        'politica-de-privacidade' => array(
            'title' => 'Política de Privacidade',
            'content' => str_replace(
                array('%nome do site%', '%site%', '%email%', '%mes%', '%ano%'),
                array($site_name, $site_url, $admin_email, $current_month, $current_year),
                file_get_contents(plugin_dir_path(__FILE__) . 'templates/politica-de-privacidade.php')
            )
        ),
        'politica-de-frete' => array(
            'title' => 'Política de Frete',
            'content' => str_replace(
                array('%nome do site%', '%site%', '%email%'),
                array($site_name, $site_url, $admin_email),
                file_get_contents(plugin_dir_path(__FILE__) . 'templates/politica-de-frete.php')
            )
        ),
        'politica-de-troca-e-devolucao' => array(
            'title' => 'Política de Troca e Devolução',
            'content' => str_replace(
                array('%nome do site%', '%site%', '%email%', '%telefone%'),
                array($site_name, $site_url, $admin_email, $store_phone),
                file_get_contents(plugin_dir_path(__FILE__) . 'templates/politica-de-troca-e-devolucao.php')
            )
        )
    );

    // Cria as páginas
    foreach ($pages as $slug => $page) {
        // Verifica se a página já existe
        $existing_page = get_page_by_path($slug);
        
        if (!$existing_page) {
            // Cria a página
            wp_insert_post(array(
                'post_title' => $page['title'],
                'post_name' => $slug,
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
        }
    }
}

/**
 * Função executada na desativação do plugin
 */
function paginas_essenciais_deactivate() {
    // Obtém os slugs das páginas
    $slugs = paginas_essenciais_get_slugs();

    // Remove cada página
    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug);
        if ($page) {
            wp_delete_post($page->ID, true); // true para excluir permanentemente
        }
    }
}

register_activation_hook(__FILE__, 'paginas_essenciais_activate');
register_deactivation_hook(__FILE__, 'paginas_essenciais_deactivate'); 