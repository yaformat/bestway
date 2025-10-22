import './app-bar-search'
import './apps/user-list'
import './jwt'
import mock from './mock'

const navData = [
  { heading: 'Dashboards' },
  {
    title: 'Home',
    icon: 'i-mdi-home',
    to: 'index',
    action: 'read',
    subject: 'Auth',
  },
]

mock.onGet('/navigation').reply(async () => {
  await new Promise(resolve => setTimeout(resolve, 2000))
  return [200, navData]
})

// forwards the matched request over network
mock.onAny().passThrough()
