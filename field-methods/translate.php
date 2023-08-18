<?php

use Kirby\Content\Field;
use Kirby\Toolkit\Str;
use Tobiaswolf\MachineTranslation\Translate;

/**
 * Translates the given field to the target language based on the field type.
 *
 * @param Field $field The field to be translated.
 * @param string $targetLang The target language code (e.g., 'en', 'fr', 'es').
 * @param array|null $blueprintField (Optional) The blueprint settings for the field, if not provided, it will be fetched from the parent model. If provided, it must contain 'type' and can have 'translate'.
 *
 * @return Field  Returns the translated field as a Field object, or the original field if translation is not allowed or not possible.
 */
return function (Field $field, string $targetLang, ?array $blueprintField = null): Field
{
	$key = $field->key();

	// try to get the blueprint field from parent model (page/site)
	if ($blueprintField === null) {
		// All fields of the page’s blueprint (with lowercase keys)
		$blueprintFields = array_change_key_case($field->model()->blueprint()->fields(), CASE_LOWER);
		$defaulBlueprintField = [
			'type' => 'text',
			'translate' => true,
		];
		$blueprintField = $blueprintFields[$field->key()] ?? $defaulBlueprintField;
	}

	if (($blueprintField['translate'] ?? true) === false || $key === 'uuid' || $field->isEmpty()) {
		return $field;
	}

	switch ($blueprintField['type']) {
		case 'tags':
		case 'text':
		case 'textarea':
		case 'list':
		case 'writer':
			$field = Translate::translateTextField($field, $targetLang);
			break;
		case 'blocks':
			$field = Translate::translateBlocksField($field, $targetLang, $blueprintField);
			break;
		case 'layout':
			$field = Translate::translateLayoutField($field, $targetLang, $blueprintField);
			break;
		case 'structure':
			$field = Translate::translateStructureField($field, $targetLang, $blueprintField);
			break;
		case 'object':
			$field = Translate::translateObjectField($field, $targetLang, $blueprintField);
			break;
	}

	if ($field->value !== null && is_string($field->value)) {
		$field->value = Str::replace($field->value, '&amp;', '&');
	}

	return $field;
};
