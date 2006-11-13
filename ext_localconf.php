<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$TYPO3_CONF_VARS['EXTCONF']['dam_cron']['setup'] = unserialize($_EXTCONF);


?>