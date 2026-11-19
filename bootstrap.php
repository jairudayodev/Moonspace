<?php

/**
 * bootstrap
 * 
 * @package Moonspace
 * @author Jair Roger
 */

// set system version
define('SYS_VER', '3.4');


// set system locale (o Moonspace é 100% brasileiro)
define('SYS_LOCALE', 'pt-BR');              // identificador regional (BCP 47): <html lang>, Intl, moment
define('SYS_LOCALE_DIR', 'pt_BR');          // pasta da tradução em content/languages/locale/
define('SYS_TIMEZONE', 'America/Sao_Paulo'); // fuso usado para mostrar datas e horas


// set absolut & base path
define('ABSPATH', dirname(__FILE__) . '/');
define('BASEPATH', dirname($_SERVER['PHP_SELF']));


// check the config file
if (!file_exists(ABSPATH . 'includes/config.php')) {
    /* the config file doesn't exist -> show how to create it (the installer was removed) */
    header('HTTP/1.1 503 Service Unavailable');
    header('Content-Type: text/plain; charset=utf-8');
    exit('Moonspace: arquivo includes/config.php não encontrado. Copie includes/config.example.php para includes/config.php e preencha os dados do servidor.');
}


// get system configurations
require_once(ABSPATH . 'includes/config.php');


// enviroment settings
if (DEBUGGING) {
    ini_set("display_errors", true);
    /* hide notices and deprecations (Smarty 3.1 floods PHP 8.2+ with them) */
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);
} else {
    ini_set("display_errors", false);
    error_reporting(0);
}
$system['DEBUGGING'] = DEBUGGING;


// get functions
require_once(ABSPATH . 'includes/functions.php');


// check system URL
check_system_url();


// start session
ini_set('session.cookie_httponly', 1);
if (get_system_protocol() == "https") {
    ini_set('session.cookie_secure', 1);
}
session_start();
/* set session secret */
if (!isset($_SESSION['secret'])) {
    $_SESSION['secret'] = get_hash_token();
}


// gettext config
require_once(ABSPATH . 'includes/libs/GettextTranslator/autoloader.php');
$t = new Gettext\Translator();
$t->register();
/* único idioma do site: português do Brasil */
$t->loadTranslations(Gettext\Translations::fromPoFile(ABSPATH . 'content/languages/locale/' . SYS_LOCALE_DIR . '/LC_MESSAGES/messages.po'));


// time config
/* o banco e as datas gravadas ficam em UTC; a exibição usa o fuso de Brasília (SYS_TIMEZONE) */
date_default_timezone_set('UTC');
$time = time();
$minutes_to_add = 0;
$DateTime = new DateTime();
$DateTime->add(new DateInterval('PT' . $minutes_to_add . 'M'));
$date = $DateTime->format('Y-m-d H:i:s');


// connect to the database
$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
$db->set_charset('utf8mb4');
if (mysqli_connect_error()) {
    _error("DB_ERROR");
}
/* set db time to UTC */
$db->query("SET time_zone = '+0:00'");


// check if the viewer IP is banned
$check_banned_ip = $db->query(sprintf("SELECT COUNT(*) as count FROM blacklist WHERE node_type = 'ip' AND node_value = %s", secure(get_user_ip()))) or _error("SQL_ERROR");
if ($check_banned_ip->fetch_assoc()['count'] > 0) {
    _error(__("System Message"), __("Your IP has been blocked"));
}


// get system options
$get_system_options = $db->query("SELECT * FROM system_options") or _error("SQL_ERROR");
while ($system_option = $get_system_options->fetch_assoc()) {
    $system[$system_option['option_name']] = $system_option['option_value'];
}

/* set system URL */
$system['system_url'] = SYS_URL;

/* set system BASEPATH */
$system['BASEPATH'] = ltrim(BASEPATH, '/');

/* set system version */
$system['system_version'] = SYS_VER;

/* set session hash */
$session_hash = session_hash($system['session_hash']);

/* set system uploads */
if ($system['uploads_cdn_url']) {
    $system['system_uploads'] = $system['uploads_cdn_url'];
} else {
    if ($system['s3_enabled']) {
        $endpoint = "https://s3." . $system['s3_region'] . ".amazonaws.com/" . $system['s3_bucket'];
        $system['system_uploads'] = $endpoint . "/uploads";
    } elseif ($system['digitalocean_enabled']) {
        $endpoint = "https://" . $system['digitalocean_space_name'] . "." . $system['digitalocean_space_region'] . ".digitaloceanspaces.com";
        $system['system_uploads'] = $endpoint . "/uploads";
    } elseif ($system['wasabi_enabled']) {
        $endpoint = "https://s3." . $system['wasabi_region'] . ".wasabisys.com/" . $system['wasabi_bucket'];
        $system['system_uploads'] = $endpoint . "/uploads";
    } elseif ($system['ftp_enabled']) {
        $system['system_uploads'] = $system['ftp_endpoint'];
    } else {
        $system['system_uploads'] = $system['system_url'] . '/' . $system['uploads_directory'];
    }
}

/* set agora uploads */
if ($system['live_enabled'] && $system['save_live_enabled']) {
    $system['system_agora_uploads'] = "https://s3." . $system['agora_s3_region'] . ".amazonaws.com/" . $system['agora_s3_bucket'];
}

/* set uploads accpeted extensions */
$system['accpeted_video_extensions'] = set_extensions_string($system['video_extensions']);
$system['accpeted_audio_extensions'] = set_extensions_string($system['audio_extensions']);
$system['accpeted_file_extensions'] = set_extensions_string($system['file_extensions']);

/* get system themes */
$get_system_themes = $db->query("SELECT * FROM system_themes") or _error("SQL_ERROR");
while ($theme = $get_system_themes->fetch_assoc()) {
    if ($theme['default']) {
        $system['theme'] = $theme['name'];
    }
    if ($theme['enabled']) {
        $system['themes'][$theme['name']] = $theme;
    }
}

/* set system theme */
if (isset($_GET['theme'])) {
    if (array_key_exists($_GET['theme'], $system['themes'])) {
        if (file_exists(ABSPATH . 'content/themes/' . $_GET['theme'])) {
            $system['theme'] = $_GET['theme'];
            /* set theme cookie */
            $secured = (get_system_protocol() == "https") ? true : false;
            $expire = time() + 2592000;
            setcookie('s_theme', $_GET['theme'], $expire, '/', "", $secured, true);
        }
    }
} elseif (isset($_COOKIE['s_theme'])) {
    if (array_key_exists($_COOKIE['s_theme'], $system['themes'])) {
        if (file_exists(ABSPATH . 'content/themes/' . $_COOKIE['s_theme'])) {
            $system['theme'] = $_COOKIE['s_theme'];
        } else {
            setcookie('s_theme', NULL, -1, '/');
        }
    }
} else {
    if (!isset($system['theme'])) {
        $system['theme'] = "default";
    }
}

/* set system theme (day|night) mode */
if ($system['system_theme_mode_select']) {
    if (isset($_COOKIE['s_night_mode'])) {
        $system['theme_mode_night'] = ($_COOKIE['s_night_mode']) ? 1 : 0;
    } else {
        $system['theme_mode_night'] = $system['system_theme_night_on'];
    }
} else {
    $system['theme_mode_night'] = $system['system_theme_night_on'];
}

/* system language: o Moonspace é só em português do Brasil (sem seletor de idioma).
   A tradução (.po) já foi carregada na configuração do gettext, lá em cima. */
$system['language'] = array(
    'language_id' => 0,
    'code' => SYS_LOCALE,
    'title' => 'Português (Brasil)',
    'flag' => '',
    'dir' => 'LTR',
    'default' => '1',
    'enabled' => '1'
);
$system['default_language'] = $system['language'];
$system['languages'] = array(SYS_LOCALE => $system['language']);
$system['current_language'] = SYS_LOCALE;

/* system currency: real brasileiro, no formato R$ 1.234,56 (ver print_money) */
$system['system_currency'] = 'BRL';
$system['system_currency_symbol'] = 'R$';
$system['system_currency_dir'] = 'left';

/* formato de data e hora dos formulários (datetimepicker e set_datetime): dd/mm/aaaa hh:mm */
$system['system_datetime_format'] = 'd/m/Y H:i';

/* get system withdrawal method array */
$system['wallet_payment_method_array'] = explode(",", $system['wallet_payment_method']);
$system['affiliate_payment_method_array'] = explode(",", $system['affiliate_payment_method']);
$system['points_payment_method_array'] = explode(",", $system['points_payment_method']);
$system['funding_payment_method_array'] = explode(",", $system['funding_payment_method']);


// smarty config
require_once(ABSPATH . 'includes/libs/Smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->template_dir = ABSPATH . 'content/themes/' . $system['theme'] . '/templates';
$smarty->compile_dir = ABSPATH . 'content/themes/' . $system['theme'] . '/templates_compiled';
$smarty->loadFilter('output', 'trimwhitespace');
/* datas no padrão brasileiro: {$data|date_br:'long'} -> "5 de novembro de 2026" (ver format_date_br) */
$smarty->registerPlugin('modifier', 'date_br', 'format_date_br');
/* números no padrão brasileiro: {$n|number_format} -> 1.234 e {$n|number_format:2} -> 1.234,56 */
$smarty->registerPlugin('modifier', 'number_format', 'format_number_br');
if (DEBUGGING) {
    /* templates read optional vars freely: PHP 8 turned those notices into warnings, mute them only while rendering */
    $smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED;
}


// get user
require_once(ABSPATH . 'includes/class-user.php');
try {
    $user = new User();
    /* assign variables */
    $smarty->assign('user', $user);
} catch (Exception $e) {
    _error(__("Error"), $e->getMessage());
}


// check if system is live
if (!$system['system_live'] && ((!$user->_logged_in && !isset($override_shutdown)) || ($user->_logged_in && $user->_data['user_group'] != 1))) {
    _error(__('System Message'), "<p class='text-center'>" . $system['system_message'] . "</p>");
}


// check if the viewer is banned
if ($user->_logged_in && (!$user->_is_admin && $user->_data['user_banned'])) {
    $ban_message = ($user->_data['user_banned_message']) ? $user->_data['user_banned_message'] : __("Your account has been blocked");
    _error(__("System Message"), $ban_message);
}


// init affiliates system
$user->init_affiliates();


// get reactions
$reactions = get_reactions();
$smarty->assign('reactions', $reactions);


// init control panel
if ($user->_is_admin) {
    $control_panel['title'] = __("Admin");
    $control_panel['url'] = "admincp";
} elseif ($user->_is_moderator) {
    $control_panel['title'] = __("Moderator");
    $control_panel['url'] = "modcp";
}


// assign system varibles
$smarty->assign('secret', $_SESSION['secret']);
$smarty->assign('session_hash', $session_hash);
$smarty->assign('system', $system);
$smarty->assign('date', $date);
