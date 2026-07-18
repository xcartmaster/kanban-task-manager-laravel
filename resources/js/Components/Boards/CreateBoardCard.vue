<script setup>
const emit = defineEmits(['addNewBoardButtonClicked']);

function handleCardClick() {
    emit('addNewBoardButtonClicked');
}
</script>

<template>
    <!-- Interactive placeholder card with hover animations -->
    <button
        @click="handleCardClick"
        type="button"
        class="group flex flex-col items-center justify-center p-5 min-h-[116px] bg-gray-50/50 hover:bg-blue-50/50 dark:bg-zinc-800/30 dark:hover:bg-blue-950/20 border-2 border-dashed border-gray-200 hover:border-blue-400 dark:border-zinc-700 dark:hover:border-blue-500 rounded-xl transition-all duration-200 cursor-pointer text-center w-full"
        title="Click to create a new board"
    >
        <!-- Animated Plus Icon -->
        <span class="text-xl mb-1 text-gray-400 group-hover:text-blue-500 group-hover:scale-110 transition-transform duration-200">
            ➕
        </span>
        <span class="font-medium text-sm text-gray-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            Create New Board
        </span>
    </button>
</template>
