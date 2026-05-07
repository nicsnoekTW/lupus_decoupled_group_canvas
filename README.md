Bridge between lupus_decoupled and drupal groups allowing groups to have canvas content. POC only, no tests, some known issues.

Issue: Can not create a new template for a group in the editor. 
Workaround: Create a sync file, eg for a group type called ***national_org*** create the file `config/sync/canvas.content_template.group.national_org.full.yml`:

<pre>
langcode: en
status: true
dependencies:
  config:
    - core.entity_view_mode.group.full
    - group.type.national_org
  module:
    - group
id: group.national_org.full
content_entity_type_id: group
content_entity_type_bundle: national_org
content_entity_type_view_mode: full
component_tree: {  }
exposed_slots: {  }
</pre>

and import it using `drush cim`
