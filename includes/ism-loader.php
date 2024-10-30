<?php



  if ( ! defined( 'ABSPATH' ) ) exit;

/**
  * CORE
  */
  require_once(ISM_PATH . 'vendor/autoload.php' );
  require_once(ISM_PATH . 'includes/functions.php' );
  require_once(ISM_PATH . 'includes/in-stock-manager.php' );
  require_once(ISM_PATH . 'includes/class.mailchimp-collector.php' );
  require_once(ISM_PATH . 'includes/cron-manager.php' );
  require_once(ISM_PATH . 'includes/api-manager.php' );
  require_once(ISM_PATH . 'includes/display-manager.php' );
  require_once(ISM_PATH . 'includes/enqueue-manager.php' );

  if ( is_admin() ) {

   /**
    * PLUGIN SETTINGS
    */
     require_once(ISM_PATH . 'includes/options/callbacks.php' );
     require_once(ISM_PATH . 'includes/options/option-validate.php' );
     require_once(ISM_PATH . 'includes/options/settings-register.php' );

   /**
    * ACTIVATION - UPDATE MANAGER
    */
     require_once(ISM_PATH . 'includes/activation-manager.php' );
     require_once(ISM_PATH . 'includes/update-manager.php' );

  }
