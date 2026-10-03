import Index from './pages/Index.vue'
import Show from './pages/Show.vue'

Statamic.booting(() => {
    Statamic.$inertia.register('mail-log-statamic::Index', Index)
    Statamic.$inertia.register('mail-log-statamic::Show', Show)
})
