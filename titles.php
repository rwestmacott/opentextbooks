<?php
/**
 * copy and paste master template for new pages
 */
include_once 'autoloader.php';

use BCcampus\OpenTextBooks\Controllers\Catalogue;

include(OTB_DIR . 'assets/templates/partial/header.php');
include(OTB_DIR . 'assets/templates/partial/head.php');
include(OTB_DIR . 'assets/templates/partial/error-level.php');
include(OTB_DIR . 'assets/templates/partial/container-solr-start.php');
?>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <?php
        
        $args = ['lists' => 'titles', 'type_of' => 'books'];
        new Catalogue\OtbController($args);
        
        ?>
    </div>

<?php
include(OTB_DIR . 'assets/templates/partial/container-end.php');
include(OTB_DIR . 'assets/templates/partial/footer.php' );
?>