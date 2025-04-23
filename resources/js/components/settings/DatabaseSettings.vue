<template>
  <div class="flex items-center justify-center">
    <div class="w-full max-w-4xl">
      <div class="bg-white rounded shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold text-gray-800">Database Settings</h1>
        </div>

        <div class="mb-6">
          <div class="flex flex-wrap -mx-4">
            <div class="w-full md:w-1/2 px-4 mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="database_host">
                Database Host
              </label>
              <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="database_host"
                type="text"
                v-model="form.database_host"
                placeholder="localhost"
              />
            </div>

            <div class="w-full md:w-1/2 px-4 mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="database_port">
                Database Port
              </label>
              <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="database_port"
                type="number"
                v-model="form.database_port"
                placeholder="3306"
              />
            </div>

            <div class="w-full md:w-1/2 px-4 mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="database_name">
                Database Name
              </label>
              <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="database_name"
                type="text"
                v-model="form.database_name"
                placeholder="database_name"
              />
            </div>

            <div class="w-full md:w-1/2 px-4 mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="database_username">
                Database Username
              </label>
              <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="database_username"
                type="text"
                v-model="form.database_username"
                placeholder="username"
              />
            </div>

            <div class="w-full px-4 mb-6">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="database_password">
                Database Password
              </label>
              <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="database_password"
                type="password"
                v-model="form.database_password"
                placeholder="password"
              />
            </div>
          </div>

          <div class="flex justify-end">
            <button
              class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2"
              @click="testConnection"
              :disabled="isLoading"
            >
              Test Connection
            </button>
            <button
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              @click="saveSettings"
              :disabled="isLoading"
            >
              Save Settings
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        database_host: '',
        database_port: 3306,
        database_name: '',
        database_username: '',
        database_password: ''
      },
      isLoading: false
    }
  },

  mounted() {
    this.getSettings()
  },

  methods: {
    async getSettings() {
      try {
        const response = await axios.get('/api/v1/settings/database')
        if (response.data.settings) {
          this.form = response.data.settings
        }
      } catch (error) {
        console.error('Error fetching database settings:', error)
      }
    },

    async testConnection() {
      this.isLoading = true
      try {
        const response = await axios.post('/api/v1/settings/database/test', this.form)
        this.$notify({
          title: 'Success',
          text: response.data.message,
          type: 'success'
        })
      } catch (error) {
        this.$notify({
          title: 'Error',
          text: error.response.data.error || 'Failed to test database connection',
          type: 'error'
        })
      } finally {
        this.isLoading = false
      }
    },

    async saveSettings() {
      this.isLoading = true
      try {
        await axios.post('/api/v1/settings/database', this.form)
        this.$notify({
          title: 'Success',
          text: 'Database settings saved successfully',
          type: 'success'
        })
      } catch (error) {
        this.$notify({
          title: 'Error',
          text: error.response.data.error || 'Failed to save database settings',
          type: 'error'
        })
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script> 