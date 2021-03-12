<template>
    <div class="form">
        <BaseInputText
                placeholder="Input test"
                v-on:keydown.enter="fetchMethod"
        />

    </div>
</template>

<script>
    import BaseInputText from './BaseInputText.vue';
    export default {
        components: {
            BaseInputText
        },
        data(){
            return {
                products: [
                    {
                        id :244,
                        product_name: "Product name",
                        currency: '$',
                        pirce: 200
                    }
                ]
            }
        },
        name: 'Form',
        methods: {
            fetchMethod(event) {
                console.log(event.target.value);
            }
        }
    }
</script>

