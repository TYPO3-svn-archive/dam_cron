#!/usr/bin/php -q
<?php

     // Defining circumstances for CLI mode:
define('TYPO3_cliMode', TRUE);

    // Defining PATH_thisScript here: Must be the ABSOLUTE path of this script (realpath make the trick)
define('PATH_thisScript', realpath($_SERVER['argv'][0]));

     // change to the script dir
chdir(dirname(PATH_thisScript));


unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');


if(!$TYPO3_CONF_VARS['EXTCONF']['dam_cron']['setup']['enable']) {
	die(PATH_thisScript.' disabled in Extension Manager'."\n");
}



require_once(PATH_typo3.'sysext/lang/lang.php');
$LANG = t3lib_div::makeInstance('language');
$LANG->init($BE_USER->uc['lang']);




require_once(PATH_txdam.'lib/class.tx_dam_scbase.php');
require_once(PATH_txdam.'lib/class.tx_dam_indexing.php');


class dam_cron_indexer extends tx_dam_SCbase {

  		// the command line options
	var $argv = array();

  		// the indexing setup file
	var $setupFile = '';

		// this overrides files and folder from setup
	var $filesAndFolder = array();

		// if indexing should be a dry run
	var $dryRun = false;



	function init() {
		global $_SERVER;

		$thisScript = $_SERVER['argv'][0];

		if($_SERVER['argc'] < 2) {
			die('Usage: '.basename($thisScript).' --setup=<filename> [--dry-run] [--exec="<command FILE>"] [<files and folders overriding setup>]'."\n");
		}

		$this->_loadArgv();
		$this->_processArgs();


		if($this->setupFile=='') {
			die ('No setup file defined. (--setup=<filename>)'."\n");
		}
		if(!file_exists($this->setupFile)) {
			die ('Setup file not found: '.$this->setupFile."\n");
		}
		if(!is_readable($this->setupFile)) {
			die ('Setup file not readable: '.$this->setupFile."\n");
		}

		if($this->fileProcessingScript) {
			echo 'file processing command: '.$this->fileProcessingScript."\n";
		}

		parent::init();
	}

	function main() {

		//
		// Init indexing object
		//

		$this->index = t3lib_div::makeInstance('tx_dam_indexing');
		$this->index->init();
		$this->index->setRunType('cron');

		if (!$this->index->restoreSerializedSetup(t3lib_div::getUrl($this->setupFile))) {
			die ('Setup file is not a valid indexing setup: '.$this->setupFile."\n");
		}

		$filePreprocessingCallback = NULL;
		if ($this->fileProcessingScript) {
			$filePreprocessingCallback = array(&$this, 'filePreprocessingCallback');
		}

		$this->index->setDryRun($this->dryRun);

			// overrride the paths
		if(count($this->filesAndFolder)) {
			$this->index->setPathsList($this->filesAndFolder);
		}

		$this->index->indexUsingCurrentSetup(array(&$this, 'doIndexingCallback'), NULL, $filePreprocessingCallback);
		if($this->index->stat['totalCount']) {
			echo (string)$this->index->stat['totalCount'].' files indexed in '.max(1,ceil($this->index->stat['totalTime']/1000)).' sec.'."\n";
		} else {
			echo 'No files indexed.'."\n";
		}
	}


	function doIndexingCallback($type, $meta, $absFile, $fileArrKey, &$pObj) {
		if(is_array($meta)) {
			echo 'indexed: '.$meta['fields']['file_path'].$meta['fields']['file_name']."\n";
		}
	}


	function filePreprocessingCallback($type, $absFile, &$indexObj) {
		#$this->fileProcessingScript = '/usr/bin/convert -resize 800 -crop 800x600+0+0 FILE FILE';

		if($this->fileProcessingScript) {
			if (is_file($absFile)) {
				if($indexObj->isDryRun()) {
					echo 'not executed (dry run): '.str_replace('FILE', "'".$absFile."'", $this->fileProcessingScript."\n");
				} else {
					echo 'file processing: '.str_replace('FILE', "'".$absFile."'", $this->fileProcessingScript."\n");
					shell_exec(str_replace('FILE', "'".$absFile."'", $this->fileProcessingScript));
				}
			} else {
				echo 'file not found for preprocessing: '.str_replace('FILE', "'".$absFile."'", $this->fileProcessingScript."\n");
			}
		}
	}


	/***************************************
	 *
	 *	 arg processing
	 *
	 ***************************************/


	function _loadArgv() {
		global $_SERVER;

		$phpArgv = $_SERVER['argv'];
		array_shift($phpArgv);
		$phpArgc = $_SERVER['argc']-1;

		$IsValue = false;
		for ($i = 0; $i < $phpArgc; $i++) {
			if($phpArgv[$i]{0} != '-') {
				// unnamed parameter in here like
				// phing help
				// array(
				//        'help' => true
				// );
				if ($IsValue) {
					$key = substr($phpArgv[$i-1], 1);
					$this->argv[$key] = $phpArgc[$i];
					$IsValue = false;
				} else {
					$this->argv[$phpArgv[$i]] = true;
				}
			}
			else {
				if($phpArgv[$i]{1} != '-') {
					// named parameter in here
					// phing -name value
					// the next value in $argv array should be value
					// this one is name of the key
					$IsValue = true;
				}
				else {
					// named parameter in here
					// phing --name=value
					$tmp = explode('=', $phpArgv[$i]);
					$key = substr($tmp['0'], 2);
					$this->argv[$key] = $tmp['1'];
				}
			}
		}
		return(true);
	}

	function _processArgs() {
		foreach($this->argv as $opt => $value) {
			switch($opt) {
				case 'setup':
					$this->setupFile = $value;
				break;
				case 'exec':
					$this->fileProcessingScript = $value;
				break;
				case 'dry-run':
					$this->dryRun = true;
				break;
				default:
				print_r($opt);
					$this->filesAndFolder[] = $opt;
				break;
			}
		}
	}
}



$SOBE = t3lib_div::makeInstance('dam_cron_indexer');
$SOBE->init();
$SOBE->main();

?>