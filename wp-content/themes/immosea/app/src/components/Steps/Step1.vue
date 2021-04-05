<template>
    <div>
        <div v-if="kindProperty">
            <h1 class="text-center">{{ title }}</h1>
            <div class="columns">
                <div>
                    <div class="columns-box">
                        <div class="columns-box-title">
                            <div class="columns-box-icon">
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.168 16.1367H23.6836V19.6523H20.168V16.1367Z" fill="#256469"/>
                                    <path d="M20.168 24.2109H23.6836V27.7266H20.168V24.2109Z" fill="#256469"/>
                                    <path d="M36.3164 24.2109H39.832V27.7266H36.3164V24.2109Z" fill="#256469"/>
                                    <path d="M44.3789 24.2109H47.8945V27.7266H44.3789V24.2109Z" fill="#256469"/>
                                    <path d="M36.3164 32.2734H39.832V35.7891H36.3164V32.2734Z" fill="#256469"/>
                                    <path d="M44.3789 32.2734H47.8945V35.7891H44.3789V32.2734Z" fill="#256469"/>
                                    <path d="M36.3164 40.3477H39.832V43.8633H36.3164V40.3477Z" fill="#256469"/>
                                    <path dr="M44.3789 40.3477H47.8945V43.8633H44.3789V40.3477Z" fill="#256469"/>
                                    <path d="M36.3164 48.4102H39.832V51.9258H36.3164V48.4102Z" fill="#256469"/>
                                    <path d="M44.3789 48.4102H47.8945V51.9258H44.3789V48.4102Z" fill="#256469"/>
                                    <path d="M12.1055 24.2109H15.6211V27.7266H12.1055V24.2109Z" fill="#256469"/>
                                    <path d="M20.168 32.2734H23.6836V35.7891H20.168V32.2734Z" fill="#256469"/>
                                    <path d="M12.1055 32.2734H15.6211V35.7891H12.1055V32.2734Z" fill="#256469"/>
                                    <path d="M20.168 40.3477H23.6836V43.8633H20.168V40.3477Z" fill="#256469"/>
                                    <path d="M12.1055 40.3477H15.6211V43.8633H12.1055V40.3477Z" fill="#256469"/>
                                    <path d="M55.9688 56.4844V16.1367H31.7578V7.74609L27.7266 8.41406V0H8.07422V11.6953L4.04297 12.3633V56.4844H0V60H60V56.4844H55.9688ZM52.4531 19.6523V56.4844H31.7578V19.6523H52.4531ZM11.5898 3.51562H24.2109V9.01172L11.5898 11.1211V3.51562ZM7.54688 15.3516L28.2422 11.9062V16.1484V56.4844H23.6836V48.4102H12.0938V56.4844H7.54688V15.3516ZM20.168 56.4844H15.6094V51.9258H20.168V56.4844Z" fill="#256469"/>
                                </svg>

                            </div>
                            <h3>{{ kindProperty[0].title }}</h3>
                        </div>
                        <div class="columns-box-list">
                            <div v-for="(child, i) in kindProperty[0].items" :key="i">
                                <Radio
                                        :label="child"
                                        :value="child"
                                        name="radio"
                                        v-model="name_house"
                                />
                            </div>
                        </div>

                    </div>
                </div>
                <div>
                    <div class="columns-box">
                        <div class="columns-box-title">
                            <div class="columns-box-icon">
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 56.4844H60V60H0V56.4844Z" fill="#256469"/>
                                    <path d="M60 48.4102H55.9688V27.7266H60V21.2109L46.8633 8.07422H27.7266V0H16.1367V8.07422H13.1367L0 21.1992V27.7148H4.03125V48.4102H0V51.9258H60V48.4102ZM45.4102 11.5898L56.4844 22.6641V24.2109H42.832L30.2109 11.5898H45.4102ZM19.6523 3.51562H24.2109V8.07422H19.6523V3.51562ZM3.51562 24.2109V22.6641L14.5898 11.5898H29.7891L17.168 24.2109H3.51562ZM7.54688 35.7891H12.1055V40.3477H7.54688V35.7891ZM36.3164 48.4102H23.6836V35.7891H36.3047V48.4102H36.3164ZM52.4531 40.3477H47.8945V35.7891H52.4531V40.3477ZM52.4531 32.2734H44.3789V43.8633H52.4531V48.4219H39.832V32.2734H20.168V48.4102H7.54688V43.8516H15.6211V32.2617H7.54688V27.7031H18.6211L30 16.3242L41.3789 27.7031H52.4531V32.2734Z" fill="#256469"/>
                                    <path d="M25.9688 24.2109H34.043V27.7266H25.9688V24.2109Z" fill="#256469"/>
                                </svg>
                            </div>
                            <h3>{{ kindProperty[1].title }}</h3>
                        </div>
                        <div class="columns-box-list">
                            <div v-for="(child, i) in kindProperty[1].items" :key="i">
                                <Radio
                                        :label="child"
                                        :value="child"
                                        name="radio"
                                        v-model="name_house"
                                />
                            </div>
                        </div>

                    </div>
                </div>

                <div>
                    <div class="columns-box">
                        <div class="columns-box-title">
                            <div class="columns-box-icon">
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M50.9531 20.168H42.2461C43.1953 18.1641 43.8164 16.0312 43.8633 13.8633C43.875 6.22266 37.6523 0 30 0C22.3477 0 16.125 6.22266 16.125 13.875C16.1719 16.043 16.793 18.1758 17.7422 20.1797H9.04688L0 60H60L50.9531 20.168ZM19.6406 13.875C19.6406 8.16797 24.2812 3.51562 30 3.51562C35.7188 3.51562 40.3594 8.15625 40.3594 13.875C40.5117 19.4531 33.4102 26.707 30 29.4727C26.5898 26.6953 19.4883 19.4531 19.6406 13.875ZM11.8477 23.6836H19.8047C23.543 29.0977 28.9336 33.1172 30 33.9375C31.0664 33.1172 36.457 29.0977 40.1953 23.6836H48.1406L49.1719 28.2422H42.1875L38.1562 36.293H8.97656L11.8477 23.6836ZM4.40625 56.4844L6.35156 47.8945H16.957L22.6875 56.4844H4.40625ZM26.9062 56.4844L18.832 44.3789H7.14844L8.17969 39.8086H40.3242L44.3555 31.7578H49.9688L51 36.293H47.1914L43.1602 44.3789H24.6094L32.6719 56.4844H26.9062ZM31.1719 47.8945H45.3516L49.3828 39.8086H51.8086L55.5938 56.4844H36.9023L31.1719 47.8945ZM30 19.6289C33.1992 19.6289 35.7891 17.0273 35.7891 13.8398C35.7891 10.6523 33.1992 8.03906 30 8.03906C26.8008 8.03906 24.2109 10.6406 24.2109 13.8281C24.2109 17.0156 26.8008 19.6289 30 19.6289ZM30 11.5547C31.2539 11.5547 32.2734 12.5742 32.2734 13.8281C32.2734 15.082 31.2539 16.1016 30 16.1016C28.7461 16.1016 27.7266 15.082 27.7266 13.8281C27.7266 12.5742 28.7461 11.5547 30 11.5547Z" fill="#256469"/>
                                </svg>
                            </div>
                            <h3>{{ kindProperty[2].title }}</h3>
                        </div>
                        <div class="columns-box-list">
                            <div v-for="(child, i) in kindProperty[2].items" :key="i">
                                <Radio
                                        :label="child"
                                        :value="child"
                                        name="radio"
                                        v-model="name_house"
                                />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="info">
                <div>
                    <p>Wenn keine der Immobilienarten zu deinem Objekt passt, wähle einfach aus, was am ehesten zutrifft. Bevor du deine Daten abschickst, hast du die Möglichkeit uns noch weitere Details zu deiner Immobilie zu nennen.</p>
                    <p>Bitte berücksichtige, dass wir derzeit unsere Leistungen nicht für Gewerbeimmobilien anbieten.</p>
                </div>
                <button @click="buttonNext.click"
                        class="button button--primary"
                        :class="{'button--disabled': !name_house}"
                >
                    {{ buttonNext.title }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import Radio from '../Form/Radio.vue';
  import { kindProperty } from '../../Data/options'

  export default {
    name: 'app-step1',
    components: {
      Radio
    },
    props: ['title', 'buttonNext'],
    data() {
      return {
        kindProperty,
      }
    },
    computed: {
      name_house: {
        get() {
          return this.$store.state.collectData.name_house
        },
        set(value) {
          let type = kindProperty.find(i => i.items.includes(value)).type
          this.$store.commit('SET_COLLECT_DATA', { name_house : value })
          this.$store.commit('SET_CART_OPTIONS', { type })
        }
      }
    },
    methods: {}
  }
</script>
<style lang="scss" scoped>
    @import "../../assets/styles/variables";

    .columns {
        display: flex;
        margin: 0 -30px 40px;
        @media (max-width: $breakpoint-large-max) {
            margin: 0 -16px 40px;
        }
        @media (max-width: $breakpoint-medium-max) {
            margin: 0 -8px 40px;
        }
        @media (max-width: $breakpoint-small-max) {
            display: block;
        }
        > div {
            padding: 0 30px;
            flex: 1 1 33.33%;
            display: flex;
            @media (max-width: $breakpoint-large-max) {
                padding: 0 16px;
            }
            @media (max-width: $breakpoint-medium-max) {
                padding: 0 8px;
            }
            @media (max-width: $breakpoint-small-max) {
                flex: 1 1 100%;
                margin-bottom: 16px;
            }
        }

        &-box {
            flex: 1 1 auto;
            background: #fff;
            box-shadow: $box-shadow;
            border-radius: 8px;
            padding: 30px 45px;
            @media (max-width: $breakpoint-large-max) {
                padding-left: 20px;
                padding-right: 20px;
            }
            @media (max-width: $breakpoint-small-max) {
                padding: 30px;
            }
            h3 {
                text-transform: uppercase;
            }
        }

        &-box-title {
            padding-left: 34px;
        }

        &-box-icon {
            margin-bottom: 32px;
            width: 60px;
        }

        &-box-list {
            > div {
                margin-bottom: 16px;
            }
        }
    }

    .info {
        border-top: 1px solid $gray-light;
        padding: 20px 0 0 100px;
        margin-bottom: 50px;
        position: relative;
        display: flex;
        align-items: flex-start;

        &::before {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            content: '';
            width: 60px;
            height: 60px;
            background: url('../../assets/info.svg') no-repeat;

        }
        > div {
            margin-right: 24px;
        }


        @media (max-width: $breakpoint-small-max) {
            padding: 100px 0 0;
            flex-wrap: wrap;
            justify-content: center;
            &::before {
                left: 50%;
                top: 20px;
                transform: translateX(-50%);
            }
            > div {
                width: 100%;
                margin: 0 0 24px;
            }
        }
    }
</style>

