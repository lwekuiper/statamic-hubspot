import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import ContactPropertiesField from './components/fieldtypes/HubSpotContactPropertiesFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('hubspot-listing', Listing);
    Statamic.$components.register('hubspot-publish-form', PublishForm);
    Statamic.$components.register('hubspot_contact_properties-fieldtype', ContactPropertiesField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
});
