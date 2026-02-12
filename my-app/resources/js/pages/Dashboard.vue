<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import SuperAdminDashboard from './dashboards/SuperAdminDashboard.vue'
import AdminDashboard from './dashboards/AdminDashboard.vue'
import MemberDashboard from './dashboards/MemberDashboard.vue'
import type { User } from '@/types/auth'

const page = usePage()
const user = computed(() => page.props.auth?.user as User | null)
const role = computed(() => user.value?.role || 'Member')
</script>

<template>
    <!-- Role-based dashboard routing -->
    <SuperAdminDashboard v-if="role === 'SuperAdmin'" />
    <AdminDashboard v-else-if="role === 'Admin'" />
    <MemberDashboard v-else />
</template>