<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/**
 * Declare local interfaces matching the new layout data metrics
 */
interface BoardPivot {
    role: string;
}

interface Board {
    id: number;
    name: string;
    slug: string;
    pivot: BoardPivot;
    tasks_count?: number;    // Injected directly from our optimized controller query
    comments_count?: number; // Injected directly from our optimized controller query
}

/**
 * Leverage Vue 3.5+ Type-Based Prop Destructuring
 */
const { board } = defineProps<{
    board: Board;
}>();

// Extract the user role safely from Laravel pivot data
const userRole = computed<string>(() => board?.pivot?.role || 'member');
</script>

<template>
    <!-- Main board card container with responsive design, theme modes and hover effects -->
    <div class="p-5 min-h-[116px] bg-white dark:bg-zinc-800 shadow-sm hover:shadow-md rounded-xl border border-gray-100 dark:border-zinc-700 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-1">
                <h4 class="font-semibold text-base text-gray-900 dark:text-white line-clamp-1">
                    <Link :href="route('boards.show', {board: board.slug})">{{ board.name }}</Link>
                </h4>

                <!-- Dynamic role badge with conditional coloring -->
                <span
                    class="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded"
                    :class="userRole === 'owner'
                        ? 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30'
                        : 'text-purple-600 bg-purple-50 dark:text-purple-400 dark:bg-purple-900/30'"
                >
                    {{ userRole }}
                </span>
            </div>
        </div>

        <!-- Board Metrics Footer Section -->
        <div class="flex items-center space-x-4 mt-4 pt-2 border-t border-gray-50 dark:border-zinc-700/50 text-xs text-gray-400 dark:text-zinc-500">
            <div class="flex items-center space-x-1" title="Total Tasks">
                <span>✅</span>
                <span class="font-medium text-gray-600 dark:text-zinc-400">{{ board.tasks_count ?? 0 }} tasks</span>
            </div>

            <div class="flex items-center space-x-1" title="Total Comments Across All Tasks">
                <span>💬</span>
                <span class="font-medium text-gray-600 dark:text-zinc-400">{{ board.comments_count ?? 0 }} comments</span>
            </div>
        </div>
    </div>
</template>
