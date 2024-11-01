<?php
/*
Plugin Name: WP Autoload
Plugin URI: https://charles.lecklider.org/wordpress/wp-autoload
Description: Automatically load per-template CSS, JavaScript, and PHP files.
Version: 2.5.1
Author: Charles Lecklider
Author URI: https://charles.lecklider.org/
License: GPL2
*/

/*  Copyright 2011, 2012  Charles Lecklider  (email : wordpress@charles.lecklider.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class wp_autoload_options
{
	static $defaults	= array('js_dir'			=> 'js',
								'js_depends'		=> array(),
								'js_depends_always'	=> false,
								'js_hierarchy'		=> false,
								'js_template_parts'	=> array(),
								'css_dir'			=> 'css',
								'css_always'		=> array(),
								'css_hierarchy'		=> false,
								'inc_dir'			=> 'inc',
								'inc_global'		=> false,
								'inc_global_name'	=> 'TC');

	protected function get_options()
	{
		return array_merge(self::$defaults, get_option('wp_autoload',array()));
	}
}

if (is_admin()) {

	class wp_autoload extends wp_autoload_options
	{
		function __construct()
		{
			add_action('admin_init', array(&$this,'admin_init'));
			add_action('admin_menu', array(&$this,'admin_menu'));
		}

		function admin_init()
		{
			load_plugin_textdomain('wp-autoload', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			register_setting('wp_autoload_options', 'wp_autoload', array(&$this,'validate'));

			/*** Scripts ***/
			add_settings_section('wp_autoload_scripts',			__('hdr_scripts',		'wp-autoload'),	array(&$this,'js'),					'wp_autoload');
			add_settings_field('wp_autoload_js_dir',			__('js_dir',			'wp-autoload'),	array(&$this,'js_dir'),				'wp_autoload', 'wp_autoload_scripts');
			add_settings_field('wp_autoload_js_depends',		__('js_depends',		'wp-autoload'),	array(&$this,'js_depends'),			'wp_autoload', 'wp_autoload_scripts');
			add_settings_field('wp_autoload_js_depends_always',	__('js_depends_always',	'wp-autoload'),	array(&$this,'js_depends_always'),	'wp_autoload', 'wp_autoload_scripts');
			add_settings_field('wp_autoload_js_hierarchy',		__('js_hierarchy',		'wp-autoload'),	array(&$this,'js_hierarchy'),		'wp_autoload', 'wp_autoload_scripts');
			add_settings_field('wp_autoload_js_template_parts',	__('js_template_parts',	'wp-autoload'),	array(&$this,'js_template_parts'),	'wp_autoload', 'wp_autoload_scripts');

			/*** Styles ***/
			add_settings_section('wp_autoload_styles',			__('hdr_styles',		'wp-autoload'),	array(&$this,'css'),				'wp_autoload');
			add_settings_field('wp_autoload_css_dir',			__('css_dir',			'wp-autoload'),	array(&$this,'css_dir'),			'wp_autoload', 'wp_autoload_styles');
			add_settings_field('wp_autoload_css_always',		__('css_always',		'wp-autoload'),	array(&$this,'css_always'),			'wp_autoload', 'wp_autoload_styles');
			add_settings_field('wp_autoload_css_hierarchy',		__('css_hierarchy',		'wp-autoload'),	array(&$this,'css_hierarchy'),		'wp_autoload', 'wp_autoload_styles');

			/*** Includes ***/
			add_settings_section('wp_autoload_inc',				__('hdr_includes',		'wp-autoload'),	array(&$this,'inc'),				'wp_autoload');
			add_settings_field('wp_autoload_inc_dir',			__('inc_dir',			'wp-autoload'),	array(&$this,'inc_dir'),			'wp_autoload',	'wp_autoload_inc');
			add_settings_field('wp_autoload_inc_global',		__('inc_global',		'wp-autoload'),	array(&$this,'inc_global'),			'wp_autoload',	'wp_autoload_inc');
			add_settings_field('wp_autoload_inc_global_name',	__('inc_global_name',	'wp-autoload'),	array(&$this,'inc_global_name'),	'wp_autoload',	'wp_autoload_inc');
		}

		function admin_menu()
		{
			add_options_page('WP Autoload','WP Autoload', 'manage_options', 'wp-autoload', array(&$this,'menu'));
		}

		function menu()
		{
			$options = $this->get_options();

			include 'wp-autoload-admin.php';
		}

		function validate($input)
		{
			$new_input = self::$defaults;

			if ($input['js_dir'] == sanitize_title($input['js_dir'])) {
				$new_input['js_dir'] = $input['js_dir'];
			} else {
				add_settings_error('wp_autoload_js_dir', 'wp_autoload_js_dir_error', __('js_dir_err','wp-autoload'));
			}

			$depends_error = false;
			$js_depends = array();
			if (strlen($depends = trim($input['js_depends']))) {
				$depend = strtok($depends,',');
				while ($depend) {
					$tdepend = trim($depend);
					if ($tdepend == sanitize_title($tdepend)) {
						$js_depends[] = $tdepend;
					} else {
						$depends_error = true;
					}
					$depend = strtok(',');
				}
			}
			$new_input['js_depends'] = $js_depends;
			if ($depends_error)
				add_settings_error('wp_autoload_js_depends', 'wp_autoload_js_depends_error', __('js_depends_err','wp-autoload'));

			$new_input['js_depends_always'] = (1==$input['js_depends_always']);

			$new_input['js_hierarchy'] = (1==$input['js_hierarchy']);

			if ($input['css_dir'] == sanitize_title($input['css_dir'])) {
				$new_input['css_dir'] = $input['css_dir'];
			} else {
				add_settings_error('wp_autoload_css_dir', 'wp_autoload_css_dir_error', __('css_dir_error','wp-autoload'));
			}

			$always_error = false;
			$css_always = array();
			if (strlen($always = trim($input['css_always']))) {
				$tok = strtok($always,',');
				while ($tok) {
					$ttok = trim($tok);
					if ($ttok == sanitize_title($ttok)) {
						$css_always[] = $ttok;
					} else {
						$always_error = true;
					}
					$tok = strtok(',');
				}
			}
			$new_input['css_always'] = $css_always;
			if ($always_error)
				add_settings_error('wp_autoload_css_always', 'wp_autoload_css_always_error', __('css_always_err','wp-autoload'));

			$new_input['css_hierarchy'] = (1==$input['css_hierarchy']);

			$slugs = array_map(function($a){ return sanitize_title(basename($a,'.php')); }, glob(get_stylesheet_directory().'/*.php'));
			foreach($input['js_template_parts'] as $slug) {
				if (in_array($slug,$slugs))
					$new_input['js_template_parts'][] = $slug;
			}

			$new_input['inc_global'] = (1==$input['inc_global']);

			return $new_input;
		}

		function js()
		{
		//	_e('js_desc','wp-autoload');
		}

		function js_dir()
		{
			$this->opt_dir('js_dir');
		}

		function js_depends()
		{
			$options = $this->get_options();
			echo '<input class="regular-text" name="wp_autoload[js_depends]" type="text" value="'.implode(', ',$options['js_depends']).'" />';
		}

		function js_depends_always()
		{
			$options = $this->get_options();
			echo '<input name="wp_autoload[js_depends_always]" type="checkbox" value="1" '.checked(1,$options['js_depends_always'],false).' />';
		}

		function js_hierarchy()
		{
			$options = $this->get_options();
			echo '<input name="wp_autoload[js_hierarchy]" type="checkbox" value="1" '.checked(1,$options['js_hierarchy'],false).' />';
		}

		function js_template_parts()
		{
			$excludes = array('404','search','taxonomy','category','tag','archive','paged','author','date','attachment','single','page','front-page','home','comments-popup','index','functions','comments','header','footer','sidebar');
			$options = $this->get_options();
			$files = array_map(function($a){ return basename($a,'.php'); }, glob(get_stylesheet_directory().'/*.php'));
			// exclude category templates
			foreach(get_categories(array('hide_empty'=>false)) as $category) {
				$excludes[] = 'category-'.$category->slug;
				$excludes[] = 'category-'.$category->term_id;
			}
			// exclude page templates
			foreach(get_pages() as $page) {
				$excludes[] = 'page-'.$page->post_name;
				$excludes[] = 'page-'.$page->ID;
			}
			// exclude custom post type templates
			foreach(get_post_types(array('public'=>true)) as $post_type) {
				$excludes[] = 'archive-'.$post_type;
				$excludes[] = 'single-'.$post_type;
			}
			// exclude taxonomy templates
			foreach(get_taxonomies('','names') as $taxonomy) {
				foreach(get_terms($taxonomy,array('hide_empty'=>false)) as $term) {
					$excludes[] = 'taxonomy-'.$taxonomy.'-'.$term->slug;
				}
				$excludes[] = 'taxonomy-'.$taxonomy;
			}
			foreach(array_diff($files,$excludes) as $file) {
				$slug		= sanitize_title($file);
				$input_id	= 'wp_autoload-js_template_parts-'.$slug;
				$checked	= (in_array($slug,$options['js_template_parts'])) ? 'checked="checked"' : '';
				echo '<input id="'.$input_id.'" name="wp_autoload[js_template_parts]['.$slug.']" type="checkbox" value="'.$slug.'" '.$checked.'/><label for="'.$input_id.'">'.$file.'</label><br/>';
			}
		}

		function css()
		{
		//	_e('css_desc','wp-autoload');
		}

		function css_dir()
		{
			$this->opt_dir('css_dir');
		}

		function css_always()
		{
			$options = $this->get_options();
			echo '<input class="regular-text" name="wp_autoload[css_always]" type="text" value="'.implode(', ',$options['css_always']).'" />';
		}

		function css_hierarchy()
		{
			$options = $this->get_options();
			echo '<input name="wp_autoload[css_hierarchy]" type="checkbox" value="1" '.checked(1,$options['css_hierarchy'],false).' />';
		}

		function inc()
		{

		}

		function inc_dir()
		{
			$this->opt_dir('inc_dir');
		}

		function inc_global()
		{
			$options = $this->get_options();
			echo '<input name="wp_autoload[inc_global]" type="checkbox" value="1" '.checked(1,$options['inc_global'],false).' />';
		}

		function inc_global_name()
		{
			$options = $this->get_options();
			echo '<input class="regular-text" name="wp_autoload[inc_global_name]" type="text" value="'.$options['inc_global_name'].'" />';
		}

		protected function opt_dir($name)
		{
			$options = $this->get_options();
			echo '<input class="medium-text" name="wp_autoload['.$name.']" type="text" value="'.$options[$name].'" />';
			if ($options[$name] > '')
				echo '&nbsp;<em>Directory '.(file_exists(get_stylesheet_directory().'/'.$options[$name]) ? 'exists.' : 'doesn\'t exist.').'</em>';
		}
	}

} else {

	abstract class WP_Autoload_Template
	{
		// stub for now
	}

	class wp_autoload extends wp_autoload_options
	{
		public $template_class = array();
		protected $wp_autoload_scripts	= array();

		function __construct()
		{
			add_filter('template_include',array(&$this,'template_include'));
			add_action('get_sidebar',array(&$this,'get_sidebar'));
			add_action('wp_footer',array(&$this,'footer'));

			/*** Register actions for selected template parts ***/
			$options = $this->get_options();
			if (is_array(@$optons['js_template_parts'])) {
				foreach($optons['js_template_parts'] as $part) {
					add_action('get_template_part_'.$part, array(&$this,'template_part_scripts'), 10, 2);
				}
			}
		}

		function template_include($template,$is_sidebar=false)
		{
			global $post;

			$options	= $this->get_options();
			$classname	= null;
			$base		= basename($template,'.php');
			$baseparts	= array();
			$thisbase	= '';
			$depends	= $options['js_depends'];

			if (!$is_sidebar) {
				if ($options['js_depends_always']) {
					$lastDepends = array();
					foreach($depends as $depend) {
						$path = '/'.$options['js_dir'].'/'.$depend.'.js';
						if (file_exists(get_stylesheet_directory().$path)) {
							wp_register_script($depend,
											   get_stylesheet_directory_uri().$path,
											   $lastDepends,
											   '1.0',
											   false);
						}
						wp_enqueue_script($depend);
						$lastDepends[] = $depend;
					}
				}

				foreach($options['css_always'] as $always) {
					$path = '/'.$options['css_dir'].'/'.$always.'.css';
					if (file_exists(get_stylesheet_directory().$path)) {
						wp_register_style($always,
										  get_stylesheet_directory_uri().$path,
										  $always,
										  '1.0');
						wp_enqueue_style($always);
					}
				}
			}

			foreach(explode('-',$base) as $part) {
				$baseparts[] = $part;
				$thisbase = implode('-',$baseparts);

				/*** JavaScript ***/
				$path = '/'.$options['js_dir'].'/'.$thisbase.'.js';
				if (file_exists(get_stylesheet_directory().$path)) {
					wp_register_script($thisbase,
									   get_stylesheet_directory_uri().$path,
									   $depends,
									   '1.0',
									   true);
					wp_enqueue_script($thisbase);
					$depends = $thisbase;
				}

				/*** CSS ***/
				$path = '/'.$options['css_dir'].'/'.$thisbase.'.css';
				if (file_exists(get_stylesheet_directory().$path)) {
					wp_register_style($thisbase,
									  get_stylesheet_directory_uri().$path,
									  $thisbase,
									  '1.0');
					wp_enqueue_style($thisbase);
				}

				/*** Includes ***/
				$file = get_stylesheet_directory().'/'.$options['inc_dir'].'/'.$thisbase.'.php';
				if (file_exists($file)) {
					include_once $file;
					$classname = 'WP_Autoload_'.str_replace('-','_',$thisbase);
				}
			}

			if (!is_null($classname) && !array_key_exists($base,$this->template_class)) {
				if (is_subclass_of($classname,'WP_Autoload_Template')) {
					$this->template_class[$base] = new $classname();

					if ($options['inc_global']) {
						$GLOBALS[$options['inc_global_name']][$base] = $this->template_class[$base];
					}
				} else {
					error_log("Object not created: '{$classname}' is not a subclass of 'WP_Autoload_Template'.");
				}
			}

			if (($options['js_hierarchy'] || $options['css_hierarchy']) && !$is_sidebar && is_post_type_hierarchical(get_post_type($post->ID))) {
				$dirs = array($post->post_name);
				// post->ancestors only contains IDs and there are no hooks
				while ($post->post_parent) {
					$qry = new WP_Query('post_type=page&page_id='.$post->post_parent);
					$qry->the_post();
					array_unshift($dirs,$post->post_name);
				}

				if ($options['js_hierarchy']) {
					$path = '/'.$options['js_dir'].'/page/'.implode('/',$dirs).'.js';
					if (file_exists(get_stylesheet_directory().$path)) {
						$slug = implode('-',$dirs).'-'.$base.'-js';
						wp_register_script($slug,
										   get_stylesheet_directory_uri().$path,
										   '',//$depends,
										   '1.0',
										   true);
						wp_enqueue_script($slug);
					}
				}

				if ($options['css_hierarchy']) {
					$path = '/'.$options['css_dir'].'/page/'.implode('/',$dirs).'.css';
					if (file_exists(get_stylesheet_directory().$path)) {
						$slug = implode('-',$dirs).'-'.$base.'-css';
						wp_register_style($slug,
										  get_stylesheet_directory_uri().$path,
										  '',
										  '1.0');
						wp_enqueue_style($slug);
					}
				}
				wp_reset_postdata();
			}

			return $template;
		}

		function get_sidebar($name)
		{
			$options = $this->get_options();

			$template = 'sidebar';
			if (isset($name))
				$template .= '-'.$name;
			$template .= '.php';

			$this->template_include($template,true);
		}

		function template_part_scripts($slug, $name)
		{
			$options = $this->get_options();

			if (!array_key_exists($slug,$this->wp_autoload_scripts)) {
				$script = '/'.$options['js_dir'].'/'.$slug.'-'.$name.'.js';
				if ($this->wp_autoload_scripts[$slug] = file_exists(get_template_directory().$script)) {
					wp_register_script($slug,
									   get_template_directory_uri().$script,
									   $options['js_depends'],
									   '1.0',
									   true);
				}
			}
		}

		function footer()
		{
			foreach(array_keys($this->wp_autoload_scripts,true) as $script) {
				wp_print_scripts($script);
			}
		}

		function enqueue_script($script)
		{
			wp_enqueue_script($script);
			$this->wp_autoload_scripts[$script] = true;
		}
	}
}

$wp_autoload = new wp_autoload();

