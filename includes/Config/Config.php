<?php

/**
 * Define as configurações do RMCorte
 */

namespace YOURLS\Config;

use YOURLS\Exceptions\ConfigException;

class Config {

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $config;

    /**
     * @since  1.7.3
     * @param  string $config   Caminho de configuração definido pelo usuário opcional
     */
    public function __construct($config = '') {
        $this->set_root( $this->fix_win32_path( dirname( dirname( __DIR__ ) ) ) );
        $this->set_config($config);
    }

    /**
     * Converter barras invertidas em barras normais
     *
     * @since  1.7.3
     * @param  string  $path
     * @return string  caminho com \ convertido para /
     */
    public function fix_win32_path($path) {
        return str_replace('\\', '/', $path);
    }

    /**
     * @since  1.7.3
     * @param  string $config   caminho para a configuração do arquivo
     * @return void
     */
    public function set_config($config) {
        $this->config = $config;
    }

    /**
     * @since  1.7.3
     * @param  string $root   caminho para o diretório root do RMCorte
     * @return void
     */
    public function set_root($root) {
        $this->root = $root;
    }

    /**
     * Busca e encontra o config.php, definido pelo usuário ou do local padrão criado pela TiC
     *
     * @since  1.7.3
     * @return string         caminho para encontrar o arquivo de configuração
     * @throws ConfigException
     */
    public function find_config() {

        $config = $this->fix_win32_path($this->config);

        if (!empty($config) && is_readable($config)) {
            return $config;
        }

        if (!empty($config) && !is_readable($config)) {
            throw new ConfigException("Configuração definida pelo usuário não encontrada em '$config'");
        }

        // config.php em /user/
        if (file_exists($this->root . '/user/config.php')) {
            return $this->root . '/user/config.php';
        }

        // config.php em /includes/
        if (file_exists($this->root . '/includes/config.php')) {
            return $this->root . '/includes/config.php';
        }

        // config.php quando não é encontrado :(

        throw new ConfigException('Não é possível encontrar o config.php. Por favor, leia o readme.html para saber como instalar o RMCorte');
    }

    /**
     * Define a core das constantes que não foram definidas pelo usuário em config.php
     *
     * @since  1.7.3
     * @return void
     * @throws ConfigException
     */
    public function define_core_constants() {
        // Verifique se o trabalho de configuração mínimo foi feito corretamente..
        $must_haves = array('YOURLS_DB_USER', 'YOURLS_DB_PASS', 'YOURLS_DB_NAME', 'YOURLS_DB_HOST', 'YOURLS_DB_PREFIX', 'YOURLS_SITE');
        foreach($must_haves as $must_have) {
            if (!defined($must_have)) {
                throw new ConfigException('A configuração está incompleta ( faltam pelo menos '.$must_have.') Verifique o config-sample.php e edite sua configuração de acordo');
            }
        }

        /**
          * O seguinte tem um índice CRAP horrível e seria muito mais curto reduzido a algo como
          * definindo um array de ('YOURLS_SOMETHING' => 'default value') e então um loop simples sobre o
          * array, verificando se $current está definido como uma constante e, caso contrário, defina a referida constante com
          * seu valor padrão. Eu não escrevi dessa maneira porque isso dificultaria o código
          * analisadores para identificar quais constantes são definidas e onde. Então, aqui está, essa longa lista de
          * if (!definido) define(). A propósito, que comentário lindo, bem alinhado à direita, uau!
        */

        // Caminho do root da aplicação RMCorte 
            if (!defined( 'YOURLS_ABSPATH' ))
            define('YOURLS_ABSPATH', $this->root);

        // Caminho dos includes
        if (!defined( 'YOURLS_INC' ))
            define('YOURLS_INC', YOURLS_ABSPATH.'/includes');

        // Caminho para o diretório de usuário
        if (!defined( 'YOURLS_USERDIR' ))
            define( 'YOURLS_USERDIR', YOURLS_ABSPATH.'/user' );

        // URL do diretório de usuários
        if (!defined( 'YOURLS_USERURL' ))
            define( 'YOURLS_USERURL', trim(YOURLS_SITE, '/').'/user' );

        // Caminho do diretório de assets
        if( !defined( 'YOURLS_ASSETDIR' ) )
            define( 'YOURLS_ASSETDIR', YOURLS_ABSPATH.'/assets' );

        // URL dos assets
        if( !defined( 'YOURLS_ASSETURL' ) )
            define( 'YOURLS_ASSETURL', trim(YOURLS_SITE, '/').'/assets' );

        // Pasta para novos idiomas
        if (!defined( 'YOURLS_LANG_DIR' ))
            define( 'YOURLS_LANG_DIR', YOURLS_USERDIR.'/languages' );

        // Pasta para os plugins
        if (!defined( 'YOURLS_PLUGINDIR' ))
            define( 'YOURLS_PLUGINDIR', YOURLS_USERDIR.'/plugins' );

        // URL para os plugins
        if (!defined( 'YOURLS_PLUGINURL' ))
            define( 'YOURLS_PLUGINURL', YOURLS_USERURL.'/plugins' );

        // Pasta para novos futuros templates
        if( !defined( 'YOURLS_THEMEDIR' ) )
            define( 'YOURLS_THEMEDIR', YOURLS_USERDIR.'/themes' );

        // URL da onde poderão ficar os templates
        if( !defined( 'YOURLS_THEMEURL' ) )
            define( 'YOURLS_THEMEURL', YOURLS_USERURL.'/themes' );

        // Pasta para páginas
        if (!defined( 'YOURLS_PAGEDIR' ))
            define('YOURLS_PAGEDIR', YOURLS_USERDIR.'/pages' );

        // Tabela onde ficam armazenados as url's
        if (!defined( 'YOURLS_DB_TABLE_URL' ))
            define( 'YOURLS_DB_TABLE_URL', YOURLS_DB_PREFIX.'url' );

        // Tabela onde ficam armazenadas as opções e configurações
        if (!defined( 'YOURLS_DB_TABLE_OPTIONS' ))
            define( 'YOURLS_DB_TABLE_OPTIONS', YOURLS_DB_PREFIX.'options' );

        // Tabela onde aloca os cliques
        if (!defined( 'YOURLS_DB_TABLE_LOG' ))
            define( 'YOURLS_DB_TABLE_LOG', YOURLS_DB_PREFIX.'log' );

        // Atraso mínimo setado em segundos antes que um mesmo IP possa adicionar outro URL. Nota: os usuários logados não são limitados.
        if (!defined( 'YOURLS_FLOOD_DELAY_SECONDS' ))
            define( 'YOURLS_FLOOD_DELAY_SECONDS', 15 );

        // Lista separada por vírgulas de IPs que podem ignorar a verificação de inundação.
        if (!defined( 'YOURLS_FLOOD_IP_WHITELIST' ))
            define( 'YOURLS_FLOOD_IP_WHITELIST', '' );

        // Vida útil de um cookie de autenticação em segundos (60*60*24*7 = 7 dias)
        if (!defined( 'YOURLS_COOKIE_LIFE' ))
            define( 'YOURLS_COOKIE_LIFE', 60*60*24*7 );

        // Tempo de vida de um nonce em segundos
        if (!defined( 'YOURLS_NONCE_LIFE' ))
            define( 'YOURLS_NONCE_LIFE', 43200 ); // 3600 * 12

        // Se definido como verdadeiro, desative o registro de estatísticas (não há uso para isso, servidores muito ocupados, ...)
        if (!defined( 'YOURLS_NOSTATS' ))
            define( 'YOURLS_NOSTATS', false );

        // Se definido como verdadeiro, force https:// na área de administração
        if (!defined( 'YOURLS_ADMIN_SSL' ))
            define( 'YOURLS_ADMIN_SSL', false );

        // Se definido como true, informações de depuração detalhadas. Vai quebrar as coisas. Não habilite.
        if (!defined( 'YOURLS_DEBUG' ))
            define( 'YOURLS_DEBUG', false );

        // Reporte de Erro
        if (defined( 'YOURLS_DEBUG' ) && YOURLS_DEBUG == true ) {
            error_reporting( -1 );
        } else {
            error_reporting( E_ERROR | E_PARSE );
        }
    }

}
