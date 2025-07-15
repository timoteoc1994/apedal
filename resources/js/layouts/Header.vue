<template>
    <header class="flex items-center justify-between border-b-4  bg-white px-6 py-4" style="border-color: #fb6185;">
        <div class="flex items-center">
            <button @click="$page.props.showingMobileMenu = !$page.props.showingMobileMenu" class="text-gray-500 focus:outline-none lg:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <dropdown>
                <template #trigger>
                    <button @click="dropdownOpen = ! dropdownOpen" class="relative flex items-center gap-2">
                        <template v-if="$page.props.auth.user.logo_url">
                            <img
                                :src="'storage/'+$page.props.auth.user.logo_url"
                                alt="Avatar"
                                class="h-8 w-8 rounded-full border object-cover"
                            />
                        </template>
                        <template v-else>
                            <span class="h-8 w-8 flex items-center justify-center rounded-full bg-indigo-200 text-indigo-700 font-bold uppercase">
                                {{ getInitials($page.props.auth.user.name) }}
                            </span>
                        </template>
                        <span>{{ $page.props.auth.user.name }}</span>
                    </button>
                </template>

                <template #content>
                    <dropdown-link :href="route('profile.edit')">
                        Perfil
                    </dropdown-link>

                    <dropdown-link class="w-full text-left" :href="route('logout')" method="post" as="button">
                        Cerrar sesión
                    </dropdown-link>
                </template>
            </dropdown>
        </div>
    </header>
</template>

<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// Función para obtener iniciales
function getInitials(name) {
    if (!name) return '';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}
</script>