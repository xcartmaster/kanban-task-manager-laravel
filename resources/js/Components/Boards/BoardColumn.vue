<script setup lang="ts">
import TaskCard from "@/Components/Boards/TaskCard.vue";
import {ref, watch} from "vue";
import { VueDraggable } from 'vue-draggable-plus';

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

const localTasks = ref<Task[]>([...column.tasks]);

/**
 * Handles the event triggered immediately after a user finishes dragging a task card.
 * This function will act as a temporary console logger before we plug in the Axios/Inertia persistence logic.
 */
function handleTaskMove(event: any): void {
    console.log('Task drag operation finished safely via local state:', {
        taskId: event.item.getAttribute('data-id'),
        oldIndex: event.oldIndex,
        newIndex: event.newIndex,
        currentColumnId: column.id
    });
}

watch(() => column.tasks, (newTasks) => {
    localTasks.value = [...newTasks];
});
</script>

<template>
    <div class="w-72 shrink-0 bg-white dark:bg-zinc-900 border border-gray-200/60 dark:border-zinc-800/80 p-4 rounded-2xl shadow-sm pointer-events-auto">
        <h4 class="font-bold text-gray-800 dark:text-zinc-200 mb-3">{{ column.name }}</h4>

        <!--
          VueDraggable Container: Replaces the static task wrapper div.
          - v-model="localTasks": SAFELY mutates the fast, isolated local scope array replica.
          - group="board_tasks": Links all column lanes together to enable cross-column card dropping.
          - :animation="150": Fires beautiful hardware-accelerated card swapping physics.
          - ghost-class="opacity-40": Visually drops alpha opacity on the blank slot grid footprint.
        -->
        <VueDraggable
            v-model="localTasks"
            group="board_tasks"
            :animation="150"
            ghost-class="opacity-40"
            class="space-y-3 min-h-32 overflow-y-auto pb-2 pr-1 "
            @end="handleTaskMove"
        >
            <!-- Draggable Children: Added data-id attribute for bulletproof ID capturing in handleTaskMove -->
            <!-- Tasks Loop -->
            <TaskCard
                v-for="task in localTasks"
                :key="task.id"
                :task
                :data-id="task.id"
            />
        </VueDraggable>
    </div>
</template>
