<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import axios from 'axios'
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthBase from '@/layouts/AuthLayout.vue'
import { login } from '@/routes'

interface Props {
    token?: string
}

const props = defineProps<Props>()

const name = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const processing = ref(false)
const errors = ref<Record<string, string>>({})
const success = ref(false)
const successMessage = ref('')

const token = computed(() => props.token || '')

const validateForm = () => {
    errors.value = {}

    if (!name.value) {
        errors.value.name = 'Name is required'
    }

    if (!password.value) {
        errors.value.password = 'Password is required'
    } else if (password.value.length < 6) {
        errors.value.password = 'Password must be at least 6 characters'
    }

    if (!passwordConfirmation.value) {
        errors.value.password_confirmation = 'Please confirm your password'
    } else if (password.value !== passwordConfirmation.value) {
        errors.value.password_confirmation = 'Passwords do not match'
    }

    return Object.keys(errors.value).length === 0
}

const acceptInvitation = async () => {
    if (!validateForm()) return

    processing.value = true
    errors.value = {}

    try {
        const response = await axios.post('/api/accept-invite', {
            token: token.value,
            name: name.value,
            password: password.value
        })

        success.value = true
        successMessage.value = 'Account created successfully! Redirecting to dashboard...'

        setTimeout(() => {
            window.location.replace('/dashboard')
        }, 1500)

    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else if (error.response?.data?.message) {
            errors.value.general = error.response.data.message
        } else {
            errors.value.general = 'Failed to accept invitation. Please try again.'
        }
    } finally {
        processing.value = false
    }
}

onMounted(() => {
    if (!token.value) {
        errors.value.general = 'Invalid or missing invitation token'
    }
})
</script>

<template>
    <AuthBase title="Accept Invitation" description="Complete your registration to join the team">

        <Head title="Accept Invitation" />

        <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700 text-center">{{ successMessage }}</p>
        </div>

        <form v-else @submit.prevent="acceptInvitation" class="flex flex-col gap-6">
            <div v-if="errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 text-sm">{{ errors.general }}</p>
            </div>

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
                    <Input id="name" v-model="name" type="text" required autofocus :tabindex="1" autocomplete="name"
                        placeholder="John Doe" :disabled="processing || !token" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" v-model="password" type="password" required :tabindex="2"
                        autocomplete="new-password" placeholder="At least 6 characters"
                        :disabled="processing || !token" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input id="password_confirmation" v-model="passwordConfirmation" type="password" required
                        :tabindex="3" autocomplete="new-password" placeholder="Confirm your password"
                        :disabled="processing || !token" />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" :tabindex="4" :disabled="processing || !token"
                    data-test="accept-invitation-button">
                    <Spinner v-if="processing" />
                    Accept Invitation
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="5">
                    Log in
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
