<template>
	<section>
		<NcSettingsSection
		    :title="t('tuya_cloud', 'Tuya Cloud Integration')"
		    :description="t('tuya_cloud', 'The Tuya integration integrates all Powered by Tuya devices you have added to the Tuya Smart and Tuya Smart Life apps.')"
		    :limit-width="false">
			<NcNoteCard type="success" v-if="devicesCount > 0">
				<p>{{ t('tuya_cloud', 'You have {count} devices connected', {'count': devicesCount}) }}</p>
			</NcNoteCard>
		</NcSettingsSection>
		<NcSettingsSection
		    :title="t('tuya_cloud', 'Account settings')"
		    :limit-width="false"
		    class="tuya-settings"
		    doc-url="https://github.com/matiasdelellis/tuya_cloud">
			<NcTextField
			    :label="t('tuya_cloud', 'Username')"
			    :placeholder="t('tuya_cloud', 'Username/Email to access to SmartLife/Tuya app')"
			    :value.sync="username"
			    type="text"
			    :label-visible="true"
			    :maxlength="50"
			    @update:value="onChange"/>
			<NcTextField
			    :label="t('tuya_cloud', 'Password')"
			    :value.sync="password"
			    type="password"
			    :label-visible="true"
			    :maxlength="50"
			    @update:value="onChange"/>
			<NcTextField
			    :label="t('tuya_cloud', 'Account type')"
			    :placeholder="t('tuya_cloud', 'Account type (tuya or smart_life)')"
			    :value.sync="bizType"
			    type="text"
			    :label-visible="true"
			    :maxlength="10"
			    @update:value="onChange"/>
			<NcTextField
			    :label="t('tuya_cloud', 'Country Code')"
			    :placeholder="t('tuya_cloud', 'Country code (International dialing number), e.g. 33 for France or 1 for USA')"
			    :value.sync="countryCode"
			    type="number"
			    :label-visible="true"
			    :maxlength="3"
			    @update:value="onChange"/>
			<NcTextField
			    :label="t('tuya_cloud', 'Region')"
			    :placeholder="t('tuya_cloud', 'Region to connect the API (az=Americas, ay=Asia, eu=Europe, us=North America)')"
			    :value.sync="region"
			    type="text"
			    :label-visible="true"
			    :maxlength="2"
			    @update:value="onChange"/>
			<NcNoteCard type="error" v-if="errorMsg">
				<p>{{ errorMsg }}</p>
			</NcNoteCard>
			<NcButton type="primary"
			    class="connect-button"
			    :disabled="hasConnection"
			    @click="connect">
				{{ t('tuya_cloud', 'Connect') }}
			</NcButton>
		</NcSettingsSection>
	</section>
</template>

<script>
import '@nextcloud/dialogs/styles/toast.scss'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'

import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'

export default {
	name: 'Settings',

	components: {
		NcButton,
		NcNoteCard,
		NcSettingsSection,
		NcTextField,
	},

	data() {
		return {
			username: loadState('tuya_cloud', 'username'),
			password: loadState('tuya_cloud', 'password'),
			bizType: loadState('tuya_cloud', 'biz_type'),
			countryCode: loadState('tuya_cloud', 'country_code'),
			region: loadState('tuya_cloud', 'region'),
			hasConnection: loadState('tuya_cloud', 'has_connection'),
			devicesCount: loadState('tuya_cloud', 'devices_count'),
			errorMsg: loadState('tuya_cloud', 'error_msg')
		}
	},

	computed: {
	},

	async mounted() {
	},

	methods: {
		onChange() {
			this.devicesCount = 0
			this.hasConnection = false
			this.errorMsg = null
		},
		connect() {
			if (this.validateSettings())
				this.saveSettings()
		},
		validateSettings() {
			if (this.username.length === 0)
				this.errorMsg = t('tuya_cloud', 'The username cannot be empty')
			else if (this.password.length === 0)
				this.errorMsg = t('tuya_cloud', 'The password cannot be empty')
			else if (this.bizType !== 'tuya' && this.bizType !== 'smart_life')
				this.errorMsg = t('tuya_cloud', 'The account type of must be tuya or smart_life')
			else if (this.countryCode.length === 0)
				this.errorMsg = t('tuya_cloud', 'The country code must be a number')
			else if (!['az', 'ay', 'eu', 'us'].includes(this.region))
				this.errorMsg = t('tuya_cloud', 'The region to connect the API must be az, ay, eu or us')
			else
				this.errorMsg = null

			return this.errorMsg === null
		},
		saveSettings() {
			axios.put(
				generateUrl('/apps/tuya_cloud/settings'),
				{
					values: {
						username: this.username,
						password: this.password,
						biz_type: this.bizType,
						country_code: this.countryCode,
						region: this.region
					}
				}
			)
			.then((response) => {
				this.devicesCount = response.devices_count
				this.hasConnection = response.has_connection
				this.errorMsg = response.error_msg

				showSuccess(t('tuya_cloud', 'Tuya Cloud Account saved'))
			})
			.catch((error) => {
				console.error(error)
				showError(
					t('tuya_cloud', 'Failed to save Tuya Cloud Account')
					+ ': ' + error.response.request.responseText
				)
			})
		},
	},
}
</script>
<style scoped>
.tuya-settings {
	.connect-button {
		margin-top: 1rem;
	}
	.input-field {
		max-width: 350px;
	}
}
</style>
