<?php
/*
Plugin Name: Show Active Plugins
Plugin URI: https://github.com/johnellmore/show-active-plugins
Description: WordPress plugin for multisite installs which shows you which plugins are activated on specific sites on your network sites. Useful to determine if a plugin is being used or not.
Author: John Ellmore
Version: 1.0
Author URI: http://johnellmore.com/
*/

class ShowActivePlugins {
	
	private $activations = null;
	private $failure = false;
	
	function __construct() {
		if (is_network_admin() && @$_GET['plugin_status'] != 'mustuse') {
			add_filter('plugin_row_meta', array($this, 'addActivationInformation'), null, 4);
		}
	}
	
	function addActivationInformation($pluginMeta, $pluginSlug, $plugin_data, $status) {
		
		$this->findAllActivations();
		
		if (!$this->failure) {
			$activationSites = $this->activations[$pluginSlug];
			$activationCount = count($activationSites);
			$infoLine = '<abbr title="'.implode(', ', $activationSites).'">'.$activationCount.' site activations</abbr>';
			if (!$activationCount) $infoLine = '<span style="color: #F00; font-weight: bold;">'.$infoLine.'</span>';
			$pluginMeta[] = $infoLine;
		}
		
		return $pluginMeta;
	}
	
	function findAllActivations() {
		if (is_array($this->activations)) return;
		
		// searching this stuff may take awhile--we need a time limit to prevent timeout on large sites
		$timeLimit = apply_filters('sap_time_limit', 2); // seconds allowed for the whole search
		$timeEnd = microtime(true) + $timeLimit;
		
		$plugins = array_keys(get_plugins());
		$activations = array_fill_keys($plugins, array());
		$sites = wp_get_sites(array(
			'archived'	=> false,
			'spam'		=> false,
			'deleted'	=> false
		));
		
		// iterate through each blog
		foreach ($sites as $site) {
			
			// make sure we haven't exceeded the time limit
			if (microtime(true) > $timeEnd) {
				$this->failure = true;
				break;
			}
			
			$isCurrentBlog = (get_current_blog_id() == $site['blog_id']);
			if (!$isCurrentBlog) switch_to_blog((int)$site['blog_id']);
			
			// iterate through each plugin and check if it's active on this site
			foreach ($plugins as $plugin) {
				if (is_plugin_active($plugin))
					array_push($activations[$plugin], $site['path']);
			}
			
			if (!$isCurrentBlog) restore_current_blog();
		}
		$this->activations = $activations;
	}
}
new ShowActivePlugins;