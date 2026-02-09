<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    value: { required: true },
    meta: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:value']);

const selected = ref(null);
const fields = ref([]);

const form = computed(() => props.meta.form ?? '');

watch(selected, (val) => {
    emit('update:value', val);
});

onMounted(() => {
    selected.value = props.value;
    refreshFields();
});

function refreshFields() {
    axios
        .get(cp_url(`/hubspot/form-fields/${form.value}`))
        .then(response => {
            fields.value = response.data;
        })
        .catch(() => { fields.value = []; });
}
</script>

<template>
    <div class="statamic-form-fields-fieldtype-wrapper">
        <ui-combobox
            class="w-full"
            v-model="selected"
            :options="fields"
            optionValue="id"
            optionLabel="label"
            :label="__('Choose...')"
            :clearable="true"
            :searchable="true"
        />
    </div>
</template>
