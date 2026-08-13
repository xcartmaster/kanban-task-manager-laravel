<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useLocalStorage } from '@vueuse/core';
import { useTemplateRef } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CustomScrollbar from "@/Components/Boards/CustomScrollbar.vue"; // Imports our decoupled hand-made scrollbar component
import CustomScrollbarToggle from "@/Components/Boards/CustomScrollbarToggle.vue";

/**
 * TypeScript interfaces perfectly mapped to the database schema metrics.
 * Supports strict type safety for Eloquent relational models injected via Inertia.
 */
interface Task {
    id: number;
    column_id: number;
    title: string;
    description: string | null; // Nullable in database (YES)
    position: number;
    start_at: string | null;     // Nullable timestamp
    due_at: string | null;       // Nullable timestamp
    created_at: string | null;
    updated_at: string | null;
    comments_count?: number; // Generated asynchronously by Laravel's withCount('comments')
}

interface Column {
    id: number;
    board_id: number;
    name: string;
    position: number;
    created_at: string | null;
    updated_at: string | null;
    tasks: Task[];                // Eager-loaded nested tasks array
}

interface Board {
    id: number;
    user_id: number;
    name: string;
    slug: string;
    position: number;
    created_at: string | null;
    updated_at: string | null;
    columns: Column[];            // Eager-loaded nested columns array
}

/**
 * Leverage Vue 3.5+ Type-Based Prop Destructuring syntax.
 * The 'props' object is implicitly typed using the TS generic block.
 */
const { board } = defineProps<{
    board: Board;
}>();

/**
 * Creates a reactive reference synchronized with the browser's localStorage.
 * Explicitly typed as a boolean stream using TypeScript generics.
 */
const isCustomScrollbarEnabled = useLocalStorage<boolean>('app_custom_scrollbar_enabled', false);

/**
 * Dynamic DOM element references explicitly cast as an HTML Div element type
 * to secure precise canvas coordinate metrics calculations in the future.
 */
const kanbanViewportRef = useTemplateRef<HTMLDivElement>('kanbanViewportRef');
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
                        isCustomScrollbarEnabled ? 'overflow-x-hidden touch-none' : 'overflow-x-auto touch-auto'
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

                                    <!-- Displays the comment counter badge only if the count exists and is greater than zero -->
                                    <div v-if="task.comments_count" class="mt-2 flex items-center space-x-1 text-xs text-gray-400 dark:text-zinc-500">
                                        <!-- Tiny decorative comment icon using plain text or your SVG icon -->
                                        <span>💬</span>
                                        <span>{{ task.comments_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mounts or unmounts the custom scrollbar element dynamically based on the stored feature flag state -->
                <CustomScrollbar
                    v-if="isCustomScrollbarEnabled"
                    :parent-container-ref="kanbanViewportRef"
                />

                <!--
                  Passes down the synchronized boolean state to the child toggle component.
                  Listens to the emitted event and updates the reactive useLocalStorage variable inline using $event.
                <CustomScrollbarToggle
                    :is-custom-scrollbar-enabled="isCustomScrollbarEnabled"
                    @custom-scrollbar-toggle="isCustomScrollbarEnabled = $event"
                />
                -->
                <CustomScrollbarToggle v-model="isCustomScrollbarEnabled" />

            </div>
        </div>
    </AuthenticatedLayout>
</template>
