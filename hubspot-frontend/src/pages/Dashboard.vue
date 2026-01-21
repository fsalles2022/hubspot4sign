<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

import HubspotStatus from '../modules/hubspot/HubspotStatus.vue'
import HubspotMetrics from '../modules/hubspot/HubspotMetrics.vue'
import HubspotCharts from '../modules/hubspot/HubspotCharts.vue'

// ===== STATE (OBRIGATÓRIO) =====
const loading = ref(true)
const importing = ref(false)
const connected = ref(false)
const error = ref(null)

const account = ref(null)
const overview = ref(null)
const history = ref([])

const animatedContacts = ref(0)
const animatedCompanies = ref(0)
const animatedDeals = ref(0)

const platformName = ref('DevNest HubSpot Account')

// ===== ACTIONS =====
const connect = () => {
  window.location.href = 'http://localhost:8000/api/hubspot/redirect'
}

const importContacts = async () => {
  importing.value = true
  try {
    await api.post('/hubspot/import')
    await loadOverview()
    await loadHistory()
  } finally {
    importing.value = false
  }
}

const disconnect = async () => {
  await api.post('/hubspot/disconnect')
  connected.value = false
  overview.value = null
  account.value = null
}

// ===== LOADERS =====
const loadOverview = async () => {
  const { data } = await api.get('/hubspot/overview')
  overview.value = data

  animatedContacts.value = data.objects.contacts
  animatedCompanies.value = data.objects.companies
  animatedDeals.value = data.objects.deals
}

const loadHistory = async () => {
  const { data } = await api.get('/hubspot/history')
  history.value = data
}

// ===== INIT =====
onMounted(async () => {
  try {
    const { data } = await api.get('/hubspot/status')
    connected.value = data.connected
    account.value = data.account

    if (connected.value) {
      await loadOverview()
      await loadHistory()
    }
  } catch {
    error.value = 'Erro ao verificar HubSpot'
  } finally {
    loading.value = false
  }
})
</script>


<template>
  <HubspotStatus
    :loading="loading"
    :error="error"
    :connected="connected"
    :account="account"
    :overview="overview"
    :platformName="platformName"
    :importing="importing"
    @connect="connect"
    @import="importContacts"
    @disconnect="disconnect"
  />

  <HubspotMetrics
    v-if="overview"
    :contacts="animatedContacts"
    :companies="animatedCompanies"
    :deals="animatedDeals"
  />

  <HubspotCharts
    v-if="overview"
    :overview="overview"
    :history="history"
  />
</template>
