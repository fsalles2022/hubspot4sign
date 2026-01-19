<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import api from '../services/api'

Chart.register(...registerables)

const connected = ref(false)
const loading = ref(true)
const importing = ref(false)
const error = ref(null)
const account = ref(null)
const overview = ref(null)
const loadingOverview = ref(false)
const platformName = ref('DevNest HubSpot Account')

const animatedContacts = ref(0)
const animatedCompanies = ref(0)
const animatedDeals = ref(0)
const history = ref([])


const metricsCanvas = ref(null)
const historyCanvas = ref(null)
let metricsChart = null
let historyChart = null

const animateNumber = (targetRef, value) => {
  let start = 0
  const step = Math.ceil(value / 30) || 1
  const interval = setInterval(() => {
    start += step
    if (start >= value) {
      targetRef.value = value
      clearInterval(interval)
    } else {
      targetRef.value = start
    }
  }, 20)
}

const connect = () => { window.location.href = 'http://localhost:8000/api/hubspot/redirect' }

const importContacts = async () => {
  importing.value = true
  try {
    const { data } = await api.post('/hubspot/import')
    alert(`✅ ${data.imported} contatos importados com sucesso`)
    await loadOverview()
  } catch {
    alert('❌ Erro ao importar contatos')
  } finally { importing.value = false }
}

const disconnect = async () => {
  if (!confirm('Deseja desconectar a conta HubSpot?')) return
  try {
    await api.post('/hubspot/disconnect')
    connected.value = false
    account.value = null
    overview.value = null
    animatedContacts.value = 0
    animatedCompanies.value = 0
    animatedDeals.value = 0
    if (metricsChart) metricsChart.destroy()
    if (historyChart) historyChart.destroy()
  } catch {
    alert('Erro ao desconectar')
  }
}

const loadOverview = async () => {
  loadingOverview.value = true
  try {
    const { data } = await api.get('/hubspot/overview')
    overview.value = data
    animateNumber(animatedContacts, data.objects.contacts)
    animateNumber(animatedCompanies, data.objects.companies)
    animateNumber(animatedDeals, data.objects.deals)
    await nextTick()
    renderMetricsChart()
    renderHistoryChart()
  } catch {
    error.value = 'Não foi possível carregar os dados da conta'
  } finally { loadingOverview.value = false }
}

const loadHistory = async () => {
  const { data } = await api.get('/hubspot/history')
  history.value = data
}


const renderMetricsChart = () => {
  if (!metricsCanvas.value || !overview.value) return
  if (metricsChart) metricsChart.destroy()
  metricsChart = new Chart(metricsCanvas.value, {
    type: 'doughnut',
    data: {
      labels: ['Contatos', 'Empresas', 'Negócios'],
      datasets: [{
        data: [
          overview.value.objects.contacts,
          overview.value.objects.companies,
          overview.value.objects.deals
        ],
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
        hoverOffset: 10
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { font: { size: 14 } } } }
    }
  })
}

// const renderHistoryChart = () => {
//   if (!historyCanvas.value || !overview.value) return
//   if (historyChart) historyChart.destroy()
//   historyChart = new Chart(historyCanvas.value, {
//     type: 'line',
//     data: {
//       labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
//       datasets: [{
//         label: 'Contatos importados',
//         data: [2, 5, 3, overview.value.objects.contacts],
//         borderColor: '#3b82f6',
//         backgroundColor: 'rgba(59,130,246,0.2)',
//         tension: 0.4,
//         fill: true
//       }]
//     },
//     options: {
//       responsive: true,
//       maintainAspectRatio: false,
//       plugins: { legend: { display: true, position: 'top' } },
//       scales: {
//         y: { beginAtZero: true, ticks: { stepSize: 1 } }
//       }
//     }
//   })
// }
const renderHistoryChart = () => {
  if (!historyCanvas.value || !history.value.length) return
  if (historyChart) historyChart.destroy()

  historyChart = new Chart(historyCanvas.value, {
    type: 'line',
    data: {
      labels: history.value.map(item => item.snapshot_date),
      datasets: [
        {
          label: 'Contatos',
          data: history.value.map(item => item.contacts),
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59,130,246,0.2)',
          tension: 0.4,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}


onMounted(async () => {
  try {
    const { data } = await api.get('/hubspot/status')
    connected.value = data.connected
    account.value = data.account ?? null
    if (connected.value) await loadOverview()
  } catch {
    error.value = 'Não foi possível verificar o status do HubSpot'
  } finally { loading.value = false }
})
</script>

<template>
  <div class="page">
    <div class="card">
      <!-- Header -->
      <div class="header">
        <img src="https://www.hubspot.com/hubfs/assets/hubspot.com/style-guide/brand-guidelines/guidelines_the-logo.svg"
          alt="HubSpot Logo" />
        <h1>Integração HubSpot</h1>
        <p>Conecte sua plataforma ao HubSpot para sincronizar contatos e negócios.</p>
      </div>

      <div class="content">
        <div v-if="loading" class="badge loading">⏳ Verificando conexão...</div>
        <div v-else-if="error" class="badge error">❌ {{ error }}</div>

        <div v-else-if="!connected" class="center">
          <div class="badge warning">⚠️ HubSpot não conectado</div>
          <button class="btn primary" @click="connect">🔐 Conectar HubSpot</button>
        </div>

        <div v-else>
          <div class="badge success" style="color:chocolate">✅ HubSpot conectado</div>

          <!-- Conta -->
          <div class="account-box">
            <p><strong>Conta:</strong> {{ overview?.company_name ?? platformName }}</p>
            <p><strong>Portal ID:</strong> {{ account.portal_id }}</p>
            <p><strong>Região:</strong> {{ overview?.region ?? '—' }}</p>
            <p><strong>Timezone:</strong> {{ overview?.timezone ?? '—' }}</p>
          </div>

          <!-- Métricas animadas -->
          <div class="metrics">
            <div class="metric-card fade">
              <span>👥</span>
              <div><strong>{{ animatedContacts }}</strong><small>Contatos</small></div>
            </div>
            <div class="metric-card fade">
              <span>🏢</span>
              <div><strong>{{ animatedCompanies }}</strong><small>Empresas</small></div>
            </div>
            <div class="metric-card fade">
              <span>💼</span>
              <div><strong>{{ animatedDeals }}</strong><small>Negócios</small></div>
            </div>
          </div>

          <!-- Gráficos -->
          <div class="charts">
            <div class="chart-wrapper"><canvas ref="metricsCanvas"></canvas></div>
            <div class="chart-wrapper"><canvas ref="historyCanvas"></canvas></div>
          </div>

          <!-- Ações -->
          <div class="actions">
            <button class="btn secondary" :disabled="importing" @click="importContacts">
              <span v-if="!importing">📥 Importar Contatos</span>
              <span v-else>⏳ Importando...</span>
            </button>
            <button class="btn danger" @click="disconnect">🔌 Desconectar HubSpot</button>
          </div>
        </div>
      </div>

      <div class="footer">
        <small>Integração segura via OAuth 2.0 · HubSpot API</small>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  /* gradiente escuro */
  font-family: system-ui, -apple-system, sans-serif;
  padding: 16px
}

.card {
  width: 100%;
  max-width: 1200px;
  padding: 32px;
  border-radius: 20px;
  background: white;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  gap: 24px;
  overflow-y: auto;
  min-height: 90vh;
}

.header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.header img {
  width: 60px;
  margin-bottom: 12px;
}

.header h1 {
  font-size: 28px;
  color: #33475b;
  margin-bottom: 6px;
}

.header p {
  font-size: 16px;
  color: #516f90;
}

.metrics {
  display: flex;
  gap: 16px;
  justify-content: space-between;
  flex-wrap: wrap;
}

.metric-card {
  flex: 1 1 200px;
  background: #eaf3ff;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.metric-card:hover {
  transform: translateY(-4px) scale(1.03);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

.metric-card strong {
  font-size: 22px;
  color: #1e3a8a;
}

.metric-card small {
  color: #1e3a8a;
}

.charts {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  justify-content: space-between;
}

.chart-wrapper {
  flex: 1 1 45%;
  height: 300px;
  position: relative;
}

.chart-wrapper canvas {
  width: 100% !important;
  height: 100% !important;
}

.actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.btn {
  flex: 1;
  padding: 16px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.2s;
  border: none;
}

.btn.primary {
  background: #ff7a59;
  color: white;
}

.btn.primary:hover {
  background: #ff5c35;
}

.btn.secondary {
  background: #0091ae;
  color: white;
}

.btn.secondary:hover {
  background: #007a92;
}

.btn.danger {
  background: #e53935;
  color: white;
}

.btn.danger:hover {
  background: #b71c1c;
}

.footer {
  text-align: center;
  font-size: 12px;
  color: #7c98b6;
  margin-top: 24px;
}

.center {
  text-align: center;
}

@media(max-width: 1200px) {

  .metrics,
  .charts,
  .actions {
    flex-direction: column;
    gap: 16px;
  }

  .metric-card,
  .chart-wrapper,
  .btn {
    flex: 1 1 100%;
    height: auto;
  }
}
</style>
