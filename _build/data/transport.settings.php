<?php

$settings = array();

$tmp = array(
	'print_template' => array(
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'modhelpers_main',
	),
    'bot_user_agents' => array(
		'xtype' => 'textfield',
		'value' => 'bot,spider,slurp,ia_archiver,siteexplorer,MegaIndex',
		'area' => 'modhelpers_main',
	),
    'token_ttl' => array(
		'xtype' => 'numberfield',
		'value' => 0,
		'area' => 'modhelpers_main',
	),
    'chunks_path' => array(
		'xtype' => 'textfield',
		'value' => '{core_path}elements/chunks',
		'area' => 'modhelpers_main',
	),
    'snippets_path' => array(
		'xtype' => 'textfield',
		'value' => '{core_path}elements/snippets',
		'area' => 'modhelpers_main',
	),
);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'modhelpers_' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
