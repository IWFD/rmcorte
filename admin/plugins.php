<?php
define( 'YOURLS_ADMIN', true );
require_once( dirname( __DIR__ ).'/includes/load-yourls.php' );
yourls_maybe_require_auth();

// Gerencia páginas de administração de plugins
if( isset( $_GET['page'] ) && !empty( $_GET['page'] ) ) {
	yourls_plugin_admin_page( $_GET['page'] );
    die();
}

// Gerencia a ativação/desativação de plugins
if( isset( $_GET['action'] ) ) {

	// Checa o nonce
	yourls_verify_nonce( 'manage_plugins', $_REQUEST['nonce'] ?? '');

	// Check se o arquivo do plugin é válido
	if(isset( $_GET['plugin'] ) && yourls_is_a_plugin_file(YOURLS_PLUGINDIR . '/' . $_GET['plugin'] . '/plugin.php') ) {

		// Ativa / Desativa
		switch( $_GET['action'] ) {
			case 'activate':
				$result = yourls_activate_plugin( $_GET['plugin'].'/plugin.php' );
				if( $result === true ) {
                    yourls_redirect(yourls_admin_url('plugins.php?success=activated'), 302);
                    exit();
                }
				break;

			case 'deactivate':
				$result = yourls_deactivate_plugin( $_GET['plugin'].'/plugin.php' );
				if( $result === true ) {
                    yourls_redirect(yourls_admin_url('plugins.php?success=deactivated'), 302);
                    exit();
                }
				break;

			default:
				$result = yourls__( 'Ação não suportada' );
				break;
		}
	} else {
		$result = yourls__( 'Nenhum plug-in especificado ou não é um plug-in válido' );
	}

	yourls_add_notice( $result );
}

// Mensagem de sucesso - Seja ativando ou desativando.
if( isset( $_GET['success'] ) && ( ( $_GET['success'] == 'activated' ) OR ( $_GET['success'] == 'deactivated' ) ) ) {
	if( $_GET['success'] == 'activated' ) {
		$message = yourls__( 'Plugin foi ativado.' );
	} elseif ( $_GET['success'] == 'deactivated' ) {
		$message = yourls__( 'Plugin foi desativado.' );
	}
	yourls_add_notice( $message );
}

yourls_html_head( 'plugins', yourls__( 'Plugins' ) );
yourls_html_logo();
yourls_html_menu();
?>

	<main role="main">
	<h2><?php yourls_e( 'Plugins' ); ?></h2>

	<?php
	$plugins = (array)yourls_get_plugins();
	uasort( $plugins, 'yourls_plugins_sort_callback' );

	$count = count( $plugins );
	$plugins_count = sprintf( yourls_n( '%s plugin', '%s plugins', $count ), $count );
	$count_active = yourls_has_active_plugins();
	?>

	<p id="plugin_summary"><?php yourls_se( 'Atualmente, existe <strong>%1$s</strong> instalado, e <strong>%2$s</strong> ativado.', $plugins_count, $count_active ); ?></p>

	<table id="main_table" class="tblSorter" cellpadding="0" cellspacing="1">
	<thead>
		<tr>
			<th><?php yourls_e( 'Nome do Plugin' ); ?></th>
			<th><?php yourls_e( 'Versão' ); ?></th>
			<th><?php yourls_e( 'Descrição' ); ?></th>
			<th><?php yourls_e( 'Autor' ); ?></th>
			<th><?php yourls_e( 'Ação' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php

	$nonce = yourls_create_nonce( 'manage_plugins' );

	foreach( $plugins as $file=>$plugin ) {

		// Campos padrão para ler do cabeçalho do plugin
		$fields = array(
			'name'       => 'Nome do Plugin',
			'uri'        => 'URL do Plugin',
			'desc'       => 'Descrição',
			'version'    => 'Versão',
			'author'     => 'Autor',
			'author_uri' => 'URL do Autor'
		);

		// Percorre todos os campos padrão, obtem o valor, se houver, e o redefine
		foreach( $fields as $field=>$value ) {
			if( isset( $plugin[ $value ] ) ) {
				$data[ $field ] = $plugin[ $value ];
			} else {
				$data[ $field ] = yourls__('(sem info)');
			}
			unset( $plugin[$value] );
		}

		$plugindir = trim( dirname( $file ), '/' );

		if( yourls_is_active_plugin( $file ) ) {
			$class = 'active';
			$action_url = yourls_nonce_url( 'manage_plugins', yourls_add_query_arg( array('action' => 'deactivate', 'plugin' => $plugindir ), yourls_admin_url('plugins.php') ) );
			$action_anchor = yourls__( 'Desativado' );
		} else {
			$class = 'inactive';
			$action_url = yourls_nonce_url( 'manage_plugins', yourls_add_query_arg( array('action' => 'activate', 'plugin' => $plugindir ), yourls_admin_url('plugins.php') ) );
			$action_anchor = yourls__( 'Ativado' );
		}

		// Outros "Campos: Valor" no cabeçalho ? Pega eles também!
		if( $plugin ) {
			foreach( $plugin as $extra_field=>$extra_value ) {
				$data['desc'] .= "<br/>\n<em>$extra_field</em>: $extra_value";
				unset( $plugin[$extra_value] );
			}
		}

		$data['desc'] .= '<br/><small>' . yourls_s( 'localização do arquivo do plug-in: %s', $file) . '</small>';

		printf( "<tr class='plugin %s'><td class='plugin_name'><a href='%s'>%s</a></td><td class='plugin_version'>%s</td><td class='plugin_desc'>%s</td><td class='plugin_author'><a href='%s'>%s</a></td><td class='plugin_actions actions'><a href='%s'>%s</a></td></tr>",
			$class, $data['uri'], $data['name'], $data['version'], $data['desc'], $data['author_uri'], $data['author'], $action_url, $action_anchor
			);

	}
	?>
	</tbody>
	</table>

	<script type="text/javascript">
	yourls_defaultsort = 0;
	yourls_defaultorder = 0;
	<?php if ($count_active) { ?>
	$('#plugin_summary').append('<span id="toggle_plugins">filter</span>');
	$('#toggle_plugins').css({'background':'transparent url("../images/filter.svg") top left no-repeat','display':'inline-block','text-indent':'-9999px','width':'16px','height':'16px','margin-left':'3px','cursor':'pointer'})
		.attr('title', '<?php echo yourls_esc_attr__( 'Alternar plugins ativos/inativos' ); ?>')
		.click(function(){
			$('#main_table tr.inactive').toggle();
		});
	<?php } ?>
	</script>

	<p><?php yourls_e( 'Se algo der errado depois de ativar um plugin e você não puder usar RMCorte ou acessar esta página, simplesmente renomeie ou exclua o diretório ou renomeie o arquivo do plugin para algo diferente de <code>plugin.php</code>.' ); ?></p>

	<h3><?php yourls_e( 'Mais Plugins' ); ?></h3>

	<p><?php yourls_e( 'Para mais plugins, solicite para a TiC <a href="http://nettic.irede.net"></a>.' ); ?></p>
	</main>

<?php yourls_html_footer(); ?>
