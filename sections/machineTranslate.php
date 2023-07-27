<?php

use Kirby\Cms\Section;
use Kirby\Toolkit\Str;

return [
	'props' => [
		'label' => function ($label = null) {
			return empty($label) ? t('tobiaswolf.machine-translation.section.label') : $label;
		},
		'dateTranslated' => function () {
			$language = (string)$this->model()->kirby()->language()->code();
			$machineTranslatedObject = $this->model()->content($language)->machineTranslated()->toObject();
			if ($machineTranslatedObject->date()->isNotEmpty()) {
				return Str::date($machineTranslatedObject->date()->toTimestamp(), 'dd.LL.y, HH:mm', 'intl');
			}
			return null;
		},
		'dateModified' => function () {
			$language = (string)$this->model()->kirby()->language()->code();
			return $this->model()->modified('dd.LL.y, HH:mm', 'intl', $language);
		},
		'dateDefaultLanguage' => function () {
			$defaultLanguage = (string)$this->model()->kirby()->defaultLanguage()->code();
			return $this->model()->modified('dd.LL.y, HH:mm', 'intl', $defaultLanguage);
		},
		'isOutdated' => function () {
			$defaultLanguage = (string)$this->model()->kirby()->defaultLanguage()->code();
			$language = (string)$this->model()->kirby()->language()->code();

			$dateDefaultLanguage = (int)$this->model()->modified('U', null, $defaultLanguage);
			$dateTranslatedLanguage = (int)$this->model()->modified('U', null, $language);
			return $dateDefaultLanguage > $dateTranslatedLanguage;
		},
	]
];
