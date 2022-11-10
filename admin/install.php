<?php
define( 'YOURLS_ADMIN', true );
define( 'YOURLS_INSTALLING', true );
require_once( dirname( __DIR__ ).'/includes/load-yourls.php' );

$error   = array();
$warning = array();
$success = array();

// Faz a Checagem Inicial de Segurança.
if ( !yourls_check_PDO() ) {
	$error[] = yourls__( 'Extensão PHP PDO não foi encontrada.' );
	yourls_debug_log( 'Extensão PHP PDO não foi encontrada.' );
}

if ( !yourls_check_database_version() ) {
	$error[] = yourls_s( '%s esta versão do MySQL está muito antiga. Verifique com a TiC para fazer a devida atualização.', 'MySQL' );
	yourls_debug_log( 'Versão do MySQL: ' . yourls_get_database_version() );
}

if ( !yourls_check_php_version() ) {
	$error[] = yourls_s( '%s esta versão do PHP está muito antiga. Verifique com a TiC para fazer a devida atualização.', 'PHP' );
	yourls_debug_log( 'Versão do PHP: ' . PHP_VERSION );
}

// O RMCorte já está instalado ?
if ( yourls_is_installed() ) {
	$error[] = yourls__( 'RMCorte já está devidamente instalado.' );
	// checa se o .htaccess existe, recria ele por cima. Não faz a checagem do erro.
	if( !file_exists( YOURLS_ABSPATH.'/.htaccess' ) ) {
		yourls_create_htaccess();
	}
}

// Inicia a instalação de possível e for necessário.
if ( isset($_REQUEST['install']) && count( $error ) == 0 ) {
	// Cria/atualiza o arquivo .htaccess
	if ( yourls_create_htaccess() ) {
		$success[] = yourls__( 'Arquivo <tt>.htaccess</tt> criado/atualizado com sucesso.' );
	} else {
		$warning[] = yourls__( 'Sem permissionamento para escrita <tt>.htaccess</tt> em diretório root da aplicação. A TiC pode criar de forma manual este arquivo. Entre em contato <a href="http://nettic.irede.net">aqui</a>.' );
	}

	// Criação das tabelas de SQL
	$install = yourls_create_sql_tables();
	if ( isset( $install['error'] ) )
		$error = array_merge( $error, $install['error'] );
	if ( isset( $install['success'] ) )
		$success = array_merge( $success, $install['success'] );
}


// Inicia o output
yourls_html_head( 'install', yourls__( 'Install YOURLS' ) );
?>
<div id="login">
	<form method="post" action="?"><?php // resetar qualquer parâmetro de QUERY ?>
		<p>
			<img src="<?php yourls_site_url(); ?>/images/yourls-logo.svg" id="yourls-logo" alt="RMCorte" title="RMCorte" />
		</p>
		<?php
			// Imprime os erros, avisos e mensagens de sucesso.
			foreach ( array ('error', 'warning', 'success') as $info ) {
				if ( count( $$info ) > 0 ) {
					echo "<ul class='$info'>";
					foreach( $$info as $msg ) {
						echo '<li>'.$msg."</li>\n";
					}
					echo '</ul>';
				}
			}

			//Exibe botão de instalação ou link para área de administração, se aplicável claro..
			if( !yourls_is_installed() && !isset($_REQUEST['install']) ) {
				echo '<p style="text-align: center;"><input type="submit" name="install" value="' . yourls__( 'Instalar RMCorte') .'" class="button" /></p>';
			} else {
				if( count($error) == 0 )
					echo '<p style="text-align: center;">&raquo; <a href="'.yourls_admin_url().'" title="' . yourls__( 'Página de Administração do RMCorte') . '">' . yourls__( 'Página de Administração do RMCorte') . '</a></p>';
			}
		?>
	</form>
</div>
<?php yourls_html_footer(); ?>
