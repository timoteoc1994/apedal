<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Link, usePage } from '@inertiajs/vue3';
import { Head } from "@inertiajs/vue3";
defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <div class="">
            <div class="overflow-hidden shadow-sm sm:rounded-lg space-y-6">
                <div class="p-6 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-6 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div v-if="user.role == 'Administrador'" class="p-6 sm:p-8 bg-white shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
