<template>
    <div class="flex items-center mr-4 my-1">
        <label class="flex items-center cursor-pointer">
            <span class="bg-white border rounded border-blue w-6 h-6 flex flex-shrink-0 justify-center items-center mr-2 focus-within:border-blue-500">
                <input type="checkbox" class="opacity-0 absolute" :value="value" v-model="proxyChecked">
                <svg class="checkmark fill-current hidden w-8 h-8 text-blue-500 pointer-events-none" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
            </span>
            <span class="inline-block select-none">{{ label }}</span>
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
            get(param) {
                return this.checked;
            },

            set(val) {
                console.log(val);
                this.$emit("update:checked", val);
            },
        },
    },
}
</script>
