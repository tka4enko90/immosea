<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        <div class="grid">
            <div>
                <div class="form__row form__row--flex">
                    <div class="form__area form__area--expand">
                        <label class="form__label">Heizung</label>
                        <v-select :options="options" v-model="collectData.heater" placeholder="Select" />
                    </div>
                    <div class="form__area form__area--expand">
                        <label class="form__label">Energieausweis</label>
                        <v-select :options="options2" v-model="collectData.energyCertificate"
                                  placeholder="Select" />
                    </div>
                    <div class="form__area form__area--expand">
                        <InputText v-model="collectData.consumptionValue" type="number"
                                   label="Verbrauchskennwert (kWh/(m²*a))"
                                   placeholder="kWh/(m²*a)" />
                    </div>
                    <div class="form__area form__area--expand">
                        <label class="form__label">Energieausweis gültig bis</label>
                        <div class="form__date">
                            <datepicker v-model="collectData.validEnergyCertificate"
                                        format="yyyy-MM-dd"
                                        placeholder="Select Date"
                                        clearButton calendarButton />
                        </div>
                    </div>
                    <div class="form__area form__area--expand">
                        <Checkbox v-model="collectData.includedHotWater" label="Warmwasser enthalten" />
                    </div>
                </div>
            </div>
            <div>
                <div class="box">
                    <span class="sub-title text-center">oder</span>
                    <h4>Mir liegt noch kein Energieausweis vor</h4>
                    <ul class="form__list">
                        <li>
                            <Radio
                                    label="Ich kümmere mich selbst um die Beschaffung eines Energieausweises und übernehme die Daten ins Exposé. "
                                    value=false id="no" name="radio" v-model="energy_certificate" small
                            />
                        </li>
                        <li>
                            <Radio
                                    label="Bitte kümmert euch für mich um die Beschaffung des Energieausweises."
                                    value=true id="yes" name="radio" v-model="energy_certificate" small
                            />
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import Checkbox from '../Form/Checkbox';
  import InputText from '../Form/InputText';
  import Radio from '../Form/Radio';
  import vSelect from 'vue-select';
  import { Heizung, Energieausweis } from '../../Data/options';
  import Datepicker from 'vuejs-datepicker';


  export default {
    name: 'app-step7',
    components: {
      StepWrap,
      Checkbox,
      InputText,
      Radio,
      vSelect,
      Datepicker
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {
      options: Heizung,
      options2: Energieausweis
    }},
    computed: {
      energy_certificate: {
        get() {
          return this.$store.state.cart.energy_certificate
        },
        set(value) {
          this.$store.commit('SET_CERTIFICATE', value)
        }
      },
      collectData: {
        get() {
          return this.$store.state.collectData
        },
        set(value) {
          this.$store.commit('SET_COLLECT_DATA', { value })
        }
      }
    },
    methods: {}
  }
</script>

