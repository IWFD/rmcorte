<?php

/**
 * Exibe o <h1> header e a logo
 *
 * @return void
 */
function yourls_html_logo()
{
	yourls_do_action('pre_html_logo');
?>
	<header role="banner">
		<h1>
			<a href="<?php echo yourls_admin_url('index.php') ?>" title="RMCORTE"><span>RMCORTE</span>: <span>R</span> <span></span><span>M</span> <span>C</span>orte<br />
				<img src="<?php yourls_site_url(); ?>/images/yourls-logo.svg?v=<?php echo YOURLS_VERSION; ?>" id="yourls-logo" alt="RMCorte" title="RMCorte" /></a>
		</h1>
	</header>
<?php
	yourls_do_action('html_logo');
}

/**
  * Exibir cabeçalho HTML e tag <body>
  *
  * @param string $context Contexto da página (stats, index, infos, ...)
  * @param string $ title Título HTML da página
  * @return void
  */
function yourls_html_head($context = 'index', $title = '')
{

	yourls_do_action('pre_html_head', $context, $title);

	// Todos os componentes para false, exceto quando especificado true - Regra Geral
	$share = $insert = $tablesorter = $tabs = $cal = $charts = false;

	// Carrega os componentes necessários
	switch ($context) {
		case 'infos':
			$share = $tabs = $charts = true;
			break;

		case 'bookmark':
			$share = $insert = $tablesorter = true;
			break;

		case 'index':
			$insert = $tablesorter = $cal = $share = true;
			break;

		case 'plugins':
		case 'tools':
			$tablesorter = true;
			break;

		case 'install':
		case 'login':
		case 'new':
		case 'upgrade':
			break;
	}

	// Força a não tem cache em todas as páginas da Administração
	if (yourls_is_admin() && !headers_sent()) {
		yourls_no_cache_headers();
		yourls_no_frame_header();
		yourls_content_type_header(yourls_apply_filter('html_head_content-type', 'text/html'));
		yourls_do_action('admin_headers', $context, $title);
	}

	// Salva o Contexto
	yourls_set_html_context($context);

	// Classe do Body - Celular & Desktop
	$bodyclass = yourls_apply_filter('bodyclass', '');
	$bodyclass .= (yourls_is_mobile_device() ? 'mobile' : 'desktop');

	// Título da Página
	$_title = 'ENCURTADOR &mdash; Encurte Qualquer URL by TiC  | ' . yourls_link();
	$title = $title ? $title . " &laquo; " . $_title : $_title;
	$title = yourls_apply_filter('html_title', $title, $context);

?>
	<!DOCTYPE html>
	<html <?php yourls_html_language_attributes(); ?>>

	<head>
		<title><?php echo $title ?></title>
		<meta http-equiv="Content-Type" content="<?php echo yourls_apply_filter('html_head_meta_content-type', 'text/html; charset=utf-8'); ?>" />
		<meta name="generator" content="YOURLS <?php echo YOURLS_VERSION ?>" />
		<meta name="description" content="YOURLS &raquo; Your Own URL Shortener' | <?php yourls_site_url(); ?>" />
		<?php yourls_do_action('html_head_meta', $context); ?>
		<?php yourls_html_favicon(); ?>
		<script src="<?php yourls_site_url(); ?>/js/jquery-3.5.1.min.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<script src="<?php yourls_site_url(); ?>/js/common.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<script src="<?php yourls_site_url(); ?>/js/jquery.notifybar.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/style.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
		<?php if ($tabs) { ?>
			<link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/infos.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
			<script src="<?php yourls_site_url(); ?>/js/infos.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<?php } ?>
		<?php if ($tablesorter) { ?>
			<link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/tablesorter.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
			<script src="<?php yourls_site_url(); ?>/js/jquery-3.tablesorter.min.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
			<script src="<?php yourls_site_url(); ?>/js/tablesorte.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<?php } ?>
		<?php if ($insert) { ?>
			<script src="<?php yourls_site_url(); ?>/js/insert.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<?php } ?>
		<?php if ($share) { ?>
			<link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/share.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
			<script src="<?php yourls_site_url(); ?>/js/share.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
			<script src="<?php yourls_site_url(); ?>/js/clipboard.min.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<?php } ?>
		<?php if ($cal) { ?>
			<link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/cal.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
			<?php yourls_l10n_calendar_strings(); ?>
			<script src="<?php yourls_site_url(); ?>/js/jquery.cal.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
		<?php } ?>
		<?php if ($charts) { ?>
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
				google.load('visualization', '1.0', {
					'packages': ['corechart', 'geochart']
				});
			</script>
		<?php } ?>
		<script type="text/javascript">
			//<![CDATA[
			var ajaxurl = '<?php echo yourls_admin_url('admin-ajax.php'); ?>';
			//]]>
		</script>
		<?php yourls_do_action('html_head', $context); ?>
	</head>

	<body class="<?php echo $context; ?> <?php echo $bodyclass; ?>">
		<div id="wrap">
		<?php
	}

	/**
	* Exibe o rodapé HTML (incluindo fechamento de corpo e tags html)
	*
	* A função yourls_die() chamará esta função com o parâmetro opcional definido como false: provavelmente, se estará usando yourls_die(),
	* há um problema, então talvez não adicione a ele enviando outra consulta SQL
	*
	* @param bool $can_query Se definido como false, não tentará enviar outra consulta ao servidor de banco de dados
	* @return void
	*/
	function yourls_html_footer($can_query = true)
	{
		if ($can_query & yourls_get_debug_mode()) {
			$num_queries = yourls_get_num_queries();
			$num_queries = ' &ndash; ' . sprintf(yourls_n('1 query', '%s queries', $num_queries), $num_queries);
		} else {
			$num_queries = '';
		}

		?>
		</div><?php // wrap 
				?>
		<footer id="footer" role="contentinfo">
			<p>
				<?php
				$footer  = yourls_s('Desenvolvido por %s', '<a href="http://nettic.irede.net/" title="RMCorte by Israel Duarte">RMCorte</a> v ' . YOURLS_VERSION);
				$footer .= $num_queries;
				echo yourls_apply_filter('html_footer_text', $footer);
				?>
			</p>
		</footer>
		<?php if (yourls_get_debug_mode()) {
			echo '<div style="text-align:left"><pre>';
			echo join("\n", yourls_get_debug_log());
			echo '</pre></div>';
		} ?>
		<?php yourls_do_action('html_footer', yourls_get_html_context()); ?>
	</body>

	</html>
<?php
	}

	/**
	 * Exibe a caixa "Adicionar Nova URL"
	 *
	 * @param string $url URL para preencher a entrada com
	 * @param string $keyword Palavra-chave para preencher a entrada com
	 * @return void
	 */
	function yourls_html_addnew($url = '', $keyword = '')
	{
		$pre = yourls_apply_filter('shunt_html_addnew', false, $url, $keyword);
		if (false !== $pre) {
			return $pre;
		}
?>
	<main role="main">
		<div id="new_url">
			<div>
				<form id="new_url_form" action="" method="get">
					<div>
						<label for="add-url"><strong><?php yourls_e('Digite a URL'); ?></strong></label>:
						<input type="text" id="add-url" name="url" value="<?php echo $url; ?>" class="text" size="80" placeholder="https://" />
						<label for="add-keyword"><?php yourls_e('Opcional'); ?>: <strong><?php yourls_e('URL Curta Distinta'); ?></strong></label>:
						<input type="text" id="add-keyword" name="keyword" value="<?php echo $keyword; ?>" class="text" size="8" />
						<?php yourls_nonce_field('add_url', 'nonce-add'); ?>
						<input type="button" id="add-button" name="add-button" value="<?php yourls_e('Encurtar a URL'); ?>" class="button" onclick="add_link();" />
					</div>
				</form>
				<div id="feedback" style="display:none"></div>
			</div>
			<?php yourls_do_action('html_addnew'); ?>
		</div>
	<?php
	}

	/**
	* Exibe o Rodapé da Tabela Principal
	*
	* O array $param é definido em /admin/index.php, verifique a chamada yourls_html_tfooter()
	*
	* @param array $params Array de todos os parâmetros necessários
	* @return void
	*/
	function yourls_html_tfooter($params = array())
	{
		// Extrai manualmente todos os parâmetros do array. Eu preferi fazer desta forma, ao invés de usar extract(),
		// para tornar as coisas mais claras e explícitas sobre qual variável é usada.
		$search       = $params['search'];
		$search_text  = $params['search_text'];
		$search_in    = $params['search_in'];
		$sort_by      = $params['sort_by'];
		$sort_order   = $params['sort_order'];
		$page         = $params['page'];
		$perpage      = $params['perpage'];
		$click_filter = $params['click_filter'];
		$click_limit  = $params['click_limit'];
		$total_pages  = $params['total_pages'];
		$date_filter  = $params['date_filter'];
		$date_first   = $params['date_first'];
		$date_second  = $params['date_second'];

	?>
		<tfoot>
			<tr>
				<th colspan="6">
					<div id="filter_form">
						<form action="" method="get">
							<div id="filter_options">
								<?php

								//Primeiro controle de pesquisa: Texto para pesquisar
								$_input = '<input aria-label="' . yourls__('Buscar por') . '" type="text" name="search" class="text" size="12" value="' . yourls_esc_attr($search_text) . '" />';
								$_options = array(
									'all'     => yourls__('Todos os Campos'),
									'keyword' => yourls__('URL Encurtada'),
									'url'     => yourls__('URL'),
									'title'   => yourls__('Título'),
									'ip'      => yourls__('IP'),
								);
								$_select = yourls_html_select('search_in', $_options, $search_in, false, yourls__('Buscar em'));
								
								yourls_se('Buscar %1$s em %2$s', $_input, $_select);
								echo "&ndash;\n";

								// Segundo controle de pesquina: Ordenação por
								
								$_options = array(
									'keyword'      => yourls__('URL Encurtada'),
									'url'          => yourls__('URL'),
									'title'        => yourls__('Título'),
									'timestamp'    => yourls__('Data'),
									'ip'           => yourls__('IP'),
									'clicks'       => yourls__('Cliques'),
								);
								$_select = yourls_html_select('sort_by', $_options, $sort_by, false,  yourls__('Order por'));
								$sort_order = isset($sort_order) ? $sort_order : 'desc';
								$_options = array(
									'asc'  => yourls__('Ascendente'),
									'desc' => yourls__('Decrescente'),
								);
								$_select2 = yourls_html_select('sort_order', $_options, $sort_order, false,  yourls__('Sort order'));
								
								yourls_se('Listar %1$s %2$s', $_select, $_select2);
								echo "&ndash;\n";

								// Terceiro controle de busca: Exibe XX linhas
								
								$_input = '<input aria-label="' . yourls__('Exibição do Nº de linhas') . '" type="text" name="perpage" class="text" size="2" value="' . $perpage . '" />';
								yourls_se('Exibir %s linhas',  $_input);
								echo "<br/>\n";

								// Quarto controle de busca: Mostrar links com mais de XX cliques
								$_options = array(
									'more' => yourls__('Mais'),
									'less' => yourls__('Menos'),
								);
								$_select = yourls_html_select('click_filter', $_options, $click_filter, false, yourls__('Show links with'));
								$_input  = '<input aria-label="' . yourls__('Número de Cliques') . '" type="text" name="click_limit" class="text" size="4" value="' . $click_limit . '" /> ';
								
								yourls_se('Exibir links com %1$s que %2$s cliques', $_select, $_input);
								echo "<br/>\n";

								// Quinto Controle: Exibe links criados antes/depois/entre ...
								$_options = array(
									'before'  => yourls__('Antes'),
									'after'   => yourls__('Depois'),
									'between' => yourls__('Entre'),
								);
								$_select = yourls_html_select('date_filter', $_options, $date_filter, false, yourls__('Exibe links criados'));
								$_input  = '<input aria-label="' . yourls__('Selecione a data') . '" type="text" name="date_first" id="date_first" class="text" size="12" value="' . $date_first . '" />';
								$_and    = '<span id="date_and"' . ($date_filter === 'between' ? ' style="display:inline"' : '') . '> &amp; </span>';
								$_input2 = '<input aria-label="' . yourls__('Selecione uma data final') . '" type="text" name="date_second" id="date_second" class="text" size="12" value="' . $date_second . '"' . ($date_filter === 'between' ? ' style="display:inline"' : '') . '/>';
								
								yourls_se('Exibir links criados %1$s %2$s %3$s %4$s', $_select, $_input, $_and, $_input2);
								?>

								<div id="filter_buttons">
									<input type="submit" id="submit-sort" value="<?php yourls_e('Buscar'); ?>" class="button primary" />
									&nbsp;
									<input type="button" id="submit-clear-filter" value="<?php yourls_e('Limpar'); ?>" class="button" onclick="window.parent.location.href = 'index.php'" />
								</div>

							</div>
						</form>
					</div>

					<?php
					// Remove as chaves vazias do array $params para não sobrecarregar os links de paginação
					$params = array_filter($params, function ($val) {
						return $val !== '';
					}); // Remover chaves com valores vazios

					if (isset($search_text)) {
						$params['search'] = $search_text;
						unset($params['search_text']);
					}
					?>

					<div id="pagination">
						<span class="navigation">
							<?php if ($total_pages > 1) { ?>
								<span class="nav_total"><?php echo sprintf(yourls_n('1 página', '%s páginas', $total_pages), $total_pages); ?></span>
								<?php
								$base_page = yourls_admin_url('index.php');
								
								$p_start = max(min($total_pages - 4, $page - 2), 1);
								$p_end = min(max(5, $page + 2), $total_pages);
								if ($p_start >= 2) {
									$link = yourls_add_query_arg(array_merge($params, array('page' => 1)), $base_page);
									echo '<span class="nav_link nav_first"><a href="' . $link . '" title="' . yourls_esc_attr__('Vá para Primeira Página') . '">' . yourls__('&laquo; Primeira') . '</a></span>';
									echo '<span class="nav_link nav_prev"></span>';
								}
								for ($i = $p_start; $i <= $p_end; $i++) {
									if ($i == $page) {
										echo "<span class='nav_link nav_current'>$i</span>";
									} else {
										$link = yourls_add_query_arg(array_merge($params, array('page' => $i)), $base_page);
										echo '<span class="nav_link nav_goto"><a href="' . $link . '" title="' . sprintf(yourls_esc_attr('Página %s'), $i) . '">' . $i . '</a></span>';
									}
								}
								if (($p_end) < $total_pages) {
									$link = yourls_add_query_arg(array_merge($params, array('page' => $total_pages)), $base_page);
									echo '<span class="nav_link nav_next"></span>';
									echo '<span class="nav_link nav_last"><a href="' . $link . '" title="' . yourls_esc_attr__('Vá para Última Página') . '">' . yourls__('Última &raquo;') . '</a></span>';
								}
								?>
							<?php } ?>
						</span>
					</div>
				</th>
			</tr>
			<?php yourls_do_action('html_tfooter'); ?>
		</tfoot>
	<?php
	}

	function yourls_html_select($name, $options, $selected = '', $display = false, $label = '')
	{

		$options = yourls_apply_filter('html_select_options', $options, $name, $selected, $display, $label);
		$html = "<select aria-label='$label' name='$name' id='$name' size='1'>\n";
		foreach ($options as $value => $text) {
			$html .= "<option value='$value' ";
			$html .= $selected == $value ? ' selected="selected"' : '';
			$html .= ">$text</option>\n";
		}
		$html .= "</select>\n";
		$html  = yourls_apply_filter('html_select', $html, $name, $options, $selected, $display);
		if ($display)
			echo $html;
		return $html;
	}


	/**
	 * Exibe a caixa de compartilhamento rápido
	 *
	 * @param string $longurl          URL Original
	 * @param string $shorturl         URL Encurtada
	 * @param string $title            Título
	 * @param string $text             Texto para exibição
	 * @param string $shortlink_title  Substituição opcional para 'Seu link curto'
	 * @param string $share_title      Substituição opcional 
	 * @param bool $hidden Opcional.   Oculte a caixa por padrão (com css "display:none")
	 * @return void
	 */
	function yourls_share_box($longurl, $shorturl, $title = '', $text = '', $shortlink_title = '', $share_title = '', $hidden = false)
	{
		if ($shortlink_title == '')
			$shortlink_title = '<h2>' . yourls__('Link Encurtado ') . '</h2>';
		if ($share_title == '')
			$share_title = '<h2>' . yourls__('Compartilhamento Rápido') . '</h2>';

		
		$pre = yourls_apply_filter('shunt_share_box', false);
		if (false !== $pre)
			return $pre;

		$shorturl = yourls_normalize_uri($shorturl);

		$text   = ($text ? '"' . $text . '" ' : '');
		$title  = ($title ? "$title " : '');
		$share  = yourls_esc_textarea($title . $text . $shorturl);
		$count  = 280 - strlen($share);
		$hidden = ($hidden ? 'style="display:none;"' : '');

		// Permitir que plugins filtrem todos os dados
		$data = compact('longurl', 'shorturl', 'title', 'text', 'shortlink_title', 'share_title', 'share', 'count', 'hidden');
		$data = yourls_apply_filter('share_box_data', $data);
		extract($data);

		$_share = rawurlencode($share);
		$_url   = rawurlencode($shorturl);
	?>

		<div id="shareboxes" <?php echo $hidden; ?>>

			<?php yourls_do_action('shareboxes_before', $longurl, $shorturl, $title, $text); ?>

			<div id="copybox" class="share">
				<?php echo $shortlink_title; ?>
				<p><input id="copylink" class="text" size="32" value="<?php echo yourls_esc_url($shorturl); ?>" /></p>
				<p><small><?php yourls_e('Long link'); ?>: <a id="origlink" href="<?php echo yourls_esc_url($longurl); ?>"><?php echo yourls_esc_url($longurl); ?></a></small>
					<?php if (yourls_do_log_redirect()) { ?>
						<br /><small><?php yourls_e('Stats'); ?>: <a id="statlink" href="<?php echo yourls_esc_url($shorturl); ?>+"><?php echo yourls_esc_url($shorturl); ?>+</a></small>
						<input type="hidden" id="titlelink" value="<?php echo yourls_esc_attr($title); ?>" />
					<?php } ?>
				</p>
			</div>

			<?php yourls_do_action('shareboxes_middle', $longurl, $shorturl, $title, $text); ?>

			<div id="sharebox" class="share">
				<?php echo $share_title; ?>
				<div id="tweet">
					<span id="charcount" class="hide-if-no-js"><?php echo $count; ?></span>
					<textarea id="tweet_body"><?php echo $share; ?></textarea>
				</div>
				<p id="share_links"><?php yourls_e('Compartilhar'); ?>
					<a id="share_tw" href="https://twitter.com/intent/tweet?text=<?php echo $_share; ?>" title="<?php yourls_e('Tweet Isto!'); ?>" onclick="share('tw');return false">Twitter</a>
					<a id="share_fb" href="https://www.facebook.com/share.php?u=<?php echo $_url; ?>" title="<?php yourls_e('Compartilhe no Facebook'); ?>" onclick="share('fb');return false;">Facebook</a>
					<?php
					yourls_do_action('share_links', $longurl, $shorturl, $title, $text);
					// Nota: Na página principal de administração, não há parâmetros passados para o sharebox quando ele é desenhado.
					?>
				</p>
			</div>

			<?php yourls_do_action('shareboxes_after', $longurl, $shorturl, $title, $text); ?>

		</div>

	<?php
	}


	function yourls_die($message = '', $title = '', $header_code = 200)
	{
		yourls_do_action('pre_yourls_die', $message, $title, $header_code);

		yourls_status_header($header_code);

		if (!yourls_did_action('html_head')) {
			yourls_html_head();
			yourls_html_logo();
		}
		echo yourls_apply_filter('die_title', "<h2>$title</h2>");
		echo yourls_apply_filter('die_message', "<p>$message</p>");
	
		yourls_do_action('yourls_die');
		if (!yourls_did_action('html_footer')) {
			yourls_html_footer(false);
		}

		die(1);
	}

	/**
	* Retorna uma linha "Editar" para a tabela principal
	*
	* @param string $keyword Palavra-chave para editar
	* @return string HTML da linha de edição
	*/

	function yourls_table_edit_row($keyword)
	{
		$keyword = yourls_sanitize_keyword($keyword);
		$id = yourls_unique_element_id();
		$url = yourls_get_keyword_longurl($keyword);
		$title = htmlspecialchars(yourls_get_keyword_title($keyword));
		$safe_url = yourls_esc_attr($url);
		$safe_title = yourls_esc_attr($title);
		$safe_keyword = yourls_esc_attr($keyword);

		//Torne as strings sprintf() seguras: '%' -> '%%' | Segurança sempre!
		$safe_url = str_replace('%', '%%', $safe_url);
		$safe_title = str_replace('%', '%%', $safe_title);

		$www = yourls_link();

		$nonce = yourls_create_nonce('edit-save_' . $id);

		if ($url) {
			$return = <<<RETURN
<tr id="edit-$id" class="edit-row"><td colspan="5" class="edit-row"><strong>%s</strong>:<input type="text" id="edit-url-$id" name="edit-url-$id" value="$safe_url" class="text" size="70" /><br/><strong>%s</strong>: $www<input type="text" id="edit-keyword-$id" name="edit-keyword-$id" value="$safe_keyword" class="text" size="10" /><br/><strong>%s</strong>: <input type="text" id="edit-title-$id" name="edit-title-$id" value="$safe_title" class="text" size="60" /></td><td colspan="1"><input type="button" id="edit-submit-$id" name="edit-submit-$id" value="%s" title="%s" class="button" onclick="edit_link_save('$id');" />&nbsp;<input type="button" id="edit-close-$id" name="edit-close-$id" value="%s" title="%s" class="button" onclick="edit_link_hide('$id');" /><input type="hidden" id="old_keyword_$id" value="$safe_keyword"/><input type="hidden" id="nonce_$id" value="$nonce"/></td></tr>
RETURN;
			$return = sprintf($return, yourls__('URL Original'), yourls__('URL Encurtada'), yourls__('Title'), yourls__('Salvar'), yourls__('Salvar Novos Valores'), yourls__('Cancelar'), yourls__('Cancelar Edição'));
		} else {
			$return = '<tr class="edit-row notfound"><td colspan="6" class="edit-row notfound">' . yourls__('Erro, URL não encontrada') . '</td></tr>';
		}

		$return = yourls_apply_filter('table_edit_row', $return, $keyword, $url, $title);

		return $return;
	}

	/**
	 * Retorna uma linha "Adicionar" para a tabela principal
	 *
	 * @param string $keyword     Palavra-Chave (URL Encurtada)
	 * @param string $url         URL Original (URL Original/Longa)
	 * @param string $title       Título
	 * @param string $ip          IP
	 * @param string|int $clicks  Número de Cliques
	 * @param string $timestamp   Timestamp
	 * @return string             HTML da linha
	 */
	function yourls_table_add_row($keyword, $url, $title, $ip, $clicks, $timestamp)
	{
		$keyword  = yourls_sanitize_keyword($keyword);
		$id       = yourls_unique_element_id();
		$shorturl = yourls_link($keyword);

		$statlink = yourls_statlink($keyword);

		$delete_link = yourls_nonce_url(
			'delete-link_' . $id,
			yourls_add_query_arg(array('id' => $id, 'action' => 'delete', 'keyword' => $keyword), yourls_admin_url('admin-ajax.php'))
		);

		$edit_link = yourls_nonce_url(
			'edit-link_' . $id,
			yourls_add_query_arg(array('id' => $id, 'action' => 'edit', 'keyword' => $keyword), yourls_admin_url('admin-ajax.php'))
		);

		// Botões de link de ação: O array das opções
		$actions = array(
			'stats' => array(
				'href'    => $statlink,
				'id'      => "statlink-$id",
				'title'   => yourls_esc_attr__('Estatísticas'),
				'anchor'  => yourls__('Stats'),
			),
			'share' => array(
				'href'    => '',
				'id'      => "share-button-$id",
				'title'   => yourls_esc_attr__('Compartilhar'),
				'anchor'  => yourls__('Share'),
				'onclick' => "toggle_share('$id');return false;",
			),
			'edit' => array(
				'href'    => $edit_link,
				'id'      => "edit-button-$id",
				'title'   => yourls_esc_attr__('Editar'),
				'anchor'  => yourls__('Edit'),
				'onclick' => "edit_link_display('$id');return false;",
			),
			'delete' => array(
				'href'    => $delete_link,
				'id'      => "delete-button-$id",
				'title'   => yourls_esc_attr__('Excluir'),
				'anchor'  => yourls__('Delete'),
				'onclick' => "remove_link('$id');return false;",
			)
		);
		$actions = yourls_apply_filter('table_add_row_action_array', $actions, $keyword);

		// Botões de Ação
		$action_links = '';
		foreach ($actions as $key => $action) {
			$onclick = isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '"' : '';
			$action_links .= sprintf(
				'<a href="%s" id="%s" title="%s" class="%s" %s>%s</a>',
				$action['href'],
				$action['id'],
				$action['title'],
				'button button_' . $key,
				$onclick,
				$action['anchor']
			);
		}
		$action_links = yourls_apply_filter('action_links', $action_links, $keyword, $url, $ip, $clicks, $timestamp);

		if (!$title)
			$title = $url;

		$protocol_warning = '';
		if (!in_array(yourls_get_protocol($url), array('http://', 'https://')))
			$protocol_warning = yourls_apply_filter('add_row_protocol_warning', '<span class="warning" title="' . yourls__('Não é um link comum') . '">&#9733;</span>');

		// Linhas|Celulas - Array
		$cells = array(
			'keyword' => array(
				'template'      => '<a href="%shorturl%">%keyword_html%</a>',
				'shorturl'      => yourls_esc_url($shorturl),
				'keyword_html'  => yourls_esc_html($keyword),
			),
			'url' => array(
				'template'      => '<a href="%long_url%" title="%title_attr%">%title_html%</a><br/><small>%warning%<a href="%long_url%">%long_url_html%</a></small>',
				'long_url'      => yourls_esc_url($url),
				'title_attr'    => yourls_esc_attr($title),
				'title_html'    => yourls_esc_html(yourls_trim_long_string($title)),
				'long_url_html' => yourls_esc_html(yourls_trim_long_string(urldecode($url))),
				'warning'       => $protocol_warning,
			),
			'timestamp' => array(
				'template' => '<span class="timestamp" aria-hidden="true">%timestamp%</span> %date%',
				'timestamp' => $timestamp,
				'date'     => yourls_date_i18n(yourls_get_datetime_format('M d, Y H:i'), yourls_get_timestamp($timestamp)),
			),
			'ip' => array(
				'template' => '%ip%',
				'ip'       => $ip,
			),
			'clicks' => array(
				'template' => '%clicks%',
				'clicks'   => yourls_number_format_i18n($clicks, 0),
			),
			'actions' => array(
				'template' => '%actions% <input type="hidden" id="keyword_%id%" value="%keyword%"/>',
				'actions'  => $action_links,
				'id'       => $id,
				'keyword'  => $keyword,
			),
		);
		$cells = yourls_apply_filter('table_add_row_cell_array', $cells, $keyword, $url, $title, $ip, $clicks, $timestamp);

		// Ajustes no HTML
		$row = "<tr id=\"id-$id\">";
		foreach ($cells as $cell_id => $elements) {
			$row .= sprintf('<td class="%s" id="%s">', $cell_id, $cell_id . '-' . $id);
			$row .= preg_replace_callback('/%([^%]+)?%/', function ($match) use ($elements) {
				return $elements[$match[1]];
			}, $elements['template']);
			$row .= '</td>';
		}
		$row .= "</tr>";
		$row  = yourls_apply_filter('table_add_row', $row, $keyword, $url, $title, $ip, $clicks, $timestamp);

		return $row;
	}

	// Função para o Header
	function yourls_table_head()
	{
		$start = '<table id="main_table" class="tblSorter" cellpadding="0" cellspacing="1"><thead><tr>' . "\n";
		echo yourls_apply_filter('table_head_start', $start);

		$cells = yourls_apply_filter('table_head_cells', array(
			'shorturl' => yourls__('URL Encurtada'),
			'longurl'  => yourls__('URL Original'),
			'date'     => yourls__('Data'),
			'ip'       => yourls__('IP'),
			'clicks'   => yourls__('Cliques'),
			'actions'  => yourls__('Ações')
		));
		foreach ($cells as $k => $v) {
			echo "<th id='main_table_head_$k'><span>$v</span></th>\n";
		}

		$end = "</tr></thead>\n";
		echo yourls_apply_filter('table_head_end', $end);
	}


	function yourls_table_tbody_start()
	{
		echo yourls_apply_filter('table_tbody_start', '<tbody>');
	}

	
	function yourls_table_tbody_end()
	{
		echo yourls_apply_filter('table_tbody_end', '</tbody>');
	}

	
	function yourls_table_end()
	{
		echo yourls_apply_filter('table_end', '</table></main>');
	}



	/**
* Eco tag HTML para um link
*
* @param string $href URL para vincular
* @param string $anchor Texto âncora
* @param string $element ID do elemento
* @return void
*/
	function yourls_html_link($href, $anchor = '', $element = '')
	{
		if (!$anchor)
			$anchor = $href;
		if ($element)
			$element = sprintf('id="%s"', yourls_esc_attr($element));
		$link = sprintf('<a href="%s" %s>%s</a>', yourls_esc_url($href), $element, yourls_esc_html($anchor));
		echo yourls_apply_filter('html_link', $link);
	}

	/**
	* Exibe a tela de login. Nada além deste ponto.
	*
	* @param string $error_msg Mensagem de erro opcional a ser exibida
	* @return void
	*/

	function yourls_login_screen($error_msg = '')
	{
		yourls_html_head('login');

		$action = (isset($_GET['action']) && $_GET['action'] == 'logout' ? '?' : '');

		yourls_html_logo();
	?>
		<div id="login">
			<form method="post" action="<?php echo $action; ?>"> <?php // Reseta qualquer parâmetro de QUERY 
																	?>
				<?php
				if (!empty($error_msg)) {
					echo '<p class="error">' . $error_msg . '</p>';
				}
				yourls_do_action('login_form_top');
				?>
				<p>
					<label for="username"><?php yourls_e('Usuário'); ?></label><br />
					<input type="text" id="username" name="username" size="30" class="text" />
				</p>
				<p>
					<label for="password"><?php yourls_e('Senha'); ?></label><br />
					<input type="password" id="password" name="password" size="30" class="text" />
				</p>
				<?php
				yourls_do_action('login_form_bottom');
				?>
				<p style="text-align: right;">
					<?php yourls_nonce_field('admin_login'); ?>
					<input type="submit" id="submit" name="submit" value="<?php yourls_e('Acessar'); ?>" class="button" />
				</p>
				<?php
				yourls_do_action('login_form_end');
				?>
			</form>
			<script type="text/javascript">
				$('#username').focus();
			</script>
		</div>
	<?php
		yourls_html_footer();
		die();
	}


	/**
	 * Exibição do Menu Administrativo
	 *
	 * @return void
	 */
	function yourls_html_menu()
	{
		// Construe o Menu de Links
		if (defined('YOURLS_USER')) {
			// Cria um link de logout com um nonce associado ao usuário falso 'logout' : o usuário ainda não está definido
			// quando a verificação de logout é feita -- Qualquer coisa veja o yourls_is_valid_user()
			$logout_url = yourls_nonce_url(
				'admin_logout',
				yourls_add_query_arg(['action' => 'logout'], yourls_admin_url('index.php')),
				'nonce',
				'logout'
			);
			$logout_link = yourls_apply_filter('logout_link', sprintf(yourls__('Olá <strong>%s</strong>'), YOURLS_USER) . ' (<a href="' . $logout_url . '" title="' . yourls_esc_attr__('Logout') . '">' . yourls__('Sair') . '</a>)');
		} else {
			$logout_link = yourls_apply_filter('logout_link', '');
		}
		$help_link   = yourls_apply_filter('help_link',   '<a href="' . yourls_site_url(false) . '/readme.html">' . yourls__('Ajuda') . '</a>');

		$admin_links    = array();
		$admin_sublinks = array();

		$admin_links['admin'] = array(
			'url'    => yourls_admin_url('index.php'),
			'title'  => yourls__('Vai para área administrativa'),
			'anchor' => yourls__('Interface de Admin')
		);

		if (yourls_is_admin()) {
			$admin_links['tools'] = array(
				'url'    => yourls_admin_url('tools.php'),
				'anchor' => yourls__('Ferramentas')
			);
			$admin_links['plugins'] = array(
				'url'    => yourls_admin_url('plugins.php'),
				'anchor' => yourls__('Plugins')
			);
			$admin_sublinks['plugins'] = yourls_list_plugin_admin_pages();
		}

		$admin_links    = yourls_apply_filter('admin_links',    $admin_links);
		$admin_sublinks = yourls_apply_filter('admin_sublinks', $admin_sublinks);

		// Saída do Menu
		echo '<nav role="navigation"><ul id="admin_menu">' . "\n";
		if (yourls_is_private() && !empty($logout_link))
			echo '<li id="admin_menu_logout_link">' . $logout_link . '</li>';

		foreach ((array)$admin_links as $link => $ar) {
			if (isset($ar['url'])) {
				$anchor = isset($ar['anchor']) ? $ar['anchor'] : $link;
				$title  = isset($ar['title']) ? 'title="' . $ar['title'] . '"' : '';
				printf('<li id="admin_menu_%s_link" class="admin_menu_toplevel"><a href="%s" %s>%s</a>', $link, $ar['url'], $title, $anchor);
			}
			// Saída do Submenu, se houver. 
			//OBS: limpar, muitos códigos duplicados aqui
			if (isset($admin_sublinks[$link])) {
				echo "<ul>\n";
				foreach ($admin_sublinks[$link] as $link => $ar) {
					if (isset($ar['url'])) {
						$anchor = isset($ar['anchor']) ? $ar['anchor'] : $link;
						$title  = isset($ar['title']) ? 'title="' . $ar['title'] . '"' : '';
						printf('<li id="admin_menu_%s_link" class="admin_menu_sublevel admin_menu_sublevel_%s"><a href="%s" %s>%s</a>', $link, $link, $ar['url'], $title, $anchor);
					}
				}
				echo "</ul>\n";
			}
		}

		if (isset($help_link))
			echo '<li id="admin_menu_help_link">' . $help_link . '</li>';

		yourls_do_action('admin_menu');
		echo "</ul></nav>\n";
		yourls_do_action('admin_notices');
		yourls_do_action('admin_notice'); 
		/*
	Para exibir uma notícia:
	$message = "<div>Palmeiras não tem mundial!</div>" );
	yourls_add_action( 'admin_notices', function() use ( $message ) { echo (string) $message; } );
	*/
	}

	/**
	* Função wrapper para exibir avisos de administração
	*
	* @param string $message Mensagem a ser exibida
	* @param string $style Estilo da mensagem (padrão: 'notice')
	* @return void
	*/
	function yourls_add_notice($message, $style = 'notice')
	{
		// Aspas simples em $message para evitar quebrar a função anônima
		$message = yourls_notice_box(strtr($message, array("'" => "\'")), $style);
		yourls_add_action('admin_notices', function () use ($message) {
			echo (string) $message;
		});
	}

	/**
	* Retornar um aviso formatado
	*
	* @param string $message Mensagem a ser exibida
	* @param string $style classe CSS a ser usada para o aviso
	* @return string HTML do aviso
	*/
	function yourls_notice_box($message, $style = 'notice')
	{
		return <<<HTML
	<div class="$style">
	<p>$message</p>
	</div>
HTML;
	}

	/**
	* Exibir uma página
	*
	* Inclui o conteúdo de um arquivo PHP do diretório YOURLS_PAGEDIR, como se fosse
	* uma URL curta padrão (ou seja, http://encurta.do/$page)
	*
	* @param string $page Arquivo PHP a ser exibido
	* @return void
	*/
	function yourls_page($page)
	{
		if (!yourls_is_page($page)) {
			yourls_die(yourls_s('Page "%1$s" not found', $page), yourls__('Not found'), 404);
		}

		yourls_do_action('pre_page', $page);
		include_once(YOURLS_PAGEDIR . "/$page.php");
		yourls_do_action('post_page', $page);
	}

	/**
	* Exibe os atributos de idioma para a tag HTML.
	*
	* Constrói um conjunto de atributos html contendo a direção do texto e o idioma
	* informações para a página. Roubado do WP.
	*
	*
	* @return void
	*/
	function yourls_html_language_attributes()
	{
		$attributes = array();
		$output = '';

		$attributes[] = (yourls_is_rtl() ? 'dir="rtl"' : 'dir="ltr"');

		$doctype = yourls_apply_filter('html_language_attributes_doctype', 'html');
		
		if ($lang = str_replace('_', '-', yourls_get_locale())) {
			if ($doctype == 'xhtml') {
				$attributes[] = "xml:lang=\"$lang\"";
			} else {
				$attributes[] = "lang=\"$lang\"";
			}
		}

		$output = implode(' ', $attributes);
		$output = yourls_apply_filter('html_language_attributes', $output);
		echo $output;
	}

	/**
	 * Calendário em JavaScript
	*/

	function yourls_l10n_calendar_strings()
	{
		echo "\n<script>\n";
		echo "var l10n_cal_month = " . json_encode(array_values(yourls_l10n_months())) . ";\n";
		echo "var l10n_cal_days = " . json_encode(array_values(yourls_l10n_weekday_initial())) . ";\n";
		echo "var l10n_cal_today = \"" . yourls_esc_js(yourls__('Hoje')) . "\";\n";
		echo "var l10n_cal_close = \"" . yourls_esc_js(yourls__('Fechar')) . "\";\n";
		echo "</script>\n";

		
		yourls__('Hoje');
		yourls__('Fechar');
	}


	/**
	 * Exibe a notícia de uma versão mais nova do RMCorte
	 *
	 * @since 1.7
	 * @param string $compare_with Optional, Compara a versão do RMCorte
	 * @return void
	 */
	function yourls_new_core_version_notice($compare_with = null)
	{
		$compare_with = $compare_with ?: YOURLS_VERSION;

		$checks = yourls_get_option('core_version_checks');
		$latest = isset($checks->last_result->latest) ? yourls_sanitize_version($checks->last_result->latest) : false;

		if ($latest and version_compare($latest, $compare_with, '>')) {
			yourls_do_action('new_core_version_notice', $latest);
			$msg = yourls_s('<a href="%s">A Versão nova do RMCorte %s</a> está disponível. Por Favor atualize!', 'http://nettic.irede.net', $latest);
			yourls_add_notice($msg);
		}
	}

	/**
	 * 
	 * Exibe ou Retorna o HTML do Link do Favorito
	 *
	 */
	function yourls_bookmarklet_link($href, $anchor, $echo = true)
	{
		$alert = yourls_esc_attr__('Arraste para a barra de ferramentas.');
		$link = <<<LINK
    <a href="$href" class="bookmarklet" onclick="alert('$alert');return false;">$anchor</a>
LINK;

		if ($echo)
			echo $link;
		return $link;
	}

	/**
	 *
	 * Seta o HTML  (stats, index, infos, ...)
	 *
	 */
	function yourls_set_html_context($context)
	{
		yourls_get_db()->set_html_context($context);
	}

	/**
	 *
	 * Seta o HTML  (stats, index, infos, ...)
	 *
	 */
	function yourls_get_html_context()
	{
		return yourls_get_db()->get_html_context();
	}

	/**
	 * Imprime o link do para o favicon
	 *
	 * @since 1.7.10
	 * @return mixed|void
	 */
	function yourls_html_favicon()
	{
		
		$pre = yourls_apply_filter('shunt_html_favicon', false);
		if (false !== $pre) {
			return $pre;
		}

		printf('<link rel="shortcut icon" href="%s" />', yourls_get_yourls_favicon_url(false));
	}
