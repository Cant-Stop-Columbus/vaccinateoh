<template>
    <div>
        <div>
            <h2>
            <span class="text-capitalize">Location</span>
            <small id="datatable_info_stack">Import</small>
            </h2>
        </div>

    <form method="post" enctype="multipart/form-data" action="{{ route('admin.location.import') }}">
        <input type="file" ref="fileupload" name="fileupload" @change="uploadFile">
        <div class="inline-flex items-center" v-if="upload_progress">
            <svg class="animate-spin" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45"/>
            </svg>
            {{ upload_progress }}% uploaded... <span v-if="upload_progress == 100">Processing...</span>
        </div>
    </form>

    <div v-if="rows.length" class="row py-5">
      <div class="col">
          <h3>Summary</h3>
          <ul>
          <li v-for="(val, key) in summary"><input type="checkbox" v-model="show_statuses[val.i]" @change="refreshShowStatuses" /> {{ key }}: {{ val.count }}</li>
          </ul>
      </div>

      <div class="col">
        <fieldset>
          Map import fields:
          <table>
            <thead>
              <tr>
                <th>Import from</th>
                <th>Import to</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="header in headers_imported">
                  <td>{{ header }}</td>
                  <td>
                      <select v-model="import_header_map[header]">
                      <option v-for="(aliases, map_header) in headers_all" :value="map_header">{{ map_header }}</option>
                      <option value="-1">--exclude--</option>
                      </select>
                  </td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
      <div class="col">
        <div class="mb-3">
          <button name="import_missing" @click.prevent="importMatches(0)" class="btn btn-primary">
            Import Missing Locations
          </button>
        </div>
        <div class="mb-3">
          <button name="import_matched" @click.prevent="importMatches(1)" class="btn btn-primary">
            Import Matched Locations
          </button>
        </div>
      </div>
    </div>

    <div class="row" v-if="rows.length">
      <div class="col">
        <table id="locations-table" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
            <thead>
                <tr>
                    <th v-for="header in headers_imported">{{ header }}</th>
                    <th>Location Matches</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :data-location-count="row.locations.length">
                    <td v-for="(column, row) in row.data">{{ column }}</td>
                    <td><span :title="JSON.stringify(row.locations)">{{ row.locations.length }}</span></td>
                </tr>
            </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<style>
svg.animate-spin {
  animation: 2s linear infinite svg-animation;
  max-width: 25px;
}

@keyframes svg-animation {
  0% {
    transform: rotateZ(0deg);
  }
  100% {
    transform: rotateZ(360deg)
  }
}

svg.animate-spin circle {
  animation: 1.4s ease-in-out infinite both circle-animation;
  display: block;
  fill: transparent;
  stroke: #2f3d4c;
  stroke-linecap: round;
  stroke-dasharray: 283;
  stroke-dashoffset: 280;
  stroke-width: 10px;
  transform-origin: 50% 50%;
}

@keyframes circle-animation {
  0%,
  25% {
    stroke-dashoffset: 280;
    transform: rotate(0);
  }
  
  50%,
  75% {
    stroke-dashoffset: 75;
    transform: rotate(45deg);
  }
  
  100% {
    stroke-dashoffset: 280;
    transform: rotate(360deg);
  }
}
</style>

<script>
import toastr from 'toastr';

export default {
    props: {
    },
    data() {
        return {
            summary: {},
            headers_required: [],
            headers_uptional: [],
            headers_imported: [],
            import_header_map: {},
            upload_progress: 0,
            show_statuses: {
              0: true,
              1: true,
              '>1': true,
            },
            rows: [],
        };
    },
    methods: {
        importMatches(match_count) {
            const vue = this;
            axios.post( '/admin/api/location/import', {
              match_count: match_count,
              import_header_map: this.import_header_map,
            }).then(function(response){
                vue.rows = response.data.rows;
                vue.summary = response.data.summary;
                vue.headers_imported = response.data.headers_imported;
                vue.headers_all = response.data.headers_all;

                vue.setDefaultHeaderMappings();

                // Set 
                toastr.success('Import successful!' );
            })
            .catch(function(error){
                toastr.error('There was an error importing the missing locations.');
                console.log(error);
            });

        },
        uploadFile() {
            let file = this.$refs.fileupload;
            toastr.info('Uploading import file ' + file.value.split("\\").pop() );
            const vue = this;
            let formData = new FormData();
            formData.append('fileupload', file.files[0]);
            this.upload_progress = 1;
            axios.post( '/admin/api/location/upload',
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function(progressEvent) {
                        this.upload_progress = Math.round(progressEvent.loaded / progressEvent.total * 100);
                    }.bind(this)
                }
            ).then(function(response){
                vue.$refs.fileupload.value = null;
                vue.upload_progress = 0;
                vue.rows = response.data.rows;
                vue.summary = response.data.summary;
                vue.headers_imported = response.data.headers_imported;
                vue.headers_all = response.data.headers_all;

                vue.setDefaultHeaderMappings();
                vue.refreshShowStatuses();

                // Set 
                toastr.success('Upload successful!' );
            })
            .catch(function(error){
                console.log(error);
                toastr.error('There was an error uploading your file.');
                vue.$refs.fileupload.value = null;
                vue.upload_progress = 0;
            });
        },
        setDefaultHeaderMappings() {
          this.import_header_map = {};
          for(let i in this.headers_imported) {
            let header_imported = this.headers_imported[i];

            // Search through the header aliases for the imported header column
            var header_match = this.headers_all[header_imported.toLowerCase()];

            // If the header is a match for one of our importable headers, set it
            if(header_match) {
              this.import_header_map[header_imported] = header_match;
            } else if(header_imported) {
              this.import_header_map[header_imported] = -1;
            }
          };
        },
        refreshShowStatuses() {
          const vue = this;
          setTimeout(function() {
            let trs = document.querySelectorAll('#locations-table tbody tr');
            let show_count = 0;
            let hide_count = 0;
            for(let i in trs) {
              if(!trs[i].attributes) {
                continue;
              }

              let count = trs[i].attributes['data-location-count'].value;
              if(vue.show_statuses[count] || count > 1 && vue.show_statuses['>1']) {
                trs[i].style.display = 'table-row';
                show_count++;
              } else {
                trs[i].style.display = 'none';
                hide_count++;
              }
            }

            toastr.info('Now showing ' + show_count + ' and hiding ' + hide_count + ' locations');
          }, 500);
        }
    },
    mounted() {
      window.vueapp = this;
    },
}
</script>
