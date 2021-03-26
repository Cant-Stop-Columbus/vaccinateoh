<template>
    <div class="flex items-center mr-4 my-4">
    <label class="radio flex items-center cursor-pointer">
    <input type="radio" :name="name" :value="modelValue" v-bind:checked="value == modelValue" class="hidden" v-on:input="changeEvent">
     <span class="radio w-6 h-6 inline-block mr-2 rounded-full border border-blue flex-no-shrink"></span>
     <span>{{ label }}</span>
   </label>
   </div>
</template>

<script>

export default {
    props: {
        name: String,
        label: String,
        value: String,
        modelValue: String,
    },
    emits: ['update:modelValue'],
    data() {
        return {
        };
    },
    methods: {
        changeEvent(event) {
            this.$emit('update:modelValue', this.value);
        }
    }
}
</script>
