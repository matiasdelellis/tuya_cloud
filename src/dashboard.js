import Vue from 'vue'
import Dashboard from './components/DevicesDashboard.vue'

Vue.mixin({ methods: { t, n } })

document.addEventListener('DOMContentLoaded', () => {
	OCA.Dashboard.register('tuya_cloud', (el) => {
		const View = Vue.extend(Dashboard)
		new View().$mount(el)
	})
})
