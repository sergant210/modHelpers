<?php

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

header('Content-Type:text/html;charset=utf-8');

require_once 'build.config.php';
// Refresh model

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources = array(
	'root' => $root,
	'build' => $root . '_build/',
	'data' => $root . '_build/data/',
	'resolvers' => $root . '_build/resolvers/',
	'chunks' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/',
	'snippets' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/',
	'plugins' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/',
	'lexicon' => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
	'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
	'pages' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/pages/',
//	'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
	'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
);
unset($root);

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->getService('error', 'error.modError');
$modx->loadClass('transport.modPackageBuilder', '', false, true);
if (!XPDO_CLI_MODE) {
	echo '<pre>';
}

$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, PKG_NAMESPACE_PATH);

$modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

/* load system settings */
if (defined('BUILD_SETTING_UPDATE')) {
	$settings = include $sources['data'] . 'transport.settings.php';
	if (!is_array($settings)) {
		$modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
	}
	else {
		$attributes = array(
			xPDOTransport::UNIQUE_KEY => 'key',
			xPDOTransport::PRESERVE_KEYS => true,
			xPDOTransport::UPDATE_OBJECT => BUILD_SETTING_UPDATE,
		);
		foreach ($settings as $setting) {
			$vehicle = $builder->createVehicle($setting, $attributes);
			$builder->putVehicle($vehicle);
		}
		$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
	}
	unset($settings, $setting, $attributes);
}

/* create category */
$modx->log(xPDO::LOG_LEVEL_INFO, 'Created category.');
/* @var modCategory $category */
$category = $modx->newObject('modCategory');
$category->set('category', PKG_NAME);
/* create category vehicle */
$attr = array(
	xPDOTransport::UNIQUE_KEY => 'category',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
	xPDOTransport::RELATED_OBJECTS => true,
);

/* add plugins */
if (defined('BUILD_PLUGIN_UPDATE')) {
	$attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = array(
		xPDOTransport::PRESERVE_KEYS => false,
		xPDOTransport::UPDATE_OBJECT => BUILD_PLUGIN_UPDATE,
		xPDOTransport::UNIQUE_KEY => 'name',
	);
	$attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['PluginEvents'] = array(
		xPDOTransport::PRESERVE_KEYS => true,
		xPDOTransport::UPDATE_OBJECT => BUILD_PLUGIN_UPDATE,
		xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
	);
	$plugins = include $sources['data'] . 'transport.plugins.php';
	if (!is_array($plugins)) {
		$modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in plugins.');
	}
	else {
		$category->addMany($plugins);
		$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($plugins) . ' plugins.');
	}
}

$vehicle = $builder->createVehicle($category, $attr);

/* now pack in resolvers */
$vehicle->resolve('file', array(
	'source' => $sources['source_core'],
	'target' => "return MODX_CORE_PATH . 'components/';",
));

foreach ($BUILD_RESOLVERS as $resolver) {
	if ($vehicle->resolve('php', array('source' => $sources['resolvers'] . 'resolve.' . $resolver . '.php'))) {
		$modx->log(modX::LOG_LEVEL_INFO, 'Added resolver "' . $resolver . '" to category.');
	}
	else {
		$modx->log(modX::LOG_LEVEL_INFO, 'Could not add resolver "' . $resolver . '" to category.');
	}
}

flush();
$builder->putVehicle($vehicle);

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
	'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
	'license' => file_get_contents($sources['docs'] . 'license.txt'),
	'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
	'setup-options' => array(
		'source' => $sources['build'] . 'setup.options.php',
	),
	/*'requires' => array(
        'extra' => '>=3.2.0',
    )*/
));
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$signature = $builder->getSignature();
if (defined('PKG_AUTO_INSTALL') && PKG_AUTO_INSTALL) {
	$sig = explode('-', $signature);
	$versionSignature = explode('.', $sig[1]);

	/* @var modTransportPackage $package */
	if (!$package = $modx->getObject('transport.modTransportPackage', array('signature' => $signature))) {
		$package = $modx->newObject('transport.modTransportPackage');
		$package->set('signature', $signature);
		$package->fromArray(array(
			'created' => date('Y-m-d h:i:s'),
			'updated' => null,
			'state' => 1,
			'workspace' => 1,
			'provider' => 0,
			'source' => $signature . '.transport.zip',
			'package_name' => $sig[0],
			'version_major' => $versionSignature[0],
			'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
			'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
		));
		if (!empty($sig[2])) {
			$r = preg_split('/([0-9]+)/', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
			if (is_array($r) && !empty($r)) {
				$package->set('release', $r[0]);
				$package->set('release_index', (isset($r[1]) ? $r[1] : '0'));
			}
			else {
				$package->set('release', $sig[2]);
			}
		}
		$package->save();
	}

	if ($package->install()) {
		$modx->runProcessor('system/clearcache');
	}
}
if (!empty($_GET['download'])) {
	echo '<script>document.location.href = "/core/packages/' . $signature . '.transport.zip' . '";</script>';
}

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Execution time: {$totalTime}\n");
if (!XPDO_CLI_MODE) {
	echo '</pre>';
}
