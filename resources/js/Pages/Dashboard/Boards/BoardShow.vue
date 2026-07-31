<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CustomScrollbar from "@/Components/Boards/CustomScrollbar.vue"; // Imports our decoupled hand-made scrollbar component
import { ref } from 'vue';

const props = defineProps({
    board: { type: Object, required: true }
});

// Toggle switch to enable or disable the handmade custom scrollbar system for testing environments
const isCustomScrollbarActive = ref(true);

// Dynamic DOM element references (Template Refs automatically bound by Vue 3)
const kanbanViewportRef = ref(null); // The main horizontal columns wrapper layout
</script>

<template>
    <Head :title="`Boards :: ${board.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                {{ board.name }}
            </h2>
        </template>

        <!-- Locked layout canvas boundary viewport box -->
        <div class="w-full bg-gray-50 dark:bg-zinc-950/40 overflow-hidden p-6">
            <div class="w-full mx-auto">

                <!--
                  VIEWPORT WRAPPER: Dynamic classes adapt layout rules based on whether the custom scrollbar is active.
                  - If active: 'overflow-x-hidden' kills native scrollbars, and 'touch-none' secures precise drag math.
                  - If disabled: 'overflow-x-auto' safely restores standard native browser scrollbars for layout debugging.
                -->
                <div
                    ref="kanbanViewportRef"
                    :class="[
                        'w-full transition-all duration-150 pb-4',
                        isCustomScrollbarActive ? 'overflow-x-hidden touch-none' : 'overflow-x-auto touch-auto'
                    ]"
                >
                    <!--
                      THE INNER CANVAS: This 'w-fit mx-auto' wrapper centers the entire board when there are few columns.
                      Once columns overflow the viewport, 'mx-auto' naturally falls back to the left edge,
                      while 'justify-start' guarantees bulletproof, static coordinate matrices for Drag and Drop operations.
                    -->
                    <div class="flex pb-2 items-start select-none justify-start space-x-6 w-fit mx-auto">
                        <!-- Columns Loop -->
                        <div v-for="column in board.columns" :key="column.id" class="w-72 shrink-0 bg-white dark:bg-zinc-900 border border-gray-200/60 dark:border-zinc-800/80 p-4 rounded-2xl shadow-sm pointer-events-auto">
                            <h4 class="font-bold text-gray-800 dark:text-zinc-200 mb-3">{{ column.name }}</h4>
                            <div class="space-y-3">
                                <!-- Tasks Loop -->
                                <div v-for="task in column.tasks" :key="task.id" class="p-3 bg-gray-50/70 hover:bg-white dark:bg-zinc-800/50 dark:hover:bg-zinc-800 shadow-xs hover:shadow-md border border-gray-100/70 dark:border-zinc-700/50 rounded-xl text-gray-900 dark:text-zinc-300 text-sm transition-all duration-200 cursor-pointer transform hover:-translate-y-0.5">
                                    {{ task.title }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reusable Scrollbar Component: Mounts dynamically based on testing environment states -->
                <CustomScrollbar
                    v-if="isCustomScrollbarActive"
                    :parent-container-ref="kanbanViewportRef"
                />

            </div>
        </div>
    </AuthenticatedLayout>
</template>
