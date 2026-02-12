<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import type { ShortUrl, PaginatedResponse, User } from '@/types/auth'

const page = usePage()
const user = computed(() => page.props.auth.user as User)
const baseUrl = computed(() => window.location.origin)

// URL generator
const newUrl = ref('')
const generatedUrl = ref<ShortUrl | null>(null)
const generateLoading = ref(false)
const generateError = ref('')

// URLs data (own URLs only)
const urls = ref<ShortUrl[]>([])
const urlsLoading = ref(false)
const urlPage = ref(1)
const urlTotalPages = ref(1)
const urlSearch = ref('')

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
})
</script>

<template>
    <div class="min-h-screen" style="background-color: #E8F5E9;">
        <!-- Header -->
        <header class="px-6 py-4 flex justify-between items-center"
            style="background-color: #43A047; border-bottom: 2px solid #2E7D32;">
            <div>
                <h1 class="text-xl font-bold text-black">Member Dashboard</h1>
                <p class="text-black text-sm">{{ user?.company?.name || 'Your Company' }}</p>
            </div>
            <button @click="logout" class="px-4 py-2 bg-gray-200 text-black rounded hover:bg-gray-300">
                Logout →
            </button>
        </header>

        <div class="p-6 space-y-8 text-black">
            <!-- URL Generator Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid #43A047;">
                <h2 class="text-lg font-semibold mb-4 text-black">Generate Short URL</h2>

                <div class="flex gap-2">
                    <input v-model="newUrl" type="url" placeholder="Enter your long URL here..."
                        class="flex-1 px-4 py-2 border rounded focus:ring-2 focus:ring-green-400 focus:outline-none "
                        @keyup.enter="generateShortUrl" />
                    <button @click="generateShortUrl" :disabled="generateLoading || !newUrl"
                        class="px-6 py-2 text-black rounded hover:opacity-90 disabled:opacity-50"
                        style="background-color: #43A047;">
                        {{ generateLoading ? 'Generating...' : 'Generate' }}
                    </button>
                </div>

                <div v-if="generateError" class="mt-3 p-3 bg-red-100 text-red-700 rounded text-sm">
                    {{ generateError }}
                </div>

                <div v-if="generatedUrl" class="mt-3 p-3 rounded flex items-center justify-between"
                    style="background-color: #E8F5E9;">
                    <div>
                        <span class="text-sm text-black">Short URL:</span>
                        <code class="ml-2 text-black font-mono">{{ baseUrl }}/api/s/{{ generatedUrl.short_code }}</code>
                    </div>
                    <button @click="copyGeneratedUrl" class="px-3 py-1 text-black rounded text-sm"
                        style="background-color: #43A047;">
                        {{ copiedCode === generatedUrl.short_code ? '✓ Copied!' : 'Copy' }}
                    </button>
                </div>
            </section>

            <!-- My Short URLs Section -->
            <section class="bg-white rounded-lg shadow-md p-6" style="border: 2px solid #43A047;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">My Short URLs</h2>
                </div>

                <div v-if="urlsLoading" class="text-center py-4">Loading...</div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b" style="background-color: #E8F5E9;">
                            <th class="text-left p-3 text-black">Short URL</th>
                            <th class="text-left p-3 text-black">Base URL</th>
                            <th class="text-left p-3 text-black">Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="url in urls" :key="url.id" class="border-b hover:bg-green-50">
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <code class="text-black">/s/{{ url.short_code }}</code>
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
                        </tr>
                        <tr v-if="urls.length === 0">
                            <td colspan="4" class="text-center p-4 text-gray-500">
                                No URLs yet. Create your first short URL above!
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</template>
