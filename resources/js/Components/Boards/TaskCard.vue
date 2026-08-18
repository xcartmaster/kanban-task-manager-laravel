<script setup lang="ts">
export interface Task {
    id: number;
    column_id: number;
    title: string;
    description: string | null; // Nullable in database (YES)
    position: number;
    start_at: string | null;    // Nullable timestamp
    due_at: string | null;      // Nullable timestamp
    created_at: string | null;
    updated_at: string | null;
    comments_count?: number;    // Generated asynchronously by Laravel's withCount('comments')
}

const { task } = defineProps<{
    task: Task;
}>();
</script>

<template>
    <div class="p-3 bg-gray-50/70 hover:bg-white dark:bg-zinc-800/50 dark:hover:bg-zinc-800 shadow-xs hover:shadow-md border border-gray-100/70 dark:border-zinc-700/50 rounded-xl text-gray-900 dark:text-zinc-300 text-sm transition-all duration-200 cursor-pointer transform hover:-translate-y-0.5">
        {{ task.title }}

        <!-- Displays the comment counter badge only if the count exists and is greater than zero -->
        <div v-if="task.comments_count" class="mt-2 flex items-center space-x-1 text-xs text-gray-400 dark:text-zinc-500">
            <!-- Tiny decorative comment icon using plain text or your SVG icon -->
            <span>💬</span>
            <span>{{ task.comments_count }}</span>
        </div>
    </div>
</template>
