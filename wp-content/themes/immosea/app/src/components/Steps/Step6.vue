<template>
    <StepWrap
            :title="title"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        <div class="form__row form__row--flex">

            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.year" type="number" label="Baujahr" placeholder="YYYY" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.floors" type="number" label="Etagen" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.object" type="number" label="Objekt in Etage" placeholder="Number" />
            </div>
            <div class="form__area form__area--expand" v-if="type === 'flat' && !isRent">
                <InputText v-model="collectData.coOwnership" label="Miteigentumsanteil" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.yearUpgrade" type="number" label="Letzte Modernisierung" placeholder="YYYY" />
            </div>
            <div class="form__area form__area--expand" v-if="type === 'flat'">
                <Checkbox v-model="collectData.lift" label="Lift" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">Zustand</label>
                <v-select :options="options" v-model="collectData.status" placeholder="Select"></v-select>
            </div>
            <div class="form__area">
                <label class="form__label">Verfügbar ab</label>
                <div class="form__date">
                    <datepicker v-model="collectData.availableFrom"
                                format="yyyy-MM-dd"
                                placeholder="Select Date"
                                clearButton calendarButton />
                </div>
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.livingSpace" type="number" label="Wohnfläche (m²)" placeholder="m²" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.usableArea" type="number" label="Nutzfläche (m²)" placeholder="m²" />
            </div>
            <div class="form__area" v-if="type !== 'flat'">
                <InputText v-model="collectData.property" type="number" label="Grundstück (m²)" placeholder="m²" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.rooms" type="number" label="Zimmer gesamt" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.bedroom" type="number" label="Schlafzimmer" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.livingBedroom" type="number" label="Wohn-Schlafzimmer" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.bathroom" type="number" label="Badezimmer" placeholder="Number" />
            </div>

            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.toilets" type="number" label="Separate WCs" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.balconies" type="number" label="Anzahl Balkon" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.terrace" type="number" label="Anzahl Terrasse" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">Fensterart</label>
                <v-select :options="options2" v-model="collectData.windowType" placeholder="Select" multiple />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">Verglasung</label>
                <v-select :options="options3" v-model="collectData.glazing" placeholder="Select" multiple />
            </div>
            <div class="form__area">
                <label class="form__label">BJ Fenster (falls abweichend)</label>
                <div class="form__date">
                    <datepicker v-model="collectData.bjWindow"
                                format="yyyy-MM-dd"
                                placeholder="Select Date"
                                clearButton calendarButton />
                </div>
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">Keller</label>
                <v-select :options="options4" v-model="collectData.keller" placeholder="Select" />
            </div>
            <div class="form__area form__area--expand" v-if="type !== 'property'">
                <Checkbox v-model="collectData.garden" label="Garten" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">Stellplätze</label>
                <v-select :options="options5" v-model="collectData.parking" placeholder="Select" multiple />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.numberParking" type="number" label="Anzahl Stellplätze"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.numberUnits" type="number" label="Anzahl Einheiten" placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.residentialUnits" type="number" label="Davon Wohneinheiten"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.whichCommercial" type="number" label="Davon Gewerbe"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat' && !isRent">
                <label class="form__label">Monatliches Hausgeld </label>
                <div class="form-input">
                    <currency-input v-model="collectData.monthlyAllowance" />
                </div>
            </div>
            <div class="form__area" v-if="!isRent">
                <label class="form__label">Kaufpreis </label>
                <div class="form-input">
                    <currency-input v-model="collectData.purchasePrice" />
                </div>
            </div>
            <div class="form__area" v-if="!isRent && type !== 'property'">
                <label class="form__label">Stellplatzpreis </label>
                <div class="form-input">
                    <currency-input v-model="collectData.pitchPrice" />
                </div>
            </div>
            <div class="form__area" v-if="isRent">
                <label class="form__label">Kaltmiete </label>
                <div class="form-input">
                    <currency-input v-model="collectData.rent" />
                </div>
            </div>
            <div class="form__area" v-if="isRent">
                <label class="form__label">Nebenkosten </label>
                <div class="form-input">
                    <currency-input v-model="collectData.additionalCosts" />
                </div>
            </div>
            <div class="form__area" v-if="isRent">
                <label class="form__label">Miete Stellplatz </label>
                <div class="form-input">
                    <currency-input v-model="collectData.rentParking" />
                </div>
            </div>
            <div class="form__area form__area--expand" v-if="!isRent">
                <Checkbox v-model="collectData.fullyDeveloped" label="Voll Erschlossen" />
            </div>
            <div class="form__area form__area--expand" v-if="type !== 'property'">
                <Checkbox v-model="collectData.monumentProtection" label="Denkmalschutz" />
            </div>
            <div class="form__area form__area--expand" v-if="type !== 'property'">
                <Checkbox v-model="collectData.ensembleProtection" label="Ensembleschutz" />
            </div>
            <div class="form__area form__area--expand" v-if="type === 'property'">
                <Checkbox v-model="collectData.demolitionObject" label="Abrissobjekt" />
            </div>
        </div>

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import InputText from '../Form/InputText';
  import Checkbox from '../Form/Checkbox';
  import vSelect from 'vue-select';
  import { Zustand, Fensterart, Verglasung, Keller, Stellplatze } from '../../Data/options';
  import { CurrencyInput } from 'vue-currency-input'
  import Datepicker from 'vuejs-datepicker';


  export default {
    name: 'app-step6',
    components: {
      StepWrap,
      InputText,
      Checkbox,
      vSelect,
      CurrencyInput,
      Datepicker
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {
      options: Zustand,
      options2: Fensterart,
      options3: Verglasung,
      options4: Keller,
      options5: Stellplatze,
      time1: '',
      time2: ''
    }},
    computed: {
        type() {
          return this.$store.state.cart.type
        },
        isRent() {
          return this.$store.state.sellRent === 'rent'
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
    methods: {

    }
  }
</script>

