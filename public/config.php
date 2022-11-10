<?php

// Configs Gerais
ISQ::$general = array(
	'name' => 'Encurtador de URL', // Título no Bloco Inicial
	'qr' => TRUE, // Exibir o QR code ? | [True - Sim] .:. [False - Não]
	'customstyle' => TRUE // Habilitar o  custom stylesheet, localizado em "public/custom.css" | [True - Sim] .:. [False - Não]
);

// Links no Menu Principal
// Para adicionar mais links no array do menu usar vírgula.
ISQ::$links = array(
	array(
		'name' => ISQ::$general['name'],
		'link' => YOURLS_SITE
	),
	array(
		'name' => yourls__( '| Área Administrativa |', 'isq_translation' ),
		'link' => YOURLS_SITE . '/admin/'
	),
	array(
		'name' => '| Israel Duarte |',
		'link' => 'https://gitlabrmc.irede.net/israel.duarte/'
	),
	array(
		'name' => '| TiC |',
		'link' => 'http://nettic.irede.net'
	)
);

// Mídias Sociais
ISQ::$social = array(
	'twitter' => TRUE,
	'appdotnet' => FALSE,
	'facebook' => TRUE,
	'linkedin' => TRUE,
	'tumblr' => FALSE,
	'googleplus' => FALSE,
	'vk' => FALSE,
);

// reCAPTCHA API KEYS - Talvez para implementar no futuro..
// Referência para pegar <> https://www.google.com/recaptcha/admin
// Por enquanto não usaremos o reCAPTCHA. Basta deixar vazio e a proteção
// básica e padrão será ativada.
ISQ::$recaptcha = array(
	'sitekey' => '',
	'secret' => ''
);

// Separação de API keys para o reCAPTCHA v3
ISQ::$recaptcha_v3 = array(
	'sitekey' => '',
	'secret' => '',
	'threshold' => '0.5',
);

?>
