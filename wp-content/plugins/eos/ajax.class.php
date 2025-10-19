<?php
class ClassEOSErpAjax {
    public function __construct()
    {
        add_action('wp_ajax_eos_erp_get_datesset', array($this, 'eos_erp_get_datesset'));
        add_action('wp_ajax_nopriv_eos_erp_get_datesset', array($this, 'eos_erp_get_datesset'));
        // add_action('wp_head', array($this, 'test'));
    }

    public function wp_ajax_eos_erp_get_datesset(){
        echo "wp_ajax_eos_erp_get_datesset";
        var_dump($_POST);
    }
}
?>