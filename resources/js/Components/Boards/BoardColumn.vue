<script setup lang="ts">
import TaskCard from "@/Components/Boards/TaskCard.vue";

// Import the strictly typed interface directly from the child component node
import type { Task } from "@/Components/Boards/TaskCard.vue";

export interface Column {
    id: number;
    board_id: number;
    name: string;
    position: number;
    created_at: string | null;
    updated_at: string | null;
    tasks: Task[];              // Eager-loaded nested tasks array
}

const { column } = defineProps<{
    column: Column;
}>();
</script>

<template>
    <div class="w-72 shrink-0 bg-white dark:bg-zinc-900 border border-gray-200/60 dark:border-zinc-800/80 p-4 rounded-2xl shadow-sm pointer-events-auto">
        <h4 class="font-bold text-gray-800 dark:text-zinc-200 mb-3">{{ column.name }}</h4>
        <div class="space-y-3">
            <!-- Tasks Loop -->
            <TaskCard
                v-for="task in column.tasks"
                :key="task.id"
                :task
            />
        </div>
    </div>
</template>
