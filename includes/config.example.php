<?php

/**
 * config (modelo)
 *
 * Copie este arquivo para "includes/config.php" e preencha com os dados do servidor.
 * O config.php real NUNCA deve ir para o git (ele está no .gitignore).
 *
 * @package Moonspace
 * @author Jair Roger
 */

// ** MySQL settings ** //
/** The name of the database */
define('DB_NAME', 'nome_do_banco');

/** MySQL database username */
define('DB_USER', 'usuario_do_banco');

/** MySQL database password */
define('DB_PASSWORD', 'senha_do_banco');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** MySQL port */
define('DB_PORT', '3306');


// ** System URL ** //
define('SYS_URL', 'https://seu-dominio.com'); // sem barra no final (e.g http://example.com)


// ** i18n settings ** //
// Não é mais usado: o idioma é fixo em pt-BR (SYS_LOCALE em bootstrap.php). Mantido só por compatibilidade.
define('DEFAULT_LOCALE', 'pt_BR');


/**
 * Modo de depuração.
 * Use true apenas em desenvolvimento, para ver os erros PHP na tela.
 * Em produção deve ficar false.
 */
define('DEBUGGING', false);


// ** LICENCE ** //
/**
 * A unique key for your licence.
 */
define('LICENCE_KEY', '');
