import { createRouter, createWebHistory } from 'vue-router'
import HubspotView from '../views/HubspotView.vue'

const routes = [
  {
    path: '/hubspot',
    component: HubspotView
  }
]

export default createRouter({
  history: createWebHistory(),
  routes
})
