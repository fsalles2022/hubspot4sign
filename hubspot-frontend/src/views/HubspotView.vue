<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '../services/api'

const connected = ref(false)
const loading = ref(true)
const importing = ref(false)
const error = ref(null)
const account = ref(null)
const overview = ref(null)
const loadingOverview = ref(false)
const platformName = ref('DevNest HubSpot Account')

// Animar métricas e barras
const animatedMetrics = ref({ contacts: 0, companies: 0, deals: 0 })
const bars = ref({ contacts: 0, companies: 0, deals: 0 })

const connect = () => window.location.href = 'http://localhost:8000/api/hubspot/redirect'

const importContacts = async () => {
  importing.value = true
  try {
    const { data } = await api.post('/hubspot/import')
    alert(`✅ ${data.imported} contatos importados com sucesso`)
  } catch {
    alert('❌ Erro ao importar contatos')
  } finally {
    importing.value = false
  }
}

const disconnect = async () => {
  if (!confirm('Deseja desconectar a conta HubSpot?')) return
  try {
    await api.post('/hubspot/disconnect')
    connected.value = false
    account.value = null
    overview.value = null
    animatedMetrics.value = { contacts: 0, companies: 0, deals: 0 }
    bars.value = { contacts: 0, companies: 0, deals: 0 }
  } catch {
    alert('Erro ao desconectar')
  }
}

// Carregar overview
const loadOverview = async () => {
  loadingOverview.value = true
  try {
    const { data } = await api.get('/hubspot/overview')
    overview.value = data
    nextTick(() => animateMetrics())
  } catch {
    error.value = 'Não foi possível carregar os dados da conta'
  } finally {
    loadingOverview.value = false
  }
}

// Animação de números e barras
const animateMetrics = () => {
  const duration = 800
  const steps = 30
  const interval = duration / steps

  const update = (key, target) => {
    let current = 0
    const increment = target / steps
    const timer = setInterval(() => {
      current += increment
      animatedMetrics.value[key] = Math.round(current)
      bars.value[key] = Math.min(100, Math.round((current / target) * 100))
      if (animatedMetrics.value[key] >= target) {
        animatedMetrics.value[key] = target
        bars.value[key] = 100
        clearInterval(timer)
      }
    }, interval)
  }

  update('contacts', overview.value.objects.contacts)
  update('companies', overview.value.objects.companies)
  update('deals', overview.value.objects.deals)
}

// Lifecycle
onMounted(async () => {
  try {
    const { data } = await api.get('/hubspot/status')
    connected.value = data.connected
    account.value = data.account ?? null
    if (connected.value) await loadOverview()
  } catch {
    error.value = 'Não foi possível verificar o status do HubSpot'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="page">
    <div class="card">
      <div class="header">
        <img src="https://www.hubspot.com/hubfs/assets/hubspot.com/style-guide/brand-guidelines/guidelines_the-logo.svg" alt="HubSpot Logo"/>
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
          <div class="badge success">✅ HubSpot conectado</div>

          <div class="account-box">
            <p><strong>Conta:</strong> {{ overview?.company_name ?? platformName }}</p>
            <p><strong>Portal ID:</strong> {{ account.portal_id }}</p>
            <p><strong>Região:</strong> {{ overview?.region ?? '—' }}</p>
            <p><strong>Timezone:</strong> {{ overview?.timezone ?? '—' }}</p>
          </div>

          <div v-if="overview && !loadingOverview" class="metrics">
            <div class="metric-card contacts">
              <div class="emoji">👥</div>
              <strong>{{ animatedMetrics.contacts }}</strong>
              <small>Contatos</small>
              <div class="bar">
                <div class="bar-fill" :style="{ width: bars.contacts + '%' }"></div>
              </div>
            </div>

            <div class="metric-card companies">
              <div class="emoji">🏢</div>
              <strong>{{ animatedMetrics.companies }}</strong>
              <small>Empresas</small>
              <div class="bar">
                <div class="bar-fill" :style="{ width: bars.companies + '%' }"></div>
              </div>
            </div>

            <div class="metric-card deals">
              <div class="emoji">💼</div>
              <strong>{{ animatedMetrics.deals }}</strong>
              <small>Negócios</small>
              <div class="bar">
                <div class="bar-fill" :style="{ width: bars.deals + '%' }"></div>
              </div>
            </div>
          </div>

          <div v-else-if="loadingOverview" class="badge loading">⏳ Carregando dados da conta...</div>

          <div class="actions">
            <button class="btn secondary" :disabled="importing" @click="importContacts">
              <span v-if="!importing">📥 Importar Contatos</span>
              <span v-else>⏳ Importando...</span>
            </button>
            <button class="btn danger" @click="disconnect">🔌 Desconectar HubSpot</button>
          </div>
        </div>
      </div>

      <div class="footer"><small>Integração segura via OAuth 2.0 · HubSpot API</small></div>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: linear-gradient(135deg, #f5f7fb, #e6ecf5);
  padding: 20px;
  overflow-y: auto;
  font-family: system-ui, -apple-system, sans-serif;
}

.card {
  width: 100%;
  max-width: 540px;
  background: #fff;
  border-radius: 18px;
  padding: 32px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.header { text-align: center; margin-bottom: 28px; }
.header img { width: 60px; margin-bottom: 12px; }
.header h1 { font-size: 24px; color: #33475b; margin-bottom: 6px; }
.header p { font-size: 14px; color: #516f90; }

.badge {
  display: inline-block;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 16px;
  text-align: center;
}
.badge.success { background: #eafbe7; color: #2e7d32; }
.badge.warning { background: #fff4e5; color: #b45309; }
.badge.error { background: #fdecea; color: #b42318; }
.badge.loading { background: #f4f7f9; color: #516f90; }

.account-box {
  background: #f0f4ff;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  color: #1f2937;
  font-weight: 600;
  line-height: 1.6;
}

.metrics {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 24px;
}

.metric-card {
  flex: 1 1 calc(33% - 12px);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  font-weight: 700;
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  position: relative;
  transition: transform 0.3s, box-shadow 0.3s;
}
.metric-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px rgba(0,0,0,0.2); }
.metric-card .emoji { font-size: 28px; }
.metric-card strong { font-size: 22px; }
.metric-card small { font-size: 13px; color: #fdfdfd; }

/* BARRAS */
.bar { width: 100%; height: 6px; background: rgba(255,255,255,0.3); border-radius: 3px; margin-top: 6px; }
.bar-fill { height: 100%; background: rgba(255,255,255,0.9); border-radius: 3px; transition: width 0.6s ease; }

.metric-card.contacts { background: #4f46e5; }
.metric-card.companies { background: #059669; }
.metric-card.deals { background: #f59e0b; }

.actions { display: flex; flex-direction: column; gap: 12px; }
.btn { width: 100%; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; }
.btn.primary { background: #ff7a59; color: #fff; }
.btn.primary:hover { background: #ff5c35; }
.btn.secondary { background: #0091ae; color: #fff; }
.btn.secondary:hover { background: #007a92; }
.btn.danger { background: #e53935; color: #fff; }
.btn.danger:hover { background: #b71c1c; }

.footer { text-align: center; margin-top: 24px; font-size: 11px; color: #7c98b6; }

.center { text-align: center; }

@media (max-width: 480px) {
  .metric-card { flex: 1 1 100%; }
  .card { padding: 24px; }
}
</style>
