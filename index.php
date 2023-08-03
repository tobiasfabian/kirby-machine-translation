<?php

use Kirby\Cms\App;
use Kirby\Filesystem\F;

F::loadClasses([
	'Tobiaswolf\\MachineTranslation\\Translate' => 'lib/Translate.php'
], __DIR__);

App::plugin('tobiaswolf/machine-translation', [
	'api' => [
		'routes' => array_merge(
			include __DIR__ . '/api/routes/machine-translate.php',
		),
	],
	'blueprints' => [
			'fields/machineTranslated' => __DIR__ . '/blueprints/fields/machineTranslated.yml'
	],
	'fieldMethods' => [
		'translate' => require_once __DIR__ . '/field-methods/translate.php',
	],
	'options' => [
		'deepl.authKey' => null,
		'deepl.split_sentences' => 'nonewlines',
		'deepl.preserve_formatting' => false,
		'deepl.formality' => 'default',
		'deepl.glossary_id' => null,
		'deepl.tag_handling' => 'html',
		'deepl.outline_detection' => true,
		'deepl.non_splitting_tags' => null,
		'deepl.splitting_tags' => null,
		'deepl.ignore_tags' => null,
		'cache.translate' => true,
	],
	'pageMethods' => [
		'machineTranslate' => require_once __DIR__ . '/page-methods/machineTranslate.php',
	],
	'siteMethods' => [
		'machineTranslate' => require_once __DIR__ . '/site-methods/machineTranslate.php',
	],
	'sections' => [
		'machine-translate' => require_once __DIR__ . '/sections/machineTranslate.php',
	],
	'translations' => [
		'de' => require_once __DIR__ . '/translations/de.php',
		'en' => require_once __DIR__ . '/translations/en.php',
	]
]);
