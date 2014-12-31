<?php
/*
Plugin Name: Beltway Theme Assets
Plugin URI: https://bitbucket.org/johnellmore/beltway-theme-assets
Description: Registers common resources and provides access to common content blocks that are universal across Beltway sites.
Author: John Ellmore
Version: 1.0
Author URI: http://johnellmore.com/
*/

class ShowActivePlugins {
	
	private $activations = null;
	
	function __construct() {
		add_filter('plugin_row_meta', array($this, 'addActivationInformation'));
	}
	
	function addActivationInformation($plugin_meta, $plugin_file, $plugin_data, $status) {
		print_r($plugin_meta);
		
		return $plugin_meta;
	}
	
	function getActivationsList() {
		//if (is_array($this->activations)) return;
		//$activations = array();
	}
}
new ShowActivePlugins;