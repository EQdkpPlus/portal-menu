<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2008
 * Date:		$Date: 2012-05-01 13:28:27 +0200 (Di, 01. Mai 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: hoofy_leon $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 11769 $
 * 
 * $Id: menu_portal.class.php 11769 2012-05-01 11:28:27Z hoofy_leon $
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class menu_portal extends portal_generic {

	protected static $path		= 'menu';
	protected static $data		= array(
		'name'			=> 'Menu Module',
		'version'		=> '0.1.0',
		'author'		=> 'GodMod',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Create your own menus',
		'lang_prefix'	=> 'menu_',
		'multiple'		=> true,
	);
	protected static $positions = array('middle', 'left', 'right', 'bottom');
	
	protected $settings	= array();
	
	protected static $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'left',
		'defaultnumber'		=> '2',
	);
	
	public function get_settings($state){
		$arrOptions = array(
			' ' => '',
		);
				
		$arrMenuItems = $this->core->build_menu_array(true, true);
		
		foreach($arrMenuItems as $page){
			$link = $this->user->removeSIDfromString($page['link']);
			$hash = (isset($page['_hash'])) ? $page['_hash'] : md5($link.$page['text']);
			if ($link != "" && $link != "#" && $link != "index.php"){
				$arrOptions[$hash] = $page['text'].' ('.$link.')';
			}
		}
		
		$settings = array(
			'pk_menu_headtext'	=> array(
				'name'		=> 'pk_menu_headtext',
				'language'	=> 'pk_menu_headtext',
				'property'	=> 'text',
				'size'		=> 30,
			),
		);

		$arrItems = @unserialize($this->config('pk_menu_count'));
		if (!$arrItems) $arrItems = array();
		$maxID = (count($arrItems)) ? max($arrItems) : 0;
		$newID = $maxID+1;
				
		if ($state == 'fetch_old' || $state == 'save'){
			$count = 1;
			foreach($arrItems as $key => $value){			
				$settings['pk_menu_link_'.($key)] = array(
					'name'		=> 'pk_menu_link_'.($key),
					'language'	=> sprintf($this->user->lang('pk_link'), $count),
					'property'	=> 'dropdown',
					'options'	=> $arrOptions,
					'no_lang'	=> true,
					'javascript'=> 'onchange="load_settings()"',
					'default'	=> "",
				);
				$count++;
			}
			return $settings;
		}
		
		if ($state == 'fetch_new'){
			$count = 1;
			foreach($arrItems as $key => $value){
				if ($this->config('pk_menu_link_'.$key) == "" || $this->config('pk_menu_link_'.$key) == " " || !isset($arrOptions[$this->config('pk_menu_link_'.$key)])){
					unset($arrItems[$key]);
					$this->del_config('pk_menu_link_'.$key);
				} else {
					$settings['pk_menu_link_'.$key] = array(
						'name'		=> 'pk_menu_link_'.$key,
						'language'	=> sprintf($this->user->lang('pk_link'), $count),
						'property'	=> 'dropdown',
						'options'	=> $arrOptions,
						'no_lang'	=> true,
						'javascript'=> 'onchange="load_settings()"',
						'default'	=> "",
					);
					$count++;
				}
			}
			
			$settings['pk_menu_link_'.($newID)] = array(
				'name'		=> 'pk_menu_link_'.($newID),
				'language'	=> sprintf($this->user->lang('pk_link'), $count),
				'property'	=> 'dropdown',
				'options'	=> $arrOptions,
				'no_lang'	=> true,
				'javascript'=> 'onchange="load_settings()"',
				'default'	=> "",
			);
			$arrItems[$newID] = $newID;
			
			$this->set_config('pk_menu_count', serialize($arrItems));
		}

		return $settings;
	}

	public function output() {
		if($this->config('pk_menu_headtext')){
			$this->header = sanitize($this->config('pk_menu_headtext'));
		}
		$arrItems = @unserialize($this->config('pk_menu_count'));
		if (!$arrItems) return '';
		
		$arrMenuItems = $this->core->build_menu_array(true, true);
						
		foreach($arrMenuItems as $page){
			$link = $this->user->removeSIDfromString($page['link']);
			$hash = (isset($page['_hash'])) ? $page['_hash'] : md5($link.$page['text']);
			if ($link != "" && $link != "#" && $link != "index.php"){
				$arrOptions[$hash] = $page;
			}
		}

		$html = '<ul class="menu">';
		foreach($arrItems as $key => $value){	
			$hash = $this->config('pk_menu_link_'.$key);
			if (isset($arrOptions[$hash])){
				$data = $arrOptions[$hash];
				$html .= '<li>'.$this->core->createLink($data).'</li>';
			}	
		}
		$html .= '</ul>';
		return $html;
	}

	
	public function static reset(){
		$arrItems = @unserialize($this->config('pk_menu_count'));
		if (!$arrItems) $arrItems = array();
		foreach($arrItems as $key => $value){			
			$this->del_config('pk_menu_link_'.$key);
		}
		$this->del_config('pk_menu_count');
	}
}
?>