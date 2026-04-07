<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: { type: String, default: 'right' },
    width: { type: String, default: '48' },
    contentClasses: { type: String, default: 'py-1 bg-white' },
});

const open = ref(false);
const selected = ref(''); // NEW: track selected value

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => ({ 48: 'w-48' }[props.width.toString()]));

const alignmentClasses = computed(() => {
    if (props.align === 'left') return 'ltr:origin-top-left rtl:origin-top-right start-0';
    if (props.align === 'right') return 'ltr:origin-top-right rtl:origin-top-left end-0';
    return 'origin-top';
});

// NEW: function to select an item
const setSelected = (val) => {
    selected.value = val;
    open.value = false;
};
</script>

<template>
    <div class="relative">
        <!-- Trigger button -->
<slot name="trigger" :selected="selected" :toggle="() => open.value = !open">
  <button @click="open = !open" class="border w-full py-2 px-3 rounded text-left">
    {{ selected || 'Select' }}
  </button>
</slot>

        <!-- Full Screen Dropdown Overlay -->
       <div
    v-show="open"
    class="absolute z-50 mt-2 rounded-md shadow-lg"
    :class="[widthClass, alignmentClasses]"
    @click.stop
>\</div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses]"
                style="display: none"
                @click="open = false"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
