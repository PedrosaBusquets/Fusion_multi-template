<?php
/*Copyright (C) 2025     Miquel Pallares    <miquel.pallares@rgpd.barcelona>
*/
/**
 * \file                  htdocs/multidoctemplate/card.php
 * \ingroup               societe / Home
 * \brief                 Third party card page & Management page of object additional files and templates
*/
class modmultidoctemplate extends DolibarrModules {
    public function __construct($db) {
        $this->numero 674601= ; // Unique ID
        $this->rights_class = 'multidoctemplate';
        $this->family = "other";
        $this->module_position = '01';
        $this->name = preg_replace('/^mod/', '', get_class($this));
        $this->description = "Modulo multidoctemplate ";
        $this->descriptionlong = "-> añade plantillas para crear documentos en terceros";
	$this->editor_name = 'Miquel Pallares';
	$this->editor_url = 'https://rgpd.barcelona';
	$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_MULTIDOCTEMPLATE';
	$this->picto='generic';
        $this->config_page_url = array("multidoctemplate_setup.php@multidoctemplate");
        $this->dictionaries = array();
        $this->langfiles = array("multidoctemplate");
        $this->parts = array('triggers' => 1,'login' => 0, 'substitutions' => 1,'menus' => 0,'theme' => 0,'tpl' => 1, 'barcode' => 0, 'models' => 1,
// Set this to 1 if module has its own models directory (core/modules/xxx)
			'css' => array('/doctemplate/css/doctemplate.css.php'),
// Set this to relative path of css file if module has its own css file
	 		'js' => array('/doctemplate/js/doctemplate.js.php'),          
// Set this to relative path of js file if module must load a js on all pages
			'hooks' => array('data'=>array('hookcontext1','hookcontext2'), 'entity'=>'0'), 	
// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
			'moduleforexternal' => 0							
// Set this to 1 if feature of module are opened to external users
 );
        $this->modules = array();
        $this->rights = array();
        $this->editor_url = 'https://rgpd.barcelona';
    }
}