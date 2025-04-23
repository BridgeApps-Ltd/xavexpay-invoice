<template>
  <div class="flex flex-col">
    <div class="flex flex-col">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Database Settings</h1>
        <div class="flex items-center space-x-4">
          <button
            @click="testConnection"
            class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
            :disabled="isTesting || isMigrating"
          >
            {{ isTesting ? 'Testing Connection...' : 'Test Connection' }}
          </button>
          <button
            @click="saveSettings"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            :disabled="isSaving || isMigrating"
          >
            {{ isSaving ? 'Saving...' : 'Save Settings' }}
          </button>
          <button
            @click="runMigrations"
            class="px-4 py-2 text-sm font-medium text-white bg-green-500 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            :disabled="isMigrating || !canRunMigrations"
          >
            {{ isMigrating ? 'Running Migrations...' : 'Run Migrations' }}
          </button>
        </div>
      </div>

      <!-- Loading Overlay -->
      <div v-if="isMigrating" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="p-6 bg-white rounded-lg shadow-xl">
          <div class="flex items-center space-x-4">
            <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <div class="text-lg font-medium text-gray-900">{{ migrationStatus }}</div>
          </div>
          <div class="mt-4 text-sm text-gray-500">
            This may take a few minutes. Please do not close this window.
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-2">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Host</label>
            <input
              v-model="databaseSettings.database_connection_host"
              type="text"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Port</label>
            <input
              v-model="databaseSettings.database_connection_port"
              type="text"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Database Name</label>
            <input
              v-model="databaseSettings.database_connection_name"
              type="text"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Username</label>
            <input
              v-model="databaseSettings.database_connection_username"
              type="text"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input
              v-model="databaseSettings.database_connection_password"
              type="password"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <div class="mt-6">
        <div class="p-4 text-sm text-blue-700 bg-blue-100 rounded-md">
          Please enter your database connection details and click "Test Connection" to verify before saving.
        </div>
      </div>

      <!-- Error Messages -->
      <div v-if="error" class="mt-4">
        <div class="p-4 text-sm text-red-700 bg-red-100 rounded-md">
          {{ error }}
        </div>
      </div>

      <!-- Success Messages -->
      <div v-if="success" class="mt-4">
        <div class="p-4 text-sm text-green-700 bg-green-100 rounded-md">
          {{ success }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useCompanyStore } from '@/scripts/admin/stores/company';

export default {
  name: 'DatabaseSettings',
  setup() {
    const companyStore = useCompanyStore();
    return { companyStore };
  },
  data() {
    return {
      databaseSettings: {
        database_connection_host: '',
        database_connection_port: '',
        database_connection_name: '',
        database_connection_username: '',
        database_connection_password: ''
      },
      isTesting: false,
      isSaving: false,
      isMigrating: false,
      error: null,
      success: null,
      migrationStatus: 'Starting migrations...',
      canRunMigrations: false
    };
  },
  created() {
    this.getSettings();
  },
  methods: {
    getCompanyId() {
      // First try to get from current company in context
      if (this.companyStore && this.companyStore.selectedCompany && this.companyStore.selectedCompany.id) {
        return this.companyStore.selectedCompany.id;
      }
      
      // Then try to get from route params
      if (this.$route.params.companyId) {
        return this.$route.params.companyId;
      }
      
      console.error('No company ID available in context');
      return null;
    },
    async getSettings() {
      try {
        const companyId = this.getCompanyId();
        if (!companyId) {
          console.error('No company ID available');
          this.error = 'No company ID available. Please select a company first.';
          return;
        }

        const response = await axios.get(`/api/v1/settings/database?company_id=${companyId}`);
        console.log("... Database Settings from API /api/v1/settings/database: "+response.data);
        this.databaseSettings = {
          database_connection_host: response.data.settings.database_host || '',
          database_connection_port: response.data.settings.database_port || '',
          database_connection_name: response.data.settings.database_name || '',
          database_connection_username: response.data.settings.database_username || '',
          database_connection_password: response.data.settings.database_password || ''
        };
        this.canRunMigrations = true;
      } catch (error) {
        this.error = 'Failed to load database settings';
        console.error('... Error loading settings:', error);
      }
    },
    async testConnection() {
      this.isTesting = true;
      this.error = null;
      this.success = null;
      try {
        const companyId = this.getCompanyId();
        if (!companyId) {
          console.error('No company ID available');
          this.error = 'No company ID available. Please select a company first.';
          return;
        }

        const settings = {
          company_id: companyId,
          database_host: this.databaseSettings.database_connection_host,
          database_port: this.databaseSettings.database_connection_port,
          database_name: this.databaseSettings.database_connection_name,
          database_username: this.databaseSettings.database_connection_username,
          database_password: this.databaseSettings.database_connection_password
        };
        const response = await axios.post('/api/v1/settings/database/test', settings);
        console.log('Connection test response:', response.data);
        this.success = 'Connection successful!';
      } catch (error) {
        console.error('Connection test failed:', {
          status: error.response?.status,
          data: error.response?.data,
          message: error.message
        });
        this.error = error.response?.data?.message || 'Failed to connect to database';
      } finally {
        this.isTesting = false;
      }
    },
    async saveSettings() {
      this.isSaving = true;
      this.error = null;
      this.success = null;
      try {
        const companyId = this.getCompanyId();
        if (!companyId) {
          console.error('No company ID available');
          this.error = 'No company ID available. Please select a company first.';
          return;
        }

        const settings = {
          company_id: companyId,
          database_host: this.databaseSettings.database_connection_host,
          database_port: this.databaseSettings.database_connection_port,
          database_name: this.databaseSettings.database_connection_name,
          database_username: this.databaseSettings.database_connection_username,
          database_password: this.databaseSettings.database_connection_password
        };
        console.log('Saving settings:', {
          ...settings,
          database_password: '***' // Hide password in logs
        });
        const response = await axios.post('/api/v1/settings/database', settings);
        console.log('... Save settings response:', response.data);
        this.success = 'Settings saved successfully!';
        this.canRunMigrations = true;
      } catch (error) {
        console.error('Save settings failed:', {
          status: error.response?.status,
          data: error.response?.data,
          message: error.message
        });
        this.error = error.response?.data?.message || 'Failed to save settings';
      } finally {
        this.isSaving = false;
      }
    },
    async runMigrations() {
      this.isMigrating = true;
      this.error = null;
      this.success = null;
      this.migrationStatus = 'Starting migrations...';
      
      try {
        const companyId = this.getCompanyId();
        if (!companyId) {
          console.error('No company ID available');
          this.error = 'No company ID available. Please select a company first.';
          return;
        }
        
        // First check if migrations have already been run
        this.migrationStatus = 'Checking migration status...';
        const checkResponse = await axios.get(`/api/v1/settings/database/check-migrations?company_id=${companyId}`);
        
        if (checkResponse.data.migrations_completed) {
          this.error = 'Migrations have already been run. Running them again may cause data duplication.';
          this.migrationStatus = 'Migrations already completed';
          return;
        }

        // Update status messages during the process
        this.migrationStatus = 'Creating database...';
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate delay for status update
        
        this.migrationStatus = 'Running migrations...';
        const response = await axios.post('/api/v1/settings/database/migrate', { company_id: companyId });
        
        this.migrationStatus = 'Setting up company defaults...';
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate delay for status update
        
        this.success = 'Migrations completed successfully!';
        this.migrationStatus = 'Migrations completed!';
      } catch (error) {
        if (error.response?.data?.message?.includes('Duplicate entry')) {
          this.error = 'Migrations have already been run. Running them again may cause data duplication.';
          this.migrationStatus = 'Migrations already completed';
        } else {
          this.error = error.response?.data?.message || 'Failed to run migrations';
          this.migrationStatus = 'Migration failed';
        }
        console.error('Migration failed:', error);
      } finally {
        // Keep the loading state for a bit longer to show completion/failure
        setTimeout(() => {
          this.isMigrating = false;
        }, 2000);
      }
    }
  }
};
</script> 