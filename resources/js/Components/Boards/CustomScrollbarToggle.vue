<script setup>
/*
const props = defineProps({
    isCustomScrollbarEnabled: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['custom-scrollbar-toggle']); // Declare the custom event that will bubble up to inform the parent about the state change.

function toggleStatus() {
    emit('custom-scrollbar-toggle', !props.isCustomScrollbarEnabled);
}
*/

/**
 * Creates a two-way reactive binding with the parent component.
 * Replaces the legacy defineProps and defineEmits workflow in Vue 3.4+.
 * Mutating this local ref automatically syncs with the parent's useLocalStorage.
 */
const isCustomScrollbarEnabled = defineModel({
    type: Boolean,
    required: true
});
</script>

<template>
    <div class="flex justify-end items-center mt-2">
        <!--
          The wrapping <label> element acts as a semantic trigger. Clicking either the text
          or the decorative toggle switch automatically triggers the hidden native input.
        -->
        <label class="inline-flex items-center space-x-3 cursor-pointer select-none">
            <!--
              Hidden native HTML checkbox that manages the true underlying state.
              Binding :checked to props ensures that the parent remains the single source of truth.
            -->
            <!-- <input type="checkbox" :checked="isCustomScrollbarEnabled" @change="toggleStatus" class="sr-only peer" /> -->
            <input type="checkbox" v-model="isCustomScrollbarEnabled" class="sr-only peer" />
            <span class="text-xs text-gray-500">Enable Custom Scrollbar</span>

            <!--
              Decorative iOS-style switch track.
              Utilizes Tailwind CSS 'peer-checked' modifiers to shift the inner ball element (after:)
              and change background colors reactively when the hidden native checkbox alters its state.
            -->
            <div class="relative w-9 h-5 bg-gray-200 dark:bg-zinc-700 peer-checked:bg-blue-600 rounded-full transition-colors duration-200 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all duration-200 peer-checked:after:translate-x-full"></div>
        </label>

        <!-- Information Tooltip Wrapper -->
        <div class="group relative inline-block ml-2">
            <!-- Question mark trigger icon -->
            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-gray-200 text-[10px] text-gray-500 cursor-help"> ? </span>
            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 absolute bottom-full right-0 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-lg transition-all duration-200 text-center pointer-events-none">
                Enables a custom reactive draggable scrollbar designed to guarantee precise grid metrics coordinates during Drag and Drop operations.

                <!-- Creates a small CSS triangle pointing down to serve as a tooltip arrow indicator -->
                <div class="absolute top-full right-2 border-4 border-transparent border-t-gray-900"></div>

            </div>
        </div>
    </div>
</template>
