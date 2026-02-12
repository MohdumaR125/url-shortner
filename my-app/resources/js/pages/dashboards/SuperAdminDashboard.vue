<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import type { Company, ShortUrl, PaginatedResponse, User } from '@/types/auth'

const page = usePage()
const user = computed(() => page.props.auth.user as User)

// Companies data
const companies = ref<Company[]>([])
const companiesLoading = ref(false)
const companyPage = ref(1)
const companyTotalPages = ref(1)

// URLs data
const urls = ref<ShortUrl[]>([])
const urlsLoading = ref(false)
const urlPage = ref(1)
const urlTotalPages = ref(1)
const urlSearch = ref('')

// Invite modal
const showInviteModal = ref(false)
const inviteEmail = ref('')
const inviteLoading = ref(false)
const inviteSuccess = ref('')
const inviteError = ref('')
const inviteUrl = ref('')
const inviteLinkCopied = ref(false)

// Copy functionality
const copiedCode = ref<string | null>(null)

const loadCompanies = async () => {
    companiesLoading.value = true
    try {
        const res = await axios.get<Company[]>('/api/companies')
        companies.value = res.data
    } catch (e) {
        console.error('Error loading companies:', e)
        companies.value = []
    } finally {
        companiesLoading.value = false
    }
}

const loadUrls = async () => {
    urlsLoading.value = true
    try {
        const res = await axios.get<ShortUrl[]>('/api/short-urls')
        urls.value = res.data
    } catch (e) {
        console.error('Error loading URLs:', e)
        urls.value = []
    } finally {
        urlsLoading.value = false
    }
}
const baseUrl = computed(() => window.location.origin)

const inviteAdmin = async () => {
    if (!inviteEmail.value) return

    inviteLoading.value = true
    inviteSuccess.value = ''
    inviteError.value = ''
    inviteLinkCopied.value = false

    try {
        const res = await axios.post('/api/invite', {
            email: inviteEmail.value,
            role: 'Admin'
        })
        inviteUrl.value = `${window.location.origin}/accept-invitation?token=${res.data.token}`
        inviteSuccess.value = 'Invitation sent successfully!'
        inviteEmail.value = ''
        loadCompanies()
    } catch (e: any) {
        inviteError.value = e.response?.data?.message || 'Failed to send invitation'
    } finally {
        inviteLoading.value = false
    }
}


const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const logout = async () => {
    await axios.post('/logout')
    window.location.href = '/login'
}

onMounted(() => {
    loadCompanies()
    loadUrls()
})
</script>

<template>
    <div class="min-h-screen text-black" style="background-color: #FFF9C4;">
        <!-- Header -->
        <header class="px-6 py-4 flex justify-between items-center"
            style="background-color: #F9A825; border-bottom: 2px solid #F57F17;">
            <h1 class="text-xl font-bold text-gray-800">Super Admin Dashboard</h1>
            <button @click="logout" class="px-4 py-2 bg-gray-200 text-black rounded hover:bg-gray-300">
                Logout →
            </button>
        </header>

        <div class="p-6 space-y-8">
            <!-- Clients Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid #020202;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-black">Clients</h2>
                    <button @click="showInviteModal = true" class="px-4 py-2 text-black rounded hover:opacity-90"
                        style="background-color: #F9A825;">
                        Invite
                    </button>
                </div>

                <div v-if="companiesLoading" class="text-center py-4">Loading...</div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b" style="background-color: #FFF9C4;">
                            <th class="text-left p-3 text-black">Client Name</th>
                            <th class="text-center p-3 text-black">Total Users</th>
                            <th class="text-center p-3 text-black">Total URLs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies" :key="company.id" class="border-b hover:bg-yellow-50">
                            <td class="p-3 text-black">{{ company.name }}</td>
                            <td class="text-center p-3 text-black">{{ company.users_count || 0 }}</td>
                            <td class="text-center p-3 text-black">{{ company.short_urls_count || 0 }}</td>
                        </tr>
                        <tr v-if="companies.length === 0">
                            <td colspan="3" class="text-center p-4 text-gray-500">No companies found</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Generated Short URLs Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid #F9A825;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-black">Generated Short URLs</h2>
                </div>

                <div v-if="urlsLoading" class="text-center py-4">Loading...</div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b" style="background-color: #FFF9C4;">
                            <th class="text-left p-3 text-black">Short URL</th>
                            <th class="text-left p-3 text-black">Base URL</th>
                            <th class="text-left p-3 text-black">Client</th>
                            <th class="text-left p-3 text-black">Created Date</th>
                            <th class="text-left p-3 text-black">Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="url in urls" :key="url.id" class="border-b hover:bg-yellow-50">
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <code class="text-black">{{ baseUrl }}/api/s/{{ url.short_code }}</code>
                                </div>
                            </td>
                            <td class="p-3 max-w-xs truncate" :title="url.original_url">
                                <a :href="url.original_url" target="_blank" class="text-black hover:underline">
                                    {{ url.original_url }}
                                </a>
                            </td>
                            <td class="p-3">{{ url.company?.name || 'N/A' }}</td>
                            <td class="p-3">{{ formatDate(url.created_at) }}</td>
                            <td class="p-3">{{ url.creator?.name || 'N/A' }}</td>
                        </tr>
                        <tr v-if="urls.length === 0">
                            <td colspan="5" class="text-center p-4 text-gray-500">No URLs found</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Invite Modal -->
        <div v-if="showInviteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 text-black">
            <div class="bg-white rounded-lg p-6 w-full max-w-md" style="border: 2px solid #F9A825;">
                <h3 class="text-lg font-semibold mb-4 text-black">Invite New Client</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Admin Email</label>
                        <input v-model="inviteEmail" type="email" placeholder="admin@newcompany.com"
                            class="w-full px-3 py-2 border rounded" />
                    </div>

                    <div v-if="inviteSuccess" class="p-3 bg-green-100 text-green-700 rounded">
                        <p class="text-sm font-medium mb-2">{{ inviteSuccess }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <input :value="inviteUrl" readonly
                                class="flex-1 px-2 py-1 text-xs bg-white border rounded font-mono" />
                        </div>
                    </div>

                    <div v-if="inviteError" class="p-3 bg-red-100 text-red-700 rounded text-sm">
                        {{ inviteError }}
                    </div>

                    <div class="flex justify-end gap-2">
                        <button @click="showInviteModal = false; inviteSuccess = ''; inviteError = ''"
                            class="px-4 py-2 border rounded hover:bg-gray-100">
                            Cancel
                        </button>
                        <button @click="inviteAdmin" :disabled="inviteLoading || !inviteEmail"
                            class="px-4 py-2 text-black rounded disabled:opacity-50" style="background-color: #F9A825;">
                            {{ inviteLoading ? 'Sending...' : 'Send Invitation' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
