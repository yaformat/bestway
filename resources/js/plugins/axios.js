import axios from 'axios'
import router from '@/router'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

const axiosIns = axios.create({
  baseURL: '/api/',
  timeout: 30000,
})

// ℹ️ Add request interceptor to send the authorization header on each subsequent request after login
axiosIns.interceptors.request.use(config => {
  // Start the progress bar
  NProgress.start()

  // Retrieve token from localStorage
  const token = localStorage.getItem('accessToken')

  // If token is found
  if (token) {
    // Get request headers and if headers is undefined assign blank object
    config.headers = config.headers || {}

    // Set authorization header
    // ℹ️ JSON.parse will convert token to string
    config.headers.Authorization = token ? `Bearer ${JSON.parse(token)}` : ''
  }

  config.headers['Cache-Control'] = 'no-cache'
  config.headers['Pragma'] = 'no-cache'
  config.headers['Expires'] = '0'

  // Return modified config
  return config
})

axiosIns.interceptors.response.use(response => {
  // Complete the progress bar
  NProgress.done()
  return response.data;
}, error => {
  // Complete the progress bar
  NProgress.done()

  const { config, response: { status } } = error
  const originalRequest = config

  if (status === 401 && !originalRequest.url.includes('/auth/refresh')) {
    return axiosIns.post('/auth/refresh', {
      refreshToken: localStorage.getItem('refreshToken')
    }).then(({ data }) => {
      if (data.accessToken) {
        localStorage.setItem('accessToken', data.accessToken)
        //localStorage.setItem('refreshToken', data.refreshToken)

        // Update the headers with the new access token
        axiosIns.defaults.headers.common.Authorization = `Bearer ${data.accessToken}`

        // Repeat the original request
        return axiosIns(originalRequest)
      } else {
        localStorage.removeItem('userData')
        localStorage.removeItem('accessToken')
        localStorage.removeItem('refreshToken')
        localStorage.removeItem('userAbilities')

        router.push('/login')
      }
    }).catch((error) => {
      const { config, response: { status } } = error
      if (status === 401) router.push('/login')
    })
  }

  return Promise.reject(error)
});

export default axiosIns
