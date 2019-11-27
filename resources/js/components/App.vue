<template>
    <div class="container">
        <div class="Title"> <h1>Bank Statement Manager</h1> </div>

        <div class="table-container">
            <p>Sélectionnez un encadrement de date et un numéro de rib</p>
            <div class="row align-items-center">
                <div class="col-auto">
                    <date-range-picker
                        ref="picker"
                        :startDate="startDate"
                        :endDate="endDate"
                        :minDate="startDate" :maxDate="endDate"
                        :ranges="false"
                        v-model="dateRange"
                        @update="handleDateUpdate"
                        opens="right">
                        <div slot="input" slot-scope="picker" style="min-width: 100px;">
                            {{ picker.startDate | date }} - {{ picker.endDate | date }}
                        </div>
                    </date-range-picker>
                </div>
                <div class="col-md-4">
                    <autocomplete
                        :search="search"
                        @submit="handleRibIdUpdate"
                        placeholder="Rechercher un numéro de rib"
                    ></autocomplete>
                </div>
                <div class="col-auto">
                    <button
                        class="btn btn-primary"
                        @click="handleRangeStatementByRibId">
                        Rechercher
                    </button>
                    <button
                        class="btn btn-secondary"
                        @click="handleRecipeByRangeStatementAndByRibId">
                        Calcul du solde
                    </button>
                </div>
                <div class="col">
                    {{ recipe }}
                </div>
            </div>

            <p class="text-center">{{ isResults }}</p>
            <table class="table">
                <thead>
                    <tr>
                        <th> RIB </th>
                        <th> Date </th>
                        <th> Libellé </th>
                        <th> Montant </th>
                        <th> Devise </th>
                        <th> Recette </th>
                        <th> Dépense </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rib in ribs">
                        <td>{{ rib.RIB }}</td>
                        <td>{{ rib.Date | moment }}</td>
                        <td>{{ rib.Libelle }}</td>
                        <td>{{ rib.Montant }}</td>
                        <td>{{ rib.Devise }}</td>
                        <td>{{ rib.Recette }}</td>
                        <td>{{ rib.Depense }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
  import moment from 'moment';
  import DateRangePicker from 'vue2-daterange-picker';
  import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
  import Autocomplete from '@trevoreyre/autocomplete-vue';
  import '@trevoreyre/autocomplete-vue/dist/style.css'

  export default {
    components: { DateRangePicker, Autocomplete },
    filters: {
      date (value) {
        let options = {year: 'numeric', month: 'long', day: 'numeric'};
        return Intl.DateTimeFormat('fr-FR', options).format(value)
      },
      moment(date) {
        return moment(date).locale('fr').format('DD MMMM YYYY')
      }
    },
    data() {
      return {
        ribs: [],
        isResults: '',
        startDate: null,
        endDate: null,
        ribId: null,
        ranges: false,
        ribIds: [],
        recipe: null,
        dateRange: {
          startDate: null,
          endDate: null,
        }
      }
    },
    methods: {
      moment() {
        return moment();
      },
      getAllStatements() {
        window.axios.get('/api/getAllStatements').then(({data}) => {
          this.ribs = data;
          this.getDates();
          this.getDistinctRibIds(this.ribs);
        });
      },
      getDates() {
        this.startDate = this.ribs[0].Date;
        this.endDate = this.ribs[this.ribs.length - 1].Date;
        this.dateRange = { startDate: this.startDate, endDate: this.endDate };
      },
      getDistinctRibIds(ribs) {
        const allRibs = [];
        const distinct = (value, index, self) => self.indexOf(value) === index;
        ribs.map(rib => allRibs.push(rib.RIB));
        this.ribIds = allRibs.filter(distinct);
      },
      search(input) {
        if (input.length < 1) { return [] }
        return this.ribIds.filter(rib => {
          return rib.startsWith(input);
        })
      },
      handleRibIdUpdate(ribId) {
        this.ribId = ribId;
      },
      handleDateUpdate(updatedDates) {
        this.dateRange = {
          startDate: moment(updatedDates.startDate).format('YYYY-MM-DD'),
          endDate: moment(updatedDates.endDate).format('YYYY-MM-DD')
        };
      },
      handleRangeStatementByRibId() {
        window.axios({
          method: 'POST',
          url: '/api/getRangeStatementsByStatementId',
          data: {
            startDate: this.dateRange.startDate,
            endDate: this.dateRange.endDate,
            ribId: this.ribId
          },
        }).then(({data}) => {
          this.ribs = data;
          this.isResultsMessage();
        }).catch(function(error) {
          console.log('Cannot handle submit range statement by rib id listing', error);
        });
      },
      handleRecipeByRangeStatementAndByRibId() {
        window.axios({
          method: 'POST',
          url: '/api/getRecipeByRangeStatementAndRibId',
          data: {
            startDate: this.dateRange.startDate,
            endDate: this.dateRange.endDate,
            ribId: this.ribId
          },
        }).then(({data}) => {
          this.recipe = `Solde du compte : ${data}€`;
        }).catch(function(error) {
          console.log('Cannot handle submit total recipe', error);
        });
      },
      isResultsMessage() {
        this.isResults = (!this.ribs.length > 0 ? 'No statement for those dates' : '');
      }
    },
    created() {
      console.log('App component created.');
      this.getAllStatements();
    },
    mounted() {
      console.log('App Component mounted.');
    }
  }
</script>
<style lang="scss">
    .autocomplete-input {
        padding: 6px 12px 6px 48px;
    }
</style>
