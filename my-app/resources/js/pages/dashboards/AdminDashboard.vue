<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import type { ShortUrl, TeamMember, PaginatedResponse, User } from '@/types/auth'

const page = usePage()
const user = computed(() => page.props.auth.user as User)
const baseUrl = computed(() => window.location.origin)

// URL generator
const newUrl = ref('')
const generatedUrl = ref<ShortUrl | null>(null)
const generateLoading = ref(false)
const generateError = ref('')

// URLs data
const urls = ref<ShortUrl[]>([])
const urlsLoading = ref(false)
const urlPage = ref(1)
const urlTotalPages = ref(1)
const urlSearch = ref('')

// Team members data
const teamMembers = ref<TeamMember[]>([])
const teamLoading = ref(false)

// Invite modal
const showInviteModal = ref(false)
const inviteEmail = ref('')
const inviteRole = ref<'Admin' | 'Member'>('Member')
const inviteLoading = ref(false)
const inviteSuccess = ref('')
const inviteError = ref('')
const inviteUrl = ref('')
const inviteLinkCopied = ref(false)

// Copy functionality
const copiedCode = ref<string | null>(null)

const generateShortUrl = async () => {
    if (!newUrl.value) return

    generateLoading.value = true
    generateError.value = ''
    generatedUrl.value = null

    try {
        const res = await axios.post<ShortUrl>('/api/short-urls', {
            original_url: newUrl.value
        })
        generatedUrl.value = res.data
        newUrl.value = ''
        loadUrls() // Refresh list
    } catch (e: any) {
        generateError.value = e.response?.data?.message || 'Failed to generate short URL'
    } finally {
        generateLoading.value = false
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

const loadTeamMembers = async () => {
    teamLoading.value = true
    try {
        const res = await axios.get<PaginatedResponse<TeamMember>>('/api/team-members')
        teamMembers.value = res.data.data || []
    } catch (e) {
        console.error('Error loading team members:', e)
        teamMembers.value = []
    } finally {
        teamLoading.value = false
    }
}

const inviteMember = async () => {
    if (!inviteEmail.value) return

    inviteLoading.value = true
    inviteSuccess.value = ''
    inviteError.value = ''
    inviteLinkCopied.value = false

    try {
        const res = await axios.post('/api/invite', {
            email: inviteEmail.value,
            role: inviteRole.value
        })
        inviteUrl.value = `${window.location.origin}/accept-invitation?token=${res.data.token}`
        inviteSuccess.value = 'Invitation sent successfully!'
        inviteEmail.value = ''
        loadTeamMembers()
    } catch (e: any) {
        inviteError.value = e.response?.data?.message || 'Failed to send invitation'
    } finally {
        inviteLoading.value = false
    }
}

const copyInviteLink = async () => {
    await navigator.clipboard.writeText(inviteUrl.value)
    inviteLinkCopied.value = true
    setTimeout(() => { inviteLinkCopied.value = false }, 2000)
}

const copyToClipboard = async (code: string) => {
    const fullUrl = `${window.location.origin}/api/s/${code}`
    await navigator.clipboard.writeText(fullUrl)
    copiedCode.value = code
    setTimeout(() => { copiedCode.value = null }, 2000)
}

const copyGeneratedUrl = async () => {
    if (generatedUrl.value) {
        await copyToClipboard(generatedUrl.value.short_code)
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
    loadUrls()
    loadTeamMembers()
})
</script>

<template>
    <div class="min-h-screen" style="background-color: #E0F7FA;">
        <!-- Header -->
        <header class="px-6 py-4 flex justify-between items-center"
            style="background-color: #00ACC1; border-bottom: 2px solid #00838F;">
            <div>
                <h1 class="text-xl font-bold text-black">Admin Dashboard</h1>
                <p class="text-black text-sm">{{ user?.company?.name || 'Your Company' }}</p>
            </div>
            <button @click="logout" class="px-4 py-2 bg-gray-200 text-black rounded hover:bg-gray-300">
                Logout →
            </button>
        </header>

        <div class="p-6 space-y-8 text-black">
            <!-- URL Generator Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid #020202;">
                <h2 class="text-lg font-semibold mb-4 text-black">Generate Short URL</h2>

                <div class="flex gap-2">
                    <input v-model="newUrl" type="url" placeholder="Enter your long URL here..."
                        class="flex-1 px-4 py-2 border rounded focus:ring-2 focus:ring-cyan-400 focus:outline-none "
                        @keyup.enter="generateShortUrl" />
                    <button @click="generateShortUrl" :disabled="generateLoading || !newUrl"
                        class="px-6 py-2 text-black rounded hover:opacity-90 disabled:opacity-50"
                        style="background-color: #00ACC1;">
                        {{ generateLoading ? 'Generating...' : 'Generate' }}
                    </button>
                </div>

                <div v-if="generateError" class="mt-3 p-3 bg-red-100 text-red-700 rounded text-sm">
                    {{ generateError }}
                </div>

                <div v-if="generatedUrl" class="mt-3 p-3 rounded flex items-center justify-between"
                    style="background-color: #E0F7FA;">
                    <div>
                        <span class="text-sm text-black">Short URL:</span>
                        <code class="ml-2 text-black font-mono">{{ baseUrl }}/api/s/{{ generatedUrl.short_code }}</code>
                    </div>
                    <button @click="copyGeneratedUrl" class="px-3 py-1 text-black rounded text-sm"
                        style="background-color: #00ACC1;">
                        {{ copiedCode === generatedUrl.short_code ? '✓ Copied!' : 'Copy' }}
                    </button>
                </div>
            </section>

            <!-- Generated Short URLs Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid #020202;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-black">Generated Short URLs</h2>
                </div>

                <div v-if="urlsLoading" class="text-center py-4">Loading...</div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b" style="background-color: #E0F7FA;">
                            <th class="text-left p-3 text-black">Short URL</th>
                            <th class="text-left p-3 text-black">Base URL</th>
                            <th class="text-left p-3 text-black">Created Date</th>
                            <th class="text-left p-3 text-black">Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="url in urls" :key="url.id" class="border-b hover:bg-cyan-50">
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <code class="text-black">{{ baseUrl }}/api/s/{{ url.short_code }}</code>
                                    <button @click="copyToClipboard(url.short_code)"
                                        class="text-xs px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">
                                        {{ copiedCode === url.short_code ? '✓ Copied!' : 'Copy' }}
                                    </button>
                                </div>
                            </td>
                            <td class="p-3 max-w-xs truncate" :title="url.original_url">
                                <a :href="url.original_url" target="_blank" class="text-black hover:underline">
                                    {{ url.original_url }}
                                </a>
                            </td>
                            <td class="p-3">{{ formatDate(url.created_at) }}</td>
                            <td class="p-3">{{ url.creator?.name || 'N/A' }}</td>
                        </tr>
                        <tr v-if="urls.length === 0">
                            <td colspan="4" class="text-center p-4 text-gray-500">No URLs found</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Team Members Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid;">
                <div class="flex justify-between items-center mb-4 text-black">
                    <h2 class="text-lg font-semibold text-black">Team Members</h2>
                    <button @click="showInviteModal = true" class="px-4 py-2 text-black rounded hover:opacity-90"
                        style="background-color: #00ACC1;">
                        Invite
                    </button>
                </div>

                <div v-if="teamLoading" class="text-center py-4">Loading...</div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b" style="background-color: #E0F7FA;">
                            <th class="text-left p-3 text-black">Name</th>
                            <th class="text-left p-3 text-black">Email</th>
                            <th class="text-center p-3 text-black">Role</th>
                            <th class="text-center p-3 text-black">URLs Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="member in teamMembers" :key="member.id" class="border-b hover:bg-cyan-50">
                            <td class="p-3 text-black">{{ member.name }}</td>
                            <td class="p-3 text-black">{{ member.email }}</td>
                            <td class="text-center p-3">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                    :class="member.role === 'Admin' ? 'bg-cyan-100 text-black' : 'bg-gray-100 text-black'">
                                    {{ member.role }}
                                </span>
                            </td>
                            <td class="text-center p-3 text-black">{{ member.urls_count || 0 }}</td>
                        </tr>
                        <tr v-if="teamMembers.length === 0">
                            <td colspan="4" class="text-center p-4 text-gray-500">No team members found</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Invite Modal -->
        <div v-if="showInviteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 text-black">
            <div class="bg-white rounded-lg p-6 w-full max-w-md" style="border: 2px solid #00ACC1;">
                <h3 class="text-lg font-semibold mb-4">Invite Team Member</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input v-model="inviteEmail" type="email" placeholder="teammate@company.com"
                            class="w-full px-3 py-2 border rounded" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Role</label>
                        <select v-model="inviteRole" class="w-full px-3 py-2 border rounded">
                            <option value="Admin">Admin</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>

                    <div v-if="inviteSuccess" class="p-3 bg-green-100 text-green-700 rounded">
                        <p class="text-sm font-medium mb-2">{{ inviteSuccess }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <input :value="inviteUrl" readonly
                                class="flex-1 px-2 py-1 text-xs bg-white border rounded font-mono" />
                            <button @click="copyInviteLink"
                                class="px-3 py-1 text-xs bg-cyan-200 text-black rounded hover:bg-cyan-300">
                                {{ inviteLinkCopied ? '✓ Copied!' : 'Copy Link' }}
                            </button>
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
                        <button @click="inviteMember" :disabled="inviteLoading || !inviteEmail"
                            class="px-4 py-2 text-black rounded disabled:opacity-50" style="background-color: #00ACC1;">
                            {{ inviteLoading ? 'Sending...' : 'Send Invitation' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
