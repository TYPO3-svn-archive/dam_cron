<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_cron"
#
# Auto generated 21-08-2006 01:03
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Media>Tools>Cron Job',
	'description' => 'Cron job script and setup module for cron job configuration.',
	'category' => 'module',
	'shy' => 0,
	'version' => '1.0.2',
	'dependencies' => 'dam_index',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'cron',
	'state' => 'stable',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Rene Fritz',
	'author_email' => 'r.fritz@colorcube.de',
	'author_company' => 'Colorcube - digital media lab, www.colorcube.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'dam_index' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:9:"ChangeLog";s:4:"c791";s:21:"ext_conf_template.txt";s:4:"e1da";s:12:"ext_icon.gif";s:4:"d28d";s:17:"ext_localconf.php";s:4:"864d";s:14:"ext_tables.php";s:4:"f59e";s:16:"locallang_db.xml";s:4:"027e";s:13:"cron/conf.php";s:4:"8170";s:20:"cron/dam_indexer.php";s:4:"0741";s:14:"doc/manual.sxw";s:4:"e13b";s:38:"modfunc1/class.tx_damcron_modfunc1.php";s:4:"e3ab";s:22:"modfunc1/locallang.xml";s:4:"6544";}',
	'suggests' => array(
	),
);

?>