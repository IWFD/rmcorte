<?php

/**
  * Ações padrão do RMCorte ao Instanciar
  *
  * Esta classe define todas as ações padrão a serem executadas ao instanciar o RMCorte. A premissa disso
  * é que fica facilmente ajustável dependendo do cenário, ou seja, ao executar RMCorte para testes 
  * unitários.
  *
*/

namespace YOURLS\Config;

class InitDefaults {

   
    public $include_core_funcs = true;

    
    public $default_timezone = true;

    
    public $load_default_textdomain = true;

   
    public $check_maintenance_mode = true;

    
    public $fix_request_uri = true;

    public $redirect_ssl = true;

    
    public $include_db = true;

    
    public $include_cache = true;

   
    public $return_if_fast_init = true;

   
    public $get_all_options = true;

   
    public $register_shutdown = true;

    
    public $core_loaded = true;

   
    public $redirect_to_install = true;

   
    public $check_if_upgrade_needed = true;

   
    public $load_plugins = true;

    
    public $plugins_loaded_action = true;

  
    public $check_new_version = true;

  
    public $init_admin = true;

}
