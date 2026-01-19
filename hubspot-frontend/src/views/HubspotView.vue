<template>
  <div class="page">
    <div class="card">
      <!-- Header -->
      <div class="header">
        <img
          src="https://www.hubspot.com/hubfs/assets/hubspot.com/style-guide/brand-guidelines/guidelines_the-logo.svg"
          alt="HubSpot Logo"
        />
        <h1>Integração HubSpot</h1>
        <p>Conecte sua plataforma ao HubSpot para sincronizar contatos e negócios.</p>
      </div>

      <!-- Conteúdo com scroll -->
      <div class="content">
        <!-- Loading -->
        <div v-if="loading" class="badge loading">⏳ Verificando conexão...</div>

        <!-- Erro -->
        <div v-else-if="error" class="badge error">❌ {{ error }}</div>

        <!-- Não conectado -->
        <div v-else-if="!connected" class="center">
          <div class="badge warning">⚠️ HubSpot não conectado</div>
          <button class="btn primary" @click="connect">🔐 Conectar HubSpot</button>
        </div>

        <!-- Conectado -->
        <div v-else>
          <div class="badge success">✅ HubSpot conectado</div>

          <!-- Conta -->
          <div class="account-box">
            <p><strong>Conta:</strong> {{ overview?.company_name ?? platformName }}</p>
            <p><strong>Portal ID:</strong> {{ account.portal_id }}</p>
            <p><strong>Região:</strong> {{ overview?.region ?? '—' }}</p>
            <p><strong>Timezone:</strong> {{ overview?.timezone ?? '—' }}</p>
          </div>

          <!-- Métricas -->
          <div v-if="overview && !loadingOverview" class="metrics">
            <div class="metric-card">
              <span>👥</span>
              <div>
                <strong>{{ overview.objects.contacts }}</strong>
                <small>Contatos</small>
              </div>
            </div>
            <div class="metric-card">
              <span>🏢</span>
              <div>
                <strong>{{ overview.objects.companies }}</strong>
                <small>Empresas</small>
              </div>
            </div>
            <div class="metric-card">
              <span>💼</span>
              <div>
                <strong>{{ overview.objects.deals }}</strong>
                <small>Negócios</small>
              </div>
            </div>
          </div>

          <div v-else-if="loadingOverview" class="badge loading">
            ⏳ Carregando dados da conta...
          </div>

          <!-- Ações -->
          <div class="actions">
            <button class="btn secondary" :disabled="importing" @click="importContacts">
              <span v-if="!importing">📥 Importar Contatos</span>
              <span v-else>⏳ Importando...</span>
            </button>

            <button class="btn danger" @click="logoutFront">🚪 Deslogar </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <small>Integração segura via OAuth 2.0 · HubSpot API</small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const connected = ref(false)
const loading = ref(true)
const importing = ref(false)
const error = ref(null)
const account = ref(null)
const overview = ref(null)
const loadingOverview = ref(false)
const platformName = ref('DevNest HubSpot Account')

// Iniciar OAuth
const connect = () => { window.location.href = 'http://localhost:8000/api/hubspot/redirect' }

// Importar contatos
const importContacts = async () => {
  importing.value = true
  try { const { data } = await api.post('/hubspot/import'); alert(`✅ ${data.imported} contatos importados com sucesso`) }
  catch { alert('❌ Erro ao importar contatos') }
  finally { importing.value = false }
}

// Desconectar do Front
const logoutFront = () => { connected.value = false; account.value = null; overview.value = null }

// Carregar overview
const loadOverview = async () => {
  loadingOverview.value = true
  try { const { data } = await api.get('/hubspot/overview'); overview.value = data }
  catch { error.value = 'Não foi possível carregar os dados da conta' }
  finally { loadingOverview.value = false }
}

// Ciclo de vida
onMounted(async () => {
  try {
    const { data } = await api.get('/hubspot/status')
    connected.value = data.connected
    account.value = data.account ?? null
    if (connected.value) await loadOverview()
  } catch { error.value = 'Não foi possível verificar o status do HubSpot' }
  finally { loading.value = false }
})
</script>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 20px 0;
  background: linear-gradient(135deg, #f5f7fb, #e6ecf5);
  font-family: system-ui, -apple-system, sans-serif;
}

.card {
  max-width: 480px;
  width: 100%;
  max-height: 90vh; /* Limite máximo da altura da card */
  display: flex;
  flex-direction: column;
  padding: 32px;
  border-radius: 18px;
  background: white;
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
  animation: fadeUp 0.5s ease-out;
  overflow: hidden; /* Oculta overflow externo */
}

.content {
  overflow-y: auto; /* Scroll interno */
  flex: 1; /* Ocupa espaço restante */
  padding-right: 4px; /* Espaço para scrollbar */
}

.header {
  text-align: center;
  margin-bottom: 16px;
}

.header img { width: 50px; margin-bottom: 12px; }
.header h1 { font-size: 22px; color: #33475b; margin-bottom: 8px; }
.header p { font-size: 14px; color: #516f90; }

.badge {
  display: inline-block;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 16px;
}

.badge.success { background: #eafbe7; color: #2e7d32; }
.badge.warning { background: #fff4e5; color: #b45309; }
.badge.error { background: #fdecea; color: #b42318; }
.badge.loading { background: #f4f7f9; color: #516f90; }

.account-box {
  background: #f8fafe;
  border: 1px solid #cbd6e2;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 20px;
  color: #1f2937;
  font-weight: 600;
}

.metrics {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.metric-card {
  background: #eaf3ff;
  border-radius: 10px;
  padding: 16px;
  flex: 1;
  margin-right: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-align: center;
}

.metric-card:last-child { margin-right: 0; }
.metric-card strong { font-size: 18px; display: block; }
.metric-card small { color: #516f90; }

.actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: 0.2s;
}

.btn.primary { background: #ff7a59; color: white; }
.btn.primary:hover { background: #ff5c35; }
.btn.secondary { background: #0091ae; color: white; }
.btn.secondary:hover { background: #007a92; }
.btn.danger { background: #e53935; color: white; }
.btn.danger:hover { background: #b71c1c; }

.footer { text-align: center; margin-top: 12px; font-size: 11px; color: #7c98b6; }

.center { text-align: center; }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
