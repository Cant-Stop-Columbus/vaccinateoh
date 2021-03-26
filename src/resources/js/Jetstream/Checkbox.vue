<template>
    <div class="text-center">
        <label class="inline-block mx-auto">
            <div class="bg-white border-2 rounded border-gray-400 w-10 h-10 flex flex-shrink-0 justify-center items-center mr-2 focus-within:border-blue-500">
                <input type="checkbox" class="opacity-0 absolute" :value="value" v-model="proxyChecked">
                <svg class="checkmark fill-current hidden w-8 h-8 text-blue-500 pointer-events-none" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
            </div>
            <div class="select-none">{{ label }}</div>
        </label>
    </div>
</template>

<script>
export default {
    emits: ['update:checked'],

    props: {
        checked: {
            type: [Array, Boolean],
            default: false,
        },
        label: String,
        value: {
            default: null,
        },
    },

    computed: {
        proxyChecked: {
            get() {
                return this.checked;
            },

            set(val) {
                this.$emit("update:checked", val);
            },
        },
    },
}
</script>
