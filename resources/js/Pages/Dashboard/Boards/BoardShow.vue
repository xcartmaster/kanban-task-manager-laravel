<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    board: {
        type: Object,
        required: true
    }
});

// Reactively reference the DOM elements for synchronization
const kanbanViewport = ref(null);
const customScrollbarTrack = ref(null);
const customScrollbarThumb = ref(null);

const isDragging = ref(false);
const startX = ref(0);
const scrollLeftStart = ref(0);

// Reactive state parameters for structural viewport dynamic calculations
const showScrollbar = ref(false);
const thumbWidthPercent = ref(20);

// Calculate dynamic element bounds to resize and show/hide the scrollbar proportionally
function checkOverflow() {
    if (!kanbanViewport.value) return;

    const viewport = kanbanViewport.value;
    const hasOverflow = viewport.scrollWidth > viewport.clientWidth;
    showScrollbar.value = hasOverflow;

    if (hasOverflow) {
        const visibleRatio = viewport.clientWidth / viewport.scrollWidth;
        thumbWidthPercent.value = Math.max(10, Math.min(visibleRatio * 100, 90));

        nextTick(() => {
            handleViewportScroll();
        });
    }
}

// Synchronize the custom thumb position when the user scrolls the kanban content via mouse wheel
function handleViewportScroll() {
    if (isDragging.value || !showScrollbar.value || !kanbanViewport.value || !customScrollbarThumb.value || !customScrollbarTrack.value) return;

    const viewport = kanbanViewport.value;
    const scrollPercentage = viewport.scrollLeft / (viewport.scrollWidth - viewport.clientWidth);

    const maxThumbLeft = customScrollbarTrack.value.clientWidth - customScrollbarThumb.value.clientWidth;
    customScrollbarThumb.value.style.left = `${(scrollPercentage || 0) * maxThumbLeft}px`;
}

// Initialize custom drag-and-drop mechanics for our handmade scrollbar thumb
function startThumbDrag(event) {
    isDragging.value = true;

    // Correctly target the touch point coordinate on mobile or fallback to mouse cursor X position
    const clientX = event.touches ? event.touches[0].pageX : event.pageX;
    startX.value = clientX - customScrollbarThumb.value.offsetLeft;
    scrollLeftStart.value = kanbanViewport.value.scrollLeft;

    document.addEventListener('mousemove', handleThumbMove);
    document.addEventListener('mouseup', stopThumbDrag);
    document.addEventListener('touchmove', handleThumbMove, { passive: false });
    document.addEventListener('touchend', stopThumbDrag, { passive: false });
}

function handleThumbMove(event) {
    if (!isDragging.value || !kanbanViewport.value || !customScrollbarThumb.value || !customScrollbarTrack.value) return;

    event.preventDefault(); // Prevents standard mobile page bouncing during drag execution

    // FIXED: Correctly extracts pageX from the first active touch array item for mobile support
    const pageX = event.touches ? event.touches[0].pageX : event.pageX;
    const currentThumbLeft = pageX - startX.value;

    const maxThumbLeft = customScrollbarTrack.value.clientWidth - customScrollbarThumb.value.clientWidth;
    const boundedThumbLeft = Math.max(0, Math.min(currentThumbLeft, maxThumbLeft));

    customScrollbarThumb.value.style.left = `${boundedThumbLeft}px`;

    const scrollPercentage = boundedThumbLeft / maxThumbLeft;
    const maxViewportScroll = kanbanViewport.value.scrollWidth - kanbanViewport.value.clientWidth;
    kanbanViewport.value.scrollLeft = scrollPercentage * maxViewportScroll;
}

function stopThumbDrag() {
    isDragging.value = false;
    document.removeEventListener('mousemove', handleThumbMove);
    document.removeEventListener('mouseup', stopThumbDrag);
    document.removeEventListener('touchmove', handleThumbMove, { passive: false });
    document.removeEventListener('touchend', stopThumbDrag, { passive: false });
}

onMounted(() => {
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkOverflow);
});
</script>

<template>
    <Head :title="`Boards :: ${board.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                {{ board.name }}
            </h2>
        </template>

        <!-- Main container locked to full screen viewport height bounds -->
        <div class="h-[calc(100vh-66px)] w-full bg-gray-50 dark:bg-zinc-950/40 overflow-hidden p-6">

            <!--
              We removed 'justify-between' and used a clean max-w container.
              The content will now naturally sit at the top of the page.
            -->
            <div class="w-full mx-auto space-y-4">

                <!-- Main Kanban Viewport containing responsive lanes -->
                <div
                    ref="kanbanViewport"
                    @scroll="handleViewportScroll"
                    class="flex pb-2 items-start select-none overflow-x-hidden scrollbar-none touch-none w-full"
                    :class="showScrollbar ? 'justify-start space-x-6' : 'justify-center space-x-6'"
                    style="scrollbar-width: none; -ms-overflow-style: none;"
                >
                    <!-- Columns Loop -->
                    <div v-for="column in board.columns" :key="column.id" class="w-72 shrink-0 bg-white dark:bg-zinc-900 border border-gray-200/60 dark:border-zinc-800/80 p-4 rounded-2xl shadow-sm pointer-events-auto">
                        <!-- Column Header Title -->
                        <h4 class="font-bold text-gray-800 dark:text-zinc-200 mb-3">{{ column.name }}</h4>

                        <div class="space-y-3">
                            <!-- Tasks Loop -->
                            <div
                                v-for="task in column.tasks"
                                :key="task.id"
                                class="p-3 bg-gray-50/70 hover:bg-white dark:bg-zinc-800/50 dark:hover:bg-zinc-800 shadow-xs hover:shadow-md border border-gray-100/70 dark:border-zinc-700/50 rounded-xl text-gray-900 dark:text-zinc-300 text-sm transition-all duration-200 cursor-pointer transform hover:-translate-y-0.5"
                            >
                                {{ task.title }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- --- FIXED HANDMADE KANBAN SCROLLBAR TRACK --- -->
                <!-- Removed 'mt-auto' and wrapped in a max-w layout to match the columns perfectly -->
                <div
                    v-if="showScrollbar"
                    ref="customScrollbarTrack"
                    class="relative w-full h-3.5 bg-gray-200 dark:bg-zinc-800 rounded-full overflow-hidden select-none"
                >
                    <!-- Draggable track slider thumb with dynamic width assignment -->
                    <div
                        ref="customScrollbarThumb"
                        @mousedown="startThumbDrag"
                        @touchstart="startThumbDrag"
                        class="absolute top-0 left-0 h-full bg-gray-400 hover:bg-gray-500 dark:bg-zinc-600 dark:hover:bg-zinc-500 rounded-full cursor-grab active:cursor-grabbing transition-colors duration-150"
                        :style="{ width: thumbWidthPercent + '%' }"
                    ></div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none !important;
}
</style>
