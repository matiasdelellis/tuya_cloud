<template>
<div>
	<NcDashboardWidget :items="devices"
	    empty-content-icon="icon-home"
	    :empty-content-message="t('tuya_cloud', 'No devices yet')"
	    :loading="loading">
		<template #default="{ item }">
			<div :key="item.id"
			    @click="onDeviceClick(item)">
				<NcDashboardWidgetItem
				    :main-text="item.name"
				    :sub-text="getFriendlyDeviceState(item)">
					<template #avatar>
						<template v-if="item.icon">
							<NcAvatar :size="44" :url="getDeviceIcon(item)"/>
						</template>
					</template>
				</NcDashboardWidgetItem>
			</div>
		</template>
	</NcDashboardWidget>
	<NcModal
	    :show.sync="showDeviceModal"
	    @close="showDeviceModal=false"
	    name="disconected_modal"
	    :outTransition="true">
		<div class="modal__content">
			<div v-if="device && !device.data.online">
				<h2>{{ t('tuya_cloud', 'Unable to connect to {name}', {name: device.name}) }}</h2>
				<p><span>{{ t('tuya_cloud', 'Make sure that the equipment is powered on normally (or the battery is sufficient).') }}</span></p>
			</div>
			<div v-else-if="device && device.dev_type === 'switch'">
				<h2>{{ device.name }}</h2>
				<NcCheckboxRadioSwitch
				    type="switch"
				    :checked.sync="device.data.state"
				    @update:checked="changeDeviceState">
					{{ getFriendlyDeviceState(device) }}
				</NcCheckboxRadioSwitch>
			</div>
			<div v-else>
				<h2>{{ t('tuya_cloud', 'Device not supported yet') }}</h2>
				<p><span>{{ t('tuya_cloud', 'This application is in the early stages of development and currently supports few devices.') }}</span></p>
			</div>
		</div>
	</NcModal>
</div>
</template>

<script>
import NcAvatar from '@nextcloud/vue/dist/Components/NcAvatar'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcDashboardWidget from '@nextcloud/vue/dist/Components/NcDashboardWidget.js'
import NcDashboardWidgetItem from '@nextcloud/vue/dist/Components/NcDashboardWidgetItem.js'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import { generateUrl } from '@nextcloud/router'

import { getDashboardData, setDeviceState } from '../service/TuyaCloudService.js'

export default {
	name: 'DevicesDashboard',

	components: {
		NcAvatar,
		NcCheckboxRadioSwitch,
		NcDashboardWidget,
		NcDashboardWidgetItem,
		NcModal,
	},

	data() {
		return {
			loading: true,
			devices: [],
			device: null,
			showDeviceModal: false
		}
	},

	computed: {
		getDeviceIcon() {
			return (device) => {
				return generateUrl('apps/tuya_cloud/devices/icon/' + device.id)
			}
		},

		getFriendlyDeviceState() {
			return (device) => {
				if (!device.data.online) return t('tuya_cloud', 'Device disconnected…')
				else return device.data.state ? t('tuya_cloud', 'Device on') : t('tuya_cloud', 'Device off')
			}
		}
	},

	created() {
		this.loadDashboardData()
	},

	methods: {
		loadDashboardData() {
			getDashboardData().then(data => {
				this.devices = data.devices
				this.loading = false
			})
		},
		onDeviceClick(device) {
			this.device = device
			this.showDeviceModal = true
		},
		changeDeviceState(newState) {
			setDeviceState(this.device.id, 'turnOnOff', newState ? 1 : 0)
		}
	},

}
</script>
<style scoped>
.modal__content {
	margin: 50px;
}

.modal__content h2 {
	text-align: center;
}
</style>
