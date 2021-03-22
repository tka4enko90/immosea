<template>
  <div class="hello">
    <Step1/>
    <InputText
            label="testsss"
            type="text"
            name="input1"
            placeholder="Input test"
            v-model="input1"
    />
    <InputText
            label="testsss"
            type="text"
            name="input2"
            placeholder="Input test"
            v-model="input2"
    />

    <Checkbox
            id="single"
            label="single Checkbox"
            v-model="single"
    />

    <Radio
            label="Radio No"
            value="no"
            id="no"
            name="radio"
            v-model="radio"
    />
    <Radio
            label="Radio Yes"
            value="yes"
            id="yes"
            name="radio"
            v-model="radio"
    />

    <GroupCheckbox  :options="[
        {label: 'Jack', value: 'jack', id:2},
        {label: 'Tim', value: 'tim', id:1},
        {label: 'ttt', value: 'ttt', id:3}
        ]" v-model="checkedNames" />
    <br/>
    {{ input1 }}
    <br/>
    {{ input2 }}
    <br/>
    {{ checkedNames }}
    <br/>
    {{single}}
    <br/>
    radio {{radio}}
    {{products}}
  </div>
</template>

<script>
  import InputText from './Form/InputText.vue';
  import Checkbox from './Form/Checkbox.vue';
  import Radio from './Form/Radio.vue';
  import GroupCheckbox from './Form/GroupCheckbox.vue';
  import Step1 from './Steps/Step1'
export default {
  name: 'HelloWorld',
  props: {
    msg: String
  },
  components: {
    InputText,
    Checkbox,
    Radio,
    GroupCheckbox,
    Step1
  },
  data() {
    return {
      options: [
        {id: 3,title: 'dd', value: 'dd'},
        {id:4,title: 'c', value: 'cc'}
      ],

      input1: '',
      input2: '',
      checkedNames: [],
      single: false,
      radio: '',
    }
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
h3 {
  margin: 40px 0 0;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}
</style>
