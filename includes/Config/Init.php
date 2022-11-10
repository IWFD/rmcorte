<?php

/**
 * Ações do RMCorte ao instanciar
 */

namespace YOURLS\Config;

class Init {

    /**
     * @var InitDefaults
     */
    protected $actions;

    /**
     * @since  1.7.3
     *
     * @param InitDefaults $actions
     */
    public function __construct(InitDefaults $actions) {

        $this->actions = $actions;

        // Inclui arquivos principais
        if ($actions->include_core_funcs === true) {
            $this->include_core_functions();
        }

        // Aplica o fuso horário UTC. A data/hora podem ser ajustadas com um plugin ou alteração no código.
        if ($actions->default_timezone === true) {
            date_default_timezone_set( 'UTC' );
        }

        // Carrega Local
        if ($actions->load_default_textdomain === true) {
            yourls_load_default_textdomain();
        }

        // Verifique se a aplicação está em modo de manutenção - se sim, ele morrerá aqui.
        if ($actions->check_maintenance_mode === true) {
            yourls_check_maintenance_mode();
        }

        // Ajsuta o  REQUEST_URI para o IIS
        if ($actions->fix_request_uri === true) {
            yourls_fix_request_uri();
        }

        // Se a solicitação de uma página de administração for http:// e SSL for necessário, redireciona..
        if ($actions->redirect_ssl === true) {
            $this->redirect_ssl_if_needed();
        }

        // Crie o objeto RMCorte $ydb que conterá tudo o que precisa globalmente
        if ($actions->include_db === true) {
            $this->include_db_files();
        }

        // Permitir a inclusão antecipada de uma camada de cache
        if ($actions->include_cache === true) {
            $this->include_cache_files();
        }

        // Interrompe a inicialização aqui se desejar uma inicialização rápida (para testes/depuração/não use)
        if ($actions->return_if_fast_init === true && defined('YOURLS_FAST_INIT') && YOURLS_FAST_INIT){
            return;
        }

        // Lê as opções desde o início
        if ($actions->get_all_options === true) {
            yourls_get_all_options();
        }

        // Função de desligamento do registro
        if ($actions->register_shutdown === true) {
            register_shutdown_function( 'yourls_shutdown' );
        }

        // Core é carregado neste momento
        if ($actions->core_loaded === true) {
            yourls_do_action( 'init' ); // plugins não são ativados ainda
        }

        // Verifica se é necessário redirecionar para o procedimento de instalação
        if ($actions->redirect_to_install === true) {
            if (!yourls_is_installed() && !yourls_is_installing()) {
                yourls_no_cache_headers();
                yourls_redirect( yourls_admin_url('install.php'), 307 );
                exit();
            }
        }

        // Verifique se a atualização é necessária (ignorada se atualizar ou instalar)
        if ($actions->check_if_upgrade_needed === true) {
            if (!yourls_is_upgrading() && !yourls_is_installing() && yourls_upgrade_is_needed()) {
                yourls_no_cache_headers();
                yourls_redirect( yourls_admin_url('upgrade.php'), 307 );
                exit();
            }
        }

        // Carrega todos os plugins
        if ($actions->load_plugins === true) {
            yourls_load_plugins();
        }

        // Acionar a ação carregada do plug-in em específico
        if ($actions->plugins_loaded_action === true) {
            yourls_do_action( 'plugins_loaded' );
        }

        // Verifica se esta é a última versão do RMCorte
        if ($actions->check_new_version === true) {
            if (yourls_is_installed() && !yourls_is_upgrading()) {
                yourls_tell_if_new_version();
            }
        }

        if ($actions->init_admin === true) {
            if (yourls_is_admin()) {
                yourls_do_action( 'admin_init' );
            }
        }

    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function redirect_ssl_if_needed() {
        if (yourls_is_admin() && yourls_needs_ssl() && !yourls_is_ssl()) {
            if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
                yourls_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
            } else {
                yourls_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            }
            exit();
        }
    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function include_db_files() {
        // Permitir substituição drop-in para o mecanismo de banco de dados
        if (file_exists(YOURLS_USERDIR.'/db.php')) {
            require_once YOURLS_USERDIR.'/db.php';
        } else {
            require_once YOURLS_INC.'/class-mysql.php';
            yourls_db_connect();
        }
    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function include_cache_files() {
        if (file_exists(YOURLS_USERDIR.'/cache.php')) {
            require_once YOURLS_USERDIR.'/cache.php';
        }
    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function include_core_functions() {
        require_once YOURLS_INC.'/version.php';
        require_once YOURLS_INC.'/functions.php';
        require_once YOURLS_INC.'/functions-geo.php';
        require_once YOURLS_INC.'/functions-shorturls.php';
        require_once YOURLS_INC.'/functions-debug.php';
        require_once YOURLS_INC.'/functions-options.php';
        require_once YOURLS_INC.'/functions-links.php';
        require_once YOURLS_INC.'/functions-plugins.php';
        require_once YOURLS_INC.'/functions-formatting.php';
        require_once YOURLS_INC.'/functions-api.php';
        require_once YOURLS_INC.'/functions-kses.php';
        require_once YOURLS_INC.'/functions-l10n.php';
        require_once YOURLS_INC.'/functions-compat.php';
        require_once YOURLS_INC.'/functions-html.php';
        require_once YOURLS_INC.'/functions-http.php';
        require_once YOURLS_INC.'/functions-infos.php';
        require_once YOURLS_INC.'/functions-deprecated.php';
        require_once YOURLS_INC.'/functions-auth.php';
        require_once YOURLS_INC.'/functions-upgrade.php';
        require_once YOURLS_INC.'/functions-install.php';
    }

}
