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
	'title' => 'Media CLI Indexer',
	'description' => 'Provides a cli script for indexing files for DAM which can be used by cron jobs.',
	'category' => 'be',
	'shy' => 0,
	'version' => '1.0.101',
	'dependencies' => 'dam',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'cron',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'The DAM development team',
	'author_email' => 'typo3-project-dam@lists.netfielders.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'dam' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(
	),
);

?>