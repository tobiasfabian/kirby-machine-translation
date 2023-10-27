# Machine Translation

This Kirby plugin allows you to automatically translate pages using the DeepL API. All field types are supported.

**If you are using machine translation, you should inform your users of this fact.**\
For example, you could display a message like this (Text available via [`t('tobiaswolf.machine-translation.info')`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/translations/en.php#L17))

```
This page has been machine translated. Despite the high quality of machine translation, the translation may contain errors.
```

## Installation

### Download

Download and copy this repository to `/site/plugins/machine-translation`.

### Git submodule

```
git submodule add https://github.com/tobiasfabian/machine-translation.git site/plugins/machine-translation
```

### Composer

```
composer require tobiasfabian/machine-translation
```

## Requirements

- Kirby 4.0+
- Authentication Key for DeepL API ([DeepL API for developers](https://www.deepl.com/de/pro#developer))

## Setup

To use this plugin you have to set your personal *Authentication Key for DeepL API*.
[API Access / Authentication – DeepL Documentation](https://www.deepl.com/docs-api/api-access/authentication/)

```php
// config/config.php
return [
  'tobiaswolf.machine-translation.deepl.authKey' => '279a2e9d-83b3-c416-7e2d-f721593e42a0:fx',
];
```

### DeepL Options

You can set several options, provided by DeepL. Read more about the options in the [API Documentation](https://www.deepl.com/docs-api/translate-text/translate-text/) of DeepL. Each option has to be prefixed. `tobiaswolf.machine-translation.deepl.{Name}`

| Name                      | Type      | Default       | Description
|---------------------------|-----------|---------------|--------
| `authKey` **(required)**  | `string`  | `null`        | You can find your Auth key on your account page. [DeepL Documentation](https://www.deepl.com/docs-api/api-access/authentication/)
| `split_sentences`         | `string`  | `nonewlines`  | Possible values are: `0` no splitting at all, whole input is treated as one sentence. `1` splits on punctuation and on newlines. `nonewlines` splits on punctuation only, ignoring newlines
| `preserve_formatting`     | `bool`    | `false`       | Sets whether the translation engine should respect the original formatting, even if it would usually correct some aspects.
| `formality`               | `string`  | `default`     | You can use one of these options: `default` (default), `more` for a more formal language, `less` for a more informal language, `prefer_more` for a more formal language if available, otherwise fallback to default formality, `prefer_less` for a more informal language if available, otherwise fallback to default formality.
| `glossary_id`             | `string`  | `null`        | Specify the glossary to use for the translation. The language pair of the glossary has to match the language pair of the request.
| `tag_handling`            | `string`  | `html`        | Sets which kind of tags should be handled. Options currently available: `xml`, `html`
| `outline_detection`       | `bool`    | `true`        | [API Documentation](https://www.deepl.com/docs-api/translate-text/translate-text/)
| `non_splitting_tags`      | `array`   | `null`        | List of XML or HTML tags.
| `splitting_tags`          | `array`   | `null`        | List of XML or HTML tags.
| `ignore_tags`             | `array`   | `null`        | List of XML or HTML tags.


## Usage

### Blueprint section

Add the section [`machine-translate`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/sections/machineTranslate.php) to your blueprint to get the interface to translate the page.

```yaml
sections:
  machineTranslate:
    type: machine-translate
```

<img width="542" alt="Screenshot of Kirby Panel with Button “Translate page”" src="https://github.com/tobiasfabian/machine-translation/assets/1524319/f85d94a1-0cb7-4b8c-9ed1-9a4a0a93f98c">

After the page is translated an object field [`machineTranslated`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/blueprints/fields/machineTranslated.yml) with `date` and `showInfo` is saved to the translated page content. This can be used to detect machine translated pages and display a notice/warning on the frontend that the text is machine translated. You can add this object field to any fields section (optional).

```yaml
sections:
  fields:
    type: fields
    fields:
      machineTranslated:
        extends: fields/machineTranslated
```

### API endpoint

This plugin provides an API endpoint [`/api/machine-translate/pages/(:any)`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/api/routes/machine-translate.php#L10-L41) that can be used to translate an entire page. Read [Kirby’s API Guide](https://getkirby.com/docs/guide/api/introduction) to learn more about using the API.

The endpoint allows `get` and `post` requests. The endpoint requires a `language` (target language) query. When making a `post` request, `sourceLang` and `forceOverwrite` can be added. By default `sourceLang` is the default language. If `forceOverwrite` is false or not specified, only fields where the target field does not exist or is empty will be translated.

```JavaScript
const pageId = 'test';
const targetLang = 'es';
const csrf = '…';
fetch(`/api/machine-translate/pages/{ pageId }?language={ targetLang }`, {
	method: 'post',
	body: JSON.stringify({
		sourceLang: 'en',
		forceOverwrite: true,
	}),
	headers: {
		'x-csrf': csrf,
	},
});
```

```php
$pageId = 'test';

kirby()->api()->call('machine-translate/pages/' . $pageId, 'POST', [
	'query' => [
		'language' => 'en',
	],
	'body' => [
		'sourceLang' => 'de',
		'forceOverwrite' => true,
	],
]);
```

API endpoint to translate the site content. [`/api/machine-translate/site`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/api/routes/machine-translate.php#L42-L73)

### Field method

The field method [`$field->translate($targetLang, $blueprintField)`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/field-methods/translate.php) translates the field value and returns the field with the translated value. All field types are supported. The type of field is specified via `$blueprintField['type']`.

If you want to save the translated field, you can do this like this.

```php
$targetLang = 'de'
$text = $page->text(); // e.g. Hello World
$translatedText = $text->translate($targetLang); // returns the field with the translated text (Hallo Welt)
$page->update([
  'text' => $translatedText,
], $targetLang);
```

### Page method

The page method [`$page->machineTranslate($targetLang, $sourceLang, $force)`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/page-methods/machineTranslate.php) allows you to translate the content of a page into a target language. By default already translated fields will not be overwritten. By setting `$force` to `true` all fields will be translated, existing fields will be overwritten.

An object field `machineTranslated` with `date` and `showInfo` is added to the translated page content. This can be used to detect machine translated pages and display a notice/warning on the frontend that the text is machine translated.

To translate the site content, use [`$site->machineTranslate($targetLang, $sourceLang, $force)`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/site-methods/machineTranslate.php).

### Translate Class

If you want to, you can use the static method of the [`Translate`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/lib/Translate.php) class. Use the [`translate($text, $targetLang, $sourceLang)`](https://github.com/tobiasfabian/kirby-machine-translation/blob/main/lib/Translate.php#L201) method to translate text. Make sure you pass an array as the first parameter (you can translate multiple texts at once). You can omit the third parameter to have the source language automatically detected.

```php
use Tobiaswolf\MachineTranslation\Translate;

$sourceTexts = ['Hello World', 'Greetings Earthlings'];
$translatedTexts = Translate::translate($sourceTexts, 'es');

var_dump($translatedTexts);
// array(2) { [0]=> array(2) { ["detected_source_language"]=> string(2) "EN" ["text"]=> string(10) "Hola Mundo" } [1]=> array(2) { ["detected_source_language"]=> string(2) "EN" ["text"]=> string(19) "Saludos terrícolas" } }
```

### Cache

Each request to DeepL is cached. This has the advantage that if the same text appears more than once on the website, a new request is not always made. This saves you *Character usage* of your DeepL plan.

You can disable the cache via config.

```php
// config/config.php
return [
	'cache.tobiaswolf.machine-translation.translate' => false, // default true
]
```

## License

MIT

## Credits

- [Tobias Wolf](https://github.com/tobiasfabian)
