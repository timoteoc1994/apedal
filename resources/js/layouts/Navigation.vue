<template>
    <div :class="$page.props.showingMobileMenu ? 'block' : 'hidden'" @click="$page.props.showingMobileMenu = false"
         class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden"></div>

    <div :class="$page.props.showingMobileMenu ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
         class="overflow-y-auto fixed inset-y-0 left-0 z-30 w-60  transition duration-300 transform lg:translate-x-0 lg:static lg:inset-0" style="background-color: #5849e2;">
        <div class="flex justify-center items-center mt-8">
            <div class="flex items-center">
                <Link :href="route('dashboard')" class="flex items-center">
                    <img src="/storage/plataforma/logoplataforma.png" width="150px" alt="">

                </Link>
            </div>
        </div>

        <nav class="mt-10" x-data="{ isMultiLevelMenuOpen: false }">
            <nav-link :href="route('dashboard')" :active="route().current('dashboard')">
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="m0,9.756h14v5.244H0v-5.244Zm0,8c0,2.757,2.243,5,5,5h9v-5.756H0v.756Zm16,5h3c2.757,0,5-2.243,5-5v-8h-8v13ZM24,6v2H0v-2C0,3.243,2.243,1,5,1h14c2.757,0,5,2.243,5,5Zm-18-1.5c0-.828-.672-1.5-1.5-1.5s-1.5.672-1.5,1.5.672,1.5,1.5,1.5,1.5-.672,1.5-1.5Zm4,0c0-.828-.672-1.5-1.5-1.5s-1.5.672-1.5,1.5.672,1.5,1.5,1.5,1.5-.672,1.5-1.5Zm4,0c0-.828-.672-1.5-1.5-1.5s-1.5.672-1.5,1.5.672,1.5,1.5,1.5,1.5-.672,1.5-1.5Z"></path></svg>
                </template>
                Inicio
            </nav-link>

            <p class="text-white ml-4 mt-4 font-bold">Usuarios</p>
 <nav-link :href="route('asociation.index')" :active="route().current('asociation*')">
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16ZM5.683,16H1a1,1,0,0,1-1-1A6.022,6.022,0,0,1,5.131,9.084a1,1,0,0,1,1.1,1.266A6.009,6.009,0,0,0,6,12a5.937,5.937,0,0,0,.586,2.57,1,1,0,0,1-.9,1.43ZM17,24H7a1,1,0,0,1-1-1,6,6,0,0,1,12,0A1,1,0,0,1,17,24ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8Zm17,8H18.317a1,1,0,0,1-.9-1.43A5.937,5.937,0,0,0,18,12a6.009,6.009,0,0,0-.236-1.65,1,1,0,0,1,1.105-1.266A6.022,6.022,0,0,1,24,15,1,1,0,0,1,23,16Z"></path></svg>
                </template>
                Asociaciones
            </nav-link>
             <nav-link :href="route('ciudadano.index')" :active="route().current('ciudadano.index*')">
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M6.365,3.773c-1.026-1.026-2.705-1.026-3.731,0l-1.949,1.949c-.439,.439-.685,1.034-.685,1.654v11.623c0,2.761,2.239,5,5,5h4V7.377c0-.621-.247-1.216-.686-1.655l-1.949-1.949Zm-1.365,15.227h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Z"></path><path d="M19,0h-3c-2.761,0-5,2.239-5,5V24h8c2.761,0,5-2.239,5-5V5c0-2.761-2.239-5-5-5Zm-3,19h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm4,12h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Zm0-4h-1c-.552,0-1-.448-1-1s.448-1,1-1h1c.552,0,1,.448,1,1s-.448,1-1,1Z"></path></svg>
                </template>
                Ciudadanos
            </nav-link>

            <p class="text-white ml-4 mt-4 font-bold">Configuracion</p>
            <nav-link :href="route('ciudad.index')" :active="route().current('ciudad.index')">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </template>
                Ciudades
            </nav-link>
            <nav-link :href="route('zonas.mapa')" :active="route().current('zonas.mapa')">
                <template #icon>
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11.536,1.465C9.674-.475,6.326-.475,4.464,1.465c-.944,.944-1.464,2.199-1.464,3.535s.52,2.591,1.472,3.543l1.913,1.871c.89,.871,2.34,.871,3.23,0l1.921-1.879c.944-.944,1.464-2.199,1.464-3.535s-.52-2.591-1.464-3.535Zm-3.536,5.529c-1.105,0-2-.895-2-2s.895-2,2-2,2,.895,2,2-.895,2-2,2Zm16,.006s-2.007,4.037-2,4.5c.009,.575,0,1.5,0,2.5,0,.5-.34,.805-.55,1.17-.378,.657-1.004,1.39-1.355,2.059-.155,.301-.17,.741-.056,1.037,.257,.656,.58,1.265,.915,1.89,.14,.261,.251,.529,.309,.813,.109,.533-.295,1.032-.839,1.031h-.014c-.279,0-.537-.132-.7-.354-.345-.469-.711-1.141-.711-1.647,0-1-1.383-1-2-1-.419,0-.662,.175-.804,.379-.274,.394-.73,.621-1.21,.621h-.987c-.331,.24-.907,1.978-1.356,3-.325,.74-1.313,.877-1.828,.255-.324-.391-.635-1.355-.816-1.716-.169-.339-.731-.469-1.217-.517-.493-.049-.926-.35-1.157-.788-.041-.078-.084-.157-.127-.233-.508-.896-2.423-1.821-4-2-.286-.032-.561,.026-.815,.126-.442,.174-.945-.019-1.174-.436-.813-1.482-1.511-3.467-1.511-5.171v-1.256c0-1.356,.499-3.424,.792-4.52,.054-.202,.172-.371,.323-.499,.251,1.398,.918,2.689,1.958,3.729l1.913,1.871c1.59,1.62,4.438,1.621,6.027,0l1.937-1.895c1.035-1.035,1.7-2.339,1.942-3.752,.596,.14,1.235,.301,1.781,.445,.782,.205,1.327,.911,1.327,1.72v1.138c0,.5,.844,.201,1.125-.096,.35-.371,1.061-1.307,1.499-1.895,.198-.266,.465-.473,.775-.59,.365-.138,.568-.442,.601-.918,.015-.217-.006-.842-.013-1.35-.004-.361,.29-.654,.652-.647,.005,0,.009,0,.014,0,.232,.004,.443,.133,.552,.337,.344,.649,.795,1.78,.795,1.78v.88Z"></path></svg>
                </template>
                Zonas
            </nav-link>
            <nav-link :href="route('versions.index')" :active="route().current('versions.index')">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </template>
                Versión APK
            </nav-link>
            <nav-link :href="route('puntos.edit')" :active="route().current('puntos.edit')">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </template>
                Puntos
            </nav-link>
            <nav-link :href="route('notificacionpush.create')" :active="route().current('notificacionpush.create')">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </template>
                Notificaciones push
            </nav-link>


            <nav-link :href="route('users.index')" :active="route().current('users.index')">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </template>
                Users
            </nav-link>

           

           
        </nav>
    </div>

</template>

<script>
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue'

export default {
    components: {
        NavLink,
        Link,
    },

    setup() {
        let showingTwoLevelMenu = ref(false)

        return {
            showingTwoLevelMenu
        }
    },
}
</script>

<style scoped>

</style>
