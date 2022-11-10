<?php
/*
 * Funções Gerais do RMCorte
 *
 */

/**
 * Faz um regexp padrão otimizado a partir de uma string de caracteres
 *
 * @param string $string
 * @return string
 */
function yourls_make_regexp_pattern( $string ) {
    // Benchmarks simples mostram que regexp com sequências mais inteligentes (0-9, a-z, A-Z...) não são mais rápidos ou mais lentos que 0123456789 etc...
     // adiciona @ como um caractere de escape porque @ é usado como delimitador regexp em yourls-loader.php - Só por isso.
    return preg_quote( $string, '@' );
}

/**
 * Pera o IP do cliente. Retorna de forma segura.
 *
 * @return string
 */
function yourls_get_IP() {
	$ip = '';

	// Precedência: se definido, X-Forwarded-For > HTTP_X_FORWARDED_FOR > HTTP_CLIENT_IP > HTTP_VIA > REMOTE_ADDR
	$headers = [ 'X-Forwarded-For', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'REMOTE_ADDR' ];
	foreach( $headers as $header ) {
		if ( !empty( $_SERVER[ $header ] ) ) {
			$ip = $_SERVER[ $header ];
			break;
		}
	}

	// os cabeçalhos podem conter vários IPs (X-Forwarded-For = client, proxy1, proxy2). Pega o primeiro, normalmente.
	if ( strpos( $ip, ',' ) !== false )
		$ip = substr( $ip, 0, strpos( $ip, ',' ) );

	return (string)yourls_apply_filter( 'get_IP', yourls_sanitize_ip( $ip ) );
}

/**
 * Obtem o próximo ID que um novo link terá se nenhuma palavra-chave personalizada for fornecida pelo usuário.
 *
 * @since 1.0
 * @return int            id do próximo link
 */
function yourls_get_next_decimal() {
	return (int)yourls_apply_filter( 'get_next_decimal', (int)yourls_get_option( 'next_id' ) );
}

/**
  * Atualize o id para o próximo link sem palavra-chave personalizada
  *
  * Nota: Esta função depende de yourls_update_option(), que retornará true ou false
  * dependendo se houve uma consulta real do MySQL atualizando o banco de dados.
  * Em outras palavras, esta função pode retornar false, mas isso não significa que ela falhou funcionalmente
  * Em outras palavras, não tenho certeza se realmente precisamos dessa função para retornar algo como um emoji
  * Consulte-me depois para saber mais sobre isso.
 *
 * @since 1.0
 * @param integer $int id para o próximo link
 * @return bool        true ou false dependendo se houve uma consulta real do MySQL. Veja nota acima. |^|
 */
function yourls_update_next_decimal( $int = 0 ) {
	$int = ( $int == 0 ) ? yourls_get_next_decimal() + 1 : (int)$int ;
	$update = yourls_update_option( 'next_id', $int );
	yourls_do_action( 'update_next_decimal', $int, $update );
	return $update;
}

/**
 * Retorna a saída do XML.
 *
 * @param array $array
 * @return string
 */
function yourls_xml_encode( $array ) {
    return (\Spatie\ArrayToXml\ArrayToXml::convert($array, '', true, 'UTF-8'));
}

/**
 * Atualiza a contagem de cliques em uma URL curta. Retorne 0/1 para erro/sucesso.
 *
 * @param string $keyword
 * @param false|int $clicks
 * @return int 0 ou 1 para erro/sucesso
 */
function yourls_update_clicks( $keyword, $clicks = false ) {
	
	$pre = yourls_apply_filter( 'shunt_update_clicks', false, $keyword, $clicks );
	if ( false !== $pre ) {
        return $pre;
    }

	$keyword = yourls_sanitize_keyword( $keyword );
	$table = YOURLS_DB_TABLE_URL;
	if ( $clicks !== false && is_int( $clicks ) && $clicks >= 0 ) {
        $update = "UPDATE `$table` SET `clicks` = :clicks WHERE `keyword` = :keyword";
        $values = [ 'clicks' => $clicks, 'keyword' => $keyword ];
    } else {
        $update = "UPDATE `$table` SET `clicks` = clicks + 1 WHERE `keyword` = :keyword";
        $values = [ 'keyword' => $keyword ];
    }

	// Experimenta e atualiza a contagem de cliques. 
    // Um erro provavelmente significa um problema de simultaneidade: basta pular a atualização
    try {
        $result = yourls_get_db()->fetchAffected($update, $values);
    } catch (Exception $e) {
	    $result = 0;
    }

	yourls_do_action( 'update_clicks', $keyword, $result, $clicks );

	return $result;
}


/**
 * Retorna a matriz de estatísticas. (string)$filter é 'bottom', 'last', 'rand' ou 'top'. (int)$limit é o número de links para retornar
 *
 * @param string $filter  'bottom', 'last', 'rand' ou 'top'
 * @param int $limit       Número de links para retorno
 * @param int $start       Desloca para incializar
 * @return array           Array dos links
 */
function yourls_get_stats($filter = 'top', $limit = 10, $start = 0) {
	switch( $filter ) {
		case 'bottom':
			$sort_by    = '`clicks`';
			$sort_order = 'asc';
			break;
		case 'last':
			$sort_by    = '`timestamp`';
			$sort_order = 'desc';
			break;
		case 'rand':
		case 'random':
			$sort_by    = 'RAND()';
			$sort_order = '';
			break;
		case 'top':
		default:
			$sort_by    = '`clicks`';
			$sort_order = 'desc';
			break;
	}

	// Fetch dos links
	$limit = intval( $limit );
	$start = intval( $start );
	if ( $limit > 0 ) {

		$table_url = YOURLS_DB_TABLE_URL;
		$results = yourls_get_db()->fetchObjects( "SELECT * FROM `$table_url` WHERE 1=1 ORDER BY $sort_by $sort_order LIMIT $start, $limit;" );

		$return = [];
		$i = 1;

		foreach ( (array)$results as $res ) {
			$return['links']['link_'.$i++] = [
				'shorturl' => yourls_link($res->keyword),
				'url'      => $res->url,
				'title'    => $res->title,
				'timestamp'=> $res->timestamp,
				'ip'       => $res->ip,
				'clicks'   => $res->clicks,
            ];
		}
	}

	$return['stats'] = yourls_get_db_stats();

	$return['statusCode'] = 200;

	return yourls_apply_filter( 'get_stats', $return, $filter, $limit, $start );
}

/**
  * Recebe o número total de URLs e a soma de cliques. Entrada: cláusula opcional "AND WHERE". Retorna a matriz
  *
  * O parâmetro $where conterá argumentos SQL adicionais:
  * $where['sql'] irá concatenar cláusulas SQL: $where['sql'] = ' AND algo = :value AND otherthing < :othervalue';
  * $where['binds'] manterá os pares de espaço reservado (name => value): $where['binds'] = array('value' => $value, 'othervalue' => $value2)
 *
 * @param  array $where Só ver o comentário acima.
 * @return array
 */
function yourls_get_db_stats( $where = [ 'sql' => '', 'binds' => [] ] ) {
	$table_url = YOURLS_DB_TABLE_URL;

	$totals = yourls_get_db()->fetchObject( "SELECT COUNT(keyword) as count, SUM(clicks) as sum FROM `$table_url` WHERE 1=1 " . $where['sql'] , $where['binds'] );
	$return = [ 'total_links' => $totals->count, 'total_clicks' => $totals->sum ];

	return yourls_apply_filter( 'get_db_stats', $return, $where );
}

/**
 * Retorna uma string de agente do usuário higienizada. 
 * Dado o que encontrei em http://www.user-agents.org/ deve estar tudo bem.
 *
 * @return string
 */
function yourls_get_user_agent() {
    $ua = '-';

    if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
        $ua = strip_tags( html_entity_decode( $_SERVER['HTTP_USER_AGENT'] ));
        $ua = preg_replace('![^0-9a-zA-Z\':., /{}\(\)\[\]\+@&\!\?;_\-=~\*\#]!', '', $ua );
    }

    return yourls_apply_filter( 'get_user_agent', substr( $ua, 0, 255 ) );
}

/**
 * Retorna o referenciador higienizado enviado pelo navegador - Testei no Firefox, Chrome, Edge, Safari e Brave.
 *
 * @return string               HTTP Referência ou 'direct' se não tiver nenhuma referência
 */
function yourls_get_referrer() {
    $referrer = isset( $_SERVER['HTTP_REFERER'] ) ? yourls_sanitize_url_safe( $_SERVER['HTTP_REFERER'] ) : 'direct';

    return yourls_apply_filter( 'get_referrer', substr( $referrer, 0, 200 ) );
}

/**
  * Redireciona para outra página
  *
  * Redirecionamento RMCorte, seja para URLs internas ou externas. Se os cabeçalhos não foram enviados, o redirecionamento
  * é obtido com o header() do PHP. Se os cabeçalhos já foram enviados e não estamos em uma linha de comando
  * cliente, o redirecionamento ocorre com Javascript.
  *
  * Nota: yourls_redirect() não sai automaticamente e quase sempre deve ser seguido por uma chamada para exit()
  * para evitar que a aplicação continue.
 *
 * @since 1.4
 * @param string $location      URL para redirecionar para
 * @param int    $code          Código de Status HTTP para envio
 * @return int                  1 para o redirecionamento do header, 2 para o redirecionamento de JS, 3 para o crontário (CLI)
 */
function yourls_redirect( $location, $code = 301 ) {
	yourls_do_action( 'pre_redirect', $location, $code );
	$location = yourls_apply_filter( 'redirect_location', $location, $code );
	$code     = yourls_apply_filter( 'redirect_code', $code, $location );

	// Redireciona, adequadamente, se possível, ou via Javascript, caso contrário
	if( !headers_sent() ) {
		yourls_status_header( $code );
		header( "Location: $location" );
        return 1;
	}

	// Headers enviado : Redireciona com o JS se não for via CLI
	if( php_sapi_name() !== 'cli') {
        yourls_redirect_javascript( $location );
        return 2;
	}

	// Cliente...
	return 3;
}

/**
 * Redireciona para uma URL curta existente
 *
 * Redirecione o cliente para uma URL curta existente (sem verificação realizada) e executa tarefas diversas: atualização
 * cliques para URL curta, logs de atualização e envia um cabeçalho nocache para evitar que os bots indexem URL curtas.
 *
 * @since  1.7.3
 * @param  string $url
 * @param  string $keyword
 * @return void
 */
function yourls_redirect_shorturl($url, $keyword) {
    yourls_do_action( 'redirect_shorturl', $url, $keyword );

    // Tentativa de atualizar a contagem de cliques na tabela principal
    yourls_update_clicks( $keyword );

    // Atualiza log detalhado para estatísticas
    yourls_log_redirect( $keyword );

    // Diz aos bots do (Google) para não indexarem essa URL curta.
    if ( !headers_sent() ) {
        header( "X-Robots-Tag: noindex", true );
    }

    yourls_redirect( $url, 301 );
}

/**
 * Envie cabeçalhos para informar explicitamente ao navegador para não armazenar em cache o conteúdo ou o redirecionamento
 *
 * @since 1.7.10
 * @return void
 */
function yourls_no_cache_headers() {
    if( !headers_sent() ) {
        header( 'Expires: Thu, 23 Mar 1972 07:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
    }
}

/**
 * Envia o cabeçalho para evitar a exibição dentro de um frame de outro site (evitando o clickjacking)
 *
 * Este cabeçalho impossibilita que um site externo exiba o administrador RMCorte em um quadro,
 * que permite o clickjacking.
 * Veja https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
 * Dito isso, toda a função é shuntable: usos legítimos de iframes ainda devem ser possíveis. (Se a TiC precisar..)
 *
 * @since 1.8.1
 * @return void|mixed
 */
function yourls_no_frame_header() {
  
    $pre = yourls_apply_filter( 'shunt_no_frame_header', false );
    if ( false !== $pre ) {
        return $pre;
    }

    if( !headers_sent() ) {
        header( 'X-Frame-Options: SAMEORIGIN' );
    }
}

/**
 * Envia um conteúdo filtrado via tipo (Header)
 *
 * @since 1.7
 * @param string $type tipo de conteúdo ('text/html', 'application/json', ...)
 * @return bool whether header já foi enviado.
 */
function yourls_content_type_header( $type ) {
    yourls_do_action( 'content_type_header', $type );
	if( !headers_sent() ) {
		$charset = yourls_apply_filter( 'content_type_header_charset', 'utf-8' );
		header( "Content-Type: $type; charset=$charset" );
		return true;
	}
	return false;
}

/**
 * Seta o status do header HTTP
 *
 * @since 1.4
 * @param int $code  status do código do header 
 * @return bool      SE o header já foi enviado
 * 
 **/

function yourls_status_header( $code = 200 ) {
	yourls_do_action( 'status_header', $code );

	if( headers_sent() )
		return false;

	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
		$protocol = 'HTTP/1.0';

	$code = intval( $code );
	$desc = yourls_get_HTTP_status( $code );

	@header ("$protocol $code $desc"); // Isso causa problemas no IIS e em algumas configurações do FastCGI.

    return true;
}

function yourls_redirect_javascript( $location, $dontwait = true ) {
    yourls_do_action( 'pre_redirect_javascript', $location, $dontwait );
    $location = yourls_apply_filter( 'redirect_javascript', $location, $dontwait );
    if ( $dontwait ) {
        $message = yourls_s( 'if you are not redirected after 10 seconds, please <a href="%s">click here</a>', $location );
        echo <<<REDIR
		<script type="text/javascript">
		window.location="$location";
		</script>
		<small>($message)</small>
REDIR;
    }
    else {
        echo '<p>'.yourls_s( 'Por Favor <a href="%s">Clique aqui</a>', $location ).'</p>';
    }
    yourls_do_action( 'post_redirect_javascript', $location );
}

/**
 * Retorna o status do HTTP
 *
 * @param int $code
 * @return string
 */
function yourls_get_HTTP_status( $code ) {
	$code = intval( $code );
	$headers_desc = [
		100 => 'Continue',
		101 => 'Mudança de Protocolos',
		102 => 'Processando',

		200 => 'OK',
		201 => 'Criado',
		202 => 'Aceito',
		203 => 'Informação Não Autorizada',
		204 => 'Sem Conteúdo',
		205 => 'Conteúdo Resetado',
		206 => 'Conteúdo Parcial',
207 => 'Multi-Status',
226 => 'IM Usado',

300 => 'Múltiplas opções',
301 => 'Movido Permanentemente',
302 => 'Encontrado',
303 => 'Ver Outro',
304 => 'Não Modificado',
305 => 'Usar Proxy',
306 => 'Reservado',
307 => 'Redirecionamento Temporário',

400 => 'Pedido Indevido',
401 => 'Não autorizado',
402 => 'Pagamento obrigatório',
403 => 'Proibido',
404 => 'Não encontrado',
405 => 'Método não permitido',
406 => 'Não Aceitável',
407 => 'Autenticação Proxy Necessária',
408 => 'Tempo limite de solicitação',
409 => 'Conflito',
410 => 'Desapareceu',
411 => 'Comprimento Necessário',
412 => 'Falha na pré-condição',
413 => 'Solicitar Entidade Muito Grande',
414 => 'Request-URI muito longo',
415 => 'Tipo de mídia não suportado',
416 => 'Intervalo Solicitado Não Satisfável',
417 => 'Falha na expectativa',
422 => 'Entidade Não Processável',
423 => 'Bloqueado',
424 => 'Falha na Dependência',
426 => 'Atualização necessária',

500 => 'Erro do servidor interno',
501 => 'Não implementado',
502 => 'Gateway ruim',
503 => 'Serviço indisponível',
504 => 'Tempo limite do gateway',
505 => 'Versão HTTP não suportada',
506 => 'Variante Também Negocia',
507 => 'Armazenamento insuficiente',
510 => 'Não Estendido'
    ];

    return $headers_desc[$code] ?? '';
}


function yourls_log_redirect( $keyword ) {
	
	$pre = yourls_apply_filter( 'shunt_log_redirect', false, $keyword );
	if ( false !== $pre ) {
        return $pre;
    }

	if (!yourls_do_log_redirect()) {
        return true;
    }

	$table = YOURLS_DB_TABLE_LOG;
    $ip = yourls_get_IP();
    $binds = [
        'now' => date( 'Y-m-d H:i:s' ),
        'keyword'  => yourls_sanitize_keyword($keyword),
        'referrer' => substr( yourls_get_referrer(), 0, 200 ),
        'ua'       => substr(yourls_get_user_agent(), 0, 255),
        'ip'       => $ip,
        'location' => yourls_geo_ip_to_countrycode($ip),
    ];

    
    try {
        $result = yourls_get_db()->fetchAffected("INSERT INTO `$table` (click_time, shorturl, referrer, user_agent, ip_address, country_code) VALUES (:now, :keyword, :referrer, :ua, :ip, :location)", $binds );
    } catch (Exception $e) {
        $result = 0;
    }

    return $result;
}

/**
 * Verifica se não quer registrar redirecionamentos (para estatísticas)
 *
 * @return bool
 */
function yourls_do_log_redirect() {
	return ( !defined( 'YOURLS_NOSTATS' ) || YOURLS_NOSTATS != true );
}

/**
 * Checa se precisa de atualização
 *
 * @return bool
 */
function yourls_upgrade_is_needed() {
    
    list( $currentver, $currentsql ) = yourls_get_current_version_from_sql();
    if ( $currentsql < YOURLS_DB_VERSION ) {
        return true;
    }

    // Verifique se o YOURLS_VERSION existe && corresponde ao valor armazenado no YOURLS_DB_TABLE_OPTIONS, atualiza o banco de dados, se necessário
    if ( $currentver < YOURLS_VERSION ) {
        yourls_update_option( 'version', YOURLS_VERSION );
    }

    return false;
}

/**
 * Obtenha a versão atual e a versão do banco de dados conforme armazenado nas opções do banco de dados. Antes de 1.4 não há tabela de opções.
 *
 * @return array
 */
function yourls_get_current_version_from_sql() {
    $currentver = yourls_get_option( 'version' );
    $currentsql = yourls_get_option( 'db_version' );

    // Verifica se os valores são a partir da versão 1.3
    if ( !$currentver ) {
        $currentver = '1.3';
    }
    if ( !$currentsql ) {
        $currentsql = '100';
    }

    return [ $currentver, $currentsql ];
}

/**
 * Determina se a página atual é privada
 *
 * @return bool
 */
function yourls_is_private() {
    $private = defined( 'YOURLS_PRIVATE' ) && YOURLS_PRIVATE;

    if ( $private ) {

        // Permitir anulação para páginas específicas:

        // API
        if ( yourls_is_API() && defined( 'YOURLS_PRIVATE_API' ) ) {
            $private = YOURLS_PRIVATE_API;
        }
        // Página de Status
        elseif ( yourls_is_infos() && defined( 'YOURLS_PRIVATE_INFOS' ) ) {
            $private = YOURLS_PRIVATE_INFOS;
        }
       
    }

    return yourls_apply_filter( 'is_private', $private );
}

/**
 * Permite várias URLs curtas para a mesmo URL Original ?
 *
 * @return bool
 */
function yourls_allow_duplicate_longurls() {
    // Tratamento "especial" para possível tratativa em WordPress
    if ( yourls_is_API() && isset( $_REQUEST[ 'source' ] ) && $_REQUEST[ 'source' ] == 'plugin' ) {
            return false;
    }

    return yourls_apply_filter('allow_duplicate_longurls', defined('YOURLS_UNIQUE_URLS') && !YOURLS_UNIQUE_URLS);
}


function yourls_check_IP_flood( $ip = '' ) {

	
	$pre = yourls_apply_filter( 'shunt_check_IP_flood', false, $ip );
	if ( false !== $pre )
		return $pre;

	yourls_do_action( 'pre_check_ip_flood', $ip ); // verifique se o seu plugin se conecta aqui

	
	if(
		( defined('YOURLS_FLOOD_DELAY_SECONDS') && YOURLS_FLOOD_DELAY_SECONDS === 0 ) ||
		!defined('YOURLS_FLOOD_DELAY_SECONDS') ||
		yourls_is_installing()
	)
		return true;

	// Usuários Logados
	if( yourls_is_private() ) {
		 if( yourls_is_valid_user() === true )
			return true;
	}

	// Whitelist de IP's - Liberados
	if( defined( 'YOURLS_FLOOD_IP_WHITELIST' ) && YOURLS_FLOOD_IP_WHITELIST ) {
		$whitelist_ips = explode( ',', YOURLS_FLOOD_IP_WHITELIST );
		foreach( (array)$whitelist_ips as $whitelist_ip ) {
			$whitelist_ip = trim( $whitelist_ip );
			if ( $whitelist_ip == $ip )
				return true;
		}
	}

	$ip = ( $ip ? yourls_sanitize_ip( $ip ) : yourls_get_IP() );

	yourls_do_action( 'check_ip_flood', $ip );

	$table = YOURLS_DB_TABLE_URL;
	$lasttime = yourls_get_db()->fetchValue( "SELECT `timestamp` FROM $table WHERE `ip` = :ip ORDER BY `timestamp` DESC LIMIT 1", [ 'ip' => $ip ] );
	if( $lasttime ) {
		$now = date( 'U' );
		$then = date( 'U', strtotime( $lasttime ) );
		if( ( $now - $then ) <= YOURLS_FLOOD_DELAY_SECONDS ) {
			// Flood!
			yourls_do_action( 'ip_flood', $ip, $now - $then );
			yourls_die( yourls__( 'Muitas URLs adicionadas muito rápido. Devagar, por favor.' ), yourls__( 'Muitas Requisições ao mesmo tempo' ), 429 );
		}
	}

	return true;
}

/**
 * Checa se o RMCorte está instalando
 *
 * @since 1.6
 * @return bool
 */
function yourls_is_installing() {
	return (bool)yourls_apply_filter( 'is_installing', defined( 'YOURLS_INSTALLING' ) && YOURLS_INSTALLING );
}

/**
 * Checa se o RMCorte está atualizando
 *
 * @since 1.6
 * @return bool
 */
function yourls_is_upgrading() {
    return (bool)yourls_apply_filter( 'is_upgrading', defined( 'YOURLS_UPGRADING' ) && YOURLS_UPGRADING );
}


function yourls_is_installed() {
	return (bool)yourls_apply_filter( 'is_installed', yourls_get_db()->is_installed() );
}

/**
 * Checa a instalação
 *
 * @since  1.7.3
 * @param bool $bool se o RMCorte está instalado ou não
 * @return void
 */
function yourls_set_installed( $bool ) {
    yourls_get_db()->set_installed( $bool );
}

function yourls_rnd_string ( $length = 5, $type = 0, $charlist = '' ) {
    $length = intval( $length );

    // Define os caracteres possíveis
    switch ( $type ) {

        // sem vogais para não formar palavra ofensiva, sem 0/1/o/l para evitar confusão entre letras e dígitos. Perfeito para senhas. #ficadica
        case '1':
            $possible = "23456789bcdfghjkmnpqrstvwxyz";
            break;

        // Mesmo, com inferior + superior
        case '2':
            $possible = "23456789bcdfghjkmnpqrstvwxyzBCDFGHJKMNPQRSTVWXYZ";
            break;

        // todas as letras, minúsculas
        case '3':
            $possible = "abcdefghijklmnopqrstuvwxyz";
            break;

        // todas as letras, minúsculas + maiúsculas
        case '4':
            $possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            break;

        // todos os dígitos e letras minúsculas
        case '5':
            $possible = "0123456789abcdefghijklmnopqrstuvwxyz";
            break;

        // todos os dígitos e letras minúsculas + maiúsculas
        case '6':
            $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            break;

        // lista de caracteres personalizados - Conjunto de caracteres conforme definido na configuração
        default:
        case '0':
            $possible = $charlist ? $charlist : yourls_get_shorturl_charset();
            break;
    }

    $str = substr( str_shuffle( $possible ), 0, $length );
    return yourls_apply_filter( 'rnd_string', $str, $length, $type, $charlist );
}

/**
 * API
 *
 * @return bool
 */
function yourls_is_API() {
    return (bool)yourls_apply_filter( 'is_API', defined( 'YOURLS_API' ) && YOURLS_API );
}

/**
 * Ajax
 *
 * @return bool
 */
function yourls_is_Ajax() {
    return (bool)yourls_apply_filter( 'is_Ajax', defined( 'YOURLS_AJAX' ) && YOURLS_AJAX );
}


function yourls_is_GO() {
    return (bool)yourls_apply_filter( 'is_GO', defined( 'YOURLS_GO' ) && YOURLS_GO );
}


function yourls_is_infos() {
    return (bool)yourls_apply_filter( 'is_infos', defined( 'YOURLS_INFOS' ) && YOURLS_INFOS );
}


function yourls_is_admin() {
    return (bool)yourls_apply_filter( 'is_admin', defined( 'YOURLS_ADMIN' ) && YOURLS_ADMIN );
}

function yourls_is_windows() {
	return defined( 'DIRECTORY_SEPARATOR' ) && DIRECTORY_SEPARATOR == '\\';
}

/**
 * Checa se o SSL é necessário.
 *
 * @return bool
 */
function yourls_needs_ssl() {
    return (bool)yourls_apply_filter( 'needs_ssl', defined( 'YOURLS_ADMIN_SSL' ) && YOURLS_ADMIN_SSL );
}

/**
 * Checa se o SSL é usado, "Rouba" do WP.
 *
 * @return bool
 */
function yourls_is_ssl() {
    $is_ssl = false;
    if ( isset( $_SERVER[ 'HTTPS' ] ) ) {
        if ( 'on' == strtolower( $_SERVER[ 'HTTPS' ] ) ) {
            $is_ssl = true;
        }
        if ( '1' == $_SERVER[ 'HTTPS' ] ) {
            $is_ssl = true;
        }
    }
    elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) ) {
        if ( 'https' == strtolower( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) ) {
            $is_ssl = true;
        }
    }
    elseif ( isset( $_SERVER[ 'SERVER_PORT' ] ) && ( '443' == $_SERVER[ 'SERVER_PORT' ] ) ) {
        $is_ssl = true;
    }
    return (bool)yourls_apply_filter( 'is_ssl', $is_ssl );
}

/**
  * Obtem um título de página remota
  *
  * Esta função retorna uma string: o título da página conforme definido em HTML ou a URL se não for encontrada
  * A função tenta converter caracteres encontrados em títulos para UTF8, a partir do charset detectado.
  * O conjunto de caracteres em uso é adivinhado a partir da meta tag HTML ou, se não for encontrado, da resposta 'content-type' do servidor.
  *
  * @param string $url URL
  * @return string Título ou URL se nenhum título for encontrado
  */
function yourls_get_remote_title( $url ) {
    
    $pre = yourls_apply_filter( 'shunt_get_remote_title', false, $url );
    if ( false !== $pre ) {
        return $pre;
    }

    $url = yourls_sanitize_url( $url );

    
    if ( !in_array( yourls_get_protocol( $url ), [ 'http://', 'https://' ] ) ) {
        return $url;
    }

    $title = $charset = false;

    $max_bytes = yourls_apply_filter( 'get_remote_title_max_byte', 32768 ); // limite do data fetching de 32K na ordem para ser encontrado.

    $response = yourls_http_get( $url, [], [], [ 'max_bytes' => $max_bytes ] ); // Pode fazer request.
    if ( is_string( $response ) ) {
        return $url;
    }

    $content = $response->body;
    if ( !$content ) {
        return $url;
    }

    if ( preg_match( '/<title>(.*?)<\/title>/is', $content, $found ) ) {
        $title = $found[ 1 ];
        unset( $found );
    }
    if ( !$title ) {
        return $url;
    }

   
    if ( preg_match( '/<meta[^>]*charset\s*=["\' ]*([a-zA-Z0-9\-_]+)/is', $content, $found ) ) {
        $charset = $found[ 1 ];
        unset( $found );
    }
    else {
   
        $_charset = current( $response->headers->getValues( 'content-type' ) );
        if ( preg_match( '/charset=(\S+)/', $_charset, $found ) ) {
            $charset = trim( $found[ 1 ], ';' );
            unset( $found );
        }
    }

    if ( strtolower( $charset ) != 'utf-8' && function_exists( 'mb_convert_encoding' ) ) {
       
        if ( $charset ) {
            $title = @mb_convert_encoding( $title, 'UTF-8', $charset );
        }
        else {
            $title = @mb_convert_encoding( $title, 'UTF-8' );
        }
    }

    // Remove entidades HTML
    $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );

    // Limpa o Título
    $title = yourls_sanitize_title( $title, $url );

    return (string)yourls_apply_filter( 'get_remote_title', $title, $url );
}

/**
 * Checagem para aparelhos & celulares
 *
 * @return bool
 */
function yourls_is_mobile_device() {
	// Strings que são procuradas
	$mobiles = [
		'android', 'blackberry', 'blazer',
		'compal', 'elaine', 'fennec', 'hiptop',
		'iemobile', 'iphone', 'ipod', 'ipad',
		'iris', 'kindle', 'opera mobi', 'opera mini',
		'palm', 'phone', 'pocket', 'psp', 'symbian',
		'treo', 'wap', 'windows ce', 'windows phone'
    ];

	// User-agent
	$current = strtolower( $_SERVER['HTTP_USER_AGENT'] );

	// Checagem & Retorno
	$is_mobile = ( str_replace( $mobiles, '', $current ) != $current );
	return (bool)yourls_apply_filter( 'is_mobile_device', $is_mobile );
}


function yourls_get_request($yourls_site = '', $uri = '') {
 
    $pre = yourls_apply_filter( 'shunt_get_request', false );
    if ( false !== $pre ) {
        return $pre;
    }

    yourls_do_action( 'pre_get_request', $yourls_site, $uri );

    // Valores Padrões
    if ( '' === $yourls_site ) {
        $yourls_site = yourls_get_yourls_site();
    }
    if ( '' === $uri ) {
        $uri = $_SERVER[ 'REQUEST_URI' ];
    }

    // Mesmo que o exemplo de configuração a vaviável "YOURLS_SITE"  deve ser definida sem barra à direita
    $yourls_site = rtrim( $yourls_site, '/' );


    $yourls_site = parse_url( $yourls_site, PHP_URL_PATH ).'/';

    // Retirar parte do caminho da solicitação, se existir
    $request = $uri;
    if ( substr( $uri, 0, strlen( $yourls_site ) ) == $yourls_site ) {
        $request = ltrim( substr( $uri, strlen( $yourls_site ) ), '/' );
    }

    // A menos que o pedido se pareça com uma URL completa (ou seja, o pedido é uma palavra-chave simples) tira a string de consulta
    if ( !preg_match( "@^[a-zA-Z]+://.+@", $request ) ) {
        $request = current( explode( '?', $request ) );
    }

    $request = yourls_sanitize_url( $request );

    return (string)yourls_apply_filter( 'get_request', $request );
}

// Para implementação no WP
function yourls_fix_request_uri() {

    $default_server_values = [
        'SERVER_SOFTWARE' => '',
        'REQUEST_URI'     => '',
    ];
    $_SERVER = array_merge( $default_server_values, $_SERVER );

    // Correção para IIS ao executar com PHP ISAPI
    if ( empty( $_SERVER[ 'REQUEST_URI' ] ) || ( php_sapi_name() != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//', $_SERVER[ 'SERVER_SOFTWARE' ] ) ) ) {

        // IIS Mod-Rewrite
        if ( isset( $_SERVER[ 'HTTP_X_ORIGINAL_URL' ] ) ) {
            $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_ORIGINAL_URL' ];
        }
        // IIS Isapi_Rewrite
        elseif ( isset( $_SERVER[ 'HTTP_X_REWRITE_URL' ] ) ) {
            $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_REWRITE_URL' ];
        }
        else {
            // Usa ORIG_PATH_INFO se não houver PATH_INFO
            if ( !isset( $_SERVER[ 'PATH_INFO' ] ) && isset( $_SERVER[ 'ORIG_PATH_INFO' ] ) ) {
                $_SERVER[ 'PATH_INFO' ] = $_SERVER[ 'ORIG_PATH_INFO' ];
            }

            // Algumas configurações do IIS + PHP colocam o nome do script no path-info ( Não é necessário anexá-lo duas vezes :) )
            if ( isset( $_SERVER[ 'PATH_INFO' ] ) ) {
                if ( $_SERVER[ 'PATH_INFO' ] == $_SERVER[ 'SCRIPT_NAME' ] ) {
                    $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'PATH_INFO' ];
                }
                else {
                    $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'SCRIPT_NAME' ].$_SERVER[ 'PATH_INFO' ];
                }
            }

            // Anexa a string de consulta se ela existir e não for nula
            if ( !empty( $_SERVER[ 'QUERY_STRING' ] ) ) {
                $_SERVER[ 'REQUEST_URI' ] .= '?'.$_SERVER[ 'QUERY_STRING' ];
            }
        }
    }
}


function yourls_check_maintenance_mode() {
	$file = YOURLS_ABSPATH . '/.maintenance' ;

    if ( !file_exists( $file ) || yourls_is_upgrading() || yourls_is_installing() ) {
        return;
    }

	global $maintenance_start;
	include_once( $file );
	// Se o timestamp $maintenance_start tiver mais de 10 minutos, não morre.
	if ( ( time() - $maintenance_start ) >= 600 ) {
        return;
    }

	// Usa qualquer arquivo /user/maintenance.php
	if( file_exists( YOURLS_USERDIR.'/maintenance.php' ) ) {
		include_once( YOURLS_USERDIR.'/maintenance.php' );
		die();
	}

    // Ou use as mensagens padrão que deixarei aqui..
	$title   = yourls__( 'Serviço não disponível temporariamente, consulte a TiC.' );
	$message = yourls__( 'Nosso serviço está passando por manutenção programada.' ) . "</p>\n<p>" .
	yourls__( 'As alterações não devem durar muito, agradecemos por sua paciência e desculpe-nos o inconveniente' );
	yourls_die( $message, $title , 503 );
}

/**
  * Verifica se um protocolo da URL é permitido
  *
  * Verifica uma URL em relação a uma lista de protocolos na lista de permissões. Os protocolos devem ser definidos com
  * o nome completo do esquema, ou seja, 'stuff:' ou 'stuff://' (por exemplo, 'mailto:' é um
  * protocolo, 'mailto://' não é, e 'http:' sem barra dupla também não é
  *
  * @desde 1.6
  * 
  *
  * @param string $url URL a ser verificada
  * @param array $protocols Opcional. Matriz de protocolos, o padrão é global $ yourls_allowedprotocols
  * @return bool true se protocolo permitido, false caso contrário
  */

function yourls_is_allowed_protocol( $url, $protocols = [] ) {
    if ( empty( $protocols ) ) {
        global $yourls_allowedprotocols;
        $protocols = $yourls_allowedprotocols;
    }

    return yourls_apply_filter( 'is_allowed_protocol', in_array( yourls_get_protocol( $url ), $protocols ), $url, $protocols );
}

/**
 * Pega o protocolo vindo da URL(ex. mailto:, http:// ...)
 *
 * A nomeclatura "protocolo" no RMCorte é o nome do esquema + dois pontos + barras duplas se houver um URI. Exemplos:
 * "Alguma Coisa://blah" -> "Alguma Coisa://"
 * "Alguma Coisa:blah"   -> "Alguma Coisa:"
 * "Alguma Coisa:/blah"  -> "Alguma Coisa:"
 *
 * Testes de unidade para esta função estão localizados em tests/format/urls.php
 *
 */

function yourls_get_protocol( $url ) {
	
/*

    +============================+
    + A Título de Curiosidade    + 
    + Israel tbm é cultura       +
    + Senta que lá vem história  +
    +----------------------------+


    http://en.wikipedia.org/wiki/URI_scheme#Generic_syntax
    O nome do esquema consiste em uma sequência de caracteres começando com uma letra e seguido por qualquer
    combinação de letras, dígitos, mais ("+"), ponto ("."), ou hífen ("-"). Embora os esquemas sejam
    não diferenciam maiúsculas de minúsculas, a forma canônica é minúscula e os documentos que especificam esquemas devem fazê-lo
    com letras minúsculas. É seguido por dois pontos (":").
*/
    preg_match( '!^[a-zA-Z][a-zA-Z0-9+.-]+:(//)?!', $url, $matches );
	return (string)yourls_apply_filter( 'get_protocol', isset( $matches[0] ) ? $matches[0] : '', $url );
}

/**
  * Obter URL relativa (por exemplo, 'abc' de 'http://encurta.do/abc')
  *
  * Trate com indiferença http e https. Se uma URL não for relativa à instalação RMCorte, retorne-a como está
  * ou retorna uma string vazia se $strict for true
  *
  * @desde 1.6
  * @param string $url URL para relativizar
  * @param bool $strict Se verdadeiro e se a URL não for relativa à instalação do RMCorte, retorna uma string vazia
  * URL da string @return
  */

function yourls_get_relative_url( $url, $strict = true ) {
    $url = yourls_sanitize_url( $url );

    // Remove os protocolos para ficar mais fácil
    $noproto_url = str_replace( 'https:', 'http:', $url );
    $noproto_site = str_replace( 'https:', 'http:', yourls_get_yourls_site() );

    // Faz um Trim na URL da URL raiz RMCorte: Se nenhuma modificação foi feita, a URL não era relativa
    $_url = str_replace( $noproto_site.'/', '', $noproto_url );
    if ( $_url == $noproto_url ) {
        $_url = ( $strict ? '' : $url );
    }
    return yourls_apply_filter( 'get_relative_url', $_url, $url );
}

/**
  * O "Marks" marca uma função como obsoleta e informa que foi utilizada. Vinda do WP.
  *
  * Existe um hook deprecated_function que será chamado e pode ser usado
  * para obter o backtrace até qual arquivo e função chamou de obsoleto
  * função.
  *
  * O comportamento atual é acionar um erro do usuário se YOURLS_DEBUG for verdadeiro.
  *
  * Esta função deve ser usada em todas as funções que estão obsoletas.
  *
  * 
  * @uses yourls_do_action() Chama 'deprecated_function' e passa o nome da função, o que usar em vez disso,
  * e a versão em que a função foi preterida.
  * @uses yourls_apply_filter() Chama 'deprecated_function_trigger_error' e espera que o valor booleano true faça
  * disparar ou false para não disparar erro.
  *
  * @param string $function A função que foi chamada
  * @param string $version A versão do WordPress que desativou a função ( 99% de chance de não acontecer)
  * @param string $replacement Opcional. A função que deveria ter sido chamada
  * @return nulo
  */

function yourls_deprecated_function( $function, $version, $replacement = null ) {

	yourls_do_action( 'deprecated_function', $function, $replacement, $version );

	// Permite que o plug-in filtre o gatilho de erro de saída
	if ( yourls_get_debug_mode() && yourls_apply_filter( 'deprecated_function_trigger_error', true ) ) {
		if ( ! is_null( $replacement ) )
			trigger_error( sprintf( yourls__('%1$s está <strong>descontinuada</strong> desde da versão %2$s! Use %3$s em vez dessa.'), $function, $version, $replacement ) );
		else
			trigger_error( sprintf( yourls__('%1$s está <strong>descontinuada</strong> desde da versão %2$s sem alternativa disponível.'), $function, $version ) );
	}
}

/**
 * Explode uma URL em uma matriz de ( 'protocolo' , 'barras se houver', 'resto da URL' )
 *
 * Alguns hosts dão uma problemática quando uma string de consulta contém 'http://' - veja http://git.io/j1FlJg
 * A ideia é que ao invés de passar a URL inteira para um "Favorito", por exemplo index.php?u=http://blah.com.br,
 * passamos por partes para enganar o servidor, por exemplo index.php?proto=http:&slashes=//&rest=blah.com.br
 *
 * Limitação conhecida: isso não funcionará se o restante da própria URL contiver 'http://', ​​por exemplo
 * if rest = blah.com.br/file.php?url=http://foo.com.br
 *
 * Devoluções de amostra:
 *
 * Com 'mailto:julio.filho@example.com.br?subject=hey':
 * array( 'protocolo' => 'mailto:', 'barras' => '', 'rest' => 'julio.filho@example.com.br?subject=hey')
 *
 * com 'http://example.com/blah.html':
 * array( 'protocolo' => 'http:', 'barras' => '//', 'rest' => 'example.com/blah.html')
 *
 *
 * @param string $url URL a ser analisada
 * @param array $array Opcional, array de nomes de chaves a serem usados ​​no array retornado
 * @return array|false false se nenhum protocolo for encontrado, array de ('protocol' , 'slashes', 'rest') caso seja o contrário
 */
function yourls_get_protocol_slashes_and_rest( $url, $array = [ 'protocol', 'slashes', 'rest' ] ) {
    $proto = yourls_get_protocol( $url );

    if ( !$proto or count( $array ) != 3 ) {
        return false;
    }

    list( $null, $rest ) = explode( $proto, $url, 2 );

    list( $proto, $slashes ) = explode( ':', $proto );

    return [
        $array[ 0 ] => $proto.':',
        $array[ 1 ] => $slashes,
        $array[ 2 ] => $rest
    ];
}

/**
  * Definir esquema de URL (HTTP ou HTTPS) para uma URL
  *
  *
  * @param string $url URL
  * @param string $ esquema esquema, seja 'http' ou 'https'
  * URL da string @return com o esquema escolhido
  */
function yourls_set_url_scheme( $url, $scheme = '' ) {
    if ( in_array( $scheme, [ 'http', 'https' ] ) ) {
        $url = preg_replace( '!^[a-zA-Z0-9+.-]+://!', $scheme.'://', $url );
    }
    return $url;
}

/**
  * Informe se há uma nova versão do RMCorte
  *
  * Esta função verifica, se necessário, se há uma nova versão do RMCorte e, se aplicável, exibe
  * um aviso de atualização.
  *
  */

function yourls_tell_if_new_version() {
    yourls_debug_log( 'Verificar se existe uma nova versão: '.( yourls_maybe_check_core_version() ? 'sim' : 'não' ) );
    yourls_new_core_version_notice(YOURLS_VERSION);
}
