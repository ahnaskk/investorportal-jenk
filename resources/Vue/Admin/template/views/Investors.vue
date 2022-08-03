<template>
  <div class="wrapper">
    <formBase 
      :data="descriptors" 
      @submit="submitForm"
    />
  </div>
</template>

<script>
import formBase from "@ac/form/formBase.vue";
export default {
  name: "investors",
  components: {
    formBase,
  },
  data() {
    return {
      descriptors: {
        form: "investorForm",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "Name",
                label: "Name",
                type: "text",
                placeholder: "",
                required: false,
              },
              {
                name: "Investortype",
                label: "Investor type",
                type: "select",
                placeholder: "",
                required: false,
                options: [
                  { option: "Investor 1", value: "value 1" },
                  { option: "Investor 2", value: "value 2" },
                  { option: "Investor 3", value: "value 3" },
                ],
              },
              {
                name: "Name",
                label: "Management Fee (%)",
                type: "number",
                placeholder: "",
                required: false,
              },
            ],
          },
          {
            className: "dark",
            fields: [
              {
                name: "Name",
                label: "Syndication Fee (%)",
                type: "number",
                placeholder: "",
              },
              {
                type: "radio",
                placeholder: "",
                radioList: [{ "label": "Pre-paid Amount?","name":"radio1","value":"value1" }],
                componentClass:"dark"
              },
              {
                type: "radio",
                placeholder: "",
                radioList: [{ "label": "Pre-paid RTR?","name":"radio1","value":"value2" }],
                componentClass:"dark"
              },
            ],
          },
          {
            fields: [
              {
                name: "Name",
                label: "Syndication Fee (%)",
                type: "number",
                placeholder: "",
              },
              {
                name: "password",
                label:"Password",
                type: "password",
                placeholder: "",
              },
              {
                name: "password",
                label:"Password",
                type: "password",
                placeholder: "",
                confirm:true
              },
            ],
          },
          {
            fields: [
              {
                name: "boolean1",
                label: "Enable/Disable",
                type: "boolean",
                placeholder: "",
                value:"bool1",
                componentClass:"dark",
              },
              {
                label:"File type",
                type: "radio",
                placeholder: "",
                componentClass:"dark",
                radioList: [{ "label": "CSV","name":"radio2","value":"value1" },{ "label": "PDF","name":"radio2","value":"value2" }],
              },
              {
                name: "Name",
                label:"Email Notification",
                type: "checkbox",
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
            ],
          },
          {
            fields: [
              {
                name: "boolean2",
                value:"value2",
                label: "Enable/Disable",
                type: "boolean",
                placeholder: "",
                componentClass:"dark",
              },
              {
                name: "Name1",
                label: "Name",
                type: "text",
                placeholder: "",
                required: false,
              },
              {
                type: "button",
                buttonList: [{ "label": "View Investors","type":"button","":"","customClass":"btn green-bt" },{ "label": "Create","type":"submit","customClass":"btn blue-bt" }],
              },
            ],
          },             
        ],
      },
    };
  },
  methods:{
    submitForm(data){
      console.log(data)
    }
  }
};
</script>

<style>
  
</style>