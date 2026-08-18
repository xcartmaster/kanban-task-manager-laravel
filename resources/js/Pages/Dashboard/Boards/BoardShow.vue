<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useLocalStorage } from '@vueuse/core';
import { useTemplateRef } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CustomScrollbar from "@/Components/Boards/CustomScrollbar.vue"; // Imports our decoupled hand-made scrollbar component
import CustomScrollbarToggle from "@/Components/Boards/CustomScrollbarToggle.vue";
import BoardColumn from "@/Components/Boards/BoardColumn.vue";

// Import the strictly typed interface directly from the child component node
import type { Column } from "@/Components/Boards/BoardColumn.vue";

/**
 * TypeScript interfaces perfectly mapped to the database schema metrics.
 * Supports strict type safety for Eloquent relational models injected via Inertia.
 * Define the main Board interface, embedding the Column structure
 * imported directly from the BoardColumn.vue component.
 */
interface Board {
    id: number;
    user_id: number;
    name: string;
    slug: string;
    position: number;
    created_at: string | null;
    updated_at: string | null;
    /**
     * Using the exported Column type ensures that PhpStorm provides full
     * autocomplete and error highlighting for deeply nested columns and tasks.
     */
    columns: Column[];
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
                        <!-- Columns Loop using dynamic sub-component extraction -->
                        <BoardColumn
                            v-for="column in board.columns"
                            :key="column.id"
                            :column
                        />
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
