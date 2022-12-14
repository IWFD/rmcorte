<?php include('header.php');

$url     = isset( $_REQUEST['url'] ) ? yourls_sanitize_url( $_REQUEST['url'] ) : '' ;
$keyword = isset( $_REQUEST['keyword'] ) ? yourls_sanitize_keyword( $_REQUEST['keyword'] ) : '' ;
$title   = isset( $_REQUEST['title'] ) ? yourls_sanitize_title( $_REQUEST['title'] ) : '' ;

?>

<div class="content index">
	<center><h2><?php yourls_e( '.: Digite a URL que Deseja Encurtar :.', 'isq_translation'); ?></h2></center>
	<form method="post" action="result.php" class="newurl" novalidate>
		<div class="form-item full-width">
			<p><label for="url" class="primary"><?php yourls_e( 'URL Original', 'isq_translation'); ?></label></p>
			<p><label for="url" class="secondary"><?php yourls_e( 'Cole aqui a URL original. É um passo obrigatório.', 'isq_translation'); ?></label></p>
			<input type="url" id="url" name="url" value="<?php echo($url); ?>" autofocus>
		</div>

		<div class="halves">
			<div class="form-item half-width left">
				<p><label for="keyword" class="primary"><?php yourls_e( 'Palavra Chave Personalizada', 'isq_translation'); ?></label></p>
				<p><label for="keyword" class="secondary"><?php yourls_e( 'Palavra que substitui a URL gerada aleatóriamente.', 'isq_translation'); ?></label></p>
				<input type="text" id="keyword" name="keyword" autocorrect="off" autocapitalize="none" value="<?php echo($keyword); ?>">
			</div>

			<div class="form-item half-width right">
				<p><label for="title" class="primary"><?php yourls_e( 'Título Personalizado', 'isq_translation'); ?></label></p>
				<p><label for="title" class="secondary"><?php yourls_e( 'Opcional, usado para compartilhar em redes sociais.', 'isq_translation'); ?></label></p>
				<input type="text" id="title" name="title" value="<?php echo($title); ?>">
			</div>
		</div>

		<?php
			switch( is_get_antispam_method() ) {
				case 'login': ?>
					<input type="hidden" name="antispam_method" value="user_login" class="hidden">
				<?php break;

				case 'recaptcha_v3':
					$dependencies[] = 'recaptcha_v3';
				?>
					<input type="hidden" name="antispam_method" value="recaptcha_v3" class="hidden">
					<input type="hidden" name="recaptcha_token" id="recaptcha_token" class="hidden">
				<?php break;

				case 'recaptcha':
					$dependencies[] = 'recaptcha';
				?>
					<input type="hidden" name="antispam_method" value="recaptcha" class="hidden">

					<div class="form-item recaptcha-container">
						<p><label class="primary" title=""><?php yourls_e( 'Verificação', 'isq_translation'); ?></label></p>
						<p><label class="secondary"><?php yourls_e( 'reCAPTCHA verificação sendo usada para provar que você não é um robô.', 'isq_translation'); ?></label></p>
						<div class="g-recaptcha" data-sitekey="<?php echo ISQ::$recaptcha['sitekey']; ?>"></div>
					</div>
				<?php break;

				default: ?>
					<input type="hidden" name="antispam_method" value="basic" class="hidden">
					<input type="hidden" name="basic_antispam" class="hidden">
				<?php break;
			}
		?>

		<div class="form-item submit-container">
			<input type="submit" class="button" value="<?php yourls_e( 'Encurtar', 'isq_translation'); ?>">
		</div>
	</form>
</div>

<?php include('footer.php'); ?>
