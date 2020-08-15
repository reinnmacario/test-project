<template>
  <div class="container">
    <div class="row">
      <div class="col-md-5">
        <div class="mt-5 mb-3 card">
          <div class="card-body">
            <h5 class="card-title">Mail Merge Tool</h5>
            <form @submit.prevent="onSubmit" enctype="multipart/form-data">
              <hr />
              <div class="form-group">
                <label for="subject">Data table file</label>
                <input
                  @input="onSelectFile"
                  accept=".csv"
                  type="file"
                  class="form-control-file"
                  ref="file"
                  required
                />
              </div>
              <hr />
              <div class="mb-4">
                <h6>Message Templates</h6>
              </div>
              <div
                v-for="(template, index) in templates"
                v-bind:key="index"
                class="template-container"
              >
                <div class="row">
                  <div class="col-md-3 text-center" style="border-right: 1px solid black;">
                    <div class="template-info mt-5">
                      <h6>Template</h6>
                      <h5>{{index+1}}</h5>
                    </div>
                  </div>
                  <div class="col-md-9">
                    <div class="form-group">
                      <label for="subject">Subject</label>
                      <input
                        v-model="templates[index].subject"
                        type="text"
                        name="subject"
                        class="form-control"
                        id="subject"
                        required
                      />
                    </div>
                    <div class="form-group">
                      <label for="message">Message</label>
                      <textarea
                        v-model="templates[index].message"
                        class="form-control"
                        id="message"
                        rows="2"
                      ></textarea>
                    </div>
                    <div class="text-right">
                      <button
                        v-if="templates.length > 1"
                        @click="deleteTemplate"
                        type="button"
                        class="btn btn-danger btn-sm"
                      >Delete</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="text-right mt-3">
                <button @click="addTemplate" type="button" class="btn btn-sm btn-primary">Add</button>
              </div>
              <div class="text-right mt-3">
                <button type="submit" name="message" class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div v-if="modifiedTemplates.length > 0" class="mt-5 px-3 py-3 card">
          <div class="table-responsive">
            <table id="table-template" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Message</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(template, index) in modifiedTemplates" v-bind:key="index">
                  <td>{{ template.subject }}</td>
                  <td>{{ template.message }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      file: '',
      templates: [
        {
          subject: '',
          message: ''
        }
      ],
      modifiedTemplates: []
    }
  },
  methods: {
    onSubmit() {
      let formData = new FormData()
      formData.append('csv_file', this.file)
      formData.append('templates', JSON.stringify(this.templates))
      axios
        .post(`api/app/import-parse`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then((response) => {
          this.modifiedTemplates = response.data
        })
    },
    onSelectFile() {
      this.file = this.$refs.file.files[0]
    },
    addTemplate() {
      this.templates.push({
        subject: '',
        message: ''
      })
    },
    deleteTemplate(index) {
      this.templates.splice(index, 1)
    }
  }
}
</script>
