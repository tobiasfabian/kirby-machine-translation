<?php

use Kirby\Cms\Page;
use Kirby\Content\Field;

/**
 * Translate the page into the target language.
 *
 * @param string $targetLang The target language code to which the Page will be translated.
 * @param string|null $sourceLang The source language code of the Page content. If not provided, the default language will be used.
 * @param bool $force Set to true to overwrite all fields. When set to false only empty fields of target language content will be translated and saved.
 *
 * @return Page The translated Page containing the translated content.
 */
return function (string $targetLang, ?string $sourceLang = null, bool $force = false): Page {
	/** @var Page $this */

	// All fields of the page’s blueprint (with lowercase keys)
	$blueprintFields = array_change_key_case($this->blueprint()->fields(), CASE_LOWER);

	$translatedFields = array_map(function (Field $field) use ($blueprintFields, $targetLang, $force) {
		// Get blueprint field of the content field
		$blueprintField = $blueprintFields[$field->key()] ?? null;

		if ($force === true) {
			/** @var ?Field $translatedField */
			$translatedField = $field->translate($targetLang, $blueprintField);
		} else {
			$key = $field->key();
			$targetLangField = $this->content($targetLang)->$key();
			if ($targetLangField->isEmpty() || $targetLangField->value === $field->value) {
				$translatedField = $field->translate($targetLang, $blueprintField);
			}
		}

		return $translatedField->value ?? null;
	}, $this->content($sourceLang)->fields());

	// filter out empty fields
	$translatedFields = array_filter($translatedFields, fn ($item) => $item !== null);

	// mark page with machineTranslated
	$page = $this->update(array_merge($translatedFields, [
		'machineTranslated' => [
			'date' => date('c'),
			'showInfo' => true,
		],
	]), $targetLang);

	return $page;
};
