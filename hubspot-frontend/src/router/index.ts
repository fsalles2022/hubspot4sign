import { createRouter, createWebHistory } from 'vue-router'
import HubspotView from '../views/HubspotView.vue'
import Dashboard from '../pages/Dashboard.vue'

const routes = [
  {
    path: '/hubspot',
    component: Dashboard
  }
]

export default createRouter({
  history: createWebHistory(),
  routes
})
