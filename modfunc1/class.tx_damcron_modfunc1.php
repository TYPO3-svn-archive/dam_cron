<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2006 Rene Fritz (r.fritz@colorcube.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module extension (addition to function menu) 'Cron Job' for the 'dam_cron' extension.
 *
 * @author	Rene Fritz <r.fritz@colorcube.de>
 */


require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');

require_once(t3lib_extMgm::extPath('dam_index').'modfunc_index/class.tx_damindex_index.php');


$LANG->includeLLFile('EXT:dam_index/modfunc_index/locallang.xml');

/**
 * Module 'Tools>Media>Cron job'
 *
 * @author	Rene Fritz <r.fritz@colorcube.de>
 */
class tx_damcron_modfunc1 extends tx_damindex_index {

	var$uploadFolder = 'uploads/tx_damcron/';

	/**
	 * Function menu initialization
	 *
	 * @return	array		Menu array
	 */
	function modMenu()    {
		global $LANG;

		return array(
			'tx_damindex_index_func' => array(
				'definfo' => $LANG->getLL('tx_damcron.func_definfo'),
				'index' => $LANG->getLL('tx_damcron.func_defindex'),
				'info' => $LANG->getLL('tx_damindex_index.func_info'),
			),
		);

	}

	function head() {
		global  $TYPO3_CONF_VARS, $FILEMOUNTS;

		if(!is_object($GLOBALS['SOBE']->basicFF)) {
			$GLOBALS['SOBE']->basicFF = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			$GLOBALS['SOBE']->basicFF->init($FILEMOUNTS,$TYPO3_CONF_VARS['BE']['fileExtensions']);
		}

		$this->pObj->guiCmdIconsDeny[] = 'popup';

		return parent::head();
	}


	function getCurrentFunc() {

		$func = parent::getCurrentFunc();

		if (t3lib_div::_GP('indexSave')) {
			$func = 'indexSave';
		}

		if ($func=='indexSave' AND !($file = t3lib_div::_GP('filename'))) {
			$func = 'indexStart';
		}

		return $func;
	}


	/**
	 * Generates the module content
	 *
	 * @return	string		HTML content
	 */
	function moduleContent($header='', $description='', $lastStep=4)    {
		global  $BE_USER, $LANG, $BACK_PATH, $FILEMOUNTS;

		$content = '';


		switch($this->getCurrentFunc())    {
			case 'index':
			case 'index1':
				$content.= parent::moduleContent('Indexing start point', '<p style="margin:0.8em 0 1.2em 0">With this wizard similar to the indexing wizard you can define and save a setup for a stand-alone indexing script usable for cron jobs.</p>');
			break;

			//
			// setup summary
			//

			case 'index4':

				$step=4;

				$content.= $this->pObj->getPathInfoHeaderBar($this->pObj->pathInfo, FALSE, $this->cmdIcons);
				$content.= $this->pObj->doc->spacer(10);

				$header = $LANG->getLL('tx_damindex_index.setup_summary');

				$stepsBar = $this->getStepsBar($step,$lastStep, '' ,'', '', $LANG->getLL('tx_damcron.finish'));
				$content.= $this->pObj->doc->section($header,$stepsBar,0,1);

				$content.= '<strong>Set Options:</strong><table border="0" cellspacing="0" cellpadding="4" width="100%">'.$this->index->getIndexingOptionsInfo().'</table>';

				$content.= $this->pObj->doc->spacer(10);

				$rec=array_merge($this->index->dataPreset,$this->index->dataPostset);

// TODO This is quick'n'dirty. The function simply modifies the comma separated UIDs in the category key into a comma separated list of UID|CategoryTitle pairs which can then be displayed in the form
$rec = $this->modifyValuesForDisplay($rec);


				$fixedFields = array_keys($this->index->dataPostset);
				$content.= '<strong>Meta data preset:</strong><br /><table border="0" cellpadding="4" width="100%"><tr><td bgcolor="'.$this->pObj->doc->bgColor3dim.'">'.
								$this->showPresetData($rec, $fixedFields).
								'</td></tr></table>';

				$content.= $this->pObj->doc->spacer(10);

			break;


			case 'indexStart':

				$content.= $this->pObj->doc->section('Indexing default setup','',0,1);

				$filename = '.indexing.setup.xml';

				$path = tx_dam::path_makeAbsolute($this->pObj->path);

				if (is_file($path.$filename) AND is_readable($path.$filename)) {

					$content.= '<br /><strong>Overwrite existing default indexer setup to this folder:</strong><br />'.htmlspecialchars($this->pObj->path).'<br />';
					$content.= '<br /><input type="submit" name="indexSave" value="Overwrite" />';
				} else {
					$content.= '<br /><strong>Save default indexer setup for this folder:</strong><br />'.htmlspecialchars($this->pObj->path).'<br />';
					$content.= '<br /><input type="submit" name="indexSave" value="Save" />';
				}
				$content.= '<input type="hidden" name="setuptype" value="folder">';



				$content.= $this->pObj->doc->spacer(10);


				$content.= $this->pObj->doc->section('CRON','',0,1);

				$path = PATH_site.$this->uploadFolder;
				$filename = preg_replace('#[^a-zA-Z0-9]#','_',$this->pObj->path);
				$filename = preg_replace('#_$#','',$filename);
				$filename = preg_replace('#^_#','',$filename);
				$content.= '<br /><strong>Save setup as cron indexer setup:</strong><br />'.htmlspecialchars($path).'<br /><input type="text" size="25" maxlength="25" name="filename" value="'.htmlspecialchars($filename).'"> .xml';
				$content.= '<br /><input type="submit" name="indexSave" value="Save" />';

				$files = t3lib_div::getFilesInDir($path,'xml',0,1);

				$out = '';
				foreach ($files as $file) {
					$out.= htmlspecialchars($file).'<br />';
				}
				if($out) {
					$content.= '<br /><br /><strong>Existing cron setups:</strong><div style="border-top:1px solid grey;border-bottom:1px solid grey;">'.$out.'</div><br />';
				}

				$extraSetup = '';

				$this->index->setPath($this->pObj->path);
				$this->index->setRecursive($this->index->ruleConf['tx_damindex_rule_recursive']['enabled']);
				$this->index->setPID($this->pObj->defaultPid);
				$this->index->enableMetaCollect(TRUE);
				$this->index->setDryRun($this->index->ruleConf['tx_damindex_rule_dryRun']['enabled']);
				$this->index->enableReindexing($this->index->ruleConf['tx_damindex_rule_doReindexing']['enabled']);
				$setup = $this->index->serializeSetup($extraSetup, false);



				$content.= $this->pObj->doc->section('Set Options',t3lib_div::view_array($setup),0,1);


				$content.= '<br /><textarea style="width:100%" rows="15">'.htmlspecialchars(str_replace('{', "{\n",$this->index->serializeSetup($extraSetup))).'</textarea>';


			break;

			case 'indexSave':
				$content.= $this->pObj->getPathInfoHeaderBar($this->pObj->pathInfo, FALSE, $this->cmdIcons);
				$content.= $this->pObj->doc->spacer(10);
				$content.= '<div style="width:100%;text-align:right;">'.$this->pObj->btn_back().'</div>';

				if (t3lib_div::_GP('setuptype')=='folder') {
					$path = tx_dam::path_makeAbsolute($this->pObj->path);
					$filename = $path.'.indexing.setup.xml';
				} else {
					$path = PATH_site.$this->uploadFolder;
					$filename = $path.t3lib_div::_GP('filename').'.xml';
				}

				$this->index->setPath($this->pObj->path);
				$this->index->setRecursive($this->index->ruleConf['tx_damindex_rule_recursive']['enabled']);
				$this->index->setPID($this->pObj->defaultPid);
				$this->index->enableMetaCollect(TRUE);
				$this->index->setDryRun($this->index->ruleConf['tx_damindex_rule_dryRun']['enabled']);
				$this->index->enableReindexing($this->index->ruleConf['tx_damindex_rule_doReindexing']['enabled']);
				$setup = $this->index->serializeSetup($extraSetup);

				if ($handle = fopen($filename, 'wb')) {
					if (!fwrite($handle, $setup)) {
						 $content.= 'Can\'t write to file '.htmlspecialchars($filename);
					}
					fclose($handle);
					t3lib_div::fixPermissions($filename);
				} else {
					 $content.= 'Can\'t open file '.htmlspecialchars($filename);
				}
			break;

			case 'definfo':

				$content.= $this->pObj->getHeaderBar('', implode('&nbsp;',$this->cmdIcons));
				$content.= $this->pObj->doc->spacer(10);

				$files = t3lib_div::getFilesInDir(PATH_site.$this->uploadFolder,'xml',1,1);

				$out = '';
				foreach ($files as $file) {
					if($file==$filename) {
						$out.= '<strong>'.htmlspecialchars($file).'</strong><br />';
					} else {
						$out.= htmlspecialchars($file).'<br />';
					}
				}
				$filename = $filename ? $filename : $file;

				if($out) {
					$content.= '<br /><br /><strong>Existing setups:</strong><div style="border-top:1px solid grey;border-bottom:1px solid grey;">'.$out.'</div><br />';
				} else {
					$content.= '<br /><br /><strong>No setups available.</strong><br />';
				}

				if($out) {
					$cronscript = t3lib_extMgm::extPath('dam_cron').'cron/dam_indexer.php';
					$content.= '<br /><strong>Call indexer script example:</strong><br />';
					$content.= '<span style="font-family: monaco,courier,monospace;">/usr/bin/php '.htmlspecialchars($cronscript).' --setup='.htmlspecialchars($filename).'</span>';
				}
			break;

			case 'doIndexing':
			break;

			default:
				$content.= parent::moduleContent($header, $description, $lastStep);
		}
		return $content;
	}




// TODO -------------- quick fix - needs to be done right


	/**
	 * Modifies values posted during the indexing process from step 3 to step 4
	 * Used to display selected Categories in step 4
	 *
	 * @param	string		Path
	 * @return	string		Output
	 */
	function modifyValuesForDisplay ($rec) {
		$tmp = array();
		if ($rec['category'] AND !strpos($rec['category'],'|')) {
			$cats = implode(',',t3lib_div::trimExplode(',',$rec['category'],1));
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_dam_cat','uid IN ('.$cats.')');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$tmp[] = $row['uid'].'|'.$row['title'];
			}
			$rec['category'] = implode(',',$tmp);
		}
		return $rec;
	}

}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dam_cron/modfunc1/class.tx_damcron_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dam_cron/modfunc1/class.tx_damcron_modfunc1.php"]);
}

?>