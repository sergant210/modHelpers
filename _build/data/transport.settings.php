<?php

$settings = array();

$tmp = array(
	'print_template' => array(
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'modhelpers_main',
	),

);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'modhelpers.' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
