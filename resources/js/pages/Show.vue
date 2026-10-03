<template>
    <Head :title="mailLog.subject" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="mailLog.subject" icon="mail">
            <Button :href="indexUrl" icon="arrow-left" :text="__('Back to mail log')" />
        </Header>

        <div class="space-y-6">
            <CardPanel :heading="__('Delivery details')">
                <Table>
                    <TableRows>
                        <TableRow>
                            <TableCell class="mail-log-label">{{ __('Status') }}</TableCell>
                            <TableCell>
                                <Badge
                                    :text="statusLabel(mailLog.status)"
                                    :color="statusColor(mailLog.status)"
                                    pill
                                />
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="mail-log-label">{{ __('From') }}</TableCell>
                            <TableCell>{{ mailLog.from }}</TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="mail-log-label">{{ __('To') }}</TableCell>
                            <TableCell>{{ mailLog.to }}</TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="mail-log-label">{{ __('Created') }}</TableCell>
                            <TableCell>{{ formatDate(mailLog.createdAt) }}</TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="mail-log-label">{{ __('Sent') }}</TableCell>
                            <TableCell>{{ formatDate(mailLog.sentAt) }}</TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="mail-log-label">{{ __('Message ID') }}</TableCell>
                            <TableCell class="font-mono text-xs">{{ mailLog.messageId }}</TableCell>
                        </TableRow>
                    </TableRows>
                </Table>
            </CardPanel>

            <CardPanel :heading="__('Email preview')">
                <iframe
                    :src="mailLog.previewUrl"
                    class="mail-log-preview"
                    sandbox=""
                    referrerpolicy="no-referrer"
                    loading="lazy"
                    :title="__('Email preview')"
                />
            </CardPanel>

            <CardPanel :heading="__('Mail data')">
                <pre class="mail-log-data" v-text="formattedData" />
            </CardPanel>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head } from '@statamic/cms/inertia'
import {
    Badge,
    Button,
    CardPanel,
    Header,
    Table,
    TableCell,
    TableRow,
    TableRows,
} from '@statamic/cms/ui'

const { indexUrl, mailLog } = defineProps({
    indexUrl: {
        type: String,
        required: true,
    },
    mailLog: {
        type: Object,
        required: true,
    },
})

const statusColors = {
    success: 'green',
    pending: 'amber',
    failed: 'red',
}

const statusLabels = {
    success: __('Sent'),
    pending: __('Pending'),
    failed: __('Failed'),
}

const dateFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
})

const formattedData = computed(() => JSON.stringify(mailLog.data, null, 2))
const formatDate = (value) => value ? dateFormatter.format(new Date(value)) : __('Not sent')
const statusColor = (status) => statusColors[status] ?? 'default'
const statusLabel = (status) => statusLabels[status] ?? status
</script>

<style scoped>
.mail-log-label {
    width: 12rem;
    font-weight: 600;
}

.mail-log-preview {
    display: block;
    width: 100%;
    min-height: 42rem;
    border: 0;
    background: #fff;
}

.mail-log-data {
    max-height: 24rem;
    overflow: auto;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    font-size: 0.75rem;
    line-height: 1.5;
}

@media (max-width: 640px) {
    .mail-log-label {
        width: 8rem;
    }

    .mail-log-preview {
        min-height: 32rem;
    }
}
</style>
