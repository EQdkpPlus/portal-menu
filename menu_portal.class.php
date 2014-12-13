<?php
/*	Project:	EQdkp-Plus
 *	Package:	Menu Portal Module
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
		'icon'			=> 'fa-bars',
		'description'	=> 'Create your own menus',
		'lang_prefix'	=> 'menu_',
		'multiple'		=> true,
	);
	protected static $positions = array('middle', 'left', 'right', 'bottom');
	
	protected $settings	= array(
	);
	
	protected static $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'left',
		'defaultnumber'		=> '2',
	);
	
	protected static $apiLevel = 20;
	
	public function get_settings($state){
		$arrOptions = array(
			' ' => '',
		);
		// get all available links for dropdown
		$arrMenuItems = $this->core->build_menu_array(true, true);
		foreach($arrMenuItems as $page){
			$link = $this->user->removeSIDfromString($page['link']);
			$hash = (isset($page['_hash'])) ? $page['_hash'] : md5($link.$page['text']);
			if ($link != "" && $link != "#" && $link != "index.php"){
				$arrOptions[$hash] = $page['text'].' ('.$link.')';
			}
		}

		$maxID = (int) $this->config('link_count');
		$newID = $maxID+1;
		// add a new entry if new settings are displayed / fetched and last link is filled
		$maxloop = ($state == 'fetch_new' && $this->config('link_'.$maxID) != ' ') ? $newID : $maxID;
		
		for($i=1;$i<=$maxloop;$i++) {
			// check for outdated links
			if($state == 'fetch_new') {
				if ($i != $maxloop && ($this->config('link_'.$i) == "" || $this->config('link_'.$i) == " " || !isset($arrOptions[$this->config('link_'.$i)]))) {
					$this->del_config('link_'.$i);
					// move all links one number down
					for($j=$i+1;$j<=$maxloop;$j++) {
						$this->set_config('link_'.($j-1), $this->config('link_'.$j));
					}
					$maxloop--;
				}
			}
			$this->settings['link_'.$i] = array(
				'dir_lang'	=> sprintf($this->user->lang('menu_f_link'), $i),
				'type'		=> 'dropdown',
				'options'	=> $arrOptions,
				'class'		=> 'js_reload',
				'default'	=> '',
			);
		}
		if($state == 'fetch_new') $this->set_config('link_count', $i-1);
		return $this->settings;
	}

	public function output() {
		if (!$this->config('link_count')) return '';
		
		$arrMenuItems = $this->core->build_menu_array(true, true);
		foreach($arrMenuItems as $page){
			$link = $this->user->removeSIDfromString($page['link']);
			$hash = (isset($page['_hash'])) ? $page['_hash'] : md5($link.$page['text']);
			if ($link != "" && $link != "#" && $link != "index.php"){
				$arrOptions[$hash] = $page;
			}
		}

		$html = '<ul class="menu">';
		for($i=1;$i<=$this->config('link_count');$i++){	
			$hash = $this->config('link_'.$i);
			if (isset($arrOptions[$hash])){
				$data = $arrOptions[$hash];
				$html .= '<li>'.$this->core->createLink($data).'</li>';
			}	
		}
		$html .= '</ul>';
		return $html;
	}

	public static function uninstall(){
		$menu_portals = register('pdh')->get('portal', 'id_list', array('path' => 'menu'));
		$conf = register('config');
		foreach($menu_portals as $id) {
			for($i=1;$i<=$conf->get('link_count', 'pmod_'.$id);$i++) {
				$conf->del('link_'.$i, 'pmod_'.$id);
			}
		}
		$conf->del('link_count');
	}
}
?>