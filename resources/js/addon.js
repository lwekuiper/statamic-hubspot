import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import ContactPropertiesField from './components/fieldtypes/HubSpotContactPropertiesFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import Index from './pages/Index.vue';
import Empty from './pages/Empty.vue';
import Edit from './pages/Edit.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('hubspot::Index', Index);
    Statamic.$inertia.register('hubspot::Empty', Empty);
    Statamic.$inertia.register('hubspot::Edit', Edit);

    Statamic.$components.register('hubspot-listing', Listing);
    Statamic.$components.register('hubspot-publish-form', PublishForm);
    Statamic.$components.register('hubspot_contact_properties-fieldtype', ContactPropertiesField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
});
