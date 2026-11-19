<?php

/**
 * static
 * 
 * @package Moonspace
 * @author Jair Roger
 */

// fetch bootloader
require('bootloader.php');

// valid inputs
if (!isset($_GET['url'])) {
	_error(404);
}

try {

	// get static page
	$get_static = $db->query(sprintf("SELECT * FROM static_pages WHERE page_url = %s", secure($_GET['url']))) or _error("SQL_ERROR_THROWEN");
	if ($get_static->num_rows == 0) {
		_error(404);
	}
	$static = $get_static->fetch_assoc();
	$static['page_text'] = html_entity_decode($static['page_text'], ENT_QUOTES);
	$static['page_text'] = $smarty->fetch('string:' . $static['page_text']);
	/* assign variables */
	$smarty->assign('static', $static);
} catch (Exception $e) {
	_error(__("Error"), $e->getMessage());
}

// page header (título específico primeiro e descrição tirada do próprio texto da página)
$static_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($static['page_text'], ENT_QUOTES, 'UTF-8'))));
if (mb_strlen($static_description) > 160) {
	$static_description = rtrim(mb_substr($static_description, 0, 157)) . '...';
}
page_header(__($static['page_title']) . ' | ' . $system['system_title'], $static_description);

// page footer
page_footer("static");
