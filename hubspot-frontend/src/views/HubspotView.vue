<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const connected = ref(false)
const loading = ref(true)
const importing = ref(false)
const error = ref(null)
const account = ref(null)
const platformName = ref('DevNest HubSpot Account') // Nome fictício da conta

// Função para iniciar o fluxo OAuth
const connect = () => {
  window.location.href = 'http://localhost:8000/api/hubspot/redirect'
}

// Função para importar contatos
const importContacts = async () => {
  importing.value = true
  try {
    const { data } = await api.post('/hubspot/import')
    alert(`✅ ${data.imported} contatos importados com sucesso`)
  } catch (err) {
    alert('❌ Erro ao importar contatos')
  } finally {
    importing.value = false
  }
}

// Função para desconectar
const disconnect = async () => {
  if (!confirm('Deseja desconectar a conta HubSpot?')) return

  try {
    await api.post('/hubspot/disconnect')
    connected.value = false
    account.value = null
  } catch (err) {
    alert('Erro ao desconectar')
  }
}

// Ciclo de vida - Unificado para evitar conflitos
onMounted(async () => {
  try {
    const { data } = await api.get('/hubspot/status')
    connected.value = data.connected
    account.value = data.account ?? null
  } catch (err) {
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
        <div class="logo-wrapper">
          <img
            src="https://www.hubspot.com/hubfs/assets/hubspot.com/style-guide/brand-guidelines/guidelines_the-logo.svg"
            alt="HubSpot" />
        </div>

        <h1 class="title">Integração HubSpot</h1>
        <p class="subtitle">
          Conecte sua plataforma ao HubSpot para sincronizar contatos e negócios.
        </p>
      </div>

      <div class="content">
        <!-- Loading -->
        <div v-if="loading" class="badge loading">
          ⏳ Verificando conexão...
        </div>

        <!-- Erro -->
        <div v-else-if="error" class="badge error">
          ❌ {{ error }}
        </div>

        <!-- Não conectado -->
        <div v-else-if="!connected">
          <div class="badge warning">⚠️ HubSpot não conectado</div>

          <button class="btn primary" @click="connect">
            🔐 Conectar HubSpot
          </button>
        </div>

        <!-- Conectado -->
        <div v-else>
          <div class="badge success">✅ HubSpot conectado</div>

          <div v-if="account" class="account-box">
            <strong>Conta conectada</strong>
            <p style="color: blue;">{{ platformName }}</p>
            <small>Portal ID: {{ account.portal_id }}</small>
          </div>

          <button class="btn secondary" :disabled="importing" @click="importContacts">
            <span v-if="!importing">📥 Importar Contatos</span>
            <span v-else>⏳ Importando...</span>
          </button>

          <button class="btn danger" @click="disconnect">
            🔌 Desconectar HubSpot
          </button>
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
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f7fb, #e6ecf5);
  font-family: system-ui, -apple-system, sans-serif;
}

.card {
  width: 100%;
  max-width: 420px;
  padding: 32px;
  border-radius: 18px;
  background: white;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  animation: fadeUp 0.5s ease-out;
}

.header {
  text-align: center;
  margin-bottom: 24px;
}

.header img {
  width: 50px;
  margin-bottom: 12px;
}

.header h1 {
  font-size: 22px;
  color: #33475b;
  margin-bottom: 8px;
}

.header p {
  font-size: 14px;
  color: #516f90;
  line-height: 1.5;
}

.badge {
  display: inline-block;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 20px;
}

.badge.success {
  background: #eafbe7;
  color: #2e7d32;
}

.badge.warning {
  background: #fff4e5;
  color: #b45309;
}

.badge.error {
  background: #fdecea;
  color: #b42318;
}

.badge.loading {
  background: #f4f7f9;
  color: #516f90;
}

.account-box {
  background: #f8fafe;
  border: 1px solid #cbd6e2;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 20px;
  text-align: left;
}

.account-label {
  font-size: 12px;
  color: #516f90;
  margin: 0;
}

.account-name {
  font-weight: bold;
  color: #33475b;
  margin: 4px 0;
}

.portal-id {
  color: #7c98b6;
  font-size: 11px;
}

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

.btn.primary {
  background: #ff7a59;
  color: white;
}

/* Cor oficial HubSpot */
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

.btn.danger-link {
  background: transparent;
  color: #ff5c35;
  text-decoration: underline;
  font-size: 13px;
}

.footer {
  margin-top: 24px;
  text-align: center;
  color: #7c98b6;
  font-size: 11px;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
