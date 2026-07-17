<script setup>
// Define the incoming props from Laravel Controller
const props = defineProps({
    boards: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">My Boards</h1>

        <!-- 1. Show this block if the user has boards -->
        <div v-if="boards.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="board in boards" :key="board.id" class="p-4 bg-white shadow rounded-lg">
                <h3 class="font-semibold text-lg">{{ board.name }}</h3>
                <p class="text-gray-500 text-sm">Slug: {{ board.slug }}</p>
            </div>
        </div>

        <!-- 2. Beautiful empty state placeholder if there are no boards -->
        <div v-else class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
            <p class="text-gray-500 mb-4">You don't have any boards yet.</p>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Create Your First Board
            </button>
        </div>
    </div>
</template>
