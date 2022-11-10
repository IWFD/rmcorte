<?php
include('header.php');

// Aqui começam os erros - Condicionados ao usuário final..
if ( empty( $_REQUEST['url'] ) ) {
	display_error( yourls__( 'Você não digitou a URL para ser encurtada.', 'isq_translation' ) );
};

// Verifica se a palavra chave está reservada.
if ( !empty( $_REQUEST['keyword'] ) && yourls_keyword_is_reserved( $_REQUEST['keyword'] ) ) {
	display_error( sprintf( yourls__( 'The keyword %1$s is reserved.'), '<span class="key">' . $_REQUEST['keyword'] . '</span>' ) );
}

// Verifica se a palavra chave já está sendo utilizada.
if ( !empty( $_REQUEST['keyword'] ) && yourls_keyword_is_taken( $_REQUEST['keyword'] ) ) {
	display_error( sprintf( yourls__( 'The keyword %1$s is taken.'), '<span class="key">' . $_REQUEST['keyword'] . '</span>' ) );
}

// Verifica qual método CAPTCHA foi usado
$antispam_method = $_REQUEST['antispam_method'];

switch( is_get_antispam_method() ) {
	case 'login':
		if( !yourls_is_valid_user() ) {
			display_error( yourls__( 'Você não está logado - Por favor, volte e tente novamente.', 'isq_translation' ) );
		}
	break;

	case 'recaptcha_v3':
		$recaptcha_data = get_remote_file( 'https://www.google.com/recaptcha/api/siteverify?secret=' . ISQ::$recaptcha_v3['secret'] . '&response=' . $_POST['recaptcha_token'] );
		$recaptcha = json_decode( $recaptcha_data );

		if( $recaptcha->success != true || $recaptcha->action != 'homepage' || $recaptcha->score < ISQ::$recaptcha_v3['threshold'] ) {
			display_error( yourls__( 'Você é um robô ? Google certamente pensa que você é.', 'isq_translation' ) );
		}
	break;

	case 'recaptcha':
		// Google reCAPTCHA quando está habilitado
		$recaptcha_data = get_remote_file( 'https://www.google.com/recaptcha/api/siteverify?secret=' . ISQ::$recaptcha['secret'] . '&response=' . $_POST['g-recaptcha-response'] );
		$recaptcha_json = json_decode( $recaptcha_data, true );

		// O que acontece quando o reCAPTCHA foi completado incorretamente
		if ( $recaptcha_json['success'] != 'true' ) {
			display_error( yourls__( 'Você é um robô ? certamente pensa que você é', 'isq_translation' ) );
		}
	break;

	case 'basic':
		// Proteção Anti Spam
		// O que acontece quando não é completado corretamente
		if( !empty( $_POST['basic_antispam'] ) ) {
			display_error( yourls__( 'Você é um robô ? The anti-spam check was not completed successfully.', 'isq_translation' ) );
		}
	break;

	default:
		// Nenhuma verificação anti-spam foi concluída
		display_error( yourls__( 'Você é um robô ? No anti-spam check was completed successfully.', 'isq_translation' ) );
	break;
}

// Obtenha parâmetros -- todos eles serão higienizados em yourls_add_new_link()
$url     = $_REQUEST['url'];
$keyword = isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : '' ;
$title   = isset( $_REQUEST['title'] ) ?  $_REQUEST['title'] : '' ;
$text    = isset( $_REQUEST['text'] ) ?  $_REQUEST['text'] : '' ;

// Cria a URL encurtada, recebe um array $return com várias informações
$return  = yourls_add_new_link( $url, $keyword, $title );

$shorturl = isset( $return['shorturl'] ) ? $return['shorturl'] : '';
$message  = isset( $return['message'] ) ? $return['message'] : '';
$title    = isset( $return['title'] ) ? $return['title'] : '';
$status   = isset( $return['status'] ) ? $return['status'] : '';

// Faz a checagem para todos os outros erros
if( empty( $shorturl ) ) {
	display_error( yourls__( 'Esta URL não pode ser encurtada.', 'isq_translation' ) );
}

// URL encodada por links para os botões das mídias sociais
$encoded_shorturl = urlencode($shorturl);
$encoded_title = urlencode($title);

// Adiciona Dependências
$dependencies[] = 'clipboard.js';

?>

<div class="content result">
	<!-- MSG de Erro -->
	<?php isset( $error ) ? $error : ''; ?>

	<!-- Padrão de Saída -->
	<h2><?php yourls_e( 'Sua Nova URL Encurtada!', 'isq_translation'); ?></h2>

	<div class="output">
		<div class="form-item full-width">
			<label for="longurl" class="primary"><?php yourls_e( 'URL Original', 'isq_translation'); ?></label>
			<div class="input-with-copy">
				<input type="text" name="longurl" id="longurl" onclick="this.select();" onload="this.select();" value="<?php echo $url; ?>">
				<button data-clipboard-target="#longurl" class="copy-button button" title="<?php yourls_e( 'Copy to clipboard', 'isq_translation' ); ?>"><img src="public/images/clippy.svg"></button>
				<div class="copy-message success" id="copy-success"><?php yourls_e( 'Copiado', 'isq_translation' ); ?></div>
				<div class="copy-message error" id="copy-error">
					<span class="os macos"><?php yourls_e( 'Pressione ⌘+C para copiar', 'isq_translation' ); ?></span>
					<span class="os pc"><?php yourls_e( 'Pressione Ctrl+C para copiar', 'isq_translation' ); ?></span>
					<span class="os mobile"><?php yourls_e( 'Toque em Copiar', 'isq_translation' ); ?></span>
					<span class="os other"><?php yourls_e( 'Falha em copiar', 'isq_translation' ); ?></span>
				</div>
			</div>
		</div>

		<div class="halves">
			<div class="form-item half-width left">
				<label for="shorturl" class="primary"><?php yourls_e( 'URL Encurtada', 'isq_translation'); ?></label>
				<div class="input-with-copy">
					<input type="text" name="shorturl" id="shorturl" onclick="this.select();" value="<?php echo $shorturl; ?>">
					<button data-clipboard-target="#shorturl" class="copy-button button" title="<?php yourls_e( 'Copiar', 'isq_translation' ); ?>"><img src="public/images/clippy.svg"></button>
					<div class="copy-message success" id="copy-success"><?php yourls_e( 'Copiado', 'isq_translation' ); ?></div>
					<div class="copy-message error" id="copy-error">
						<span class="os macos"><?php yourls_e( 'Pressione ⌘+C para copiar', 'isq_translation' ); ?></span>
						<span class="os pc"><?php yourls_e( 'Pressione Ctrl+C para copiar', 'isq_translation' ); ?></span>
						<span class="os mobile"><?php yourls_e( 'Toque em Copiar', 'isq_translation' ); ?></span>
						<span class="os other"><?php yourls_e( 'Falha em copiar', 'isq_translation' ); ?></span>
					</div>
				</div>
			</div>

			<div class="form-item half-width right">
				<label for="stats" class="primary"><?php  yourls_e( 'Status', 'isq_translation'); ?></label>
				<div class="input-with-copy">
					<input type="text" name="stats" id="stats" onclick="this.select();" value="<?php echo $shorturl . '+'; ?>" id="stats-copy">
					<button data-clipboard-target="#stats" class="copy-button button" title="<?php yourls_e( 'Copiado', 'isq_translation' ); ?>"><img src="public/images/clippy.svg"></button>
					<div class="copy-message success" id="copy-success"><?php yourls_e( 'Copiado', 'isq_translation' ); ?></div>
					<div class="copy-message error" id="copy-error">
						<span class="os macos"><?php yourls_e( 'Pressione ⌘+C to copy', 'isq_translation' ); ?></span>
						<span class="os pc"><?php yourls_e( 'Pressione Ctrl+C to copy', 'isq_translation' ); ?></span>
						<span class="os mobile"><?php yourls_e( 'Toque em Copiar', 'isq_translation' ); ?></span>
						<span class="os other"><?php yourls_e( 'Falha em copiar', 'isq_translation' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Compartilhamento de Mídias Sociais -->
	<h2><?php yourls_e( 'Compartilhar', 'isq_translation'); ?></h2>
	<p><?php yourls_e( 'Compartilhar sua URL encurtada nas mídias socias.', 'isq_translation'); ?></p>
	<div class="social-sharing">
		<?php
			if ( ISQ::$social['twitter'] ) { echo '<span onclick="window.open(\'https://twitter.com/intent/tweet?url=' . $encoded_shorturl . '&text=' . $encoded_title . '\',\'_blank\',\'width=550,height=380\')" class="button social-button twitter" title="Compartilhe on Twitter">' . file_get_contents('public/images/twitter.svg') . '</span>'; }

			if ( ISQ::$social['appdotnet'] ) { echo '<span onclick="window.open(\'https://account.app.net/intent/post/?text=' . $encoded_title . '&url=' . $encoded_shorturl . '\',\'_blank\',\'width=550,height=380\')" class="button social-button appdotnet" title="Compartilhe on App.net">' . file_get_contents('public/images/appdotnet.svg') . '</span>'; }

			if ( ISQ::$social['facebook'] ) { echo '<span onclick="window.open(\'https://www.facebook.com/sharer/sharer.php?u=' . $shorturl . '\',\'_blank\',\'width=550,height=380\')" class="button social-button facebook" title="Compartilhe on Facebook">' . file_get_contents('public/images/facebook.svg') . '</span>'; }

			if ( ISQ::$social['tumblr'] ) { echo '<span onclick="window.open(\'http://www.tumblr.com/share/link?url=' . $encoded_shorturl . '&name=' . $encoded_title . '\',\'_blank\',\'width=550,height=380\')" class="button social-button tumblr" title="Compartilhe on Tumblr">' . file_get_contents('public/images/tumblr.svg') . '</span>'; }

			if ( ISQ::$social['linkedin'] ) { echo '<span onclick="window.open(\'https://www.linkedin.com/shareArticle?mini=true&url=' . $encoded_shorturl . '&title=' . $encoded_title . '\',\'_blank\',\'width=550,height=380\')" class="button social-button linkedin" title="Compartilhe on LinkedIn">' . file_get_contents('public/images/linkedin.svg') . '</span>'; }

			if ( ISQ::$social['googleplus'] ) { echo '<span onclick="window.open(\'https://plus.google.com/share?url=' . $encoded_shorturl . '\',\'_blank\',\'width=550,height=380\')" class="button social-button googleplus" title="Compartilhe on Google+">' . file_get_contents('public/images/googleplus.svg') . '</span>'; }

			if ( ISQ::$social['vk'] ) { echo '<span onclick="window.open(\'https://vk.com/share.php?url=' . $encoded_shorturl . '\',\'_blank\',\'width=550,height=380\')" class="button social-button vk" title="Compartilhe on VK">' . file_get_contents('public/images/vk.svg') . '</span>'; }
		?>
	</div>

	<?php if ( ISQ::$general['qr'] ) : ?>
		<!-- QR code -->
		<h2><?php yourls_e( 'QR Code', 'isq_translation' ); ?></h2>
		<p><?php yourls_e( 'Compartilhe seu link com dispositivos externos.', 'isq_translation' ); ?></p>

	<?php
		// Biblioteca resumida criada para PHP - QR Code - Baixo Nível
		include('public/phpqrcode/qrlib.php');

		echo QRcode::svg( $shorturl, 'url-qr-code', false, QR_ECLEVEL_L, '600' );
	endif; ?>

</div>

<?php include('footer.php'); ?>
