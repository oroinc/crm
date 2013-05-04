Datagrid Frontend Listener
--------------------------

Datagrids at edit pages for user roles and user groups have frontend listeners to synchronize datagrid data
with edit form.

### Oro.User.GridListener

Oro.User.GridListener is the class responsible for synchronization of selected users in datagrid with form fields.
It reads data from datagrid checkboxes and sets it to form and to additional request parameters in datagrid.

This class is extended from abstract grid listener Oro.Datagrid.Listener.AbstractListener.


### Template

Template OroUserBundle:Include:listener.html.twig is used to create and initialize listener with specified parameters.

#### Template content

```
{% javascripts
    '@OroGridBundle/Resources/public/js/app/datagrid/listener/abstractlistener.js'
    '@OroUserBundle/Resources/public/js/gridlistener.js'
    filter='?yui_js'
    output='js/oro.user.listener.js'
%}
    <script src="{{ asset_url }}" type="text/javascript"></script>
{% endjavascripts %}

<script type="text/javascript">
    $(function() {
        var listener = new Oro.User.GridListener(_.extend({
            'datagridName': {{ datagridView.datagrid.name|json_encode|raw }},
            'dataField': 'id'
        }, {{ parameters|json_encode|raw }}));
    });
</script>
```

#### Example of usage

```
{% set listenerParameters = {
    'columnName': 'has_role',
    'selectors': {
        'included': '#roleAppendUsers',
        'excluded': '#roleRemoveUsers'
    }
} %}
{% include 'OroUserBundle:Include:listener.html.twig' with {'datagridView': datagrid, 'parameters': listenerParameters} %}
```
