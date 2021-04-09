<template>
    <StepWrap
            :title="title"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{...buttonNext}"
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
                <InputText v-model="collectData.coownership" label="Miteigentumsanteil" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.year_upgrade"
                           type="number"
                           label="Letzte Modernisierung"
                           placeholder="YYYY" />
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
                    <datepicker v-model="collectData.available_from"
                                format="yyyy-MM-dd"
                                placeholder="Select Date"
                                clearButton calendarButton />
                </div>
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.living_space" type="number" label="Wohnfläche (m²)" placeholder="m²" />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <InputText v-model="collectData.usable_area" type="number" label="Nutzfläche (m²)" placeholder="m²" />
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
                <InputText v-model="collectData.living_bedroom" type="number" label="Wohn-Schlafzimmer"
                           placeholder="Number" />
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
                <v-select :options="options2" v-model="collectData.window_type" placeholder="Select" multiple />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">Verglasung</label>
                <v-select :options="options3" v-model="collectData.glazing" placeholder="Select" multiple />
            </div>
            <div class="form__area" v-if="type !== 'property'">
                <label class="form__label">BJ Fenster (falls abweichend)</label>
                <div class="form__date">
                    <datepicker v-model="collectData.bjwindow"
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
                <InputText v-model="collectData.number_parking" type="number" label="Anzahl Stellplätze"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.number_units" type="number" label="Anzahl Einheiten"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.residential_units" type="number" label="Davon Wohneinheiten"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat'">
                <InputText v-model="collectData.which_commercial" type="number" label="Davon Gewerbe"
                           placeholder="Number" />
            </div>
            <div class="form__area" v-if="type === 'flat' && !isRent">
                <label class="form__label">Monatliches Hausgeld </label>
                <div class="form-input">
                    <currency-input v-model="collectData.monthly_allowance" />
                </div>
            </div>
            <div class="form__area" v-if="!isRent">
                <label class="form__label">Kaufpreis </label>
                <div class="form-input">
                    <currency-input v-model="collectData.purchase_price" />
                </div>
            </div>
            <div class="form__area" v-if="!isRent && type !== 'property'">
                <label class="form__label">Stellplatzpreis </label>
                <div class="form-input">
                    <currency-input v-model="collectData.pitch_price" />
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
                    <currency-input v-model="collectData.additional_costs" />
                </div>
            </div>
            <div class="form__area" v-if="isRent">
                <label class="form__label">Miete Stellplatz </label>
                <div class="form-input">
                    <currency-input v-model="collectData.rent_parking" />
                </div>
            </div>
            <div class="form__area form__area--expand" v-if="!isRent && type === 'property'">
                <Checkbox v-model="collectData.fully_developed" label="Voll Erschlossen" />
            </div>
            <div class="form__area form__area--expand" v-if="type !== 'property'">
                <Checkbox v-model="collectData.monument_protection" label="Denkmalschutz" />
            </div>
            <div class="form__area form__area--expand" v-if="type !== 'property'">
                <Checkbox v-model="collectData.ensemble_protection" label="Ensembleschutz" />
            </div>
            <div class="form__area form__area--expand" v-if="type === 'property'">
                <Checkbox v-model="collectData.demolition_object" label="Abrissobjekt" />
            </div>
            <div class="form__area">
                <label for="particularities" class="form__label">Besonderheiten</label>
                <textarea class="form__textarea" id="particularities" cols="30" rows="10"
                          v-model="collectData.particularities"></textarea>
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
      options5: Stellplatze
    }},
    computed: {
        type() {
          return this.$store.state.cart.type
        },
        isRent() {
          return this.$store.state.collectData.sell_rent === 'rent'
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

