<?php

/**
 * Conectando ao Banco de Dados
 *
 * @since 1.0
 * @return \YOURLS\Database\YDB
 */
function yourls_db_connect() {
    global $ydb;

    if ( !defined( 'YOURLS_DB_USER' )
         or !defined( 'YOURLS_DB_PASS' )
         or !defined( 'YOURLS_DB_NAME' )
         or !defined( 'YOURLS_DB_HOST' )
    ) {
        yourls_die( yourls__( 'Incorrect DB config, please refer to documentation' ), yourls__( 'Fatal error' ), 503 );
    }

    $dbhost = YOURLS_DB_HOST;
    $user = YOURLS_DB_USER;
    $pass = YOURLS_DB_PASS;
    $dbname = YOURLS_DB_NAME;

    // Gambiarra
    yourls_do_action( 'set_DB_driver', 'deprecated' );

    // Escolhe uma porta se houver necessidade
    if ( false !== strpos( $dbhost, ':' ) ) {
        list( $dbhost, $dbport ) = explode( ':', $dbhost );
        $dbhost = sprintf( '%1$s;port=%2$d', $dbhost, $dbport );
    }

    $charset = yourls_apply_filter( 'db_connect_charset', 'utf8mb4' );

 
    $dsn = sprintf( 'mysql:host=%s;dbname=%s;charset=%s', $dbhost, $dbname, $charset );
    $dsn = yourls_apply_filter( 'db_connect_custom_dsn', $dsn );

  
    $driver_options = yourls_apply_filter( 'db_connect_driver_option', [] ); 
    $attributes = yourls_apply_filter( 'db_connect_attributes', [] ); 

    $ydb = new \YOURLS\Database\YDB( $dsn, $user, $pass, $driver_options, $attributes );
    $ydb->init();

    // Passado deste ponto, você estará conectado
    yourls_debug_log( sprintf( 'Connected to database %s on %s ', $dbname, $dbhost ) );

    yourls_debug_mode( YOURLS_DEBUG );

    return $ydb;
}


function yourls_get_db() {
    
    $pre = yourls_apply_filter( 'shunt_get_db', false );
    if ( false !== $pre ) {
        return $pre;
    }

    global $ydb;
    $ydb = ( isset( $ydb ) ) ? $ydb : yourls_db_connect();
    return yourls_apply_filter('get_db', $ydb);
}

function yourls_set_db($db) {
    global $ydb;

    if (is_null($db)) {
        unset($ydb);
    } else {
        $ydb = $db;
    }
}
