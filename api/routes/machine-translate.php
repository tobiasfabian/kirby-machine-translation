<?php

use Kirby\Api\Api;
use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\Exception;

return [
	[
		/**
		 * Translates the entire page.
		 * Returns the translated page.
		 */
		'pattern' => 'machine-translate/pages/(:any)',
		'auth' => false,
		'method' => 'POST|GET',
		'action'	=> function (string $pageId): Page
		{
			/** @var Api $this */

			$page = Find::page($pageId);
			$targetLang = $this->requestQuery('language');
			$sourceLang = $this->requestBody('sourceLang', $this->kirby()->defaultLanguage()->code());
			$forceOverwrite = (bool)$this->requestBody('forceOverwrite', false);

			if (!is_string($targetLang)) {
				throw new Exception('Missing “targetLang” in post request body.');
			}

			if (is_string($sourceLang)) {
				$this->kirby()->setCurrentLanguage($sourceLang);
			}

			$page = $page->machineTranslate($targetLang, $sourceLang, $forceOverwrite);

			$this->kirby()->setCurrentLanguage($targetLang);

			return $page;
		}
	],
	[
		/**
		 * Translates the site content.
		 * Returns the translated site.
		 */
		'pattern' => 'machine-translate/site',
		'auth' => false,
		'method' => 'POST|GET',
		'action'	=> function (): Site
		{
			/** @var Api $this */

			$site = $this->site();
			$targetLang = $this->requestQuery('language');
			$sourceLang = $this->requestBody('sourceLang', $this->kirby()->defaultLanguage()->code());
			$forceOverwrite = (bool)$this->requestBody('forceOverwrite', false);

			if (!is_string($targetLang)) {
				throw new Exception('Missing “targetLang” in post request body.');
			}

			if (is_string($sourceLang)) {
				$this->kirby()->setCurrentLanguage($sourceLang);
			}

			$site = $site->machineTranslate($targetLang, $sourceLang, $forceOverwrite);

			$this->kirby()->setCurrentLanguage($targetLang);

			return $site;
		}
	],
];
