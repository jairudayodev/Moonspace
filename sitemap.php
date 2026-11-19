<?php

/**
 * sitemap
 *
 * Gera /sitemap.xml para os buscadores (rota no .htaccess).
 * Lista só o que um visitante sem login consegue abrir: home, páginas estáticas,
 * contato e, quando o site é público, grupos públicos e páginas.
 * Perfis de usuários ficam de fora de propósito (privacidade).
 *
 * @package Moonspace
 * @author Jair Roger
 */

// fetch bootstrap
require('bootstrap.php');

$urls = array();
$base = rtrim($system['system_url'], '/');

/* home */
$urls[] = array('loc' => $base . '/', 'changefreq' => 'daily', 'priority' => '1.0');

/* páginas estáticas (sobre, termos, privacidade...) */
$get_static = $db->query("SELECT page_url FROM static_pages ORDER BY page_order ASC");
if ($get_static) {
    while ($static_page = $get_static->fetch_assoc()) {
        $urls[] = array('loc' => $base . '/static/' . rawurlencode($static_page['page_url']), 'changefreq' => 'monthly', 'priority' => '0.5');
    }
}

/* contato */
if ($system['contact_enabled']) {
    $urls[] = array('loc' => $base . '/contacts', 'changefreq' => 'yearly', 'priority' => '0.3');
}

/* conteúdo aberto a visitantes (só quando o site está como público no admin) */
if ($system['system_public']) {
    if ($system['groups_enabled']) {
        $urls[] = array('loc' => $base . '/groups', 'changefreq' => 'weekly', 'priority' => '0.6');
        $get_groups = $db->query("SELECT group_name FROM `groups` WHERE group_privacy = 'public' ORDER BY group_id DESC LIMIT 1000");
        if ($get_groups) {
            while ($group = $get_groups->fetch_assoc()) {
                $urls[] = array('loc' => $base . '/groups/' . rawurlencode($group['group_name']), 'changefreq' => 'weekly', 'priority' => '0.5');
            }
        }
    }
    if ($system['pages_enabled']) {
        $urls[] = array('loc' => $base . '/pages', 'changefreq' => 'weekly', 'priority' => '0.6');
        $get_pages = $db->query("SELECT page_name FROM pages ORDER BY page_id DESC LIMIT 1000");
        if ($get_pages) {
            while ($_page = $get_pages->fetch_assoc()) {
                $urls[] = array('loc' => $base . '/pages/' . rawurlencode($_page['page_name']), 'changefreq' => 'weekly', 'priority' => '0.5');
            }
        }
    }
}

/* saída */
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
    echo '    <priority>' . $url['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
