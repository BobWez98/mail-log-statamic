<template>
    <Head :title="__('Mail Log')" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="__('Mail Log')" icon="mail" />

        <Listing
            :url="listingUrl"
            :columns="columns"
            sort-column="created_at"
            sort-direction="desc"
            push-query
            :sortable="false"
            :allow-search="false"
            :allow-bulk-actions="false"
            :allow-customizing-columns="false"
            :allow-presets="false"
        >
            <template #cell-subject="{ row, value }">
                <Link :href="row.show_url" class="font-medium text-blue-600 hover:text-blue-700">
                    {{ value }}
                </Link>
            </template>

            <template #cell-status="{ value }">
                <Badge :text="statusLabel(value)" :color="statusColor(value)" pill />
            </template>

            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>

            <template #prepended-row-actions="{ row }">
                <DropdownItem :href="row.show_url" icon="eye" :text="__('View')" />
            </template>
        </Listing>
    </div>
</template>

<script setup>
import { Head, Link } from '@statamic/cms/inertia'
import { Badge, DropdownItem, Header, Listing } from '@statamic/cms/ui'

const { listingUrl } = defineProps({
    listingUrl: {
        type: String,
        required: true,
    },
})

const columns = [
    { field: 'subject', label: __('Subject'), sortable: false, visible: true },
    { field: 'to', label: __('To'), sortable: false, visible: true },
    { field: 'status', label: __('Status'), sortable: true, visible: true },
    { field: 'created_at', label: __('Created'), sortable: true, visible: true },
]

const statusColors = {
    success: 'green',
    pending: 'amber',
}

const statusLabels = {
    success: __('Sent'),
    pending: __('Pending'),
}

const dateFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
})

const formatDate = (value) => dateFormatter.format(new Date(value))
const statusColor = (status) => statusColors[status] ?? 'default'
const statusLabel = (status) => statusLabels[status] ?? status
</script>
