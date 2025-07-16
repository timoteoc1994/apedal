<script setup lang="ts">
import NavFooter from '@/Components/NavFooter.vue';
import NavMain from '@/Components/NavMain.vue';
import NavUser from '@/Components/NavUser.vue';
import IconDashboard from '@/Components/icons/IconDashboard.vue';
import IconSolicitudes from '@/Components/icons/IconSolicitudes.vue';
import IconAsociaciones from '@/Components/icons/IconAsociaciones.vue';
import IconCiudadanos from '@/Components/icons/IconCiudadanos.vue';
import IconMapa from '@/Components/icons/IconMapa.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/Components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, Building } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: IconDashboard,
    },
    {
        title: 'Asociaciones',
        href: route('asociation.index'),
        icon: IconAsociaciones,
    },
    {
        title: 'Ciudades',
        href: route('ciudad.index'),
        icon: Building,
    },
    {
        title: 'Mapa',
        href: route('zonas.mapa'),
        icon: IconMapa,
    },
    {
        title: 'Version',
        href: route('versions.index'),
        icon: Building,
    },
    {
        title: 'Puntos',
        href: route('puntos.edit'),
        icon: Folder,
    },
    {
        title: 'Ciudadanos',
        href: route('ciudadano.index'),
        icon: IconCiudadanos,
    },
    {
        title: 'Recicladores',
        href: route('reciclador.index'),
        icon: Building,
    },
    {
        title: 'Solicitudes',
        href: route('solicitud.listar'),
        icon: Building,
    },
    
];

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
