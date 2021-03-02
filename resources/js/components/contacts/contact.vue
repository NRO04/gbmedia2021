<template>
  <div id="contacts">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="card border-0 shadow">
            <div class="card-header">
              <span class="h3">Contactos</span>

              <span class="float-right">
                <b-button size="sm" :variant="selected.length ? 'danger':'info'" @click="selected.length > 0 ? clearSelected() : selectAllRows()" v-text="selected.length ? 'Limpiar todos' : 'seleccionar todos'"></b-button>
              </span>
              <span class="float-right">
                <b-button class="mx-2" variant="success" size="sm" v-if="selected.length >= 1">
                  <i class="fas fa-paper-plane"></i> Enviar email ({{ selected.length }})
                </b-button>
              </span>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
                  <div class="row mb-3">
                    <div class="col-md-7">
                      <b-input-group size="sm">
                        <b-form-input debounce="500" v-model.trim="filter" type="email" id="filterInput" placeholder="Buscar contacto..."></b-form-input>
                        <b-input-group-append>
                          <b-button variant="success" :disabled="!(filter && validEmail)" @click="onSearch">Añadir</b-button>
                        </b-input-group-append>
                      </b-input-group>
                    </div>
                    <div class="col-md-4">
                      <b-form-select @change="GetAllContacts" class="float-right" v-model="from" id="perPageSelect" size="sm">
                        <b-form-select-option v-for="(cfrom, i) in comingFrom" :value="i + 1" :key="i">{{ cfrom }}</b-form-select-option>
                      </b-form-select>
                    </div>
                    <div class="col-md-1 float-right">
                      <b-form-select class="float-right" v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                    </div>
                  </div>

                  <hr>

                  <b-table show-empty small stacked="sm" :fields="fields"
                           :items="items" responsive="sm" class="table-striped"
                           :current-page="currentPage" :per-page="perPage" :filter="filter"
                           :sort-by.sync="sortBy" :sort-desc.sync="sortDesc"
                           ref="selectableTable"
                           selected-variant="info"
                           selectable
                           @row-selected="onRowSelected"
                           :busy.sync="isBusy"
                           empty-text="No hay contactos ahora"
                  >
                    <template #cell(index)="data">
                      {{ data.index + 1 }}
                    </template>

                    <template #cell(from)="data">
                      <b :class="data.value === 'Juicy Service' ? 'text-warning':'text-info'">{{ data.value }}</b>
                    </template>
                  </b-table>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <div class="float-left">
                [Sorteando por: <b>{{ sortBy }}</b>], [Direccion de sorteo: <b>{{ sortDesc ? 'Descendiente' : 'Ascendiente' }}</b>],
                [Numero de registros: <b>{{ items.length }}</b> contactos]
              </div>
              <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="float-right"></b-pagination>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  name: "contact",
  data: () => {
    return {
      isBusy: false,
      sortBy: 'email',
      sortDesc: false,
      totalRows: 1,
      currentPage: 1,
      perPage: 25,
      pageOptions: [25, 50, 100],
      from: 1,
      comingFrom: ['Todos', 'Juicy service', 'GBMedia', 'Grupo Bedoya', 'Generico'],
      filter: null,
      fields: [
        {key: 'index', label: '#', sortable: false},
        {key: 'email', label: 'Email de contacto', sortable: false},
        {key: 'from', label: 'contacto de', sortable: false},
        {key: 'modified_by', label: 'Modificado por', sortable: false},
        {key: 'created_at', label: 'Añadido en', sortable: false}
      ],
      items: [],
      selected: [],
      validationErrors: []
    }
  },

  computed: {
    validEmail() {
      var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(this.filter);
    },
  },

  methods: {
    GetAllContacts() {
      let url = "/contacts/GetAllContacts?from=" + this.from
      this.isBusy = true
      axios.get(url).then((response) => {
        this.items = response.data.contacts
        this.totalRows = this.items.length
        console.log(this.items.length)
        this.isBusy = false
      })
    },

    onSearch() {
      let url = "/contacts/search"

      let contact_email = this.filter
      
      axios.post(url, {contact_email}).then((response) => {
        Fire.$emit("contactInserted")
        if (response.data.code === 403) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      }).catch((error) => {
        if (error.response.status === 422){
          this.validationErrors = error.response.data.errors;
        }
      })
    },

    onRowSelected(items) {
      this.selected = items
    },

    selectAllRows() {
      this.$refs.selectableTable.selectAllRows()
    },

    clearSelected() {
      this.$refs.selectableTable.clearSelected()
    },
  },

  mounted() {
    Fire.$on("contactInserted", () => {
      this.GetAllContacts()
    });
    this.GetAllContacts()
  }
}
</script>

<style scoped>
.table-striped tbody tr:nth-of-type(odd), .c-dark-theme .table-striped tbody tr:nth-of-type(odd) {
  background-color: #45a16463 !important;
}

.table.b-table > tbody > .table-active > td {
  background-color: #45a16469 !important;
}
</style>