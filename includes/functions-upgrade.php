<?php

/**
  * Atualize o RMCorte e esquemático do banco de dados
  *
  * Nota para desenvolvedores da TiC: Prefira atualizar nomes de funções usando a versão SQL, por exemplo yourls_update_to_506(),
  * em vez de usar o número da versão RMCorte, por exemplo yourls_update_to_18(). Isso é para permitir ter
  * várias atualizações de SQL durante o ciclo de desenvolvimento da mesma versão RMCorte.
  *
  * @param string|int $step
  * @param string $oldver
  * @param string $newver
  * @param string|int $oldsql
  * @param string|int $newsql
  * @return nulo
  */
function yourls_upgrade($step, $oldver, $newver, $oldsql, $newsql ) {

    /**
      * Higieniza a entrada - Duas notas:
      * - Eles já devem ser higienizados no chamador, por exemplo, admin/upgrade.php
      *   	(mas ei, vamos ter certeza né, o seguro morreu de velho)
      * - Algumas vars não podem ser usadas no momento
      * 	(e tá tudo bem, elas estão aqui caso um procedimento de atualização futura precise delas)
      */
    $step   = intval($step);
    $oldsql = intval($oldsql);
    $newsql = intval($newsql);
    $oldver = yourls_sanitize_version($oldver);
    $newver = yourls_sanitize_version($newver);

    yourls_maintenance_mode(true);


	if( $oldsql == 100 ) {
		yourls_upgrade_to_14( $step );
	}

	// Outras atualizações que são feitas em uma única passagem
	switch( $step ) {

	case 1:
	case 2:
		if( $oldsql < 210 )
			yourls_upgrade_to_141();

		if( $oldsql < 220 )
			yourls_upgrade_to_143();

		if( $oldsql < 250 )
			yourls_upgrade_to_15();

		if( $oldsql < 482 )
			yourls_upgrade_482(); 

		if( $oldsql < 506 ) {
         
			if( $oldsql == 505 ) {
                yourls_upgrade_505_to_506();
            } else {
                yourls_upgrade_to_506();
            }
        }

		yourls_redirect_javascript( yourls_admin_url( "upgrade.php?step=3" ) );

		break;

	case 3:
		// Opções de atualização para refletir a versão mais recente
		yourls_update_option( 'version', YOURLS_VERSION );
		yourls_update_option( 'db_version', YOURLS_DB_VERSION );
        yourls_maintenance_mode(false);
		break;
	}
}

/************************** 1.6 -> 1.8 **************************/

function yourls_upgrade_505_to_506() {
    echo "<p>Atualizando o BD. Por favor aguarde..</p>";
	
	$query = sprintf('ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;', YOURLS_DB_TABLE_URL);

    try {
        yourls_get_db()->perform($query);
    } catch (\Exception $e) {
        echo "<p class='error'>Não foi possível atualizar o banco de dados.</p>";
        echo "<p>Não foi possível alterar o agrupamento. Você terá que corrigir as coisas manualmente :( .
        <pre>";
        echo $e->getMessage();
        echo "/n</pre>";
        die();
    }

    echo "<p class='success'>OK!</p>";
}

/**
 * Atualizada para 506
 *
 */
function yourls_upgrade_to_506() {
    $ydb = yourls_get_db();
    $error_msg = [];

    echo "<p>Atualizando o banco de dados. Por favor, espere...</p>";

    $queries = array(
        'database charset'     => sprintf('ALTER DATABASE `%s` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;', YOURLS_DB_NAME),
        'options charset'      => sprintf('ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', YOURLS_DB_TABLE_OPTIONS),
        'short URL varchar'    => sprintf("ALTER TABLE `%s` CHANGE `keyword` `keyword` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';", YOURLS_DB_TABLE_URL),
        'short URL type url'   => sprintf("ALTER TABLE `%s` CHANGE `url` `url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;", YOURLS_DB_TABLE_URL),
        'short URL type title' => sprintf("ALTER TABLE `%s` CHANGE `title` `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci", YOURLS_DB_TABLE_URL),
        'short URL charset'    => sprintf('ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;', YOURLS_DB_TABLE_URL),
    );

    foreach($queries as $what => $query) {
        try {
            $ydb->perform($query);
        } catch (\Exception $e) {
            $error_msg[] = $e->getMessage();
        }
    }

    if( $error_msg ) {
        echo "<p class='error'>Não foi possível atualizar o banco de dados.</p>";
        echo "<p>Você terá que corrigir as coisas manualmente, desculpe o inconveniente :(</p>";
        echo "<p>Os erros foram:
        <pre>";
        foreach( $error_msg as $error ) {
            echo "$error\n";
        }
        echo "</pre>";
        die();
    }

    echo "<p class='success'>OK!</p>";
}

/************************** 1.5 -> 1.6 **************************/

/**
 * Atualização r482
 *
 */
function yourls_upgrade_482() {
	// Altere o conjunto de caracteres do título da URL para UTF8
	$table_url = YOURLS_DB_TABLE_URL;
	$sql = "ALTER TABLE `$table_url` CHANGE `title` `title` TEXT CHARACTER SET utf8;";
	yourls_get_db()->perform( $sql );
	echo "<p>Atualizando a estrutura da tabela. Por favor, espere..</p>";
}

/************************** 1.4.3 -> 1.5 **************************/

/**
 * Função principal atualizada da 1.4.3 to 1.5
 *
 */
function yourls_upgrade_to_15( ) {
	// Cria uma entrada 'active_plugins' vazia na opção, se necessário
	if( yourls_get_option( 'active_plugins' ) === false )
		yourls_add_option( 'active_plugins', array() );
	echo "<p>Ativando a API do plug-in. Por favor, espere...</p>";

	// Alter URL table to store titles
	$table_url = YOURLS_DB_TABLE_URL;
	$sql = "ALTER TABLE `$table_url` ADD `title` TEXT AFTER `url`;";
	yourls_get_db()->perform( $sql );
	echo "<p>Atualizando a estrutura da tabela. Por favor, aguarde..</p>";

	// Atualiza o .htaccess
	yourls_create_htaccess();
	echo "<p>Atualizando o arquivo .htaccess. Por gentileza, aguarde..</p>";
}

/************************** 1.4.1 -> 1.4.3 **************************/

/**
 * Atualizando a função principal
 *
 */
function yourls_upgrade_to_143( ) {
	// Verifique se temos 'palavra-chave' (instalação borked) ou 'shorturl' (instalação ok)
	$ydb = yourls_get_db();
	$table_log = YOURLS_DB_TABLE_LOG;
	$sql = "SHOW COLUMNS FROM `$table_log`";
	$cols = $ydb->fetchObjects( $sql );
	if ( $cols[2]->Field == 'keyword' ) {
		$sql = "ALTER TABLE `$table_log` CHANGE `keyword` `shorturl` VARCHAR( 200 ) BINARY;";
		$ydb->query( $sql );
	}
	echo "<p>Estrutura das tabelas existentes atualizada. Por favor, espere...</p>";
}

/************************** 1.4 -> 1.4.1 **************************/

/**
 * Atualizando a função principal
 *
 */
function yourls_upgrade_to_141( ) {
	// Aqui mata todos os cookies velhos
	setcookie('yourls_username', '', time() - 3600 );
	setcookie('yourls_password', '', time() - 3600 );
	// alter table da URL
	yourls_alter_url_table_to_141();
	// recria o arquivo .htaccess se necessário
	yourls_create_htaccess();
}

/**
 * Alter table da URL para nova versão 1.4.1
 *
 */
function yourls_alter_url_table_to_141() {
	$table_url = YOURLS_DB_TABLE_URL;
	$alter = "ALTER TABLE `$table_url` CHANGE `keyword` `keyword` VARCHAR( 200 ) BINARY, CHANGE `url` `url` TEXT BINARY ";
	yourls_get_db()->perform( $alter );
	echo "<p>Estrutura das tabelas existentes atualizada. Por favor, espere...</p>";
}


/************************** 1.3 -> 1.4 **************************/

/**
 * Atualizando a função principal
 *
 */
function yourls_upgrade_to_14( $step ) {

	switch( $step ) {
	case 1:
	// cria log de tabela e opções de tabela
	// atualiza a estrutura da url da tabela
	// atualiza .htaccess
		yourls_create_tables_for_14();  // nenhum valor retornado, assumindo que deu certo
		yourls_alter_url_table_to_14(); // nenhum valor retornado, assumindo que deu certo
		$clean = yourls_clean_htaccess_for_14(); // retorna o bool
		$create = yourls_create_htaccess(); // retorna o bool
		if ( !$create )
			echo "<p class='warning'>Por favor crie o arquivo <tt>.htaccess</tt>. Entre em contato com a TiC</a>.";
		yourls_redirect_javascript( yourls_admin_url( "upgrade.php?step=2&oldver=1.3&newver=1.4&oldsql=100&newsql=200" ), $create );
		break;

	case 2:
		// converter cada link na url da tabela
		yourls_update_table_to_14();
		break;

	case 3:
		// Atualizar a estrutura da URL da tabela 
		// parte 2: recriar índices
		yourls_alter_url_table_to_14_part_two();
		// atualiza a versão & db_version & next_id na tabela de opções
		// tenta eliminar YOURLS_DB_TABLE_NEXTDEC
		yourls_update_options_to_14();
		// Atualiza a versão..
		yourls_redirect_javascript( yourls_admin_url( "upgrade.php?step=1&oldver=1.4&newver=1.4.1&oldsql=200&newsql=210" ) );
		break;
	}
}

/**
 * Opções de atualização para refletir na nova versão
 *
 */
function yourls_update_options_to_14() {
	yourls_update_option( 'version', '1.4' );
	yourls_update_option( 'db_version', '200' );

	if( defined('YOURLS_DB_TABLE_NEXTDEC') ) {
		$table = YOURLS_DB_TABLE_NEXTDEC;
		$next_id = yourls_get_db()->fetchValue("SELECT `next_id` FROM `$table`");
		yourls_update_option( 'next_id', $next_id );
		yourls_get_db()->perform( "DROP TABLE `$table`" );
	} else {
		yourls_update_option( 'next_id', 1 ); // Caso alguém tenha deletado por engano a constante ou tabela next_id muito cedo..
	}
}

/**
 *  Criar novas tabelas para o RMCorte v.1.4: Opções & log
 */
function yourls_create_tables_for_14() {
	$ydb = yourls_get_db();

	$queries = array();

	$queries[YOURLS_DB_TABLE_OPTIONS] =
		'CREATE TABLE IF NOT EXISTS `'.YOURLS_DB_TABLE_OPTIONS.'` ('.
		'`option_id` int(11) unsigned NOT NULL auto_increment,'.
		'`option_name` varchar(64) NOT NULL default "",'.
		'`option_value` longtext NOT NULL,'.
		'PRIMARY KEY (`option_id`,`option_name`),'.
		'KEY `option_name` (`option_name`)'.
		');';

	$queries[YOURLS_DB_TABLE_LOG] =
		'CREATE TABLE IF NOT EXISTS `'.YOURLS_DB_TABLE_LOG.'` ('.
		'`click_id` int(11) NOT NULL auto_increment,'.
		'`click_time` datetime NOT NULL,'.
		'`shorturl` varchar(200) NOT NULL,'.
		'`referrer` varchar(200) NOT NULL,'.
		'`user_agent` varchar(255) NOT NULL,'.
		'`ip_address` varchar(41) NOT NULL,'.
		'`country_code` char(2) NOT NULL,'.
		'PRIMARY KEY (`click_id`),'.
		'KEY `shorturl` (`shorturl`)'.
		');';

	foreach( $queries as $query ) {
		$ydb->perform( $query ); // Não há resultado a ser retornado para verificar se a tabela foi criada (exceto fazer outra consulta para verificar a existência da tabela, o que evitaremos não é mesmo pessoal da TiC ;) )
	}

	echo "<p>Novas tabelas criadas. Por favor aguarde..</p>";

}

/**
 * Alterar estrutura da tabela, parte 1 (alterar esquema, descartar índice)
 *
 */
function yourls_alter_url_table_to_14() {
	$ydb = yourls_get_db();
	$table = YOURLS_DB_TABLE_URL;

	$alters = array();
	$results = array();
	$alters[] = "ALTER TABLE `$table` CHANGE `id` `keyword` VARCHAR( 200 ) NOT NULL";
	$alters[] = "ALTER TABLE `$table` CHANGE `url` `url` TEXT NOT NULL";
	$alters[] = "ALTER TABLE `$table` DROP PRIMARY KEY";

	foreach ( $alters as $query ) {
		$ydb->perform( $query );
	}

	echo "<p>Estrutura das tabelas existentes atualizada. Por favor, espere...</p>";
}

/**
 * Alterar estrutura da tabela, parte 2 (recriar índices depois que a tabela estiver atualizada)
 *
 */
function yourls_alter_url_table_to_14_part_two() {
	$ydb = yourls_get_db();
	$table = YOURLS_DB_TABLE_URL;

	$alters = array();
	$alters[] = "ALTER TABLE `$table` ADD PRIMARY KEY ( `keyword` )";
	$alters[] = "ALTER TABLE `$table` ADD INDEX ( `ip` )";
	$alters[] = "ALTER TABLE `$table` ADD INDEX ( `timestamp` )";

	foreach ( $alters as $query ) {
		$ydb->perform( $query );
	}

	echo "<p>Novo índice de tabela criado</p>";
}


function yourls_update_table_to_14() {
	$ydb = yourls_get_db();
	$table = YOURLS_DB_TABLE_URL;

	// Modifica a cada link para refletir a nova estrutura
	$chunk = 45;
	$from = isset($_GET['from']) ? intval( $_GET['from'] ) : 0 ;
	$total = yourls_get_db_stats();
	$total = $total['total_links'];

	$sql = "SELECT `keyword`,`url` FROM `$table` WHERE 1=1 ORDER BY `url` ASC LIMIT $from, $chunk ;";

	$rows = $ydb->fetchObjects($sql);

	$count = 0;
	$queries = 0;
	foreach( $rows as $row ) {
		$keyword = $row->keyword;
		$url = $row->url;
		$newkeyword = yourls_int2string( $keyword );
		if( true === $ydb->perform("UPDATE `$table` SET `keyword` = '$newkeyword' WHERE `url` = '$url';") ) {
			$queries++;
		} else {
			echo "<p>Huho... Não foi possível atualizar a linha com url='$url', da palavra-chave '$keyword' para a palavra-chave '$newkeyword'</p>"; // Descubra o que deu errado :/
		}
		$count++;
	}

	// Tudo feito para este pedaço de consultas, tudo correu conforme o esperado ?
	$success = true;
	if( $count != $queries ) {
		$success = false;
		$num = $count - $queries;
		echo "<p>$num ocorreu(ram) erro(s) ao atualizar a tabela de URLs :(</p>";
	}

	if ( $count == $chunk ) {
		// Provavelmente há outras linhas para converter - Fica a dica ;)
		$from = $from + $chunk;
		$remain = $total - $from;
		echo "<p>Linhas do banco de dados $chunk  ($remain restantes). Continuando... Por favor, não feche esta janela até que esteja terminada!</p>";
		yourls_redirect_javascript( yourls_admin_url( "upgrade.php?step=2&oldver=1.3&newver=1.4&oldsql=100&newsql=200&from=$from" ), $success );
	} else {
		// Tudo certo e tudo pronto..
		echo '<p>Todas as linhas convertidas! Por favor, espere...</p>';
		yourls_redirect_javascript( yourls_admin_url( "upgrade.php?step=3&oldver=1.3&newver=1.4&oldsql=100&newsql=200" ), $success );
	}

}

/**
 * Limpa o .htaccess como existia antes da versão 1.4. Retorna o booleano
 *
 */
function yourls_clean_htaccess_for_14() {
	$filename = YOURLS_ABSPATH.'/.htaccess';

	$result = false;
	if( is_writeable( $filename ) ) {
		$contents = implode( '', file( $filename ) );
		// Remove o bloco "Encurtador" 
		$contents = preg_replace( '/# BEGIN ShortURL.*# END ShortURL/s', '', $contents );
		// RewriteRule obsoleto
		$find = 'RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization},L]';
		$replace = "# Você pode remover com segurança este bloco de 5 linhas - ele não é mais usado no RMCorte\n".
				"# $find";
		$contents = str_replace( $find, $replace, $contents );

		// Grava o arquivo limpo
		$f = fopen( $filename, 'w' );
		fwrite( $f, $contents );
		fclose( $f );

		$result = true;
	}

	return $result;
}
