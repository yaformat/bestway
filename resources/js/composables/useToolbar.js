// composables/useToolbar.js
import { ref, reactive } from 'vue'

const toolbarState = reactive({
  title: '',
  subtitle: '',
  showBackButton: false,
  backRoute: null,
  backAction: null,
  showSearch: false,
  showNotifications: false,
  showMenu: false,
  showBurger: true,
  loading: false,
  customButtons: [],
  menuItems: []
})

export const useToolbar = () => {
  const setTitle = (title) => {
    toolbarState.title = title
  }

  const setBackButton = (options = {}) => {
    toolbarState.showBackButton = true
    toolbarState.backRoute = options.route || null
    toolbarState.backAction = options.action || null
  }

  const hideBackButton = () => {
    toolbarState.showBackButton = false
    toolbarState.backRoute = null
    toolbarState.backAction = null
  }

  const setRightContent = (content, action = null) => {
    toolbarState.rightContent = content
    toolbarState.rightAction = action
    toolbarState.showBurger = false
  }

  const showBurgerMenu = () => {
    toolbarState.showBurger = true
    toolbarState.rightContent = null
    toolbarState.rightAction = null
  }

  const setCustomContent = (content) => {
    toolbarState.customContent = content
  }

  const reset = () => {
    toolbarState.title = ''
    toolbarState.showBackButton = false
    toolbarState.backRoute = null
    toolbarState.backAction = null
    toolbarState.rightContent = null
    toolbarState.rightAction = null
    toolbarState.showBurger = true
    toolbarState.customContent = null
  }

  return {
    toolbarState,
    setTitle,
    setBackButton,
    hideBackButton,
    setRightContent,
    showBurgerMenu,
    setCustomContent,
    reset
  }
}
