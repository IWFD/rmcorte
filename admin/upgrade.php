<?php
define( 'YOURLS_ADMIN', true );
define( 'YOURLS_UPGRADING', true );
require_once( dirname( __DIR__ ).'/includes/load-yourls.php' );
yourls_maybe_require_auth();

yourls_html_head( 'upgrade', yourls__( 'Upgrade YOURLS' ) );
yourls_html_logo();
yourls_html_menu();
?>
		<h2><?php yourls_e( 'Upgrade YOURLS' ); ?></h2>
<?php

// Checa se está atualizado ou vai precisar 
if ( !yourls_upgrade_is_needed() ) {
	echo '<p>' . yourls_s( 'Atualização não é necessária. Voltar ao <a href="%s">Início</a>!', yourls_admin_url('index.php') ) . '</p>';


} else {
	/*
	Passo 1: Criar novas tabelas e preenchê-las, atualizar a estrutura de tabelas antigas.
	Passo 2: Converta cada linha de tabelas desatualizadas, se necessário.
	Passo 3: - Se aplicável, termine de atualizar as tabelas desatualizadas (índices etc..)
	- atualize a versão e db_version nas opções, está tudo feito! Vai tomar um café lá na Márcia.
	*/

	// O que a TiC estaria atualizando ?
	if ( isset( $_GET['oldver'] ) && isset( $_GET['oldsql'] ) ) {
		$oldver = yourls_sanitize_version($_GET['oldver']);
		$oldsql = intval($_GET['oldsql']);
	} else {
		list( $oldver, $oldsql ) = yourls_get_current_version_from_sql();
	}

	// Pra que estamos atualizando ?
	$newver = YOURLS_VERSION;
	$newsql = YOURLS_DB_VERSION;

	// Verbose & detalhes feios
	yourls_debug_mode(true);

	// Vamos lá...
	$step = ( isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 0 );
	switch( $step ) {

		default:
		case 0:
			?>
			<p><?php yourls_e( 'Sua instalação atual precisa ser atualizada.' ); ?></p>
			<p><?php yourls_e( 'Por favor, é recomendado que você faça <strong>backup do </strong>banco de dados<br/>(A TiC tem que fazer isso regularmente mesmo..)' ); ?></p>
			<p><?php yourls_e( "Nada terrível <em>DEVE</em> acontecer, mas não quer dizer que não <em>venha</em> acontecer, certo ? ;)" ); ?></p>
			<p><?php yourls_e( "Em cada etapa, se <span class='error'>algo der errado</span>, você verá uma mensagem e a TiC espera ter uma maneira rápida de corrigir." ); ?></p>
			<p><?php yourls_e( 'Se tudo for rápido demais e você não conseguir ler, <span class="success">bom pra você</span>, segue o baile :)' ); ?></p>
			<p><?php yourls_e( 'Você estando pronto, pressione "Upgrade" !' ); ?></p>
			<?php
			echo "
			<form action='upgrade.php?' method='get'>
			<input type='hidden' name='step' value='1' />
			<input type='hidden' name='oldver' value='$oldver' />
			<input type='hidden' name='newver' value='$newver' />
			<input type='hidden' name='oldsql' value='$oldsql' />
			<input type='hidden' name='newsql' value='$newsql' />
			<input type='submit' class='primary' value='" . yourls_esc_attr__( 'Upgrade' ) . "' />
			</form>";

			break;

		case 1:
		case 2:
			$upgrade = yourls_upgrade( $step, $oldver, $newver, $oldsql, $newsql );
			break;

		case 3:
			$upgrade = yourls_upgrade( 3, $oldver, $newver, $oldsql, $newsql );
			echo '<p>' . yourls__( 'Sua instalação agora está atualizada ! ' ) . '</p>';
			echo '<p>' . yourls_s( 'Voltar para <a href="%s">Interface de Admin</a>', yourls_admin_url('index.php') ) . '</p>';
	}

}

?>

<?php yourls_html_footer(); ?>
