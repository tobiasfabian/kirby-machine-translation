<template>
	<section class="k-section k-machine-translate-section">
		<header class="k-section-header">
			<h2 class="k-label k-section-label">
				<span class="k-label-text">{{ label }}</span>
			</h2>
		</header>

		<k-info-field
			v-if="isDefaultLanguage"
			:text="$t('tobiaswolf.machine-translation.section.info.defaultLanguage', { defaultLanguage })"
		/>

		<k-button
			v-if="!isDefaultLanguage"
			variant="filled"
			icon="globe"
			@click="translate"
		>
			{{ $t('tobiaswolf.machine-translation.section.translate') }}
		</k-button>

		<k-input
			v-if="!isDefaultLanguage"
			v-model="overwrite"
			type="toggle"
			:text="$t('tobiaswolf.machine-translation.section.toggle.overwrite')"
			name="overwrite"
		/>

		<k-text
			v-if="!isDefaultLanguage"
			class="k-field-help"
			:html="helpHtml"
		/>
	</section>
</template>

<script>
export default {
	data() {
		return {
			label: "",
			dateTranslated: "…",
			dateDefaultLanguage: "…",
		}
	},
	computed: {
		defaultLanguage() {
			console.log(this.$panel.languages);
			return this.$panel.languages.find((language) => language.default === true).code.toUpperCase();
		},
		isDefaultLanguage() {
			console.log(this.$panel.language);
			return this.$panel.language.default;
		},
		helpHtml() {
			const {
				dateTranslated,
				dateDefaultLanguage,
				dateModified = null,
				defaultLanguage,
				isOutdated,
				$t,
			} = this;
			let html = '';
			if (dateTranslated) {
				if (dateTranslated === dateModified) {
					html += $t('tobiaswolf.machine-translation.section.info.translation.short', { dateTranslated, dateModified }) + '<br>';
				} else {
					html += $t('tobiaswolf.machine-translation.section.info.translation', { dateTranslated, dateModified }) + '<br>';
				}
			}
			if (!dateTranslated) {
				html += $t('tobiaswolf.machine-translation.section.info.translationNotTranslated') + '<br>';
			}
			html += $t('tobiaswolf.machine-translation.section.info.translationDefaultLanguage', { dateDefaultLanguage, defaultLanguage }) + '<br>';
			if (isOutdated) {
				html += `<strong>${ $t('tobiaswolf.machine-translation.section.info.outdated') }</strong><br>`;
			}
			return html;
		}
	},
	created() {
		this.load().then(response => {
			this.label = response.label;
			this.updateProps();
		});
	},
	methods: {
		updateProps() {
			this.load().then(response => {
				this.dateTranslated = response.dateTranslated;
				this.dateModified = response.dateModified;
				this.dateDefaultLanguage = response.dateDefaultLanguage;
				this.isOutdated = response.isOutdated;
			});
		},
		async translate() {
			const apiUrl = this.$panel.urls.api;
			const link = this.$panel.view.props.link;
			const language = this.$panel.language.code;
			const path = `machine-translate${link}?language=${language}`;
			const body = {
				forceOverwrite: this.overwrite,
			};
			const data = await this.$api.request(`machine-translate${link}?language=${language}`, {
				method: 'post',
				headers: {
					'x-csrf': this.$panel.system.csrf,
					'x-language': this.$panel.language.code,
				},
				body: JSON.stringify(body),
			}).catch((error) => {
				console.error(error);
				this.$panel.notification.error(error);
			});
			if (data) {
				this.$reload();
				this.updateProps();
			}
		}
	}
};
</script>

<style>
.k-machine-translate-section > .k-section-header {
	margin-bottom: var(--spacing-2);
}
.k-machine-translate-section > .k-input {
	max-width: 20rem;
}
.k-machine-translate-section > .k-button {
	margin-bottom: var(--spacing-2);
}
.k-machine-translate-section > .k-field-help {
	margin-top: var(--spacing-2);
}
</style>
