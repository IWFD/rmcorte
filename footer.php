
<div class="content">
	<h2><?php yourls_e( 'Como Usar ?', 'isq_translation') ?></h2>

	<?php $bookmarkletdialog = yourls__('Salve como favorito.') ?>
	<p><?php yourls_e( 'Adicionar aos seus favoritos ou arraste-o para a barra de favoritos para acessar as funções de encurtamento.', 'isq_translation') ?> </p>
	<p class="bookmarklet-container"><a href="javascript:(function()%7Bvar%20d=document,w=window,enc=encodeURIComponent,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),s2=((s.toString()=='')?s:enc(s)),f='<?php echo YOURLS_SITE . '/index.php'; ?>',l=d.location,p='?url='+enc(l.href)+'&title='+enc(d.title)+'&keyword='+s2,u=f+p;try%7Bthrow('ozhismygod');%7Dcatch(z)%7Ba=function()%7Bif(!w.open(u))l.href=u;%7D;if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else%20a();%7Dvoid(0);%7D)();" onClick="alert('<?php echo $bookmarkletdialog; ?>'); return false;" class="bookmarklet button"><span class="icon-move"><?php include('public/images/move.svg'); ?></span><?php yourls_e( 'Shorten1', 'isq_translation') ?></a></p>
	<p><?php yourls_e( 'Este favorito pega a URL e o título da página e abre uma nova aba, onde você pode preencher um CAPTCHA. Se você selecionou texto antes de usar o favorito, ele será usado como palavra-chave.', 'isq_translation') ?></p>
	<p><?php yourls_e( 'O suporte para favoritar no celular varia. Por exemplo, eles funcionam no Chrome para Android, mas você precisa adicioná-los e sincronizá-los na área de trabalho.', 'isq_translation') ?></p>
</div>

<footer class="content site-footer">
	<p><?php yourls_e( 'Desenvolvido por <a href="http://nettic.irede.net/">TiC</a>. Criado por <a href="https://gitlabrmc.irede.net/israel.duarte/">Israel Duarte</a>.', 'isq_translation') ?> <a class="icon-github" href="https://github.com/crashforce"><?php include('public/images/github.svg'); ?></a></p>
	<?php if( 'recaptcha' == is_get_antispam_method() || 'recaptcha_v3' == is_get_antispam_method() ) : ?>
		<p class="recaptcha-cookie"><?php yourls_e('Esta aplicação usa cookies para o Google reCAPTCHA','isq_translation'); ?>.<p>
	<?php endif; ?>
</div>
</div>
</div>

<?php global $dependencies; ?>

<?php if( in_array( 'recaptcha_v3', $dependencies ) ) : ?>
	<script type="text/template" id="recaptcha-sitekey"><?php echo ISQ::$recaptcha_v3['sitekey']; ?></script>
	<script src="https://www.google.com/recaptcha/api.js?render=<?php echo ISQ::$recaptcha_v3['sitekey']; ?>"></script>
<?php elseif( in_array( 'recaptcha', $dependencies ) ) : ?>
	<script src="https://www.google.com/recaptcha/api.js"></script>
<?php endif; ?>

<?php if( in_array( 'clipboard.js', $dependencies ) ) { ?>
	<script src="public/js/clipboard.min.js"></script>
<?php } ?>

<?php if( in_array( 'recaptcha_v3', $dependencies ) || in_array( 'clipboard.js', $dependencies ) ) : ?>
	<script src="public/js/app.js"></script>
<?php endif; ?>

</body>
</html>
