<script setup>
import { computed } from 'vue';

// Define the incoming props for the board object
const props = defineProps({
    board: {
        type: Object,
        required: true
    }
});

// Extract the user role safely from Laravel pivot data
const userRole = computed(() => props.board?.pivot?.role || 'member');

// Calculate total tasks across all columns inside this board
const totalTasksCount = computed(() => {
    if (!props.board.columns) return 0;
    return props.board.columns.reduce((sum, column) => {
        return sum + (column.tasks ? column.tasks.length : 0);
    }, 0);
});

// Calculate columns count safely
const columnsCount = computed(() => props.board.columns ? props.board.columns.length : 0);
</script>

<template>
    <!-- Main board card container with responsive design, theme modes and hover effects -->
    <div class="p-5 min-h-[116px] bg-white dark:bg-zinc-800 shadow-sm hover:shadow-md rounded-xl border border-gray-100 dark:border-zinc-700 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-1">
                <h4 class="font-semibold text-base text-gray-900 dark:text-white line-clamp-1">
                    {{ board.name }}
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
            <p class="text-gray-400 dark:text-zinc-500 text-xs truncate">Slug: {{ board.slug }}</p>
        </div>

        <!-- Board Metrics Footer Section -->
        <div class="flex items-center space-x-4 mt-4 pt-2 border-t border-gray-50 dark:border-zinc-700/50 text-xs text-gray-400 dark:text-zinc-500">
            <div class="flex items-center space-x-1" title="Total Columns">
                <span>📋</span>
                <span class="font-medium text-gray-600 dark:text-zinc-400">{{ columnsCount }} cols</span>
            </div>
            <div class="flex items-center space-x-1" title="Total Tasks">
                <span>✅</span>
                <span class="font-medium text-gray-600 dark:text-zinc-400">{{ totalTasksCount }} tasks</span>
            </div>
        </div>
    </div>
</template>
