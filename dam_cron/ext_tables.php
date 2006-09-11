<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');




if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'txdamM1_tools',
		'tx_damcron_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_damcron_modfunc1.php',
		'LLL:EXT:dam_cron/locallang_db.xml:moduleFunction.tx_damcron_modfunc1'
	);


}
?>