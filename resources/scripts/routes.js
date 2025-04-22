{
    path: '/admin/settings/database',
    name: 'DatabaseSettings',
    component: () => {
        console.log('Loading DatabaseSettings component...');
        return import('./pages/settings/DatabaseSettings.vue').catch(error => {
            console.error('Error loading DatabaseSettings component:', error);
            throw error;
        });
    },
    meta: {
        requiresAuth: true,
        ownerOnly: true
    }
}, 