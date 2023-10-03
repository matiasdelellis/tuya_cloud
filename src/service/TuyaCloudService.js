import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'

function url(url) {
	url = `apps/tuya_cloud${url}`
	return generateUrl(url)
}

export const getDashboardData = () => {
	return axios
		.get(url('/dashboard'))
		.then(response => {
			return response.data
		})
		.catch(err => {
			console.error(err)
			showError(t('tuya_cloud', 'There was an error fetching your Tuya devices for the dashboard'))
			throw err
		})
}

export const setDeviceState = (deviceId, command, value) => {
	return axios
		.put(url('/devices/' + deviceId), {
			command: command,
			value: value
		})
		.then(response => {
			return response.data
		})
		.catch(err => {
			console.error(err)
			showError(t('tuya_cloud', 'There was an error controlling your Tuya device'))
			throw err
		})
}
