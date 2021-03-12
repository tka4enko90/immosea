<template>
    <input type="text" class="input" :value="value" v-on="listeners" />
</template>

<script>
    export default {
        props: {
            value: {},
        },
        computed: {
            listeners() {
                return {
                    ...this.$listeners,
                    input: (event) => this.$emit("input", event.target.value),
                };
            },
        },
    };
</script>
