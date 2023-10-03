import { generateFilePath } from '@nextcloud/router'

import Vue from 'vue'
import Settings from './components/Settings.vue'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('tuya_cloud', '', 'js/')

Vue.mixin({ methods: { t, n } })

export default new Vue({
	el: '#tuyacloud-content',
	render: h => h(Settings),
})
