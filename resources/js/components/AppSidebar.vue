<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import IconDashboard from '@/components/icons/IconDashboard.vue';
import IconSolicitudes from '@/components/icons/IconSolicitudes.vue';
import IconAsociaciones from '@/components/icons/IconAsociaciones.vue';
import IconCiudadanos from '@/components/icons/IconCiudadanos.vue';
import IconMapa from '@/components/icons/IconMapa.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
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
