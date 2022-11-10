<?php
/* Bootstrap RMCorte
 *
 * Este arquivo inicializa tudo o que é necessário para RMCorte
 * Se você precisar inicializar o RMCorte (ou seja, acessar suas funções e recursos), 
 * basta incluir este arquivo.
 */

require __DIR__ . '/vendor/autoload.php';

// Setup das configurações do RMCorte

$config = new \YOURLS\Config\Config;
/* O require deve estar em nível global para que as variáveis dentro do config.php, incluindo as definidas
 * pelo usuário, se houver, estão registrados no escopo global. Se este require for movido em 
 * \YOURLS\Config\Config, $yourls_user_passwords para
 * a instância que não está registrada.
*/
if (!defined('YOURLS_CONFIGFILE')) {
    define('YOURLS_CONFIGFILE', $config->find_config());
}
require_once YOURLS_CONFIGFILE;
$config->define_core_constants();

// Inicializa o RMCorte com comportamento padrão.

$init_defaults = new \YOURLS\Config\InitDefaults;
new \YOURLS\Config\Init($init_defaults);
